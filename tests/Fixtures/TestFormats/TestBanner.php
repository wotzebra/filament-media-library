<?php

namespace Wotz\MediaLibrary\Tests\Fixtures\TestFormats;

use Spatie\Image\Enums\Fit;
use Wotz\MediaLibrary\Formats\Format;
use Wotz\MediaLibrary\Formats\Manipulations;
use Wotz\MediaLibrary\Tests\Fixtures\TestModels\TestModel;

class TestBanner extends Format
{
    protected string $description = 'Wide banner';

    public function definition(): Manipulations
    {
        return $this->manipulations
            ->fit(Fit::Crop, 200, 50);
    }

    public function registerModelsForFormatter(): void
    {
        $this->registerFor(TestModel::class, [
            'test_id',
        ]);
    }
}
