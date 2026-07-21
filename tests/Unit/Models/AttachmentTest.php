<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Wotz\MediaLibrary\Facades\Formats;
use Wotz\MediaLibrary\Models\Attachment;
use Wotz\MediaLibrary\Tests\TestFormats\TestHero;

uses(RefreshDatabase::class);

it('deletes root directory when attachment is removed from db', function () {
    Storage::fake('public');

    /** @var Attachment $attachment */
    $attachment = createAttachment([
        'type' => 'image',
        'extension' => 'jpg',
        'disk' => 'public',
    ]);

    $attachment->getStorage()->put(
        $attachment->file_path,
        File::get(__DIR__ . '/../../TestFiles/test.jpg')
    );

    $attachment->delete();

    Storage::assertMissing($attachment->directory);
});

it('returns the right url for a format', function () {
    Formats::register([TestHero::class]);

    /** @var Attachment $attachment */
    $attachment = createAttachment([
        'type' => 'image',
        'extension' => 'jpg',
        'disk' => 'public',
        'name' => 'filename',
    ]);

    expect($attachment->getFormat('test-hero'))
        ->toEndWith('test_hero__filename.webp');
});

it('returns null for a format that does not exist', function () {
    /** @var Attachment $attachment */
    $attachment = createAttachment([
        'type' => 'image',
        'extension' => 'jpg',
        'disk' => 'public',
    ]);

    expect($attachment->getFormat('format-does-not-exist'))
        ->toBeNull();
});

it('returns the original image when unknown format is requested', function () {
    /** @var Attachment $attachment */
    $attachment = createAttachment([
        'type' => 'image',
        'extension' => 'jpg',
        'disk' => 'public',
    ]);

    expect($attachment->getFormatOrOriginal('test-notfound'))
        ->toEndWith("$attachment->filename");
});
