<?php

use Wotz\MediaLibrary\Conversions\LocalConversion;

return [
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
];
