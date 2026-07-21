<?php

namespace Wotz\MediaLibrary\Support;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploadConfig
{
    public function getMaxFilesize(): int
    {
        return UploadedFile::getMaxFilesize();
    }
}
