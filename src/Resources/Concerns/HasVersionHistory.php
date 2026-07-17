<?php

namespace Wotz\MediaLibrary\Resources\Concerns;

use Filament\Actions\ActionGroup;
use InvalidArgumentException;
use Wotz\MediaLibrary\Filament\Actions\ReplaceAttachmentAction;
use Wotz\MediaLibrary\Filament\Actions\VersionHistoryAction;
use Wotz\MediaLibrary\Models\Attachment;

trait HasVersionHistory
{
    protected function getReplaceFileAction(): ReplaceAttachmentAction
    {
        return ReplaceAttachmentAction::make('replaceFile');
    }

    protected function getVersionHistoryAction(): ActionGroup
    {
        $record = $this->getRecord();

        if (! $record instanceof Attachment) {
            throw new InvalidArgumentException(sprintf(
                '%s can only be used on a page whose record is a %s.',
                static::class,
                Attachment::class,
            ));
        }

        return VersionHistoryAction::make($record);
    }
}
