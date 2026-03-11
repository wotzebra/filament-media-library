<?php

use Wotz\MediaLibrary\Facades\Formats;
use Wotz\MediaLibrary\Formats\Format;
use Wotz\MediaLibrary\Tests\TestFormats\TestHero;
use Wotz\MediaLibrary\Tests\TestModels\TestModel;

it('registers model', function () {
    expect(Formats::register([TestHero::class]))
        ->toBeInstanceOf(\Wotz\MediaLibrary\Collections\Formats::class)
        ->toHaveKey(TestModel::class)
        ->first()
        ->first()
        ->toBeInstanceOf(Format::class);
});

it('returns format if format is registered', function () {
    Formats::register([TestHero::class]);

    expect(Formats::exists('test-hero'))
        ->toBeInstanceOf(TestHero::class);

    expect(Formats::exists('format-does-not-exist'))
        ->toBeNull();
});

it('returns null if format is not registered', function () {
    Formats::register([TestHero::class]);

    expect(Formats::exists('format-does-not-exist'))
        ->toBeNull();
});

it('returns collection with kebab keys', function () {
    Formats::register([TestHero::class]);

    expect(Formats::mapToKebab())
        ->toHaveKey('test-hero');
});
