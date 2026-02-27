<?php

namespace Wotz\MediaLibrary\Models\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Wotz\MediaLibrary\Events\AttachmentReplaced;
use Wotz\MediaLibrary\Events\AttachmentReverted;
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
        $previousVersion = $this->createVersionSnapshot();

        $this->archiveCurrentFiles();

        $disk = $this->disk ?? 'public';
        $extension = $file->getClientOriginalExtension();
        $dimensions = is_image_with_dimensions($extension) ? $file->dimensions() : [];

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
                ->slug(),

            'version' => ($this->version ?? 1) + 1,
        ])->save();

        $file->storeAs($this->directory, $this->filename, ['disk' => $disk]);

        $this->formats()->delete();

        Formats::dispatchGeneration($this);

        GenerateAttachmentFormat::dispatchSync($this, Thumbnail::make());

        $this->pruneOldVersions();

        Event::dispatch(new AttachmentReplaced($this, $previousVersion));
    }

    public function revertToVersion(AttachmentVersion $version): void
    {
        $this->createVersionSnapshot();

        $this->archiveCurrentFiles();

        $storage = $this->getStorage();
        $versionDirectory = $this->getVersionDirectory($version->version_number);

        foreach ($storage->files($versionDirectory) as $file) {
            $storage->move($file, $this->directory . '/' . basename($file));
        }

        $storage->deleteDirectory($versionDirectory);

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
}
