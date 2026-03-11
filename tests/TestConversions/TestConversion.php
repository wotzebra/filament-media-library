<?php

namespace Wotz\MediaLibrary\Tests\TestConversions;

use Wotz\MediaLibrary\Conversions\Conversion;
use Wotz\MediaLibrary\Formats\Format;
use Wotz\MediaLibrary\Models\Attachment;

class TestConversion implements Conversion
{
    public function convert(Attachment $attachment, Format $format, bool $force = false): bool {}
}
