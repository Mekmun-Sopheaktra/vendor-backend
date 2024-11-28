<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([
            CategoriesSeeder::class,
            BannerSeeder::class,
            ProductSeeder::class,
            UserSeeder::class,
            NotificationSeeder::class,
            Tags::class,
            PrioritySeeder::class,
        ]);

        User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@shop.com',
            'password' => 'admin_admin',
            'is_superuser' => 1,
            'is_vendor' => 0,
        ]);

        //create vendor account
        User::factory()->create([
            'name' => 'vendor',
            'email' => 'vendor@shop.com',
            'password' => 'vendor_vendor',
            'is_superuser' => 0,
            'is_vendor' => 1,
        ]);
    }
}
