<?php

namespace Wotz\MediaLibrary\Tests\Fixtures\TestFormats;

use Spatie\Image\Enums\Fit;
use Wotz\MediaLibrary\Formats\Format;
use Wotz\MediaLibrary\Formats\Manipulations;
use Wotz\MediaLibrary\Tests\Fixtures\TestModels\TestModel;

class TestHero extends Format
{
    protected string $description = 'Test format';

    public function definition(): Manipulations
    {
        return $this->manipulations
            ->fit(Fit::Crop, 100, 100)
            ->sepia();
    }

    public function registerModelsForFormatter(): void
    {
        $this->registerFor(TestModel::class, [
            'test_id',
        ]);
    }
}
