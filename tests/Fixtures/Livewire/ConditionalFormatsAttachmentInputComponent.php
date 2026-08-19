<?php

namespace Wotz\MediaLibrary\Tests\Fixtures\Livewire;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Livewire\Component;
use Wotz\MediaLibrary\Filament\AttachmentInput;
use Wotz\MediaLibrary\Tests\Fixtures\TestFormats\TestBanner;
use Wotz\MediaLibrary\Tests\Fixtures\TestFormats\TestHero;

/**
 * Hosts an AttachmentInput whose allowed formats follow a sibling select, the way an
 * Architect block picks the rendered format from another field.
 */
class ConditionalFormatsAttachmentInputComponent extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    /** @var array<string, mixed> */
    public array $data = [];

    public function mount(): void
    {
        $this->form->fill(['orientation' => 'landscape', 'image' => null]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('orientation')
                    ->options(['landscape' => 'Landscape', 'portrait' => 'Portrait'])
                    ->live(),

                AttachmentInput::make('image')
                    ->allowedFormats(fn (Get $get): array => match ($get('orientation')) {
                        'portrait' => [TestHero::class],
                        default => [TestBanner::class],
                    }),
            ])
            ->statePath('data');
    }

    public function render(): string
    {
        return '<div>{{ $this->form }}<x-filament-actions::modals /></div>';
    }
}
