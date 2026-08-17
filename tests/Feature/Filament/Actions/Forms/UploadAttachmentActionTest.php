<?php

use Filament\Actions\Testing\TestAction;
use Filament\Schemas\Components\View;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Wotz\MediaLibrary\Tests\Fixtures\Livewire\AttachmentInputComponent;

function mountUploadAction(array $parameters = []): Testable
{
    return Livewire::test(AttachmentInputComponent::class, $parameters)
        ->mountAction(TestAction::make('attachment-upload')->schemaComponent('image'));
}

/**
 * Action modals are not part of the component render in tests, so the format block is
 * rendered straight from the schema of the mounted action.
 */
function uploadFormatsHtml(Testable $test): string
{
    $livewire = $test->instance();
    $schema = $livewire->getSchema($livewire->getMountedActionSchemaName());

    $block = collect($schema->getFlatComponents())
        ->first(fn ($component): bool => $component instanceof View
            && $component->getView() === 'filament-media-library::filament.upload-formats');

    return $block?->toHtml() ?? '';
}

it('lists the formats of the field before a file is uploaded', function () {
    $html = uploadFormatsHtml(mountUploadAction());

    expect($html)
        ->toContain('Formats for this field')
        ->toContain('Test Hero')
        ->toContain('Test Banner')
        ->toContain('2 formats')
        ->toContain('Upload at least 200 × 100 px to cover every format without upscaling.');
});

it('reports every format as covered for a large enough image', function () {
    $html = uploadFormatsHtml(
        mountUploadAction()->fillForm(['attachments' => [TemporaryUploadedFile::fake()->image('hero.png', 400, 400)]])
    );

    expect($html)
        ->toContain('all 2 covered')
        ->toContain('This image is large enough for all 2 formats.')
        ->not->toContain('upscaled');
});

it('warns that an undersized image gets upscaled', function () {
    $html = uploadFormatsHtml(
        mountUploadAction()->fillForm(['attachments' => [TemporaryUploadedFile::fake()->image('hero.png', 150, 150)]])
    );

    expect($html)
        ->toContain('1 will be upscaled')
        ->toContain('1 format will be upscaled from 150 px.');
});

it('shows the worst case per format when several files are uploaded', function () {
    $html = uploadFormatsHtml(
        mountUploadAction(['multiple' => true])->fillForm(['attachments' => [
            TemporaryUploadedFile::fake()->image('large.png', 400, 400),
            TemporaryUploadedFile::fake()->image('small.png', 120, 120),
        ]])
    );

    expect($html)->toContain('1 format will be upscaled from 120 px.');
});

it('reports a FileRule failure in the block as soon as the file is uploaded', function () {
    $test = mountUploadAction()
        ->fillForm(['attachments' => [TemporaryUploadedFile::fake()->image('huge.png', 5000, 3000)]]);

    // The block is advisory: submitting the step is what raises the actual error.
    $test->assertHasNoFormErrors();

    expect(uploadFormatsHtml($test))
        ->toContain('Formats for this field')
        ->toContain('File `huge.png` has the dimensions of 5000x3000 which is greater than the maximum allowed 4000x2400')
        ->toContain('border-danger-200')
        // A rejected file resolves no formats, so they are listed without a verdict.
        ->not->toContain('This image is large enough');
});

it('keeps reporting the failure after the step has been submitted', function () {
    $test = mountUploadAction()
        ->fillForm(['attachments' => [TemporaryUploadedFile::fake()->image('huge.png', 5000, 3000)]])
        ->goToNextWizardStep()
        ->assertHasFormErrors(['attachments']);

    expect(uploadFormatsHtml($test))->toContain('greater than the maximum allowed');
});

it('gives the format verdict back once an acceptable file replaces the failing one', function () {
    $html = uploadFormatsHtml(
        mountUploadAction()
            ->fillForm(['attachments' => [TemporaryUploadedFile::fake()->image('huge.png', 5000, 3000)]])
            ->fillForm(['attachments' => [TemporaryUploadedFile::fake()->image('hero.png', 400, 400)]])
    );

    expect($html)
        ->toContain('This image is large enough for all 2 formats.')
        ->not->toContain('greater than the maximum allowed');
});

it('leaves the block out for uploads without image dimensions', function () {
    $html = uploadFormatsHtml(
        mountUploadAction()->fillForm(['attachments' => [TemporaryUploadedFile::fake()->create('document.pdf', 10)]])
    );

    expect($html)->not->toContain('Formats for this field');
});

it('does not show the block when the field has no formats', function () {
    expect(uploadFormatsHtml(mountUploadAction(['allowedFormats' => []])))->toBe('');
});
