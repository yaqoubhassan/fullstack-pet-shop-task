<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\File;
use App\Models\Category;
use App\Models\Brand;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence($nbWords = rand(1, 4));
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        $file = File::factory()->create();
        return [
            'uuid' => (string) Str::uuid(),
            'category_uuid' => $category->uuid,
            'title' => $title,
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'description' => $this->faker->sentence(),
            'metadata' => [
                [
                    'brand' => $brand->uuid,
                    'image' => $file->uuid
                ]
            ]
        ];
    }
}
