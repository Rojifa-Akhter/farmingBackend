<?php

namespace Database\Seeders;

use App\Models\Insurance;
use App\Models\User;
use App\Models\Farm;
use Illuminate\Database\Seeder;

class InsuranceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $farm = Farm::first();

        if (!$farm) {
            return;
        }

        $user = User::whereIn('role', ['farmer', 'investor'])->first();

        if (!$user) {
            return;
        }

        Insurance::create([
            'farm_id' => $farm->id,
            'user_id' => $user->id,
            'provider' => 'ABC Insurance Ltd.',
            'policy_number' => 'ABC123456789',
            'coverage_details' => 'Covers damage due to natural disasters, theft, and fire.',
            'coverage_amount' => 50000.00, // Example coverage amount
            'premium' => 1500.00, // Example premium amount
            'insurance_status' => 'active',
            'claim_status' => 'none',
        ]);

        Insurance::create([
            'farm_id' => $farm->id,
            'user_id' => $user->id,
            'provider' => 'XYZ Insurance Co.',
            'policy_number' => 'XYZ987654321',
            'coverage_details' => 'Covers damage due to flooding, hailstorms, and fire.',
            'coverage_amount' => 75000.00,
            'premium' => 2000.00,
            'insurance_status' => 'expired',
            'claim_status' => 'pending',
        ]);

        Insurance::create([
            'farm_id' => $farm->id,
            'user_id' => $user->id,
            'provider' => 'FarmSecure Insurance',
            'policy_number' => 'FS987654',
            'coverage_details' => 'Covers theft and loss of crops, fire damage.',
            'coverage_amount' => 30000.00,
            'premium' => 1000.00,
            'insurance_status' => 'claimed',
            'claim_status' => 'approved',
        ]);
    }
}
