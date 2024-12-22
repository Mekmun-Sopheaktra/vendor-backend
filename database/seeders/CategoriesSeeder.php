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
            ['name' => 'Computer', 'slug' => 'computer', 'icon' => 'icon', 'parent' => 0],
            ['name' => 'Electronics', 'slug' => 'electronics', 'icon' => 'icon', 'parent' => 0],
            ['name' => 'Arts & Crafts', 'slug' => 'arts-crafts', 'icon' => 'icon', 'parent' => 0],
            ['name' => 'Automotive', 'slug' => 'automotive', 'icon' => 'icon', 'parent' => 0],
            ['name' => 'Baby', 'slug' => 'baby', 'icon' => 'icon', 'parent' => 0],
            ['name' => 'Beauty and personal care', 'slug' => 'beauty-and-personal-care', 'icon' => 'icon', 'parent' => 0],
            ['name' => 'Women\'s Fashion', 'slug' => 'womens-fashion', 'icon' => 'icon', 'parent' => 0],
            ['name' => 'Men\'s Fashion', 'slug' => 'mens-fashion', 'icon' => 'icon', 'parent' => 0],
            ['name' => 'Health and Household', 'slug' => 'health-and-household', 'icon' => 'icon', 'parent' => 0],
            ['name' => 'Home and Kitchen', 'slug' => 'home-and-kitchen', 'icon' => 'icon', 'parent' => 0],
            ['name' => 'Industrial and Scientific', 'slug' => 'industrial-and-scientific', 'icon' => 'icon', 'parent' => 0],
            ['name' => 'Luggage', 'slug' => 'luggage', 'icon' => 'icon', 'parent' => 0],
            ['name' => 'Movies & Television', 'slug' => 'movies-television', 'icon' => 'icon', 'parent' => 0],
            ['name' => 'Pet supplies', 'slug' => 'pet-supplies', 'icon' => 'icon', 'parent' => 0],
            ['name' => 'Sports and Outdoors', 'slug' => 'sports-and-outdoors', 'icon' => 'icon', 'parent' => 0],
            ['name' => 'Tools & Home Improvement', 'slug' => 'tools-home-improvement', 'icon' => 'icon', 'parent' => 0],
            ['name' => 'Toys and Games', 'slug' => 'toys-and-games', 'icon' => 'icon', 'parent' => 0],
        ];

        foreach ($categories as $category) {
            Category::query()->create($category);
        }
    }
}
