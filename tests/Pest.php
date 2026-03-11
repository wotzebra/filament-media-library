<?php

use Wotz\MediaLibrary\Models\Attachment;
use Wotz\MediaLibrary\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

function createAttachment($data = [])
{
    return Attachment::withoutEvents(
        fn () => Attachment::factory($data)->create()
    );
}
