<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Livewire;
use Wotz\MediaLibrary\Models\Attachment;
use Wotz\MediaLibrary\Resources\AttachmentResource\Pages\EditAttachment;

uses(RefreshDatabase::class);

function editableAttachment(): Attachment
{
    /** @var Attachment $attachment */
    $attachment = createAttachment([
        'type' => 'image',
        'extension' => 'jpg',
        'name' => 'original',
        'disk' => 'public',
    ]);

    $attachment->getStorage()->put(
        $attachment->file_path,
        File::get(__DIR__ . '/../../../TestFiles/test.jpg')
    );

    return $attachment;
}

it('renders the versioning actions on the attachment edit page', function () {
    Storage::fake('public');

    $attachment = editableAttachment();

    Livewire::test(EditAttachment::class, ['record' => $attachment->getKey()])
        ->assertActionExists('replaceFile')
        ->assertActionExists('no_versions');
});

it('replaces the file through the replace action', function () {
    Queue::fake();
    Storage::fake('public');

    $attachment = editableAttachment();

    Livewire::test(EditAttachment::class, ['record' => $attachment->getKey()])
        ->mountAction('replaceFile')
        ->fillForm(['file' => TemporaryUploadedFile::fake()->image('replacement.png', 120, 120)])
        ->callMountedAction()
        ->assertHasNoFormErrors();

    expect($attachment->fresh())
        ->name->toBe('replacement')
        ->extension->toBe('png')
        ->version->toBe(2);

    Storage::disk('public')->assertExists($attachment->fresh()->file_path);
});

it('rejects a replacement with an unsupported extension', function () {
    Queue::fake();
    Storage::fake('public');

    $attachment = editableAttachment();

    Livewire::test(EditAttachment::class, ['record' => $attachment->getKey()])
        ->mountAction('replaceFile')
        ->fillForm(['file' => TemporaryUploadedFile::fake()->create('malware.exe')])
        ->callMountedAction()
        ->assertHasFormErrors(['file']);

    expect($attachment->fresh())
        ->name->toBe('original')
        ->extension->toBe('jpg');
});

it('lists a revertable version once the file has been replaced', function () {
    Queue::fake();
    Storage::fake('public');

    $attachment = editableAttachment();
    $attachment->replaceFile(temporaryUploadedFile('replacement.jpg', 120, 120));

    Livewire::test(EditAttachment::class, ['record' => $attachment->getKey()])
        ->assertActionExists('revert_v1')
        ->callAction('revert_v1');

    expect($attachment->fresh())
        ->name->toBe('original')
        ->version->toBe(3);
});
