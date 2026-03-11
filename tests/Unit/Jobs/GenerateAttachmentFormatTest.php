<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use Wotz\MediaLibrary\Conversions\Conversion;
use Wotz\MediaLibrary\Jobs\GenerateAttachmentFormat;
use Wotz\MediaLibrary\Models\Attachment;
use Wotz\MediaLibrary\Tests\TestFormats\TestHero;

uses(RefreshDatabase::class);

it('dispatches conversions for all formats', function () {
    $format = new TestHero('test');

    /** @var Attachment $attachment */
    $attachment = createAttachment([
        'type' => 'image',
        'extension' => 'jpg',
        'disk' => 'public',
    ]);

    $this->mock(Conversion::class, function (MockInterface $mock) use (
        $format, $attachment
    ) {
        $mock->shouldReceive('convert')
            ->once()
            ->withArgs([$attachment, $format, false]);
    });

    $job = new GenerateAttachmentFormat($attachment, $format);
    $job->handle();
});

it('applies manual crops when regenerating formats', function () {
    Storage::fake('public');

    $format = new TestHero('test');

    /** @var Attachment $attachment */
    $attachment = createAttachment([
        'type' => 'image',
        'extension' => 'jpg',
        'disk' => 'public',
    ]);

    // Create actual image file for conversion to work
    $attachment->getStorage()->put(
        $attachment->file_path,
        File::get(__DIR__ . '/../../TestFiles/test.jpg')
    );

    // Create realistic crop coordinates like those saved by the formatter
    $formatRecord = $attachment->formats()->create([
        'format' => $format->key(),
        'data' => [
            'x' => 10,
            'y' => 20,
            'width' => 300,
            'height' => 200,
            'rotate' => 0,
            'scaleX' => 1,
            'scaleY' => 1,
        ],
    ]);

    // Make sure the relationship is working
    expect($attachment->formats()->count())->toBe(1);
    expect($attachment->formats()->where('format', $format->key())->first())->not()->toBeNull();
    expect($attachment->formats()->where('format', $format->key())->first()->data)->not()->toBeEmpty();

    // Use the real LocalConversion class to test our fix
    $conversion = new \Wotz\MediaLibrary\Conversions\LocalConversion;

    // The conversion should return true and process WITH the manual crop applied
    $result = $conversion->convert($attachment, $format, force: true);

    expect($result)->toBe(true);

    // The file should exist because conversion proceeded with manual crop
    expect($attachment->getStorage()->exists("$attachment->directory/{$format->filename($attachment)}"))->toBe(true);
});
