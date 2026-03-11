<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use Spatie\Image\Image;
use Wotz\MediaLibrary\Conversions\LocalConversion;
use Wotz\MediaLibrary\Facades\Formats;
use Wotz\MediaLibrary\Models\Attachment;
use Wotz\MediaLibrary\Tests\TestFormats\TestHero;

uses(RefreshDatabase::class);

it('skips generation if attachment is not an image', function () {
    Formats::register([TestHero::class]);

    $attachment = createAttachment([
        'type' => 'not-an-image',
        'extension' => 'txt',
    ]);

    /** @var \Wotz\MediaLibrary\Conversions\Conversion $conversion */
    $conversion = app(LocalConversion::class);

    expect($conversion->convert($attachment, Formats::exists('test-hero')))
        ->toBeFalse();
});

it('skips generation if attachment is a gif', function () {
    Formats::register([TestHero::class]);

    $attachment = createAttachment([
        'type' => 'image',
        'extension' => 'gif',
    ]);

    /** @var \Wotz\MediaLibrary\Conversions\Conversion $conversion */
    $conversion = app(LocalConversion::class);

    expect($conversion->convert($attachment, Formats::exists('test-hero')))
        ->toBeFalse();
});

it('converts image to webp', function () {
    Storage::fake('public');

    $this->mock(Image::class, function (MockInterface $mock) {
        $mock->shouldReceive('manipulate')
            ->andReturn('sdf');
    });

    Formats::register([TestHero::class]);

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

    /** @var \Wotz\MediaLibrary\Formats\Format $format */
    $format = Formats::exists('test-hero');

    /** @var \Wotz\MediaLibrary\Conversions\Conversion $conversion */
    $conversion = app(LocalConversion::class);

    expect($conversion->convert($attachment, $format, true))
        ->toBeTrue();

    expect($attachment->getStorage()->path($attachment->directory . '/' . $format->filename($attachment)))
        ->toBeFile();
});
