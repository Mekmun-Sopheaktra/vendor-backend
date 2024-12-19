<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::query()->truncate();

        $categories = [
            ['name' => 'Computer', 'icon' => 'icon', 'parent' => 0],
            ['name' => 'Electronics', 'icon' => 'icon', 'parent' => 0],
            ['name' => 'Arts & Crafts', 'icon' => 'icon', 'parent' => 0],
            ['name' => 'Automotive', 'icon' => 'icon', 'parent' => 0],
            ['name' => 'Baby', 'icon' => 'icon', 'parent' => 0],
            ['name' => 'Beauty and personal care', 'icon' => 'icon', 'parent' => 0],
            ['name' => 'Women\'s Fashion', 'icon' => 'icon', 'parent' => 0],
            ['name' => 'Men\'s Fashion', 'icon' => 'icon', 'parent' => 0],
            ['name' => 'Health and Household', 'icon' => 'icon', 'parent' => 0],
            ['name' => 'Home and Kitchen', 'icon' => 'icon', 'parent' => 0],
            ['name' => 'Industrial and Scientific', 'icon' => 'icon', 'parent' => 0],
            ['name' => 'Luggage', 'icon' => 'icon', 'parent' => 0],
            ['name' => 'Movies & Television', 'icon' => 'icon', 'parent' => 0],
            ['name' => 'Pet supplies', 'icon' => 'icon', 'parent' => 0],
            ['name' => 'Sports and Outdoors', 'icon' => 'icon', 'parent' => 0],
            ['name' => 'Tools & Home Improvement', 'icon' => 'icon', 'parent' => 0],
            ['name' => 'Toys and Games', 'icon' => 'icon', 'parent' => 0],
        ];

        foreach ($categories as $category) {
            Category::query()->create($category);
        }
    }
}
