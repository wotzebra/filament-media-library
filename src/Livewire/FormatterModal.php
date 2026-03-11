<?php

namespace Wotz\MediaLibrary\Livewire;

use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;
use Wotz\MediaLibrary\Facades\Formats;
use Wotz\MediaLibrary\Formats\Format;
use Wotz\MediaLibrary\Models\Attachment;
use Spatie\Image\Image;

class FormatterModal extends Component
{
    public Attachment $attachment;

    public array $formats = [];

    public ?array $currentFormat = null;

    public ?string $forcedMimeType = null;

    #[On('filament-media-library::open-formatter-attachment-modal')]
    public function setAttachment(string $uuid, ?array $formats = null)
    {
        $this->attachment = Attachment::find($uuid);

        $this->forcedMimeType = config(
            'filament-media-library.force-format-extension.mime-type',
            $this->attachment->mime_type,
        );

        $formats = Collection::wrap($formats ?? Formats::mapToClasses())
            ->map(fn ($format) => $format::make())
            ->filter(fn (Format $format) => $format->shownInFormatter())
            ->map->toArray();

        // Make sure we have the correct format selected when switching fields
        if ($this->currentFormat && ! $formats->pluck('key')->contains($this->currentFormat['key'])) {
            $this->currentFormat = null;
        }

        $this->currentFormat ??= $formats->first();

        $this->formats = $formats->toArray();
    }

    public function render()
    {
        $this->dispatch('filament-media-library::load-formatter', [
            'formats' => $this->formats,
        ]);

        $previousFormats = [];
        if (isset($this->attachment)) {
            $previousFormats = $this->attachment->formats()->pluck('data', 'format');
        }

        return view('filament-media-library::livewire.formatter-modal', [
            'previousFormats' => $previousFormats,
        ]);
    }

    public function saveCrop($event)
    {
        $format = $event['format']['key']::make();
        $filename = $format->filename($this->attachment);

        // Decode the lossless PNG crop from the browser
        $crop = preg_replace('/data:image\/(.*?);base64,/', '', $event['crop']);
        $crop = base64_decode(str_replace(' ', '+', $crop));

        // Save as PNG to a temp file, then convert to WebP server-side for better quality
        $tempPath = tempnam(sys_get_temp_dir(), 'crop_') . '.png';
        file_put_contents($tempPath, $crop);

        // Apply the format's manipulations (resize + quality) and save as WebP
        $savePath = $this->attachment->absolute_directory_path . '/' . $filename;
        $image = Image::load($tempPath);
        $format->definition()->apply($image);
        $image->save($savePath);

        unlink($tempPath);

        // Save the crop on the attachment, for later adjustments
        $this->attachment->formats()->updateOrCreate([
            'attachment_id' => $this->attachment->id,
            'format' => $event['format']['key'],
        ], [
            'data' => $event['data'],
        ]);

        Notification::make()
            ->title(__('filament-media-library::formatter.successfully formatted'))
            ->success()
            ->send();
    }
}
