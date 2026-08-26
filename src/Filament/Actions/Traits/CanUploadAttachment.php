<?php

namespace Wotz\MediaLibrary\Filament\Actions\Traits;

use Closure;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\View;
use Filament\Schemas\Components\Wizard\Step;
use Illuminate\Support\Arr;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Wotz\MediaLibrary\Formats\Format;
use Wotz\MediaLibrary\Models\AttachmentTag;
use Wotz\MediaLibrary\Rules\FileRule;
use Wotz\MediaLibrary\Support\FormatSummary;
use Wotz\TranslatableTabs\Forms\TranslatableTabs;
use Wotz\MediaLibrary\Support\Config;

trait CanUploadAttachment
{
    protected bool|Closure $multiple = false;

    protected null|array|Closure $allowedFormats = null;

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

    /**
     * The formats of the field this action belongs to. Left null when the action is not
     * tied to a field, and then the format block is not shown at all.
     *
     * @param  array<int, string|Format>|Closure|null  $allowedFormats
     */
    public function allowedFormats(null|array|Closure $allowedFormats): static
    {
        $this->allowedFormats = $allowedFormats;

        return $this;
    }

    /**
     * @return array<int, string|Format>|null
     */
    public function getAllowedFormats(): ?array
    {
        return $this->evaluate($this->allowedFormats);
    }

    protected function getUploadStep(): Step
    {
        return Step::make(__('filament-media-library::upload.upload step title'))
            ->description(__('filament-media-library::upload.upload step intro'))
            ->afterValidation(function (Get $get, Set $set) {
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
                    // dimensions validation
                    ->rule(new FileRule)
                    // image file size validation
                    // ->rule(File::types(collect(config('filament-media-library.extensions', []))->flatten()->toArray())
                    //     ->max('10mb'))
                        // max:config('media.validation.max_file_size', 5) * 1000000
                    // file size validation
                    // mime type validation
                    // color type validation
                    // $this->validateImageFileSize();
                    // $this->validateFileSize();
                    // $this->validateMimeType();
                    // $this->validateColorType();
                    ->saveUploadedFileUsing(function (TemporaryUploadedFile $file, Set $set, Component $livewire): string {
                        // this does not work
                        // $livewire->addError($this->getName(), 'Dimensions must be 100x100, is now 200x101');

                        $attachment = $file->save();

                        return $attachment->id;
                    }),

                // The formats of the field and how the upload resolves against them: covered,
                // upscaled, or rejected by FileRule. It never blocks the step by itself.
                View::make('filament-media-library::filament.upload-formats')
                    ->visible(fn (): bool => FormatSummary::make($this->getAllowedFormats())->isNotEmpty())
                    ->viewData(fn (Get $get): array => $this->getUploadFormatsViewData($get)),
            ]);
    }

    /**
     * @return array{summary: FormatSummary|null, results: array<int, array<string, mixed>>, sourceWidth: int|null, fileErrors: array<int, string>}
     */
    protected function getUploadFormatsViewData(Get $get): array
    {
        $data = ['summary' => null, 'results' => [], 'sourceWidth' => null, 'fileErrors' => []];

        $summary = FormatSummary::make($this->getAllowedFormats());

        if ($summary->isEmpty()) {
            return $data;
        }

        $files = collect(Arr::wrap($get('attachments')))
            ->filter(fn ($file): bool => $file instanceof TemporaryUploadedFile);

        // FileRule keeps owning the hard failures, but repeating them in the block means the
        // editor reads them the moment a file lands rather than on the way to the next step.
        $data['fileErrors'] = $files
            ->flatMap(fn (TemporaryUploadedFile $file): array => $this->getFileRuleErrors($file))
            ->all();

        // Documents and SVGs have no raster dimensions to compare formats against, so their
        // formats stay unresolved. They are still worth listing behind a failure.
        $dimensions = $files
            ->map(fn (TemporaryUploadedFile $file) => @getimagesize($file->getRealPath()))
            ->filter(fn ($size): bool => is_array($size));

        if ($files->isNotEmpty() && $dimensions->isEmpty()) {
            if (blank($data['fileErrors'])) {
                return $data;
            }

            return [...$data, 'summary' => $summary, 'results' => $summary->results(null, null)];
        }

        // One block per step, so with several files the smallest source decides per format.
        $width = $dimensions->min(fn (array $size): int => (int) $size[0]);
        $height = $dimensions->min(fn (array $size): int => (int) $size[1]);

        return [
            ...$data,
            'summary' => $summary,
            'results' => $summary->results($width, $height),
            'sourceWidth' => $width,
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function getFileRuleErrors(TemporaryUploadedFile $file): array
    {
        $errors = [];

        (new FileRule)->validate('attachments', $file, function ($message) use (&$errors): void {
            $errors[] = (string) $message;
        });

        return $errors;
    }

    protected function getAttachmentInformationStep(): Step
    {
        return Step::make(__('filament-media-library::upload.attachment information step title'))
            ->description(__('filament-media-library::upload.attachment information step intro'))
            ->schema(function ($state, Get $get) {
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

                        return Section::make()
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
                $attachment = Config::attachmentModel()::find($attachmentId);

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
