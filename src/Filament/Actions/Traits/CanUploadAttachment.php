<?php

namespace Wotz\MediaLibrary\Filament\Actions\Traits;

use Closure;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Illuminate\Support\Arr;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Wotz\MediaLibrary\Models\Attachment;
use Wotz\MediaLibrary\Models\AttachmentTag;
use Wotz\TranslatableTabs\Forms\TranslatableTabs;

trait CanUploadAttachment
{
    protected bool|Closure $multiple = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configureAction();
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

    protected function getUploadStep(): \Filament\Schemas\Components\Wizard\Step
    {
        return \Filament\Schemas\Components\Wizard\Step::make(__('filament-media-library::upload.upload step title'))
            ->description(__('filament-media-library::upload.upload step intro'))
            ->afterValidation(function (\Filament\Schemas\Components\Utilities\Get $get, \Filament\Schemas\Components\Utilities\Set $set) {
                foreach (Arr::wrap($get('attachments')) as $file) {
                    if ($file instanceof TemporaryUploadedFile) {
                        $md5 = md5($file->getClientOriginalName());

                        $set("meta.{$md5}.name", '');
                        $set("meta.{$md5}.tags", []);
                    }
                }
            })
            ->schema([
                FileUpload::make('attachments')
                    ->live()
                    ->hiddenLabel()
                    ->required()
                    ->multiple(fn () => $this->isMultiple())
                    ->saveUploadedFileUsing(function (TemporaryUploadedFile $file, \Filament\Schemas\Components\Utilities\Set $set): string {
                        $attachment = $file->save();

                        return $attachment->id;
                    }),
            ]);
    }

    protected function getAttachmentInformationStep(): \Filament\Schemas\Components\Wizard\Step
    {
        return \Filament\Schemas\Components\Wizard\Step::make(__('filament-media-library::upload.attachment information step title'))
            ->description(__('filament-media-library::upload.attachment information step intro'))
            ->schema(function ($state, \Filament\Schemas\Components\Utilities\Get $get) {
                return collect($state['attachments'] ?? [])
                    ->filter(fn ($upload) => $upload instanceof TemporaryUploadedFile)
                    ->map(function ($upload) use ($get) {
                        $md5 = md5($upload->getClientOriginalName());

                        $defaultFields = [
                            TextEntry::make('name')
                                ->state(fn () => $upload->getClientOriginalName())
                                ->label(__('filament-media-library::upload.name')),
                        ];

                        if (! is_null($get("meta.{$md5}.tags"))) {
                            $defaultFields[] = Select::make('tags')
                                ->label(__('filament-media-library::upload.select tags'))
                                ->multiple()
                                ->default([])
                                ->hidden(fn (Select $component): bool => ! $component->getOptions())
                                ->options(AttachmentTag::all()->pluck('title', 'id')->toArray())
                                ->disabled(fn (Select $component): bool => ! $component->getOptions());
                        }

                        return \Filament\Schemas\Components\Section::make()
                            ->description($upload->getClientOriginalName())
                            ->collapsible()
                            ->columns()
                            ->schema([
                                TranslatableTabs::make()
                                    ->statePath("meta.{$md5}")
                                    ->icon('heroicon-o-signal')
                                    ->columnSpan(['lg' => 2])
                                    ->persistTabInQueryString(false)
                                    ->defaultFields($defaultFields)
                                    ->translatableFields(fn () => [
                                        TextInput::make('alt')
                                            ->label(__('filament-media-library::upload.alt text')),

                                        TextInput::make('caption')
                                            ->label(__('filament-media-library::upload.caption')),
                                    ]),
                            ]);
                    })
                    ->flatten()
                    ->toArray();
            });
    }

    protected function mutateData(array $data): array
    {
        $model = app($this->getModel());

        foreach (Arr::except($data, $model->getFillable()) as $locale => $values) {
            if (! is_array($values)) {
                continue;
            }

            foreach (Arr::only($values, $model->getTranslatableAttributes()) as $key => $value) {
                $data[$key][$locale] = $value;
            }
        }

        return Arr::only($data, $model->getTranslatableAttributes());
    }

    protected function saveAttachmentsAndSendNotification(Component $livewire): void
    {
        $data = collect($livewire->mountedActions)->first(fn (array $action) => $action['name'] === $this->getName())['data'] ?? [];

        collect($data['attachments'] ?? [])
            ->map(function (string $attachmentId) use ($data) {
                $attachment = Attachment::find($attachmentId);

                if (! $attachment) {
                    return null;
                }

                $meta = $data['meta'][md5($attachment->filename)] ?? [];

                if (! $meta) {
                    return $attachmentId;
                }

                $attachment->update($this->mutateData($meta));

                if (array_key_exists('tags', $meta)) {
                    $attachment->tags()->sync($meta['tags']);
                }

                return $attachment->id;
            })
            ->filter();

        Notification::make()
            ->title(__('filament-media-library::upload.upload successful'))
            ->success()
            ->send();
    }

    public function configureAction(): void
    {
        $this->label(__('filament-media-library::upload.upload attachment'));

        $this->name('attachment-upload');

        $this->steps([
            $this->getUploadStep(),
            $this->getAttachmentInformationStep(),
        ]);

        $this->closeModalByClickingAway(false);

        $this->action(fn (Component $livewire) => $this->saveAttachmentsAndSendNotification($livewire));
    }
}
