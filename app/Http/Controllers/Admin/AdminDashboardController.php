<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Farm;
use App\Models\User;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function Analytics()
    {
        $totalUsers = User::count();
        $totalFarms = Farm::count();
        $totalInvestors = User::where('role', 'investor')->count(); // Count only investors

        return response()->json([
            'total_users' => $totalUsers,
            'total_farms' => $totalFarms,
            'total_investors' => $totalInvestors
        ]);
    }
}
