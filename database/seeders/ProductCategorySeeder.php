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
        $farmer = User::where('role', 'farmer')->first();

        if (!$farmer) {
            return;
        }

        ProductCategory::create([
            'farmer_id' => $farmer->id,
            'name' => 'Fruits',
            'icon' => 'farmer(1).jpg',
            'description' => 'Fresh and organic fruits harvested from the farm.',
        ]);

        ProductCategory::create([
            'farmer_id' => $farmer->id,
            'name' => 'Vegetables',
            'icon' => 'farmer(1).jpg',
            'description' => 'Organic vegetables cultivated with care.',
        ]);

        ProductCategory::create([
            'farmer_id' => $farmer->id,
            'name' => 'Dairy Products',
            'icon' => 'farmer(1).jpg',
            'description' => 'Fresh dairy products including milk, cheese, and yogurt.',
        ]);

        // Add more categories if needed
    }
}
