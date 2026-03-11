<?php

namespace Wotz\MediaLibrary\Filament\Actions\Tables;

use Filament\Actions\Action;
use Wotz\MediaLibrary\Filament\Actions\Traits\CanUploadAttachment;
use Wotz\MediaLibrary\Models\Attachment;

class TableUploadAttachmentAction extends Action
{
    use CanUploadAttachment;

    public function getModel(bool $withDefault = true): ?string
    {
        return Attachment::class;
    }
}
