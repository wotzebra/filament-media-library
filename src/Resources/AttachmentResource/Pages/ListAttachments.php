<?php

namespace Wotz\MediaLibrary\Resources\AttachmentResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Wotz\MediaLibrary\Filament\Actions\Tables\TableUploadAttachmentAction;
use Wotz\MediaLibrary\Models\AttachmentTag;
use Wotz\MediaLibrary\Resources\AttachmentResource;
use Wotz\MediaLibrary\Resources\AttachmentTagResource;

class ListAttachments extends ListRecords
{
    protected static string $resource = AttachmentResource::class;

    protected $listeners = [
        'filament-media-library::update-library' => '$refresh',
    ];

    public function getTitle(): string|Htmlable
    {
        return __('filament-media-library::attachment.dashboard navigation title');
    }

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->closeModalByClickingAway(false)
                ->label(__('filament-media-library::tags.create new attachment tag'))
                ->authorize(AttachmentTagResource::canCreate())
                ->model(AttachmentTag::class)
                ->outlined()
                ->schema(
                    AttachmentTagResource::form(\Filament\Schemas\Schema::make($this))
                        ->columns(2)
                        ->getComponents()
                ),

            TableUploadAttachmentAction::make('upload')
                ->multiple(),
        ];
    }

    public function table(Table $table): Table
    {
        return parent::table($table)->modifyQueryUsing(function (Builder $query, Table $table) {
            if ($table->getActiveFiltersCount() !== 0 || filled($table->getLivewire()->tableSearch)) {
                return;
            }

            $query->whereDoesntHave('tags', fn ($q) => $q->where('is_hidden', true));
        });
    }
}
