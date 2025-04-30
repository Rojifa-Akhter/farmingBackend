<?php
namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use App\Models\Farm;
use App\Models\Insurance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class InsuranceController extends Controller
{
    // Add Insurance Policy
    public function addInsurance(Request $request)
    {
        if (! Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'farm_id'          => 'nullable|exists:farms,id',
            'provider'         => 'required|string|max:255',
            'policy_number'    => 'required|string|unique:insurances,policy_number',
            'coverage_details' => 'required|string',
            'coverage_amount'  => 'required|numeric|min:1000',
            'premium'          => 'required|numeric|min:50',
            'insurance_status' => 'nullable|in:active,expired,claimed',
            'claim_status'     => 'nullable|in:none,pending,approved,rejected',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }

        // If the user is a farmer, ensure the farm belongs to them
        if ($user->role == 'farmer') {
            // Check if farm_id is provided
            if (! $request->has('farm_id')) {
                return response()->json(['message' => 'Farm ID is required for farmers.'], 400);
            }

            $farm = Farm::find($request->farm_id);

            // Check if the farm exists
            if (! $farm) {
                return response()->json(['message' => 'Farm not found.'], 200);
            }

            // Check if the farm belongs to the authenticated farmer
            if ($farm->farmer_id !== $user->id) {
                return response()->json([
                    'message'                 => 'You can only add insurance for your own farm.',
                    'farm_farmer_id'          => $farm->farmer_id,
                    'authenticated_farmer_id' => $user->id,
                ], 403);
            }
        }

        $insurance = Insurance::create(array_merge($validator->validated(), [
            'user_id' => $user->id,
        ]));

        return response()->json([
            'status'=>'true',
            'message' => 'Insurance policy added successfully!',
            'data'    => $insurance,
        ], 201);
    }

    // Update Insurance Policy
    public function updateInsurance(Request $request, $id)
    {
        $insurance = Insurance::find($id);

        if (! $insurance) {
            return response()->json(['status' => false, 'message' => 'Insurance not found'], 200);
        }

        $user = Auth::user();

        // If the user is a farmer, ensure the insurance policy belongs to their farm
        if ($user->role == 'farmer') {
            $farm = Farm::where('id', $insurance->farm_id)
                ->where('farmer_id', $user->id)
                ->first();

            if (! $farm) {
                return response()->json([
                    'status'  => false,
                    'message' => 'You can only update insurance for your own farm.',
                ], 403);
            }
        }

        $validator = Validator::make($request->all(), [
            'coverage_amount'  => 'sometimes|required|numeric|min:1000',
            'premium'          => 'sometimes|required|numeric|min:50',
            'insurance_status' => 'sometimes|required|in:active,expired,claimed',
            'claim_status'     => 'sometimes|required|in:none,pending,approved,rejected',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 400);
        }

        $insurance->update($validator->validated());

        return response()->json([
            'status'  => true,
            'message' => 'Insurance updated successfully',
            'data'    => $insurance,
        ]);
    }

    // Get Insurance Details
    public function getInsurance($id)
    {
        $insurance = Insurance::with(['farm', 'investor'])->find($id);

        if (! $insurance) {
            return response()->json(['message' => 'Insurance policy not found.'], 404);
        }

        return response()->json([
            'data' => $insurance,
        ], 200);
    }

    // Delete Insurance Policy
    public function deleteInsurance($id)
    {
        $insurance = Insurance::find($id);

        if (! $insurance) {
            return response()->json(['status' => false, 'message' => 'Insurance not found'], 200);
        }

        if ($insurance->user_id !== Auth::id()) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $insurance->delete();

        return response()->json(['status' => true, 'message' => 'Insurance deleted successfully']);
    }
    //insurance list
    public function insuranceList()
    {
        $user = Auth::user();

        if ($user->role === 'super_admin') {
            $insurances = Insurance::with('user:id,name', 'farm:id,farm_name')->paginate(10);
        } elseif ($user->role === 'farmer') {
            // Farmers should only see insurances for their own farms
            $insurances = Insurance::whereHas('farm', function ($query) use ($user) {
                $query->where('farmer_id', $user->id); //  Corrected here
            })->with('user:id,name', 'farm:id,farm_name')->paginate(10);
        } elseif ($user->role === 'investor') {
            // Investors should only see insurances where they invested
            $insurances = Insurance::where('user_id', $user->id)
                ->with('user:id,name', 'farm:id,farm_name')
                ->paginate(10);
        } else {
            // Unauthorized access
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized access',
                'data'    => [],
            ], 403);
        }

        return response()->json([
            'status'  => $insurances->isNotEmpty(),
            'message' => $insurances->isNotEmpty() ? 'Insurance list fetched successfully!' : 'No data found',
            'data'    => $insurances,
        ], 200);
    }

    //insurance details
    public function insuranceDetails($id)
    {
        $insurance = Insurance::with('user:id,name', 'farm:id,farm_name')->find($id);

        if (! $insurance) {
            return response()->json(['status' => false, 'message' => 'Insurance not found'], 404);
        }

        return response()->json(['status' => true, 'data' => $insurance]);
    }
}
