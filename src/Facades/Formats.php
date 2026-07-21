<?php

namespace Wotz\MediaLibrary\Facades;

use Illuminate\Support\Facades\Facade;

class Formats extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Wotz\MediaLibrary\Collections\Formats::class;
    }
}
