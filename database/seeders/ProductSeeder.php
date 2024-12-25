<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Product::query()->truncate();

        $products = [
            [
                'user_id' => 2,
                'vendor_id' => 1,
                'title' => 'MANGOPOP Women\'s Mock Turtle Neck Slim Fit Long Half Short Sleeve T Shirt Tight Tops Tee',
                'slug' => 'mangopop-women-mock',
                'description' => 'MANGOPOP Women\'s Mock Turtle Neck Slim Fit Long Half Short Sleeve T Shirt Tight Tops Tee',
                'price' => 19.35,
                'image' => 'https://picsum.photos/200/300',
                'volume' => 0,
                'product_code' => 'T1001',
                'manufacturing_date' => now()->subMonths(6), // example manufacturing date
                'expire_date' => now()->addMonths(6), // example expiry date
                'fragrance_family' => 'Fruity',
                'gender' => 'Women',
                'inventory' => 100,
                'view_count' => 300,
                'discount' => 0,
                'priority' => 'hot',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'vendor_id' => 1,
                'title' => 'Amazfit Band 5 Activity Fitness Tracker',
                'slug' => 'amazfit-band-5-activity-fitness-tracker',
                'description' => 'Amazfit Band 5 Activity Fitness Tracker with Alexa Built-in, 15-Day Battery Life, Blood Oxygen, Heart Rate, Sleep & Stress Monitoring, 5 ATM Water Resistant, Fitness Watch for Men Women Kids, Black',
                'price' => 120,
                'image' => 'https://picsum.photos/200/300',
                'volume' => 0,
                'product_code' => 'F1002',
                'manufacturing_date' => now()->subMonths(3),
                'expire_date' => now()->addMonths(12),
                'fragrance_family' => '',
                'gender' => 'Unisex',
                'inventory' => 30,
                'view_count' => 40,
                'discount' => 10, // 10% discount
                'priority' => 'recommended',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'vendor_id' => 1,
                'title' => 'Alpine Corporation Weather-resistant Bluetooth Solar-Powered Outdoor Wireless Rock Speaker – Set of 2, Brown',
                'slug' => 'alpine-corporation-weather-resistant',
                'description' => 'Alpine Corporation Weather-resistant Bluetooth Solar-Powered Outdoor Wireless Rock Speaker – Set of 2, Brown',
                'price' => 320,
                'image' => 'https://picsum.photos/200/300',
                'volume' => 0,
                'product_code' => 'S1003',
                'manufacturing_date' => now()->subMonths(1),
                'expire_date' => null, // no expiration
                'fragrance_family' => '',
                'gender' => 'Unisex',
                'inventory' => 30,
                'view_count' => 40,
                'discount' => 15, // 15% discount
                'priority' => 'trending',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'vendor_id' => 1,
                'title' => 'Nike model-934',
                'slug' => 'nike-model-934',
                'description' => 'Nike model-934',
                'price' => 120,
                'image' => 'https://picsum.photos/200/300',
                'volume' => 0,
                'product_code' => 'S1004',
                'manufacturing_date' => now()->subMonths(2),
                'expire_date' => null,
                'fragrance_family' => '',
                'gender' => 'Men',
                'inventory' => 10,
                'view_count' => 10,
                'discount' => 0,
                'priority' => 'discount',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'vendor_id' => 1,
                'title' => 'Nike model-934 (Different Color)',
                'slug' => 'nike-model-934-color-2',
                'description' => 'Nike model-934 (Different Color)',
                'price' => 120,
                'image' => 'https://picsum.photos/200/300',
                'volume' => 0,
                'product_code' => 'S1005',
                'manufacturing_date' => now()->subMonths(2),
                'expire_date' => null,
                'fragrance_family' => '',
                'gender' => 'Men',
                'inventory' => 10,
                'view_count' => 10,
                'discount' => 0,
                'priority' => 'new_arrival',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'vendor_id' => 1,
                'title' => 'Nike model-934 (Another Variant)',
                'slug' => 'nike-model-934-color-3',
                'description' => 'Nike model-934 (Another Variant)',
                'price' => 120,
                'image' => 'https://picsum.photos/200/300',
                'volume' => 0,
                'product_code' => 'S1006',
                'manufacturing_date' => now()->subMonths(2),
                'expire_date' => null,
                'fragrance_family' => '',
                'gender' => 'Men',
                'inventory' => 10,
                'view_count' => 10,
                'discount' => 0,
                'priority' => 'exclusive',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'vendor_id' => 1,
                'title' => 'Shirt model-131',
                'slug' => 'shirt-model-131',
                'description' => 'Shirt model-131',
                'price' => 13,
                'image' => 'https://picsum.photos/200/300',
                'volume' => 0,
                'product_code' => 'C1007',
                'manufacturing_date' => now()->subMonths(1),
                'expire_date' => null,
                'fragrance_family' => '',
                'gender' => 'Unisex',
                'inventory' => 10,
                'view_count' => 10,
                'discount' => 5, // 5% discount
                'priority' => 'upcoming',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 3,
                'vendor_id' => 2,
                'title' => 'Shirt model-132',
                'slug' => 'shirt-model-132',
                'description' => 'Shirt model-132',
                'price' => 13,
                'image' => 'https://picsum.photos/200/300',
                'volume' => 0,
                'product_code' => 'C1007',
                'manufacturing_date' => now()->subMonths(1),
                'expire_date' => null,
                'fragrance_family' => '',
                'gender' => 'Unisex',
                'inventory' => 10,
                'view_count' => 10,
                'discount' => 5, // 5% discount
                'priority' => 'upcoming',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($products as $productData) {
            Product::query()->create($productData);
            // You Can Use This Code For Use relation many to many =>
            // $categories = Category::inRandomOrder()->take(rand(1, 2))->get();

            // foreach ($categories as $category) {
            //     $product->categories()->attach($category->id);
            // }
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
