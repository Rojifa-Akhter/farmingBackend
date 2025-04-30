<?php

namespace Database\Seeders;

use App\Models\Marketplace;
use App\Models\Product;
use App\Models\Farm;
use App\Models\User;
use Illuminate\Database\Seeder;

class MarketplaceSeeder extends Seeder
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

        $product = Product::where('farmer_id', $farmer->id)->first();

        if (!$product) {
            return;
        }

        $farm = Farm::where('farmer_id', $farmer->id)->first();

        if (!$farm) {
            return;
        }

        Marketplace::create([
            'farmer_id' => $farmer->id,
            'product_id' => $product->id,
            'farm_id' => $farm->id,
            'quantity' => '100',
            'unit' => 'kg',
            'revenue' => 500.00,
            'product_status' => 'available', 
        ]);

        Marketplace::create([
            'farmer_id' => $farmer->id,
            'product_id' => $product->id,
            'farm_id' => $farm->id,
            'quantity' => '50',
            'unit' => 'pieces',
            'revenue' => 150.00,
            'product_status' => 'pending',
        ]);

        Marketplace::create([
            'farmer_id' => $farmer->id,
            'product_id' => $product->id,
            'farm_id' => $farm->id,
            'quantity' => '200',
            'unit' => 'liters',
            'revenue' => 1000.00,
            'product_status' => 'sold',
        ]);
    }
}
