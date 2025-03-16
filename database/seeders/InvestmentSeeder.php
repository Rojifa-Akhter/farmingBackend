<?php

namespace Database\Seeders;

use App\Models\Farm;
use App\Models\Investment;
use App\Models\User;
use Illuminate\Database\Seeder;

class InvestmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $investor = User::where('role', 'investor')->first();

        if (!$investor) {
            return;
        }

        $farm = Farm::first();

        if (!$farm) {
            return;
        }

        // Create an investment for the first farm
        Investment::create([
            'investor_id' => $investor->id,
            'farm_id' => $farm->id,
            'amount' => 10000.00,
            'invest_status' => 'approved',
            'profit_share' => 30.00,
        ]);

        // You can add more investments if needed. For example:
        Investment::create([
            'investor_id' => $investor->id,
            'farm_id' => $farm->id,
            'amount' => 5000.00,
            'invest_status' => 'approved',
            'profit_share' => 20.00,
        ]);
    }
}
