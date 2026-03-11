<?php

namespace Wotz\MediaLibrary\Filament;

use Closure;
use Filament\Forms\Components\Field;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Livewire\Component;
use Wotz\MediaLibrary\Facades\Formats;
use Wotz\MediaLibrary\Filament\Actions\Forms\UploadAttachmentAction;
use Wotz\MediaLibrary\Formats\Format;
use Wotz\MediaLibrary\Models\Attachment;
use Wotz\MediaLibrary\Models\AttachmentTag;
use Wotz\MediaLibrary\Resources\AttachmentResource;

class AttachmentInput extends Field
{
    protected string $view = 'filament-media-library::filament.attachment-input';

    protected bool|Closure $multiple = false;

    protected null|string|Closure $sortField = null;

    protected ?array $allowedFormats = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->saveRelationshipsUsing(static function (self $component, $state) {
            if (! $component->isMultiple()) {
                return;
            }

            // If the state is null, it means that the field has not been touched (and the state was never set)
            // So we don't want to sync the relationship, because it would remove all the existing attachments
            if ($state === null) {
                return;
            }

            $state = Collection::wrap($state);
            $sortField = $component->getSortField();

            if (is_string($sortField)) {
                $state = $state->mapWithKeys(function ($item, $index) use ($sortField) {
                    return [$item => [$sortField => $index + 10000]];
                });
            }

            $component->getRelationship()->detach();
            $component->getRelationship()->sync($state->toArray());
        });

        $this->dehydrateStateUsing(function (self $component, $state) {
            if ($component->isMultiple()) {
                return;
            }

            if (is_string($state)) {
                return $state;
            }

            return $state[0] ?? null;
        });

        $this->registerActions([
            \Filament\Actions\Action::make('remove-attachment')
                ->closeModalByClickingAway(false)
                ->icon('heroicon-o-x-circle')
                ->iconButton()
                ->color('danger')
                ->size('sm')
                ->action(function (\Filament\Schemas\Components\Utilities\Set $set, array $arguments, $state) {
                    if ($this->isMultiple()) {
                        $state = collect($state)
                            ->reject(fn ($id) => $id === $arguments['attachmentId'])
                            ->values();

                        $set($this->getStatePath(false), $state->toArray());
                    } else {
                        $set($this->getStatePath(false), null);
                    }
                }),

            \Filament\Actions\Action::make('format-attachment')
                ->closeModalByClickingAway(false)
                ->icon('heroicon-o-scissors')
                ->iconButton()
                ->color('gray')
                ->size('sm')
                ->hidden(fn () => count($this->getAllowedFormats()) === 0)
                ->action(function (array $arguments, Component $livewire) {
                    $livewire->dispatch(
                        'filament-media-library::open-formatter-attachment-modal',
                        $arguments['attachmentId'],
                        $this->getAllowedFormats(),
                    );

                    $livewire->dispatch(
                        'open-modal',
                        id: 'filament-media-library::formatter-attachment-modal',
                    );
                }),

            \Filament\Actions\Action::make('edit-attachment')
                ->closeModalByClickingAway(false)
                ->icon('heroicon-s-pencil')
                ->iconButton()
                ->color('gray')
                ->size('sm')
                ->url(function (array $arguments): string {
                    return AttachmentResource::getUrl('edit', ['record' => $arguments['attachmentId']]);
                }, true),

            UploadAttachmentAction::make('attachment-upload')
                ->closeModalByClickingAway(false)
                ->multiple(fn () => $this->isMultiple()),

            \Filament\Actions\Action::make('attachment-picker')
                ->closeModalByClickingAway(false)
                ->label(__('filament-media-library::picker.select existing media'))
                ->modalHeading(__('filament-media-library::picker.select existing media'))
                ->color('gray')
                ->outlined()
                ->modalSubmitAction(false)
                ->modalCancelAction(false)
                ->modalContent(function (self $component) {
                    return view('filament-resource-picker::picker', [
                        'resourceClass' => AttachmentResource::class,
                        'displayType' => 'filament-media-library::resource-picker.item',
                        'statePath' => $component->getStatePath(),
                        'state' => $component->getState() ?? [],
                        'keyField' => 'id',
                        'labelField' => 'id',
                        'isMultiple' => $component->isMultiple(),
                        'isGrid' => true,
                        'gridColumns' => 6,
                        'relationFilters' => collect([
                            'tags' => AttachmentTag::where('is_hidden', false)
                                ->cursor()
                                ->map(fn ($tag) => [
                                    'id' => $tag->id,
                                    'name' => $tag->title,
                                ])
                                ->toArray(),
                        ])->filter()->toArray(),
                    ]);
                }),
        ]);
    }

    public function multiple(bool|Closure $multiple = true): static
    {
        $this->multiple = $multiple;

        return $this;
    }

    public function isMultiple(): bool
    {
        return $this->evaluate($this->multiple);
    }

    public function getAttachments(): Collection
    {
        $state = $this->getState();

        if (blank($state) || empty($state)) {
            return collect();
        }

        $ids = collect($state)
            ->map(fn ($id) => "'{$id}'")
            ->join(',');

        return Attachment::whereIn('id', Arr::wrap($state))
            ->orderByRaw("FIELD(id,{$ids})")
            ->get();
    }

    public function sortField(string|Closure $sortField): static
    {
        $this->sortField = $sortField;

        return $this;
    }

    public function disableFormatter(): static
    {
        return $this->allowedFormats([]);
    }

    public function getSortField(): ?string
    {
        return $this->evaluate($this->sortField);
    }

    public function allowedFormats(?array $allowedFormats): static
    {
        $this->allowedFormats = $allowedFormats;

        return $this;
    }

    public function getAllowedFormats(): ?array
    {
        $formats = $this->evaluate($this->allowedFormats);

        // Get the model that we are editing/viewing
        if (is_null($formats)) {
            $model = $this->getModelInstance();
            if (is_null($model)) {
                $formats = Formats::flatten(1)->unique(fn (Format $format) => $format::class);
            } else {
                $formats = Formats::get($model::class);
            }
        }

        return Collection::wrap($formats)
            ->map(fn (string|Format $format) => is_string($format) ? $format : $format::class)
            ->toArray();
    }

    public function getRelationship(): BelongsToMany
    {
        return $this->getModelInstance()->{$this->getName()}();
    }

    public function getState(): mixed
    {
        $state = parent::getState();

        if ($this->isMultiple() && $state === null) {
            $relationship = $this->getRelationship();
            /** @var \Illuminate\Database\Eloquent\Collection $results */
            $results = $relationship->getResults();

            $state = $results
                ->pluck($relationship->getRelatedKeyName())
                ->toArray();

            // Set the state
            $this->state($state);
        }

        return $state;
    }
}
