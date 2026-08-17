<?php

namespace Wotz\MediaLibrary\Tests\Fixtures\TestFormats;

use Spatie\Image\Enums\Fit;
use Wotz\MediaLibrary\Formats\Format;
use Wotz\MediaLibrary\Formats\Manipulations;
use Wotz\MediaLibrary\Tests\Fixtures\TestModels\TestModel;

/**
 * Format leaves `$description` uninitialised, so not every format has one.
 */
class TestNoDescription extends Format
{
    public function definition(): Manipulations
    {
        return $this->manipulations
            ->fit(Fit::Crop, 300, 200);
    }

    public function registerModelsForFormatter(): void
    {
        $this->registerFor(TestModel::class, [
            'test_id',
        ]);
    }
}
