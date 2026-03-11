<?php

use Illuminate\Support\Str;
use Wotz\MediaLibrary\Facades\Formats;

if (! function_exists('get_placeholder_url_by_format')) {
    function get_placeholder_url_by_format($format)
    {
        $format = Formats::exists($format);

        $width = $format?->width();
        $height = $format?->height();
        $name = $format?->name();

        return "https://via.placeholder.com/{$width}x{$height}/edeced/edeced.webp";
    }
}

if (! function_exists('is_convertible_image')) {
    function is_convertible_image(string $extension): bool
    {
        $extension = Str::lower($extension);

        return in_array($extension, config('filament-media-library.extensions.image', [])) && $extension !== 'gif' && $extension !== 'svg';
    }
}

if (! function_exists('is_image_with_dimensions')) {
    function is_image_with_dimensions(string $extension): bool
    {
        $extension = Str::lower($extension);

        return in_array($extension, config('filament-media-library.extensions.image', [])) && $extension !== 'svg';
    }
}
