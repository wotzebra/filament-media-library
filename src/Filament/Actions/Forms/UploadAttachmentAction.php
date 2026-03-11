<?php

namespace Wotz\MediaLibrary\Filament\Actions\Forms;

use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Set;
use Livewire\Component;
use Wotz\MediaLibrary\Filament\Actions\Traits\CanUploadAttachment;
use Wotz\MediaLibrary\Models\Attachment;

class UploadAttachmentAction extends \Filament\Actions\Action
{
    use CanUploadAttachment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configureAction();

        $this
            ->action(function (Component $livewire, Set $schemaSet, \Filament\Schemas\Components\Component $schemaComponent) {
                $data = collect($livewire->mountedActions)->first(fn (array $action) => $action['name'] === $this->getName())['data'] ?? [];

                $attachmentIds = collect($data['attachments'] ?? [])
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

                // Set the state if this is a field
                $schemaSet(
                    $schemaComponent->getStatePath(false),
                    $this->isMultiple()
                        ? collect($schemaComponent->getState())->concat($attachmentIds)->toArray()
                        : $attachmentIds->first()
                );

                Notification::make()
                    ->title(__('filament-media-library::upload.upload successful'))
                    ->success()
                    ->send();
            });
    }

    public function getModel(bool $withDefault = false): ?string
    {
        return Attachment::class;
    }
}
