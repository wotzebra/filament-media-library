<?php

use Wotz\MediaLibrary\Support\FormatSummary;
use Wotz\MediaLibrary\Tests\Fixtures\TestFormats\TestBanner;
use Wotz\MediaLibrary\Tests\Fixtures\TestFormats\TestHero;
use Wotz\MediaLibrary\Tests\Fixtures\TestFormats\TestNoDescription;
use Wotz\MediaLibrary\Tests\Fixtures\TestFormats\TestNoDimensions;
use Wotz\MediaLibrary\Tests\Fixtures\TestFormats\TestNoHeight;

it('is empty without formats', function (?array $formats) {
    $summary = FormatSummary::make($formats);

    expect($summary->isEmpty())->toBeTrue()
        ->and($summary->count())->toBe(0)
        ->and($summary->requiredDimensions())->toBeNull()
        ->and($summary->hint())->toBeNull()
        ->and($summary->formats())->toBe([]);
})->with([
    'null' => null,
    'empty array' => [[]],
    'formats without dimensions' => [[TestNoDimensions::class]],
]);

it('describes a single format without a minimum', function () {
    $summary = FormatSummary::make([TestHero::class]);

    expect($summary->count())->toBe(1)
        ->and($summary->requiredWidth())->toBe(100)
        ->and($summary->requiredHeight())->toBe(100)
        ->and($summary->hint())->toBe('100 × 100 px');
});

it('takes the largest width and height across formats', function () {
    $summary = FormatSummary::make([TestHero::class, TestBanner::class]);

    expect($summary->requiredWidth())->toBe(200)
        ->and($summary->requiredHeight())->toBe(100)
        ->and($summary->hint())->toBe('at least 200 × 100 px · 2 formats');
});

it('does not raise the required height for width only formats', function () {
    $summary = FormatSummary::make([TestNoHeight::class]);

    expect($summary->requiredWidth())->toBe(100)
        ->and($summary->requiredHeight())->toBeNull()
        ->and($summary->requiredDimensions())->toBe('100px wide')
        ->and($summary->hint())->toBe('100px wide');
});

it('accepts format instances as well as class names', function () {
    expect(FormatSummary::make([TestHero::make()])->hint())
        ->toBe(FormatSummary::make([TestHero::class])->hint());
});

it('lists name, description and dimensions per format', function () {
    $formats = FormatSummary::make([TestHero::class, TestNoHeight::class])->formats();

    expect($formats)->toBe([
        ['name' => 'Test Hero', 'description' => 'Test format', 'width' => 100, 'height' => 100, 'dimensions' => '100 × 100 px'],
        ['name' => 'Test No Height', 'description' => 'Test format', 'width' => 100, 'height' => null, 'dimensions' => '100px wide'],
    ]);
});

it('leaves out the description of a format that has none', function () {
    $formats = FormatSummary::make([TestNoDescription::class])->formats();

    expect($formats[0]['description'])->toBeNull()
        ->and($formats[0]['dimensions'])->toBe('300 × 200 px');
});

it('marks formats larger than the source as upscaled', function () {
    $results = FormatSummary::make([TestHero::class, TestBanner::class])->results(150, 150);

    expect($results)->toHaveCount(2)
        ->and($results[0]['name'])->toBe('Test Hero')
        ->and($results[0]['isUpscaled'])->toBeFalse()
        ->and($results[1]['name'])->toBe('Test Banner')
        ->and($results[1]['isUpscaled'])->toBeTrue();
});

it('marks a format as upscaled when only the height falls short', function () {
    $results = FormatSummary::make([TestHero::class])->results(500, 50);

    expect($results[0]['isUpscaled'])->toBeTrue();
});

it('ignores the height of width only formats when resolving a source', function () {
    $results = FormatSummary::make([TestNoHeight::class])->results(100, 1);

    expect($results[0]['isUpscaled'])->toBeFalse();
});

it('marks nothing as upscaled without a source', function () {
    $results = FormatSummary::make([TestHero::class, TestBanner::class])->results(null, null);

    expect(collect($results)->pluck('isUpscaled')->all())->toBe([false, false]);
});
