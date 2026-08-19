<?php

use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;
use Wotz\MediaLibrary\Filament\AttachmentInput;
use Wotz\MediaLibrary\Tests\Fixtures\Livewire\AttachmentInputComponent;
use Wotz\MediaLibrary\Tests\Fixtures\Livewire\ConditionalFormatsAttachmentInputComponent;
use Wotz\MediaLibrary\Tests\Fixtures\TestFormats\TestBanner;
use Wotz\MediaLibrary\Tests\Fixtures\TestFormats\TestHero;

it('hints at the minimum source size and the format count', function () {
    $field = AttachmentInput::make('image')
        ->allowedFormats([TestHero::class, TestBanner::class]);

    expect($field->getHint())->toBe('at least 200 × 100 px · 2 formats');
});

it('accepts a closure for the allowed formats', function () {
    $field = AttachmentInput::make('image')
        ->allowedFormats(fn (): array => [TestHero::class]);

    expect($field->getAllowedFormats())->toBe([TestHero::class])
        ->and($field->getHint())->toBe('100 × 100 px');
});

it('resolves closure formats against sibling fields', function () {
    Livewire::test(ConditionalFormatsAttachmentInputComponent::class)
        ->assertSee('200 × 50 px')
        ->set('data.orientation', 'portrait')
        ->assertSee('100 × 100 px')
        ->assertDontSee('200 × 50 px');
});

it('hints nothing when no formats apply', function (AttachmentInput $field) {
    expect($field->getHint())->toBeNull();
})->with([
    'disableFormatter()' => fn () => AttachmentInput::make('image')->disableFormatter(),
    'allowedFormats([])' => fn () => AttachmentInput::make('image')->allowedFormats([]),
]);

it('keeps a developer set hint', function () {
    $field = AttachmentInput::make('image')
        ->allowedFormats([TestHero::class])
        ->hint('Square images only');

    expect($field->getHint())->toBe('Square images only');
});

it('renders the hint on the field, next to the label', function () {
    Livewire::test(AttachmentInputComponent::class)
        ->assertSee('at least 200 × 100 px · 2 formats')
        // The class pulls the hint back towards the label, see plugin.css.
        ->assertSee('class="fi-fo-field attachment-input"', escape: false)
        ->assertActionExists(TestAction::make('formats')->schemaComponent('image'));
});

it('leaves the label row empty when the field has no formats', function () {
    Livewire::test(AttachmentInputComponent::class, ['allowedFormats' => []])
        ->assertDontSee('at least')
        ->assertDontSee("mountAction('formats'", escape: false);
});

it('opens a table of every format from the hint action', function () {
    $test = Livewire::test(AttachmentInputComponent::class)
        ->mountAction(TestAction::make('formats')->schemaComponent('image'));

    $action = $test->instance()->getMountedAction();

    expect((string) $action->getModalDescription())
        ->toBe('Upload at least 200 × 100 px to cover every format without upscaling.');

    expect((string) $action->getModalContent()?->render())
        ->toContain('Format')
        ->toContain('Used for')
        ->toContain('Dimensions')
        ->toContain('Test Hero')
        ->toContain('Test format')
        ->toContain('100 × 100 px')
        ->toContain('Test Banner')
        ->toContain('Wide banner')
        ->toContain('200 × 50 px');
});
