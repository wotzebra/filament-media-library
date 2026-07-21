<?php

use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Wotz\MediaLibrary\Resources\AttachmentResource\Pages\ListAttachments;
use Wotz\MediaLibrary\Support\FileUploadConfig;
use Wotz\MediaLibrary\Tests\Fixtures\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create());

    config()->set('filament-media-library.validation', [
        'max_file_size' => 5,
        'max_height' => 100,
        'max_width' => 100,
    ]);
});

it('cannot upload files with too big dimensions', function () {
    Livewire::test(ListAttachments::class)
        ->mountAction('attachment-upload')
        ->assertSee('Upload')
        ->setActionData([
            'attachments' => [
                UploadedFile::fake()->image('multiple-file1.jpg', 200, 200),
            ],
        ])
        ->goToNextWizardStep()
        ->assertHasActionErrors([
            'attachments' => 'File `multiple-file1.jpg` has the dimensions of 200x200 which is greater than the maximum allowed 100x100',
        ]);
});

it('cannot upload files with too big width', function () {
    Livewire::test(ListAttachments::class)
        ->mountAction('attachment-upload')
        ->assertSee('Upload')
        ->setActionData([
            'attachments' => [
                UploadedFile::fake()->image('multiple-file1.jpg', 200, 99),
            ],
        ])
        ->goToNextWizardStep()
        ->assertHasActionErrors([
            'attachments' => 'File `multiple-file1.jpg` has the dimensions of 200x99 which is greater than the maximum allowed 100x100',
        ]);
});

it('cannot upload files with too big height', function () {
    Livewire::test(ListAttachments::class)
        ->mountAction('attachment-upload')
        ->assertSee('Upload')
        ->setActionData([
            'attachments' => [
                UploadedFile::fake()->image('multiple-file1.jpg', 99, 200),
            ],
        ])
        ->goToNextWizardStep()
        ->assertHasActionErrors([
            'attachments' => 'File `multiple-file1.jpg` has the dimensions of 99x200 which is greater than the maximum allowed 100x100',
        ]);
});

it('cannot upload file with correct dimensions', function () {
    Livewire::test(ListAttachments::class)
        ->mountAction('attachment-upload')
        ->assertSee('Upload')
        ->setActionData([
            'attachments' => [
                UploadedFile::fake()->image('multiple-file1.jpg', 100, 100),
            ],
        ])
        ->goToNextWizardStep()
        ->assertHasNoActionErrors([
            'attachments',
        ]);
});

it('cannot upload image that is too large', function () {
    Livewire::test(ListAttachments::class)
        ->mountAction('attachment-upload')
        ->assertSee('Upload')
        ->setActionData([
            'attachments' => [
                UploadedFile::fake()->image('multiple-file1.jpg')->size(6),
            ],
        ])
        ->goToNextWizardStep()
        ->assertHasActionErrors([
            'attachments' => 'File `multiple-file1.jpg` has a size of 6 KB which is greater than the maximum allowed 5 KB',
        ]);
});

it('can upload image that is not larger than max file size', function () {
    Livewire::test(ListAttachments::class)
        ->mountAction('attachment-upload')
        ->assertSee('Upload')
        ->setActionData([
            'attachments' => [
                UploadedFile::fake()->image('multiple-file1.jpg')->size(4),
            ],
        ])
        ->goToNextWizardStep()
        ->assertHasNoActionErrors([
            'attachments',
        ]);
});

it('cannot upload pdf that is too large', function () {
    $this->mock(FileUploadConfig::class)
        ->shouldReceive('getMaxFilesize')
        ->andReturn(5 * 1024); // 5 KB

    Livewire::test(ListAttachments::class)
        ->mountAction('attachment-upload')
        ->assertSee('Upload')
        ->setActionData([
            'attachments' => [
                UploadedFile::fake()->create('multiple-file1.pdf', 6), // 6 KB
            ],
        ])
        ->goToNextWizardStep()
        ->assertHasActionErrors([
            'attachments' => 'File `multiple-file1.pdf` has a size of 6 KB which is greater than the maximum allowed 5 KB',
        ]);
});

it('can upload pdf that is not larger than max file size', function () {
    $this->mock(FileUploadConfig::class)
        ->shouldReceive('getMaxFilesize')
        ->andReturn(5 * 1024); // 5 KB

    Livewire::test(ListAttachments::class)
        ->mountAction('attachment-upload')
        ->assertSee('Upload')
        ->setActionData([
            'attachments' => [
                UploadedFile::fake()->create('multiple-file1.pdf', 4),
            ],
        ])
        ->goToNextWizardStep()
        ->assertHasNoActionErrors([
            'attachments',
        ]);
});

it('cannot upload image that has no valid extension', function () {
    Livewire::test(ListAttachments::class)
        ->mountAction('attachment-upload')
        ->assertSee('Upload')
        ->setActionData([
            'attachments' => [
                UploadedFile::fake()->image('multiple-file1.xyz'),
            ],
        ])
        ->goToNextWizardStep()
        ->assertHasActionErrors([
            'attachments' => 'File `multiple-file1.xyz` has a not allowed extension of xyz',
        ]);
});

it('can upload image that has valid extension', function () {
    Livewire::test(ListAttachments::class)
        ->mountAction('attachment-upload')
        ->assertSee('Upload')
        ->setActionData([
            'attachments' => [
                UploadedFile::fake()->image('multiple-file1.jpg')->size(4),
            ],
        ])
        ->goToNextWizardStep()
        ->assertHasNoActionErrors([
            'attachments',
        ]);
});

it('cannot upload image with wrong color type', function () {
    Livewire::test(ListAttachments::class)
        ->mountAction('attachment-upload')
        ->assertSee('Upload')
        ->setActionData([
            'attachments' => [
                UploadedFile::fake()->createWithContent('cmyk.jpeg', file_get_contents(__DIR__ . '/../../../../Fixtures/images/cmyk.jpeg')),
            ],
        ])
        ->goToNextWizardStep()
        ->assertHasActionErrors([
            'attachments' => 'Image `cmyk.jpeg` must be RGB and not CMYK',
        ]);
});

it('can upload image that is rgb', function () {
    Livewire::test(ListAttachments::class)
        ->mountAction('attachment-upload')
        ->assertSee('Upload')
        ->setActionData([
            'attachments' => [
                UploadedFile::fake()->image('rgb.jpeg'),
            ],
        ])
        ->goToNextWizardStep()
        ->assertHasNoActionErrors([
            'attachments',
        ]);
});
