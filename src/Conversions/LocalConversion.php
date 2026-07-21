<?php

namespace Wotz\MediaLibrary\Conversions;

use Illuminate\Support\Str;
use Spatie\Image\Image;
use Wotz\MediaLibrary\Formats\Format;
use Wotz\MediaLibrary\Models\Attachment;

class LocalConversion implements Conversion
{
    public function convert(Attachment $attachment, Format $format, bool $force = false): bool
    {
        if (! is_convertible_image($attachment->extension)) {
            return false;
        }

        $formatName = $format->filename($attachment);
        $savePath = $attachment->absolute_directory_path . '/' . $formatName;

        if (! empty($format->definition()->toArray()['format'])) {
            $savePath = Str::replaceLast(
                $attachment->extension,
                $format->definition()->toArray()['format'],
                $savePath
            );
        }

        // Check if there's an existing manual crop for this format
        $existingFormat = $attachment->formats()
            ->where('format', $format->key())
            ->first();

        $hasManualCrop = $existingFormat && ! empty($existingFormat->data) && is_array($existingFormat->data);

        if (
            $force ||
            ! $attachment->getStorage()->exists("$attachment->directory/$formatName")
        ) {
            $image = Image::load($attachment->absolute_file_path);

            // Apply manual crop coordinates if they exist
            if ($hasManualCrop) {
                $cropData = $existingFormat->data;

                // Apply crop transformation using the saved coordinates
                if (isset($cropData['x'], $cropData['y'], $cropData['width'], $cropData['height'])) {
                    $image->manualCrop(
                        (int) $cropData['width'],
                        (int) $cropData['height'],
                        (int) $cropData['x'],
                        (int) $cropData['y']
                    );
                }
            }

            // Apply format-specific manipulations
            $format->definition()->apply($image);

            $image->save($savePath);
        }

        return true;
    }
}
