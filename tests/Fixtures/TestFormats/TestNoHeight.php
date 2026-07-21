<?php

namespace Wotz\MediaLibrary\Tests\Fixtures\TestFormats;

use Wotz\MediaLibrary\Formats\Format;
use Wotz\MediaLibrary\Formats\Manipulations;
use Wotz\MediaLibrary\Tests\Fixtures\TestModels\TestModel;
use Spatie\Image\Enums\Fit;

class TestNoHeight extends Format
{
    protected string $description = 'Test format';

    public function definition(): Manipulations
    {
        return $this->manipulations
            ->fit(Fit::Crop, 100)
            ->sepia();
    }

    public function registerModelsForFormatter(): void
    {
        $this->registerFor(TestModel::class, [
            'test_id',
        ]);
    }
}
