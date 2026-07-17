<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Wotz\MediaLibrary\Models\Attachment;
use Wotz\MediaLibrary\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

function createAttachment($data = [])
{
    return Attachment::withoutEvents(
        fn () => Attachment::factory($data)->create()
    );
}

/**
 * Builds a real Livewire TemporaryUploadedFile, as `replaceFile()` receives from a FileUpload field.
 */
function temporaryUploadedFile(string $name, ?int $width = null, ?int $height = null): TemporaryUploadedFile
{
    Storage::fake(FileUploadConfiguration::disk());

    $file = $width !== null && $height !== null
        ? UploadedFile::fake()->image($name, $width, $height)
        : UploadedFile::fake()->create($name);

    $hashName = TemporaryUploadedFile::generateHashNameWithOriginalNameEmbedded($file);

    Storage::disk(FileUploadConfiguration::disk())->putFileAs(
        FileUploadConfiguration::directory(),
        $file,
        $hashName,
    );

    return TemporaryUploadedFile::createFromLivewire($hashName);
}
