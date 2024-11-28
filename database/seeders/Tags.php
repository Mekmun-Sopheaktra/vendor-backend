<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class Tags extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Tag::query()->truncate();

        $tags = [
            ['name' => 'new-arrival', 'color' => '#28a745'], // Green
            ['name' => 'highlight', 'color' => '#007bff'], // Blue
            ['name' => 'sale', 'color' => '#dc3545'], // Red
            ['name' => 'featured', 'color' => '#ffc107'], // Yellow
            ['name' => 'limited-edition', 'color' => '#6f42c1'], // Purple
        ];

        foreach ($tags as $tag) {
            Tag::create($tag);
        }
    }
}
