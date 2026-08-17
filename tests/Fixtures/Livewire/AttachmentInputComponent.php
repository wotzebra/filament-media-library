<?php

namespace Wotz\MediaLibrary\Tests\Fixtures\Livewire;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Livewire\Component;
use Wotz\MediaLibrary\Filament\AttachmentInput;
use Wotz\MediaLibrary\Tests\Fixtures\TestFormats\TestBanner;
use Wotz\MediaLibrary\Tests\Fixtures\TestFormats\TestHero;

/**
 * Hosts a single AttachmentInput, so its hint and its upload modal can be rendered in tests.
 */
class AttachmentInputComponent extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    /** @var array<string, mixed> */
    public array $data = [];

    /** @var array<int, string>|null */
    public ?array $allowedFormats = [TestHero::class, TestBanner::class];

    public bool $multiple = false;

    public function mount(): void
    {
        $this->form->fill(['image' => $this->multiple ? [] : null]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                AttachmentInput::make('image')
                    ->multiple($this->multiple)
                    ->allowedFormats($this->allowedFormats),
            ])
            ->statePath('data');
    }

    public function render(): string
    {
        // The action modals live outside the schema, the same way a panel page renders them.
        return '<div>{{ $this->form }}<x-filament-actions::modals /></div>';
    }
}
