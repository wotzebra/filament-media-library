<?php

namespace Wotz\MediaLibrary\Support;

use Illuminate\Support\Str;
use Spatie\TemporaryDirectory\TemporaryDirectory as BaseTemporaryDirectory;

class TemporaryDirectory
{
    public static function create(): BaseTemporaryDirectory
    {
        return new BaseTemporaryDirectory(static::getTemporaryDirectoryPath());
    }

    protected static function getTemporaryDirectoryPath(): string
    {
        $path = config('filament-media-library.temporary_directory_path') ?? storage_path('filament-media-library/tmp');

        return $path . DIRECTORY_SEPARATOR . Str::random(32);
    }
}
