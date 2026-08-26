<?php

use Wotz\MediaLibrary\Conversions\LocalConversion;

return [
    // Point this at a subclass of Wotz\MediaLibrary\Models\Attachment to
    // change how attachments behave — overriding the root directory, for
    // instance, so an application can keep an existing storage layout.
    // Null uses the package's own model.
    'model' => null,

    'conversion' => LocalConversion::class,
    'enable-format-generate-action' => true,
    'force-format-extension' => [
        'extension' => 'webp',
        'mime-type' => 'image/webp',
    ],
    'format-queue' => 'default',
    'extensions' => [
        'image' => [
            'jpg',
            'jpeg',
            'svg',
            'png',
            'webp',
            'gif',
            'WEBP',
            'GIF',
            'PNG',
            'JPG',
        ],
        'document' => [
            'txt',
            'pdf',
            'doc',
            'docx',
            'xls',
            'xlsx',
            'ppt',
            'pptx',
            'zip',
            'odf',
        ],
    ],
    'temporary_directory_path' => storage_path('filament-media-library/tmp'),

    'validation' => [
        'max_file_size' => 50000, // In KB, so 50 MB
        'max_height' => 2400,
        'max_width' => 4000,
    ],

    'versioning' => [
        'keep_versions' => 5,
        // Maximum size in kilobytes for a replacement file. `null` means no limit.
        'max_file_size' => null,
    ],
];
