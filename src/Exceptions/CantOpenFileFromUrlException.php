<?php

namespace Wotz\MediaLibrary\Exceptions;

use Exception;

class CantOpenFileFromUrlException extends Exception
{
    public function __construct(string $url)
    {
        parent::__construct('Can\'t open file from url ' . $url . '.');
    }
}
