<?php

namespace Wotz\MediaLibrary\Exceptions;

use Exception;

class FormatNotFound extends Exception
{
    public static function throw(string $name)
    {
        if (app()->isLocal()) {
            throw new static("Format not found `$name`");
        }
    }
}
