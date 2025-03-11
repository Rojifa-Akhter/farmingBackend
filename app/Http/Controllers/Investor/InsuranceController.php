<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Insurance;
use App\Models\Farm;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class InsuranceController extends Controller
{
    // Purchase insurance for a farm
    public function purchaseInsurance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'farm_id' => 'required|exists:farms,id',
            'provider' => 'required|string',
            'policy_number' => 'required|string',
            'coverage_details' => 'required|string',
            'coverage_amount' => 'required|numeric|min:0',
            'premium' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }

        // Ensure the user is an investor
        if (Auth::user()->role !== 'investor') {
            return response()->json(['status' => false, 'message' => 'Only investors can purchase insurance'], 403);
        }

        // Create the insurance record
        $insurance = Insurance::create([
            'farm_id' => $request->farm_id,
            'investor_id' => Auth::id(),
            'provider' => $request->provider,
            'policy_number' => $request->policy_number,
            'coverage_details' => $request->coverage_details,
            'coverage_amount' => $request->coverage_amount,
            'premium' => $request->premium,
            'insurance_status' => 'active',
            'claim_status' => 'none',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Insurance purchased successfully',
            'insurance' => $insurance,
        ]);
    }

    // View insurance details for a farm
    public function viewInsurance($farmId)
    {
        $insurance = Insurance::where('farm_id', $farmId)->first();

        if (!$insurance) {
            return response()->json(['status' => false, 'message' => 'No insurance found for this farm'], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Insurance details fetched successfully',
            'insurance' => $insurance,
        ]);
    }

    // File a claim for insurance
    public function fileClaim(Request $request, $insuranceId)
    {
        $validator = Validator::make($request->all(), [
            'claim_details' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }

        // Ensure the user is the investor who purchased the insurance
        $insurance = Insurance::findOrFail($insuranceId);

        if ($insurance->investor_id !== Auth::id()) {
            return response()->json(['status' => false, 'message' => 'You are not authorized to file a claim for this insurance'], 403);
        }

        // Update the claim status
        $insurance->update([
            'claim_status' => 'pending',
            'coverage_details' => $request->claim_details, // Store claim details
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Claim filed successfully',
            'insurance' => $insurance,
        ]);
    }
}
