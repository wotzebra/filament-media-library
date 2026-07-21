<?php

namespace Wotz\MediaLibrary\Exceptions;

use Exception;
use Wotz\MediaLibrary\Models\Attachment;
use Wotz\MediaLibrary\Models\AttachmentVersion;

class VersionDoesNotBelongToAttachment extends Exception
{
    public static function make(AttachmentVersion $version, Attachment $attachment): self
    {
        return new self(
            "Version `{$version->version_number}` belongs to attachment `{$version->attachment_id}` and cannot be restored on attachment `{$attachment->getKey()}`."
        );
    }
}
