<?php
namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Models\Farm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FarmController extends Controller
{
    //add farm
    public function addFarm(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'farm_name'          => 'required|string|max:255',
            'location'           => 'required|string|max:255',
            'size'               => 'nullable|numeric',
            'crop_type'          => 'required|string|max:255',
            'image'              => 'nullable|max:5',
            'video'              => 'nullable|max:5',
            'operational_costs'  => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        // Image upload
        $newImages = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imageName = time() . uniqid() . '_' . $image->getClientOriginalName();
                $image->move(public_path('uploads/farm_images'), $imageName);
                $newImages[] = $imageName;
            }
        }

        // Video upload
        $newVideos = [];
        if ($request->hasFile('videos')) {
            foreach ($request->file('videos') as $video) {
                $videoName = time() . uniqid() . '_' . $video->getClientOriginalName();
                $video->move(public_path('uploads/farm_videos'), $videoName);
                $newVideos[] = $videoName;
            }
        }

        $farm = Farm::create([
            'farmer_id'          => $request->user()->id,
            'farm_name'          => $request->farm_name,
            'location'           => $request->location,
            'size'               => $request->size,
            'crop_type'          => $request->crop_type,
            'image'              => json_encode($newImages),
            'video'              => json_encode($newVideos),
            'crop_status'        => $request->crop_status ?? 'available',
            'operational_costs'  => $request->operational_costs,
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
            'farm_name'          => 'nullable|string|max:255',
            'location'           => 'nullable|string|max:255',
            'size'               => 'nullable|numeric',
            'crop_type'          => 'nullable|string|max:255',
            'image'              => 'nullable|max:5',
            'video'              => 'nullable|max:5',
            'operational_costs'  => 'nullable|numeric',
            'crop_status'        => 'nullable|in:available,invested,harvested',
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

         //image add or update
         if ($request->hasFile('images')) {
            $existingImages = $farm->image;

            // Delete old images
            foreach ($existingImages as $image) {
                $relativePath = parse_url($image, PHP_URL_PATH);
                $relativePath = ltrim($relativePath, '/');
                // return $relativePath;
                if (! file_exists(public_path('uploads/sheet_images'))) {
                    unlink(public_path($relativePath));
                }
            }

            // Upload new images
            $newImages = [];
            foreach ($request->file('images') as $image) {
                $ImageName = time() . uniqid() . $image->getClientOriginalName();
                $image->move(public_path('uploads/sheet_images'), $ImageName);

                $newImages[] = $ImageName;
            }

            $farm->image = json_encode($newImages);
        }
        // videos update or add
        if ($request->hasFile('videos')) {
            $existingVideos = $farm->video;

            // Delete old videos
            foreach ($existingVideos as $video) {
                $relativePath = parse_url($video, PHP_URL_PATH);
                $relativePath = ltrim($relativePath, '/');
                if (! file_exists(public_path('uploads/sheet_videos'))) {
                    unlink(public_path($relativePath));
                }

            }

            // Upload new videos
            $newVideos = [];
            foreach ($request->file('videos') as $video) {
                $VideoName = time() . uniqid() . $video->getClientOriginalName();
                $video->move(public_path('uploads/sheet_videos'), $VideoName);

                $newVideos[] = $VideoName;
            }

            $farm->video = json_encode($newVideos);
        }

        $farm->update($validatedData);

        return response()->json([
            'message' => 'Farm updated successfully!',
            'data'    => $farm,
        ], 200);
    }
    //farm list for this farmer
    public function farmList()
    {
        $user = Auth::user();

        if ($user->role === 'farmer') {
            // Farmers only see their own farms
            $farm_list = Farm::with('farmer:id,name')
                ->where('farmer_id', $user->id)
                ->paginate(10);
        } elseif ($user->role === 'super_admin' || $user->role === 'investor') {
            // Super Admin and Investor can see all farms
            $farm_list = Farm::with('farmer:id,name')->paginate(10);
        } else {
            // For other roles, deny access
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized access',
                'data'    => [],
            ], 403);
        }

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
