<?php

namespace Wotz\MediaLibrary\Filament\Actions;

use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Wotz\MediaLibrary\Models\Attachment;

class ReplaceAttachmentAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('filament-media-library::versioning.replace_file'))
            ->icon('heroicon-o-arrow-path')
            ->modalHeading(__('filament-media-library::versioning.replace_file'))
            ->schema([
                $this->getFileUploadField(),
            ])
            ->action(function (array $data, Component $livewire): void {
                $file = $data['file'] ?? null;
                $record = $this->getRecord();

                if (! $file instanceof TemporaryUploadedFile || ! $record instanceof Attachment) {
                    Notification::make()
                        ->title(__('filament-media-library::versioning.file_replace_failed'))
                        ->danger()
                        ->send();

                    return;
                }

                $record->replaceFile($file);

                Notification::make()
                    ->title(__('filament-media-library::versioning.file_replaced'))
                    ->success()
                    ->send();

                if (method_exists($livewire, 'refreshFormData')) {
                    $livewire->refreshFormData([
                        'name', 'extension', 'mime_type', 'type', 'size', 'width', 'height', 'version',
                    ]);
                }
            });
    }

    protected function getFileUploadField(): FileUpload
    {
        $field = FileUpload::make('file')
            ->required()
            ->storeFiles(false)
            ->rule(static fn (): Closure => static function (string $attribute, mixed $value, Closure $fail): void {
                if (! $value instanceof TemporaryUploadedFile) {
                    return;
                }

                $extension = Str::lower($value->getClientOriginalExtension());

                if (! in_array($extension, static::getAllowedExtensions(), true)) {
                    $fail(__('filament-media-library::versioning.unsupported_extension', [
                        'extension' => $extension,
                    ]));
                }
            });

        $maxSize = config('filament-media-library.versioning.max_file_size');

        // Left unset when unconfigured: maxSize(null) renders a broken `max:` rule.
        if (filled($maxSize)) {
            $field->maxSize((int) $maxSize);
        }

        return $field;
    }

    /**
     * @return array<int, string>
     */
    protected static function getAllowedExtensions(): array
    {
        return collect(Arr::flatten(config('filament-media-library.extensions', [])))
            ->map(fn (string $extension): string => Str::lower($extension))
            ->unique()
            ->values()
            ->all();
    }
}
