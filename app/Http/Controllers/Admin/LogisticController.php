<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Logistics;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class LogisticController extends Controller
{
    // Create a logistics record for an order
    public function createLogistics(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id'           => 'required|exists:orders,id',
            'transaction_id'     => 'nullable|string',
            'tracking_number'    => 'required|string|unique:logistics,tracking_number',
            'vehicle_number'     => 'nullable|string',
            'driver_name'        => 'nullable|string',
            'estimated_delivery' => 'nullable|date',
            'shipping_cost'      => 'nullable|numeric|min:0',
            'logistics_status'   => 'required|in:in-transit,delivered,failed',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }

        try {
            $logistics = Logistics::create([
                'order_id'           => $request->order_id,
                'transaction_id'     => $request->transaction_id,
                'tracking_number'    => $request->tracking_number,
                'vehicle_number'     => $request->vehicle_number,
                'driver_name'        => $request->driver_name,
                'estimated_delivery' => $request->estimated_delivery,
                'shipping_cost'      => $request->shipping_cost,
                'logistics_status'   => $request->logistics_status,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Logistics entry created successfully.',
                'data'    => $logistics,
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to create logistics entry.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // Get all logistics records (Paginated)
    public function getAllLogistics()
    {
        $user = auth()->user();  // Get authenticated user
        $logisticsQuery = Logistics::with('order:id,transaction_id,total_price');

        // Role-based access control
        if ($user->role == 'super_admin') {
            $logisticsQuery = $logisticsQuery;
        } elseif ($user->role == 'user') {
            $logisticsQuery = $logisticsQuery->whereHas('order', function ($query) use ($user) {
                $query->where('buyer_id', $user->id);
            });
        } elseif ($user->role == 'farmer') {
            $logisticsQuery = $logisticsQuery->whereHas('order.product', function ($query) use ($user) {
                $query->where('farmer_id', $user->id);
            });
        } else {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Paginate the results
        $logistics = $logisticsQuery->paginate(10);

        return response()->json([
            'status'  => $logistics->isNotEmpty(),
            'message' => $logistics->isNotEmpty() ? 'Logistics records fetched successfully' : 'No records found',
            'data'    => $logistics,
        ], 200);
    }


    // Get logistics details for a specific order
    public function getLogisticsDetails($id)
    {
        $logistics = Logistics::with('order:id,transaction_id,total_price')->find($id);

        if ($logistics) {
            return response()->json([
                'status'  => true,
                'message' => 'Logistics details fetched successfully.',
                'data'    => $logistics,
            ], 200);
        } else {
            return response()->json([
                'status'  => false,
                'message' => 'No logistics record found for this ID.',
            ], 404);
        }
    }

    // Update logistics status
    public function updateLogisticsStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'logistics_status' => 'required|in:in-transit,delivered,failed',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }

        try {
            $logistics = Logistics::findOrFail($id);
            $logistics->update(['logistics_status' => $request->logistics_status]);

            return response()->json([
                'status'  => true,
                'message' => 'Logistics status updated successfully.',
                'data'    => $logistics,
            ], 200);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'Failed to update logistics status.',
            ], 500);
        }
    }

    // Delete logistics record
    public function deleteLogistics($id)
    {
        try {
            $logistics = Logistics::findOrFail($id);
            $logistics->delete();

            return response()->json([
                'status'  => true,
                'message' => 'Logistics record deleted successfully.',
            ], 200);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'Failed to delete logistics record.',
            ], 500);
        }
    }
}
