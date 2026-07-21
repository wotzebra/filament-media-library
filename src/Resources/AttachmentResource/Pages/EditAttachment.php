<?php

namespace Wotz\MediaLibrary\Resources\AttachmentResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Wotz\MediaLibrary\Resources\AttachmentResource;
use Wotz\MediaLibrary\Resources\Concerns\HasVersionHistory;
use Wotz\TranslatableTabs\Resources\Traits\HasTranslations;

class EditAttachment extends EditRecord
{
    use HasTranslations;
    use HasVersionHistory;

    protected static string $resource = AttachmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getReplaceFileAction(),
            $this->getVersionHistoryAction(),
            DeleteAction::make(),
        ];
    }
}
