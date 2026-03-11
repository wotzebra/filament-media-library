<?php

namespace Wotz\MediaLibrary\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Wotz\MediaLibrary\Formats\Format;
use Wotz\MediaLibrary\Models\Attachment;

class GenerateAttachmentFormat implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Attachment $attachment,
        public Format $format,
        public bool $force = false,
    ) {
        $this->onQueue(config('filament-media-library.format-queue', 'default'));
    }

    public function handle()
    {
        $this->format->conversion()->convert(
            attachment: $this->attachment,
            format: $this->format,
            force: $this->force,
        );
    }
}
