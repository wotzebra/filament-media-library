<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Wotz\MediaLibrary\Facades\Formats;
use Wotz\MediaLibrary\Tests\TestFormats\TestHero;
use Wotz\MediaLibrary\Tests\TestFormats\TestNoHeight;
use Wotz\MediaLibrary\Views\Picture;

uses(RefreshDatabase::class);

it('can render the picture component', function () {
    $this->component(Picture::class)
        ->assertSee('');
});

it('can render the picture component with placeholder', function () {
    Formats::register([TestHero::class]);

    $this->component(Picture::class, [
        'placeholder' => true,
        'format' => 'test-hero',
        'pictureClass' => 'test',
        'image' => createAttachment(),
    ])
        ->assertSee('https://via.placeholder.com/100x100/edeced/edeced.webp', false);
});

it('can render the picture component with an image and format', function () {
    Formats::register([TestHero::class]);

    $this->component(Picture::class, [
        'format' => 'test-hero',
        'pictureClass' => 'test',
        'image' => createAttachment(),
    ])
        ->assertSee('width="100"', false)
        ->assertSee('height="100"', false);
});

it('can render the picture component with an image and format but will not add height or width if one of both is missing on the format', function () {
    Formats::register([TestNoHeight::class]);

    $this->component(Picture::class, [
        'format' => 'test-no-height',
        'pictureClass' => 'test',
        'image' => createAttachment(),
    ])
        ->assertDontSee('width=', false)
        ->assertDontSee('height=', false);
});
