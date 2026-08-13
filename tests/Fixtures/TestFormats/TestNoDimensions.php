<?php

namespace Wotz\MediaLibrary\Tests\Fixtures\TestFormats;

use Wotz\MediaLibrary\Formats\Format;
use Wotz\MediaLibrary\Formats\Manipulations;
use Wotz\MediaLibrary\Tests\Fixtures\TestModels\TestModel;

/**
 * A format that only recolours: no dimensions, and no description either.
 */
class TestNoDimensions extends Format
{
    public function definition(): Manipulations
    {
        return $this->manipulations->sepia();
    }

    public function registerModelsForFormatter(): void
    {
        $this->registerFor(TestModel::class, [
            'test_id',
        ]);
    }
}
