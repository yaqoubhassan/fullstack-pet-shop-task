<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'pet clean-up and odor control',
            'cat litter',
            'wet pet food',
            'pet oral care',
            'heartworm medication',
            'pet vitamins and supplements',
            'pet grooming supplies',
            'flea and tick medication',
            'pet treats and chews',
            'dry dog food'
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                [
                    'title' => $category
                ],
                [
                    'uuid' => (string) Str::uuid(),
                    'slug' => Str::slug($category)
                ]
            );
        }
    }
}
