<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\File>
 */
class FileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fileName = $this->faker->word;
        $filePath = 'pet-shop/' . $this->faker->file('public/test-file', storage_path('app/public/pet-shop'), false);
        $fileUuid = Str::uuid()->toString();

        return [
            'uuid' => $fileUuid,
            'name' => $fileName,
            'path' => $filePath,
            'size' => $this->faker->numberBetween(1000, 5000),
            'type' => 'image/jpeg',
        ];
    }
}
