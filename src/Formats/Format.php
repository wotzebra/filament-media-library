<?php

namespace Wotz\MediaLibrary\Formats;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;
use Spatie\Image\Drivers\ImageDriver;
use Wotz\MediaLibrary\Conversions\Conversion;
use Wotz\MediaLibrary\Facades\Formats;
use Wotz\MediaLibrary\Models\Attachment;

abstract class Format implements Arrayable
{
    public Manipulations $manipulations;

    public bool $shownInFormatter = true;

    protected string $name;

    protected string $description;

    abstract public function definition(): Manipulations|ImageDriver;

    abstract public function registerModelsForFormatter(): void;

    public static function make(string $column = ''): static
    {
        return new static($column);
    }

    final public function __construct(protected string $column = '')
    {
        $this->manipulations = new Manipulations;
        $this->definition();

        $this->name = Str::headline(class_basename(static::class));
    }

    public function filename(Attachment $attachment): string
    {
        return $this->prefix() . Str::replaceLast(
            $attachment->extension,
            config('filament-media-library.force-format-extension.extension', 'webp'),
            $attachment->file_name,
        );
    }

    public function prefix(): string
    {
        return Str::snake(class_basename(static::class)) . '__';
    }

    public function kebab(): string
    {
        return Str::kebab(class_basename(static::class));
    }

    public function toArray()
    {
        return [
            'key' => $this->key(),
            'name' => $this->name(),
            'description' => $this->description(),
            'width' => $this->width(),
            'height' => $this->height(),
            'aspectRatio' => $this->aspectRatio(),
            'shownInFormatter' => $this->shownInFormatter(),
        ];
    }

    public function key(): string
    {
        return get_class($this);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function column(): string
    {
        return $this->column;
    }

    public function argument(string $argument): mixed
    {
        return $this->manipulations->getManipulationArgument($argument);
    }

    public function width(): ?string
    {
        return $this->argument('fit')[1]
            ?? $this->argument('width')[0]
            ?? null;
    }

    public function height(): ?string
    {
        return $this->argument('fit')[2]
            ?? $this->argument('height')[0]
            ?? null;
    }

    public function aspectRatio(): float
    {
        $height = (int) $this->height();
        if ($height === 0) {
            return 0;
        }

        return (int) $this->width() / $height;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function shownInFormatter(): bool
    {
        return $this->shownInFormatter;
    }

    public function conversion(): Conversion
    {
        return app(Conversion::class);
    }

    public function registerFor(string $class, string|array|null $fields = null): void
    {
        Formats::registerFor($this::class, $class, $fields);
    }
}
