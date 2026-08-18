<?php

namespace Wotz\MediaLibrary\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Collection;
use Wotz\MediaLibrary\Facades\Formats;
use Wotz\MediaLibrary\Formats\Format;
use Wotz\MediaLibrary\Support\FormatSummary;

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
                'description' => FormatSummary::describe($format),
                'definition' => FormatSummary::formatDimensions($format) ?? __('filament-media-library::widgets.image-formats.auto'),
            ])
            ->sortBy('name')
            ->values();
    }
}
