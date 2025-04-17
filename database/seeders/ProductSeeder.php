<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
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

         // Get any available category (created by super_admin)
         $category = ProductCategory::first();

         if (!$category) {
             return;
         }

        Product::create([
            'farmer_id' => $farmer->id,
            'category_id' => $category->id,
            'name' => 'Organic Apples',
            'description' => 'Freshly harvested organic apples from our farm.',
            'price' => 5.00,
            'harvest_date' => now()->subMonths(1),
            'image' => 'product.jpg',
        ]);

        Product::create([
            'farmer_id' => $farmer->id,
            'category_id' => $category->id,
            'name' => 'Green Lettuce',
            'description' => 'Fresh and crispy green lettuce for your salads.',
            'price' => 5.50,
            'harvest_date' => now()->subMonths(2),
            'image' => 'product.jpg',
        ]);

        Product::create([
            'farmer_id' => $farmer->id,
            'category_id' => $category->id,
            'name' => 'Free-range Eggs',
            'description' => 'Fresh eggs from our free-range chickens.',
            'price' => 10.00,
            'harvest_date' => now()->subWeeks(3),
            'image' => 'product.jpg',
        ]);

        // You can add more products as needed
    }
}
