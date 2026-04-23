<?php

namespace Wotz\MediaLibrary\Resources\Concerns;

use Filament\Actions\ActionGroup;
use Wotz\MediaLibrary\Filament\Actions\ReplaceAttachmentAction;
use Wotz\MediaLibrary\Filament\Actions\VersionHistoryAction;

trait HasVersionHistory
{
    protected function getReplaceFileAction(): ReplaceAttachmentAction
    {
        return ReplaceAttachmentAction::make('replaceFile');
    }

    protected function getVersionHistoryAction(): ActionGroup
    {
        return VersionHistoryAction::make($this->getRecord());
    }
}
