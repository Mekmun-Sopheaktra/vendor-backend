<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Constants\ProductPriority;
use Illuminate\Support\Facades\DB;

class PrioritySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $priorities = [
            ProductPriority::HOT,
            ProductPriority::POPULAR,
            ProductPriority::BEST_SELLER,
            ProductPriority::RECOMMENDED,
            ProductPriority::PROMO,
            ProductPriority::DISCOUNT,
            ProductPriority::SALE,
            ProductPriority::SOLD_OUT,
            ProductPriority::OUT_OF_STOCK,
            ProductPriority::COMING_SOON,
            ProductPriority::PRE_ORDER,
            ProductPriority::LIMITED,
            ProductPriority::EXCLUSIVE,
            ProductPriority::RARE,
            ProductPriority::SPECIAL,
            ProductPriority::UNIQUE,
            ProductPriority::FEATURED,
            ProductPriority::TRENDING,
            ProductPriority::LATEST,
            ProductPriority::UPDATED,
            ProductPriority::RECENT,
            ProductPriority::FRESH,
            ProductPriority::NEW_ARRIVAL,
            ProductPriority::NEW_RELEASE,
            ProductPriority::NEW,
            ProductPriority::UPCOMING,
        ];

        // Prepare the data to insert
        $data = [];
        foreach ($priorities as $priority) {
            $data[] = [
                'name' => $priority,
                'index' => ProductPriority::priorityLevel($priority),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert data into the priority table
        DB::table('priority')->insert($data);
    }
}
