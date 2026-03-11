<?php

namespace Wotz\MediaLibrary\Views;

use Illuminate\View\Component;
use Wotz\MediaLibrary\Facades\Formats;
use Wotz\MediaLibrary\Formats\Format;
use Wotz\MediaLibrary\Models\Attachment;

class Picture extends Component
{
    public ?Format $formatClass = null;

    public function __construct(
        public Attachment $image,
        public ?string $format = null,
        public ?string $alt = '',
        public bool $placeholder = false,
        public array $formats = [],
        public string $containerClass = '',
        public string $pictureClass = '',
        public string $class = '',
        public ?string $title = '',
        public bool $lazyload = true,
        public ?string $lazyloadInitialFormat = 'lazyload',
    ) {
        if ($this->format) {
            $this->getFormatClass();
        }
    }

    protected function getFormatClass()
    {
        $this->formatClass = Formats::exists($this->format);
    }

    public function width(?string $format = null): string|int|null
    {
        $formatClass = Formats::exists($format ?? $this->format);

        if (! $formatClass) {
            return $this->image->width;
        }

        if ($formatClass->height() && $formatClass->width()) {
            return $formatClass->width();
        }

        return $this->getDimension('width', $format);
    }

    public function height(?string $format = null): string|int|null
    {
        $formatClass = Formats::exists($format ?? $this->format);

        if (! $formatClass) {
            return $this->image->height;
        }

        if ($formatClass->height() && $formatClass->width()) {
            return $formatClass->height();
        }

        return $this->getDimension('height', $format);
    }

    public function render()
    {
        return $this->view('filament-media-library::components.picture');
    }

    public function getDimension(string $dimension, ?string $format = null): ?int
    {
        $formatClass = Formats::exists($format ?? $this->format);

        $filename = $formatClass->filename($this->image);
        $path = "{$this->image->absolute_directory_path}/{$filename}";

        if (file_exists($path)) {
            $dimensions = getimagesize($path);

            return match ($dimension) {
                'width' => $dimensions[0],
                'height' => $dimensions[1],
                default => null,
            };
        }

        return null;
    }
}
