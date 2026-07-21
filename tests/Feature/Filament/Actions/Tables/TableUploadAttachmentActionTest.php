<?php

use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Livewire;
use Wotz\MediaLibrary\Models\Attachment;
use Wotz\MediaLibrary\Resources\AttachmentResource\Pages\ListAttachments;

it('can render the upload attachment action', function () {
    Livewire::test(ListAttachments::class)
        ->assertActionExists('attachment-upload');
});

it('can mount the upload attachment action and fill the form', function () {
    $file = TemporaryUploadedFile::fake()->image('avatar.png');

    Livewire::test(ListAttachments::class)
        ->mountAction('attachment-upload')
        ->goToNextWizardStep()
        ->assertHasFormErrors(['attachments'])
        ->fillForm([
            'attachments' => [$file],
        ])
        ->goToNextWizardStep()
        ->assertHasNoFormErrors(['attachments'])
        ->assertHasNoFormErrors()
        ->callMountedAction();

    expect(Attachment::first())
        ->name->toBe('avatar')
        ->extension->toBe('png')
        ->type->toBe('image');
});
