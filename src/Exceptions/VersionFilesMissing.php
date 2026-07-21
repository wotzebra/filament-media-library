<?php

namespace Wotz\MediaLibrary\Exceptions;

use Exception;
use Wotz\MediaLibrary\Models\AttachmentVersion;

class VersionFilesMissing extends Exception
{
    public static function make(AttachmentVersion $version, string $directory): self
    {
        return new self(
            "No stored files were found for version `{$version->version_number}` of attachment `{$version->attachment_id}` in `{$directory}`."
        );
    }
}
