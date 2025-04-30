<?php
namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Models\FarmMonitoring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FarmMonitorController extends Controller
{
    // Add Farm Monitoring Data
    public function addMonitorData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'farm_id'       => 'required|exists:farms,id',
            'temperature'   => 'required|numeric',
            'soil_moisture' => 'required|numeric',
            'rainfall'      => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 400);
        }

        // Determine farm status based on temperature, soil moisture, and rainfall
        $farm_status = $this->calculateFarmStatus($request->temperature, $request->soil_moisture, $request->rainfall);

        // Calculate yield prediction based on monitoring data
        $yield_prediction = $this->calculateYieldPrediction($request->temperature, $request->soil_moisture, $request->rainfall);

        try {
            $monitoring = FarmMonitoring::create([
                'farmer_id'        => Auth::id(),
                'farm_id'          => $request->farm_id,
                'temperature'      => $request->temperature,
                'soil_moisture'    => $request->soil_moisture,
                'rainfall'         => $request->rainfall,
                'farm_status'      => $farm_status,
                'yield_prediction' => $yield_prediction,
            ]);

            return response()->json(['status' => true, 'message' => 'Farm monitoring data recorded', 'data' => $monitoring]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Failed to record data: ' . $e->getMessage()], 500);
        }
    }

    // Calculate Farm Status
    private function calculateFarmStatus($temperature, $soil_moisture, $rainfall)
    {
        if ($temperature > 40 || $soil_moisture < 10 || $rainfall < 5) {
            return 'critical';
        } elseif ($temperature > 35 || $soil_moisture < 20 || $rainfall < 10) {
            return 'warning';
        }
        return 'normal';
    }

    // Calculate Yield Prediction
    private function calculateYieldPrediction($temperature, $soil_moisture, $rainfall)
    {
        // Example formula to calculate yield prediction based on factors (adjust as needed)
        $temperature_factor = 0.5;
        $moisture_factor    = 0.3;
        $rainfall_factor    = 0.2;

        // Yield prediction formula
        $yield_prediction = ($temperature * $temperature_factor) +
            ($soil_moisture * $moisture_factor) +
            ($rainfall * $rainfall_factor);

        return round($yield_prediction, 2); // Round the result to two decimal places
    }

    // Get All Monitoring Data for a Farm
    public function getMonitoring($farm_id)
    {
        $monitorings = FarmMonitoring::with('farm', 'farm.farmer:id,name')->where('farm_id', $farm_id)->paginate(10);
        return response()->json([
            'status'  => $monitorings->isNotEmpty(),
            'message' => $monitorings->isNotEmpty() ? 'Monitoring data list fetched successfully!' : 'No data found',
            'data'    => $monitorings,
        ], 200);
    }

    // Get Single Monitoring Record (With Related Farm)
    public function getMonitoringDetails($id)
    {
        $monitoring = FarmMonitoring::with('farm','farm.farmer:id,name')->find($id);

        if (! $monitoring) {
            return response()->json(['status' => false, 'message' => 'Monitoring data not found'], 404);
        }

        return response()->json(['status' => true, 'data' => $monitoring]);
    }

    // Update Monitoring Data
    public function updateMonitorData(Request $request, $id)
    {
        $monitoring = FarmMonitoring::find($id);

        if (! $monitoring) {
            return response()->json(['status' => false, 'message' => 'Monitoring data not found'], 404);
        }

        if ($monitoring->farmer_id !== Auth::id()) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'temperature'   => 'sometimes|required|numeric',
            'soil_moisture' => 'sometimes|required|numeric',
            'rainfall'      => 'sometimes|required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 400);
        }

        // Update monitoring data
        $monitoring->update($request->only(['temperature', 'soil_moisture', 'rainfall']));

        // Recalculate farm status and yield prediction
        $monitoring->farm_status      = $this->calculateFarmStatus($request->temperature, $request->soil_moisture, $request->rainfall);
        $monitoring->yield_prediction = $this->calculateYieldPrediction($request->temperature, $request->soil_moisture, $request->rainfall);

        $monitoring->save();

        return response()->json(['status' => true, 'message' => 'Monitoring data updated', 'data' => $monitoring]);
    }

    // Delete Monitoring Data
    public function deleteMonitorData($id)
    {
        $monitoring = FarmMonitoring::find($id);

        if (! $monitoring) {
            return response()->json(['status' => false, 'message' => 'Monitoring data not found'], 404);
        }

        if ($monitoring->farmer_id !== Auth::id()) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $monitoring->delete();

        return response()->json(['status' => true, 'message' => 'Monitoring data deleted']);
    }

}
