<?php

use Wotz\MediaLibrary\Conversions\LocalConversion;
use Wotz\MediaLibrary\Providers\MediaLibraryServiceProvider;
use Wotz\MediaLibrary\Tests\Fixtures\TestConversions\TestConversion;
use Wotz\MediaLibrary\Tests\Fixtures\TestFormats\TestHero;
use Illuminate\Support\Facades\Config;

it('returns default conversion', function () {
    $format = new TestHero('test');

    expect($format->conversion())
        ->toBeInstanceOf(LocalConversion::class);
});

it('returns defined conversion', function () {
    Config::set('filament-media-library.conversion', TestConversion::class);

    app(MediaLibraryServiceProvider::class, ['app' => app()])
        ->packageRegistered();

    $format = new TestHero('test');

    expect($format->conversion())
        ->toBeInstanceOf(TestConversion::class);
});
