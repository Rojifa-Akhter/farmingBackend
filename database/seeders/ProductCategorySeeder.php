<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('role', 'super_admin')->first();

        if (!$user) {
            return;
        }

        ProductCategory::create([
            'user_id' => $user->id,
            'name' => 'Fruits',
            'icon' => 'farmer(1).jpg',
            'description' => 'Fresh and organic fruits harvested from the farm.',
        ]);

        ProductCategory::create([
            'user_id' => $user->id,
            'name' => 'Vegetables',
            'icon' => 'farmer(1).jpg',
            'description' => 'Organic vegetables cultivated with care.',
        ]);

        ProductCategory::create([
            'user_id' => $user->id,
            'name' => 'Dairy Products',
            'icon' => 'farmer(1).jpg',
            'description' => 'Fresh dairy products including milk, cheese, and yogurt.',
        ]);

        // Add more categories if needed
    }
}
