<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence($nbWords = rand(1, 4));

        return [
            'title' => rtrim($title, '.'),
            'uuid' => (string) Str::uuid(),
            'slug' => Str::slug($title)
        ];
    }
}
