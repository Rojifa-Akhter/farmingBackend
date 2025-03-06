<?php
namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Models\Farm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FarmController extends Controller
{
    //add farm
    public function addFarm(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'farm_name' => 'required|string|max:255',
            'location'  => 'required|string|max:255',
            'size'      => 'nullable|numeric',
            'crop_type' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $farm = Farm::create([
            'farmer_id'   => $request->user()->id,
            'farm_name'   => $request->farm_name,
            'location'    => $request->location,
            'size'        => $request->size,
            'crop_type'   => $request->crop_type,
            'crop_status' => $request->crop_status ?? 'available',
        ]);

        return response()->json([
            'message' => 'Farm created successfully!',
            'data'    => $farm,
        ], 201);
    }
    //update farm data
    public function updateFarm(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'farm_name'   => 'nullable|string|max:255',
            'location'    => 'nullable|string|max:255',
            'size'        => 'nullable|numeric',
            'crop_type'   => 'nullable|string|max:255',
            'crop_status' => 'nullable|in:available,invested,harvested',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $farm = Farm::find($id);

        if (! $farm) {
            return response()->json(['message' => 'Data not found!'], 400);
        }
        if ($farm->farmer_id !== auth()->id()) {
            return response()->json(['message' => 'You are not authorized to update this farm.'], 403);
        }
        $validatedData = $validator->validated();

        $farm->update($validatedData);

        return response()->json([
            'message' => 'Farm updated successfully!',
            'data'    => $farm,
        ], 200);
    }
    //farm list for this farmer
    public function farmList()
    {
        $farm_list = Farm::with('farmer:id,name')->paginate(10);

        return response()->json([
            'status'  => $farm_list->isNotEmpty(),
            'message' => $farm_list->isNotEmpty() ? 'Farm list fetched successfully!' : 'No data found',
            'data'    => $farm_list,
        ], 200);

    }
    public function farmDetails($id)
    {
        $farm_details = Farm::with('farmer:id,name')->find($id);

        if (! $farm_details) {
            return response()->json([
                'status' => false, 'message' => 'No data found', 'data' => null], 200);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Farm Details fetched successfully!',
            'data'    => $farm_details,
        ], 200);
    }
    public function deleteFarm($id)
    {
        $farm = Farm::find($id);

        if (! $farm) {
            return response()->json(['message' => 'Farm not found!'], 400);
        }

        if ($farm->farmer_id !== auth()->id()) {
            return response()->json(['message' => 'You are not authorized to delete this farm.'], 403);
        }

        $farm->delete();

        return response()->json([
            'message' => 'Farm deleted successfully!',
        ], 200);
    }

}
