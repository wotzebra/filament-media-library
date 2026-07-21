<?php

namespace Wotz\MediaLibrary\Conversions;

use Wotz\MediaLibrary\Formats\Format;
use Wotz\MediaLibrary\Models\Attachment;

interface Conversion
{
    public function convert(Attachment $attachment, Format $format, bool $force = false): bool;
}
