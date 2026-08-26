<?php

namespace Wotz\MediaLibrary\Exceptions;

use Exception;
use Wotz\MediaLibrary\Models\Attachment;

class InvalidConfiguration extends Exception
{
    public static function modelIsNotValid(string $model): self
    {
        return new static(
            "The configured attachment model `{$model}` is invalid. "
            . 'A valid model must be a subclass of `' . Attachment::class . '`.'
        );
    }
}
