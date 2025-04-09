<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function investorList(Request $request)
    {
        $investors = User::where('role', 'investor')
            ->when($request->name, fn($q) => $q->where('full_name', 'like', "%{$request->name}%"))
            ->when($request->email, fn($q) => $q->where('email', 'like', "%{$request->email}%"))
            ->when($request->user_id, fn($q) => $q->where('id', $request->user_id))
            ->get();

        return response()->json([
            'status'  => $investors->isNotEmpty(),
            'message' => $investors->isNotEmpty() ? 'Investor list fetched successfully!' : 'No investors found',
            'data'    => $investors,
        ], 200);
    }
}
