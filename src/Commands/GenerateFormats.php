<?php

namespace Wotz\MediaLibrary\Commands;

use Illuminate\Console\Command;
use Wotz\MediaLibrary\Facades\Formats;
use Wotz\MediaLibrary\Formats\Format;
use Wotz\MediaLibrary\Jobs\GenerateAttachmentFormat;
use Wotz\MediaLibrary\Models\Attachment;

class GenerateFormats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:generate-format {--attachment-id=} {--format=} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate formats for an attachment (or all attachments)';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $formats = Formats::mapToKebab()->when(
            $this->option('format'),
            fn ($formats) => $formats->only($this->option('format'))
        );

        $attachments = Attachment::query()
            ->when(
                $this->option('attachment-id'),
                fn ($query) => $query->where('id', $this->option('attachment-id'))
            )
            ->get();

        $attachments->each(function (Attachment $attachment) use ($formats) {
            $formats->each(function (Format $format) use ($attachment) {
                $this->info("Dispatching {$format->kebab()} for {$attachment->file_path}");
                dispatch(new GenerateAttachmentFormat(
                    attachment: $attachment,
                    format: $format,
                    force: $this->option('force'),
                ));
            });
        });
    }
}
