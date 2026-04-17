<?php

namespace Wotz\MediaLibrary\Filament\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
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
                FileUpload::make('file')
                    ->required()
                    ->storeFiles(false),
            ])
            ->action(function (array $data, Component $livewire): void {
                $file = $data['file'] ?? null;

                if (! $file instanceof TemporaryUploadedFile) {
                    return;
                }

                $record = $this->getRecord();

                if (! $record instanceof Attachment) {
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
}
