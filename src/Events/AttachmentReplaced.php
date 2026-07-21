<?php

namespace Wotz\MediaLibrary\Events;

use Wotz\MediaLibrary\Models\Attachment;
use Wotz\MediaLibrary\Models\AttachmentVersion;

class AttachmentReplaced
{
    public function __construct(
        public readonly Attachment $attachment,
        public readonly AttachmentVersion $previousVersion,
    ) {}
}
