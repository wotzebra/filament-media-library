<?php

namespace Codedor\MediaLibrary\Jobs;

use Codedor\MediaLibrary\Formats\Format;
use Codedor\MediaLibrary\Models\Attachment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateAttachmentFormat implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public string $formatClass;

    public string $formatColumn;

    public function __construct(
        public Attachment $attachment,
        Format $format,
        public bool $force = false,
    ) {
        $this->formatClass = $format::class;
        $this->formatColumn = $format->column();
        $this->onQueue(config('filament-media-library.format-queue', 'default'));
    }

    public function handle()
    {
        $format = new $this->formatClass($this->formatColumn);

        $format->conversion()->convert(
            attachment: $this->attachment,
            format: $format,
            force: $this->force,
        );
    }
}
