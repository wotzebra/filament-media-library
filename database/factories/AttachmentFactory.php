<?php

namespace Wotz\MediaLibrary\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Wotz\MediaLibrary\Models\Attachment;

class AttachmentFactory extends Factory
{
    protected $model = Attachment::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'extension' => $this->faker->randomElement([
                'jpg',
                'png',
                'webp',
                'jpeg',
                'pdf',
                'gif',
            ]),
            'mime_type' => $this->faker->mimeType(),
            'md5' => $this->faker->md5,
            'type' => $this->faker->randomElement([
                'image',
                'document',
                'other',
            ]),
            'size' => $this->faker->numberBetween(),
            'width' => $this->faker->numberBetween(),
            'height' => $this->faker->numberBetween(),
            'disk' => 'public',
        ];
    }
}
