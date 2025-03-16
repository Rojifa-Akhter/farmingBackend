<?php

namespace Database\Seeders;

use App\Models\Farm;
use App\Models\FarmMonitoring;
use App\Models\User;
use Illuminate\Database\Seeder;

class FarmMonitoringSeeder extends Seeder
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

        $farm = Farm::where('farmer_id', $farmer->id)->first();

        if (!$farm) {
            return;
        }

        FarmMonitoring::create([
            'farmer_id' => $farmer->id,
            'farm_id' => $farm->id,
            'temperature' => 28.5,
            'soil_moisture' => 75.0,
            'rainfall' => 10.5,
            'yield_prediction' => 1500.00,
            'farm_status' => 'normal',
        ]);

        FarmMonitoring::create([
            'farmer_id' => $farmer->id,
            'farm_id' => $farm->id,
            'temperature' => 30.0,
            'soil_moisture' => 50.0,
            'rainfall' => 5.0,
            'yield_prediction' => 1200.00,
            'farm_status' => 'warning',
        ]);
    }
}
