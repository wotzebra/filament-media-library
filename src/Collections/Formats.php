<?php

namespace Wotz\MediaLibrary\Collections;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Wotz\MediaLibrary\Formats\Format;
use Wotz\MediaLibrary\Jobs\GenerateAttachmentFormat;
use Wotz\MediaLibrary\Models\Attachment;

class Formats extends Collection
{
    public function registerFor(string $format, string $model, string|array|null $fields = null)
    {
        $formats = $this->get($model) ?? collect();

        if (is_null($fields)) {
            $formats->push(new $format);
        }

        foreach (Arr::wrap($fields) as $field) {
            $formats->push(new $format($field));
        }

        $this->put($model, $formats);

        return $this;
    }

    public function register(string|array $formats): static
    {
        foreach (Arr::wrap($formats) as $format) {
            (new $format)->registerModelsForFormatter();
        }

        return $this;
    }

    public function exists(string $name): ?Format
    {
        return $this->mapToKebab()->get($name);
    }

    public function mapToKebab(): Collection
    {
        return $this->flatten()->mapWithKeys(fn (Format $format) => [
            $format->kebab() => $format,
        ]);
    }

    public function mapToClasses(): Collection
    {
        return $this->flatten()
            ->map(fn (Format $format) => get_class($format))
            ->unique()
            ->values();
    }

    public function dispatchGeneration(Attachment $attachment, bool $force = false): void
    {
        $this->flatten()->each(fn (Format $format) => dispatch(new GenerateAttachmentFormat(
            attachment: $attachment,
            format: $format,
            force: $force,
        )));
    }
}
