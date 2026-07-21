<?php

namespace Wotz\MediaLibrary\Models\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Throwable;
use Wotz\MediaLibrary\Events\AttachmentReplaced;
use Wotz\MediaLibrary\Events\AttachmentReverted;
use Wotz\MediaLibrary\Exceptions\VersionDoesNotBelongToAttachment;
use Wotz\MediaLibrary\Exceptions\VersionFilesMissing;
use Wotz\MediaLibrary\Facades\Formats;
use Wotz\MediaLibrary\Formats\Thumbnail;
use Wotz\MediaLibrary\Jobs\GenerateAttachmentFormat;
use Wotz\MediaLibrary\Models\AttachmentFormat;
use Wotz\MediaLibrary\Models\AttachmentVersion;

trait HasVersions
{
    public function versions(): HasMany
    {
        return $this->hasMany(AttachmentVersion::class)->orderByDesc('version_number');
    }

    public function replaceFile(TemporaryUploadedFile $file): void
    {
        $originalAttributes = $this->getAttributes();
        $archiveDirectory = $this->getVersionDirectory($this->version ?? 1);

        $disk = $this->disk ?? 'public';
        $extension = $file->getClientOriginalExtension();
        $dimensions = is_image_with_dimensions($extension) ? $file->dimensions() : [];

        try {
            $previousVersion = DB::transaction(function () use ($file, $disk, $extension, $dimensions): AttachmentVersion {
                $previousVersion = $this->createVersionSnapshot();

                $this->archiveCurrentFiles();

                $this->forceFill([
                    'extension' => $extension,
                    'mime_type' => $file->getMimeType(),
                    'md5' => $file->getMd5(),
                    'type' => $file->fileType(),
                    'size' => $file->getSize(),
                    'width' => $dimensions[0] ?? null,
                    'height' => $dimensions[1] ?? null,
                    'disk' => $disk,
                    'name' => Str::of($file->getClientOriginalName())
                        ->replace('.' . $extension, '')
                        ->slug()
                        ->toString(),

                    'version' => ($this->version ?? 1) + 1,
                ])->save();

                $file->storeAs($this->directory, $this->filename, ['disk' => $disk]);

                $this->formats()->delete();

                return $previousVersion;
            });
        } catch (Throwable $exception) {
            $this->rollbackToArchivedFiles($originalAttributes, $archiveDirectory);

            throw $exception;
        }

        $this->unsetRelation('formats');

        // Dispatched after commit so queued workers never pick up a job for a file that was rolled back.
        Formats::dispatchGeneration($this);

        GenerateAttachmentFormat::dispatchSync($this, Thumbnail::make());

        $this->pruneOldVersions();

        Event::dispatch(new AttachmentReplaced($this, $previousVersion));
    }

    public function revertToVersion(AttachmentVersion $version): void
    {
        if ($version->attachment_id !== $this->getKey()) {
            throw VersionDoesNotBelongToAttachment::make($version, $this);
        }

        $storage = $this->getStorage();
        $versionDirectory = $this->getVersionDirectory($version->version_number);

        $versionedFiles = $storage->directoryExists($versionDirectory)
            ? $storage->files($versionDirectory)
            : [];

        if ($versionedFiles === []) {
            throw VersionFilesMissing::make($version, $versionDirectory);
        }

        $originalAttributes = $this->getAttributes();
        $archiveDirectory = $this->getVersionDirectory($this->version ?? 1);

        try {
            DB::transaction(function () use ($version, $storage, $versionedFiles): void {
                $this->createVersionSnapshot();

                $this->archiveCurrentFiles();

                // Copied rather than moved, so the version directory survives a rollback.
                foreach ($versionedFiles as $file) {
                    $storage->copy($file, $this->directory . '/' . basename($file));
                }

                $this->formats()->delete();

                foreach ($version->format_data ?? [] as $formatSnapshot) {
                    $this->formats()->create([
                        'format' => $formatSnapshot['format'],
                        'data' => $formatSnapshot['data'],
                    ]);
                }

                $this->forceFill([
                    'name' => $version->name,
                    'extension' => $version->extension,
                    'mime_type' => $version->mime_type,
                    'md5' => $version->md5,
                    'type' => $version->type,
                    'size' => $version->size,
                    'width' => $version->width,
                    'height' => $version->height,
                    'disk' => $version->disk,
                    'version' => ($this->version ?? 1) + 1,
                ])->save();

                $version->delete();
            });
        } catch (Throwable $exception) {
            $this->rollbackToArchivedFiles($originalAttributes, $archiveDirectory);

            throw $exception;
        }

        $storage->deleteDirectory($versionDirectory);

        $this->unsetRelation('formats');

        $this->pruneOldVersions();

        Event::dispatch(new AttachmentReverted($this, $version));
    }

    public function pruneOldVersions(): void
    {
        $keep = config('filament-media-library.versioning.keep_versions', 5);

        $storage = $this->getStorage();

        $keepIds = $this->versions()->limit($keep)->pluck('id');

        $this->versions()->whereNotIn('id', $keepIds)->get()->each(function (AttachmentVersion $version) use ($storage): void {
            $directory = $this->getVersionDirectory($version->version_number);

            if ($storage->directoryExists($directory)) {
                $storage->deleteDirectory($directory);
            }

            $version->delete();
        });

        // Both replacing and reverting end here after adding and removing rows, so this is
        // the one place that has to drop the stale relation an already-loaded caller holds.
        $this->unsetRelation('versions');
    }

    public function getVersionDirectory(int $versionNumber): string
    {
        return "{$this->directory}/versions/{$versionNumber}";
    }

    protected function createVersionSnapshot(): AttachmentVersion
    {
        return AttachmentVersion::create([
            'attachment_id' => $this->getKey(),
            'version_number' => $this->version ?? 1,
            'name' => $this->name,
            'extension' => $this->extension,
            'mime_type' => $this->mime_type,
            'md5' => $this->md5,
            'type' => $this->type,
            'size' => $this->size,
            'width' => $this->width,
            'height' => $this->height,
            'disk' => $this->disk,
            'format_data' => $this->formats->map(fn (AttachmentFormat $format) => [
                'format' => $format->format,
                'data' => $format->data,
            ])->all(),
            'replaced_by_user_id' => auth()->id(),
            'replaced_at' => now(),
        ]);
    }

    protected function archiveCurrentFiles(): void
    {
        $storage = $this->getStorage();
        $versionDirectory = $this->getVersionDirectory($this->version ?? 1);

        $storage->makeDirectory($versionDirectory);

        foreach ($storage->files($this->directory) as $file) {
            $storage->move($file, $versionDirectory . '/' . basename($file));
        }
    }

    /**
     * @param  array<string, mixed>  $originalAttributes
     */
    protected function rollbackToArchivedFiles(array $originalAttributes, string $archiveDirectory): void
    {
        $this->setRawAttributes($originalAttributes, sync: true);
        $this->unsetRelation('formats');

        $storage = $this->getStorage();

        if (! $storage->directoryExists($archiveDirectory)) {
            return;
        }

        foreach ($storage->files($this->directory) as $file) {
            $storage->delete($file);
        }

        foreach ($storage->files($archiveDirectory) as $file) {
            $storage->move($file, $this->directory . '/' . basename($file));
        }

        $storage->deleteDirectory($archiveDirectory);
    }
}
