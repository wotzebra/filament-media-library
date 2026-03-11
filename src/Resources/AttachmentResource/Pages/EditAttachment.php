<?php

namespace Wotz\MediaLibrary\Resources\AttachmentResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Wotz\MediaLibrary\Resources\AttachmentResource;
use Wotz\TranslatableTabs\Resources\Traits\HasTranslations;

class EditAttachment extends EditRecord
{
    use HasTranslations;

    protected static string $resource = AttachmentResource::class;

    protected function getActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make(),
        ];
    }
}
