<?php

namespace Database\Seeders;

use App\Models\Farm;
use App\Models\User;
use Illuminate\Database\Seeder;

class FarmSeeder extends Seeder
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

        Farm::create([
            'farmer_id' => $farmer->id, // Using dynamically fetched farmer ID
            'farm_name' => 'Green Valley Farm',
            'location' => 'Dhaka, Bangladesh',
            'size' => 50.5,
            'crop_type' => 'Wheat',
            'image' => json_encode(['farmer.jpg']),
            'crop_status' => 'available',
            'operational_costs' => 10000.00,
        ]);

        Farm::create([
            'farmer_id' => $farmer->id,
            'farm_name' => 'Golden Harvest',
            'location' => 'Chattogram, Bangladesh',
            'size' => 75.3,
            'crop_type' => 'Rice',
            'image' => json_encode(['farmer.jpg']),
            'crop_status' => 'invested',
            'operational_costs' => 15000.00,
        ]);
    }
}
