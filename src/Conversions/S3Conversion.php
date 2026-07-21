<?php

namespace Wotz\MediaLibrary\Conversions;

use Illuminate\Support\Str;
use Spatie\Image\Image;
use Wotz\MediaLibrary\Formats\Format;
use Wotz\MediaLibrary\Models\Attachment;
use Wotz\MediaLibrary\Support\TemporaryDirectory;

class S3Conversion implements Conversion
{
    public function convert(Attachment $attachment, Format $format, bool $force = false): bool
    {
        if (! is_convertible_image($attachment->extension)) {
            return false;
        }

        $formatName = $format->filename($attachment);
        $savePath = $attachment->directory . '/' . $formatName;

        if (! empty($format->definition()->toArray()['format'])) {
            $savePath = Str::replaceLast(
                $attachment->extension,
                $format->definition()->toArray()['format'],
                $savePath
            );
        }

        $formatPath = "$attachment->directory/$formatName";

        // Check if there's an existing manual crop for this format
        $existingFormat = $attachment->formats()
            ->where('format', $format->key())
            ->first();

        $hasManualCrop = $existingFormat && ! empty($existingFormat->data) && is_array($existingFormat->data);

        if (
            $force ||
            ! $attachment->getStorage()->exists($formatPath)
        ) {
            $temporaryDirectory = TemporaryDirectory::create();
            $tempPath = $temporaryDirectory->path(Str::random(16) . '.' . $attachment->extension);
            $disk = $attachment->getStorage();

            file_put_contents($tempPath, $disk->readStream($attachment->file_path));

            $image = Image::load($tempPath);

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

            $image->save($tempPath);

            $file = fopen($tempPath, 'r');

            $disk->put(
                $formatPath,
                $file,
            );

            if (is_resource($file)) {
                fclose($file);
            }

            $temporaryDirectory->delete();
        }

        return true;
    }
}
