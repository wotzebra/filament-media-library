<?php

namespace Wotz\MediaLibrary\Resources\AttachmentTagResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Wotz\MediaLibrary\Resources\AttachmentTagResource;

class ManageAttachmentTags extends ManageRecords
{
    protected static string $resource = AttachmentTagResource::class;

    protected function getActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
