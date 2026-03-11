<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Wotz\MediaLibrary\Jobs\GenerateAttachmentFormat;
use Wotz\MediaLibrary\Models\Attachment;
use Wotz\MediaLibrary\Tests\TestFormats\TestHero;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

it('dispatches format generation', function () {
    Queue::fake();
    Storage::fake('public');

    \Wotz\MediaLibrary\Facades\Formats::register([
        TestHero::class,
    ]);

    $file = UploadedFile::fake()->image('test.jpg', 100, 100);
    $file->save();

    /** @var Attachment $attachment */
    $attachment = Attachment::first();

    Queue::assertPushed(GenerateAttachmentFormat::class, function ($job) use ($attachment) {
        return $job->attachment->id === $attachment->id && class_basename($job->format) === 'TestHero';
    });
});

it('can save an image on default public disk', function () {
    Queue::fake();
    Storage::fake('public');

    \Wotz\MediaLibrary\Facades\Formats::register([
        TestHero::class,
    ]);

    assertDatabaseCount(Attachment::class, 0);

    $file = UploadedFile::fake()->image('test.jpg', 100, 100);
    $file->save();

    assertDatabaseCount(Attachment::class, 1);

    assertDatabaseHas(Attachment::class, [
        'name' => 'test',
        'extension' => 'jpg',
        'mime_type' => 'image/jpeg',
        'md5' => md5_file($file->path()),
        'type' => 'image',
        'size' => $file->getSize(),
        'width' => 100,
        'height' => 100,
        'disk' => 'public',
    ]);

    /** @var Attachment $attachment */
    $attachment = Attachment::first();

    Storage::disk('public')->assertExists($attachment->directory);
    Queue::assertPushed(GenerateAttachmentFormat::class);
});

it('can save an image on default other disk', function () {
    Queue::fake();
    $disk = 'local';

    \Wotz\MediaLibrary\Facades\Formats::register([
        TestHero::class,
    ]);

    Storage::fake($disk);

    assertDatabaseCount(Attachment::class, 0);

    $file = UploadedFile::fake()->image('test.jpg', 100, 100);
    $file->save($disk);

    assertDatabaseCount(Attachment::class, 1);

    assertDatabaseHas(Attachment::class, [
        'name' => 'test',
        'extension' => 'jpg',
        'mime_type' => 'image/jpeg',
        'md5' => md5_file($file->path()),
        'type' => 'image',
        'size' => $file->getSize(),
        'width' => 100,
        'height' => 100,
        'disk' => $disk,
    ]);

    /** @var Attachment $attachment */
    $attachment = Attachment::first();

    Storage::disk($disk)->assertExists($attachment->directory);
    Queue::assertPushed(GenerateAttachmentFormat::class);
});

it('does a check if file is an image', function () {
    $file = UploadedFile::fake()->image('test.jpg', 100, 100);
    expect($file->isImage())->toBeTrue();

    $file = UploadedFile::fake()->create(
        'file.pdf',
        100,
        'application/pdf'
    );
    expect($file->isImage())->toBeFalse();
});

it('returns right file type', function ($type, $extension) {
    $file = UploadedFile::fake()->create("test.$extension");

    expect($file->fileType())
        ->toBe($type);
})->with('filetypes');
