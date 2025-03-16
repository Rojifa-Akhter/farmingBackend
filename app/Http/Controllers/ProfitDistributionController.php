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
    public function getProfitDistribution($farmId)
    {
        $farm = Farm::with('investments', 'marketplaces')->find($farmId);

        if (!$farm) {
            return response()->json([
                'status' => false,
                'message' => 'Farm not found',
            ], 404);
        }

        // Step 1: Get total revenue from products sold in the marketplace
        $totalRevenue = $farm->marketplaces->sum('revenue');

        // Step 2: Subtract operational costs to get the actual profit
        $totalProfit = $totalRevenue - $farm->operational_costs;

        if ($totalProfit <= 0) {
            return response()->json([
                'status' => false,
                'message' => 'No profit available after costs.',
            ], 200);
        }

        // Step 3: Calculate profit shares (30% Investor, 70% Farmer)
        $investorShare = $totalProfit * 0.30;
        $farmerShare = $totalProfit * 0.70;

        // Step 4: Distribute profit among investors
        foreach ($farm->investments as $investment) {
            ProfitDistribution::create([
                'investment_id' => $investment->id,
                'product_id' => null, // Optional: If tracking product-based profits
                'total_profit' => $totalProfit,
                'investor_share' => $investorShare,
                'farmer_share' => $farmerShare,
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Profit distribution completed successfully.',
            'total_profit' => $totalProfit,
            'investor_share' => $investorShare,
            'farmer_share' => $farmerShare,
        ], 200);
    }
}
