<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use App\Models\Investment;
use App\Models\Order;
use App\Models\ProfitDistribution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfitDistributionController extends Controller
{
    // Fetch the profit distribution for a farm
    public function getProfitDistribution($farmId)
    {
        $farm = Farm::with('investments', 'marketplace')->find($farmId);

        if (!$farm) {
            return response()->json([
                'status' => false,
                'message' => 'Farm not found',
            ], status: 200);
        }

        // Calculate profit distribution logic (example: based on revenue from orders)
        $totalRevenue = $farm->marketplace->sum('revenue');

        // Get investments for the farm
        $investments = $farm->investments;

        foreach ($investments as $investment) {
            $profitShare = ($investment->amount / $farm->operational_costs) * $totalRevenue; // Simplified calculation
            ProfitDistribution::create([
                'investment_id' => $investment->id,
                'total_profit' => $totalRevenue,
                'investor_share' => $profitShare,
                'farmer_share' => $totalRevenue - $profitShare,
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Profit distribution calculated successfully',
        ], 200);
    }

    // Create a new profit distribution record (for testing)
    public function createProfitDistribution(Request $request)
    {
        $request->validate([
            'investment_id' => 'required|exists:investments,id',
            'total_profit' => 'required|numeric',
            'investor_share' => 'required|numeric',
            'farmer_share' => 'required|numeric',
        ]);

        $profitDistribution = ProfitDistribution::create($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Profit distribution created successfully',
            'data' => $profitDistribution,
        ], 201);
    }
}
