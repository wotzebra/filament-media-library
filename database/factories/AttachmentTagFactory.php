<?php

namespace Wotz\MediaLibrary\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Wotz\MediaLibrary\Models\AttachmentTag;

class AttachmentTagFactory extends Factory
{
    protected $model = AttachmentTag::class;

    public function definition()
    {
        return [
            'title' => $this->faker->word,
        ];
    }
}
