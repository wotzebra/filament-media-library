<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Wotz\MediaLibrary\Exceptions\InvalidConfiguration;
use Wotz\MediaLibrary\Models\Attachment;
use Wotz\MediaLibrary\Resources\AttachmentResource;
use Wotz\MediaLibrary\Support\Config;
use Wotz\MediaLibrary\Tests\Fixtures\TestModels\CustomRootAttachment;

uses(RefreshDatabase::class);

it('falls back to the packaged model', function () {
    config()->set('filament-media-library.model', null);

    expect(Config::attachmentModel())->toBe(Attachment::class)
        ->and(Config::attachmentModelInstance())->toBeInstanceOf(Attachment::class);
});

it('returns the configured model', function () {
    config()->set('filament-media-library.model', CustomRootAttachment::class);

    expect(Config::attachmentModel())->toBe(CustomRootAttachment::class)
        ->and(Config::attachmentModelInstance())->toBeInstanceOf(CustomRootAttachment::class);
});

it('rejects a model that is not an attachment', function () {
    config()->set('filament-media-library.model', stdClass::class);

    Config::attachmentModel();
})->throws(InvalidConfiguration::class);

it('queries through the configured model', function () {
    config()->set('filament-media-library.model', CustomRootAttachment::class);

    createAttachment(['id' => 1]);

    expect(Config::attachmentQuery()->find(1))
        ->toBeInstanceOf(CustomRootAttachment::class);
});

it('gives the resource the configured model', function () {
    config()->set('filament-media-library.model', CustomRootAttachment::class);

    expect(AttachmentResource::getModel())->toBe(CustomRootAttachment::class);
});

it('lets the configured model decide where files are stored', function () {
    config()->set('filament-media-library.model', CustomRootAttachment::class);

    createAttachment(['id' => 1]);

    expect(Config::attachmentQuery()->find(1)->directory)
        ->toBe('files/1');
});
