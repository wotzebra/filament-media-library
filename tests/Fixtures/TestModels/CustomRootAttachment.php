<?php

namespace Wotz\MediaLibrary\Tests\Fixtures\TestModels;

use Wotz\MediaLibrary\Models\Attachment;

/**
 * The reason an application configures its own model: keeping an existing
 * storage layout instead of moving every object to `attachments/`.
 */
class CustomRootAttachment extends Attachment
{
    public function getRootDirectoryAttribute(): string
    {
        return 'files';
    }
}
