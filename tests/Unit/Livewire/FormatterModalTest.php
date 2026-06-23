<?php

use Codedor\MediaLibrary\Livewire\FormatterModal;
use Codedor\MediaLibrary\Tests\TestFormats\TestHero;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->attachment = createAttachment([
        'type' => 'image',
        'extension' => 'jpg',
    ]);
});

it('passes the stored crop data of an attachment to the formatter view', function () {
    $this->attachment->formats()->create([
        'format' => TestHero::class,
        'data' => ['x' => 424242, 'y' => 56, 'width' => 100, 'height' => 100, 'rotate' => 0],
    ]);

    Livewire::test(FormatterModal::class, ['attachment' => $this->attachment])
        ->call('setAttachment', $this->attachment->id, [TestHero::class])
        ->assertStatus(200)
        ->assertSee('424242', false);
});

it('rebuilds the cropper when switching formats instead of calling the no-op setData', function () {
    Livewire::test(FormatterModal::class, ['attachment' => $this->attachment])
        ->call('setAttachment', $this->attachment->id, [TestHero::class])
        ->assertStatus(200)
        // The fixed setFormat() rebuilds the cropper via loadFormatter()...
        ->assertSee('this.loadFormatter()', false)
        // ...and no longer applies the previous format's selection through the
        // setData() call that is a no-op for formats without stored crop data.
        ->assertDontSee('setData(this.previousFormats', false);
});
