<?php

namespace Wotz\MediaLibrary\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Collection;
use Wotz\MediaLibrary\Facades\Formats;
use Wotz\MediaLibrary\Formats\Format;

class ImageFormats extends Widget
{
    protected string $view = 'filament-media-library::filament.widgets.image-formats';

    protected static ?int $sort = 10;

    protected int|string|array $columnSpan = 'full';

    public function getFormats(): Collection
    {
        return Formats::mapToKebab()
            ->map(fn (Format $format) => [
                'name' => $format->name(),
                'description' => $format->description(),
                'definition' => $this->formatDefinition($format),
            ])
            ->sortBy('name')
            ->values();
    }

    protected function formatDefinition(Format $format): string
    {
        $width = $format->width();
        $height = $format->height();

        if ($width && $height) {
            return "{$width} × {$height} px";
        }

        if ($width) {
            return "{$width}px wide";
        }

        if ($height) {
            return "{$height}px tall";
        }

        return 'Auto';
    }
}
