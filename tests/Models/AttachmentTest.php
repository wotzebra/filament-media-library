<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Wotz\MediaLibrary\Models\Attachment;

uses(RefreshDatabase::class);

it('returns the directory', function () {
    /** @var Attachment $attachment */
    $attachment = createAttachment([
        'id' => 1,
    ]);

    expect($attachment->directory)
        ->toBe('attachments/1');
});

it('returns the filename', function () {
    /** @var Attachment $attachment */
    $attachment = createAttachment([
        'name' => 'test-file',
    ]);

    expect($attachment->filename)
        ->toBe("test-file.$attachment->extension");
});
