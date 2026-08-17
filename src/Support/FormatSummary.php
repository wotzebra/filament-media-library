<?php

namespace Wotz\MediaLibrary\Support;

use Illuminate\Support\Collection;
use Throwable;
use Wotz\MediaLibrary\Formats\Format;

/**
 * Turns the formats that apply to a field into display data. The field hint and the
 * format block in the upload modal both read from here, so they cannot drift apart.
 */
class FormatSummary
{
    /** @var Collection<int, Format> */
    protected Collection $formats;

    /**
     * @param  array<int, string|Format>|null  $formats
     */
    final public function __construct(?array $formats = null)
    {
        $this->formats = Collection::wrap($formats ?? [])
            ->map(fn (string|Format $format): Format => is_string($format) ? new $format : $format)
            // A format without dimensions says nothing about the source image.
            ->filter(fn (Format $format): bool => filled($format->width()) || filled($format->height()))
            ->values();
    }

    /**
     * @param  array<int, string|Format>|null  $formats
     */
    public static function make(?array $formats = null): static
    {
        return new static($formats);
    }

    public function isEmpty(): bool
    {
        return $this->formats->isEmpty();
    }

    public function isNotEmpty(): bool
    {
        return $this->formats->isNotEmpty();
    }

    public function count(): int
    {
        return $this->formats->count();
    }

    /**
     * The largest width any of the formats crops to.
     */
    public function requiredWidth(): ?int
    {
        return $this->max(fn (Format $format): ?int => $this->toInt($format->width()));
    }

    /**
     * The largest height any of the formats crops to. Width-only formats do not raise it.
     */
    public function requiredHeight(): ?int
    {
        return $this->max(fn (Format $format): ?int => $this->toInt($format->height()));
    }

    /**
     * The minimum source size that covers every format, e.g. `2000 × 1000 px`.
     */
    public function requiredDimensions(): ?string
    {
        return static::dimensions($this->requiredWidth(), $this->requiredHeight());
    }

    /**
     * The single line of hint text shown next to the field label.
     */
    public function hint(): ?string
    {
        $dimensions = $this->requiredDimensions();

        if ($dimensions === null) {
            return null;
        }

        if ($this->count() === 1) {
            return $dimensions;
        }

        return __('filament-media-library::upload.formats hint', ['dimensions' => $dimensions])
            . ' · '
            . $this->countLabel();
    }

    public function countLabel(): string
    {
        return trans_choice('filament-media-library::upload.formats count', $this->count(), [
            'count' => $this->count(),
        ]);
    }

    /**
     * @return array<int, array{name: string, description: string|null, width: int|null, height: int|null, dimensions: string|null}>
     */
    public function formats(): array
    {
        return $this->formats
            ->map(function (Format $format): array {
                $width = $this->toInt($format->width());
                $height = $this->toInt($format->height());

                return [
                    'name' => $format->name(),
                    'description' => $this->describe($format),
                    'width' => $width,
                    'height' => $height,
                    'dimensions' => static::dimensions($width, $height),
                ];
            })
            ->all();
    }

    /**
     * Resolves every format against a source image. A source smaller than the format in
     * either direction gets upscaled: the crop is still generated, only softer.
     *
     * @return array<int, array{name: string, description: string|null, width: int|null, height: int|null, dimensions: string|null, isUpscaled: bool}>
     */
    public function results(?int $sourceWidth, ?int $sourceHeight): array
    {
        return array_map(function (array $format) use ($sourceWidth, $sourceHeight): array {
            $format['isUpscaled'] = ($sourceWidth !== null && $format['width'] !== null && $sourceWidth < $format['width'])
                || ($sourceHeight !== null && $format['height'] !== null && $sourceHeight < $format['height']);

            return $format;
        }, $this->formats());
    }

    /**
     * The wording follows the dashboard widget: `1200 × 630 px`, or `1920px wide` for
     * formats that only constrain one side.
     */
    public static function dimensions(?int $width, ?int $height): ?string
    {
        if ($width !== null && $height !== null) {
            return __('filament-media-library::upload.formats dimensions', [
                'width' => $width,
                'height' => $height,
            ]);
        }

        if ($width !== null) {
            return __('filament-media-library::upload.formats dimensions width only', ['width' => $width]);
        }

        if ($height !== null) {
            return __('filament-media-library::upload.formats dimensions height only', ['height' => $height]);
        }

        return null;
    }

    protected function max(callable $callback): ?int
    {
        $values = $this->formats
            ->map($callback)
            ->filter(fn (?int $value): bool => $value !== null);

        return $values->isEmpty() ? null : $values->max();
    }

    protected function describe(Format $format): ?string
    {
        try {
            return $format->description() ?: null;
        } catch (Throwable) {
            // Format leaves `$description` uninitialised, so not every format defines one.
            return null;
        }
    }

    protected function toInt(null|int|string $value): ?int
    {
        return filled($value) ? (int) $value : null;
    }
}
