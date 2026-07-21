<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Wotz\MediaLibrary\Events\AttachmentReplaced;
use Wotz\MediaLibrary\Events\AttachmentReverted;
use Wotz\MediaLibrary\Exceptions\VersionDoesNotBelongToAttachment;
use Wotz\MediaLibrary\Exceptions\VersionFilesMissing;
use Wotz\MediaLibrary\Jobs\GenerateAttachmentFormat;
use Wotz\MediaLibrary\Models\Attachment;
use Wotz\MediaLibrary\Models\AttachmentVersion;

uses(RefreshDatabase::class);

function createStoredAttachment(array $data = []): Attachment
{
    /** @var Attachment $attachment */
    $attachment = createAttachment([
        'type' => 'image',
        'extension' => 'jpg',
        'name' => 'original',
        'disk' => 'public',
        ...$data,
    ]);

    $attachment->getStorage()->put(
        $attachment->file_path,
        File::get(__DIR__ . '/../../TestFiles/test.jpg')
    );

    return $attachment;
}

it('archives the current file and metadata as a version when replacing', function () {
    Queue::fake();
    Storage::fake('public');

    $attachment = createStoredAttachment();
    $originalMd5 = $attachment->md5;

    $attachment->replaceFile(temporaryUploadedFile('replacement.jpg', 120, 120));

    expect($attachment->version)->toBe(2)
        ->and($attachment->name)->toBe('replacement')
        ->and($attachment->extension)->toBe('jpg')
        ->and($attachment->width)->toBe(120)
        ->and($attachment->height)->toBe(120);

    $version = AttachmentVersion::sole();

    expect($version->version_number)->toBe(1)
        ->and($version->attachment_id)->toBe($attachment->id)
        ->and($version->name)->toBe('original')
        ->and($version->md5)->toBe($originalMd5);

    Storage::disk('public')->assertExists($attachment->getVersionDirectory(1) . '/original.jpg');
    Storage::disk('public')->assertExists($attachment->file_path);
});

it('regenerates the formats of the new file when replacing', function () {
    Queue::fake();
    Storage::fake('public');

    $attachment = createStoredAttachment();
    $attachment->formats()->create(['format' => 'stale-format', 'data' => ['x' => 1]]);

    $attachment->replaceFile(temporaryUploadedFile('replacement.jpg', 120, 120));

    expect($attachment->formats()->where('format', 'stale-format')->exists())->toBeFalse();

    Queue::assertPushed(GenerateAttachmentFormat::class);
});

it('exposes the new version on an already loaded versions relation', function () {
    Queue::fake();
    Storage::fake('public');

    $attachment = createStoredAttachment();

    expect($attachment->versions)->toHaveCount(0);

    $attachment->replaceFile(temporaryUploadedFile('replacement.jpg', 120, 120));

    expect($attachment->versions)->toHaveCount(1)
        ->and($attachment->versions->first()->name)->toBe('original');
});

it('dispatches AttachmentReplaced with the archived version', function () {
    Queue::fake();
    Storage::fake('public');
    Event::fake([AttachmentReplaced::class]);

    $attachment = createStoredAttachment();

    $attachment->replaceFile(temporaryUploadedFile('replacement.jpg', 120, 120));

    Event::assertDispatched(
        AttachmentReplaced::class,
        fn (AttachmentReplaced $event) => $event->attachment->is($attachment)
            && $event->previousVersion->version_number === 1
            && $event->previousVersion->name === 'original',
    );
});

it('restores the file and metadata when reverting', function () {
    Queue::fake();
    Storage::fake('public');

    $attachment = createStoredAttachment();
    $originalMd5 = $attachment->md5;

    $attachment->replaceFile(temporaryUploadedFile('replacement.jpg', 120, 120));

    $version = $attachment->versions()->sole();

    $attachment->revertToVersion($version);

    expect($attachment->name)->toBe('original')
        ->and($attachment->extension)->toBe('jpg')
        ->and($attachment->md5)->toBe($originalMd5)
        ->and($attachment->version)->toBe(3);

    Storage::disk('public')->assertExists($attachment->file_path);
    Storage::disk('public')->assertMissing($attachment->getVersionDirectory(1));

    expect(AttachmentVersion::find($version->id))->toBeNull();

    // The file that was just replaced becomes a version of its own, so the revert is reversible.
    expect($attachment->versions()->sole()->name)->toBe('replacement');
});

it('restores the format data that was archived with the version', function () {
    Queue::fake();
    Storage::fake('public');

    $attachment = createStoredAttachment();
    $attachment->formats()->create(['format' => 'manual-crop', 'data' => ['x' => 5, 'y' => 10]]);

    $attachment->replaceFile(temporaryUploadedFile('replacement.jpg', 120, 120));
    $attachment->revertToVersion($attachment->versions()->sole());

    $format = $attachment->formats()->sole();

    expect($format->format)->toBe('manual-crop')
        ->and($format->data)->toBe(['x' => 5, 'y' => 10]);
});

it('dispatches AttachmentReverted with the restored version', function () {
    Queue::fake();
    Storage::fake('public');

    $attachment = createStoredAttachment();
    $attachment->replaceFile(temporaryUploadedFile('replacement.jpg', 120, 120));

    Event::fake([AttachmentReverted::class]);

    $version = $attachment->versions()->sole();
    $attachment->revertToVersion($version);

    Event::assertDispatched(
        AttachmentReverted::class,
        fn (AttachmentReverted $event) => $event->attachment->is($attachment)
            && $event->revertedVersion->version_number === 1,
    );
});

it('prunes versions beyond the configured limit', function () {
    Queue::fake();
    Storage::fake('public');
    config()->set('filament-media-library.versioning.keep_versions', 2);

    $attachment = createStoredAttachment();

    foreach (['a', 'b', 'c', 'd'] as $name) {
        $attachment->replaceFile(temporaryUploadedFile("{$name}.jpg", 100, 100));
    }

    expect($attachment->versions()->pluck('version_number')->all())->toBe([4, 3]);

    Storage::disk('public')->assertMissing($attachment->getVersionDirectory(1));
    Storage::disk('public')->assertMissing($attachment->getVersionDirectory(2));
    Storage::disk('public')->assertExists($attachment->getVersionDirectory(3));
    Storage::disk('public')->assertExists($attachment->getVersionDirectory(4));
});

it('refuses to revert a version belonging to another attachment', function () {
    Queue::fake();
    Storage::fake('public');

    $attachment = createStoredAttachment();
    $other = createStoredAttachment();

    $other->replaceFile(temporaryUploadedFile('other.jpg', 100, 100));
    $foreignVersion = $other->versions()->sole();

    expect(fn () => $attachment->revertToVersion($foreignVersion))
        ->toThrow(VersionDoesNotBelongToAttachment::class);

    expect($attachment->fresh()->name)->toBe('original');
});

it('refuses to revert when the archived files are gone, leaving the current file alone', function () {
    Queue::fake();
    Storage::fake('public');

    $attachment = createStoredAttachment();
    $attachment->replaceFile(temporaryUploadedFile('replacement.jpg', 120, 120));

    $version = $attachment->versions()->sole();

    Storage::disk('public')->deleteDirectory($attachment->getVersionDirectory(1));

    expect(fn () => $attachment->revertToVersion($version))
        ->toThrow(VersionFilesMissing::class);

    Storage::disk('public')->assertExists($attachment->file_path);

    expect($attachment->fresh()->name)->toBe('replacement')
        ->and($attachment->fresh()->version)->toBe(2);
});

it('puts the original file back when replacing fails halfway', function () {
    Queue::fake();
    Storage::fake('public');

    $attachment = createStoredAttachment();
    $directory = $attachment->directory;

    Attachment::updated(function (): void {
        throw new RuntimeException('boom');
    });

    expect(fn () => $attachment->replaceFile(temporaryUploadedFile('replacement.jpg', 120, 120)))
        ->toThrow(RuntimeException::class);

    Storage::disk('public')->assertExists($directory . '/original.jpg');
    Storage::disk('public')->assertMissing($directory . '/replacement.jpg');
    Storage::disk('public')->assertMissing($directory . '/versions/1');

    expect(AttachmentVersion::count())->toBe(0)
        ->and($attachment->name)->toBe('original')
        ->and($attachment->fresh()->name)->toBe('original');
});

// The public disk is deliberately not faked here: faked disks report
// providesTemporaryUrls(), and those urls are never given a cache buster.
it('only adds a cache buster to the url once the file has been replaced', function () {
    $attachment = createAttachment([
        'name' => 'original',
        'extension' => 'jpg',
        'disk' => 'public',
    ]);

    expect($attachment->fresh()->url)->not->toContain('?v=');

    $attachment->forceFill(['version' => 2])->save();

    expect($attachment->fresh()->url)->toContain('?v=2');
});

it('leaves temporary urls untouched, since an unsigned parameter would break their signature', function () {
    Storage::fake('public');

    $attachment = createStoredAttachment();
    $attachment->forceFill(['version' => 2])->save();

    expect(Storage::disk('public')->providesTemporaryUrls())->toBeTrue()
        ->and($attachment->fresh()->url)->not->toContain('?v=');
});
