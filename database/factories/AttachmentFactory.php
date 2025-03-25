<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AttachmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'filename' => $this->faker->word . '.' . $this->faker->fileExtension(),
            'path' => 'attachments/' . $this->faker->uuid,
            'mime_type' => $this->faker->mimeType(),
            'size' => $this->faker->numberBetween(1000, 5000000),
        ];
    }
}