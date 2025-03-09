<?php
namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use App\Models\Investment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class InvestmentController extends Controller
{
    //add farm
    public function addInvest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'farm_id' => 'required|exists:farms,id',
            'amount'  => 'required|numeric|min:1',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'false', 'message' => $validator->errors()], 422);
        }

        $investment = Investment::create([
            'investor_id' => Auth::id(),
            'farm_id'     => $request->farm_id,
            'amount'      => $request->amount,
            // 'invest_status' => 'pending',
        ]);

        return response()->json(['status' => 'true', 'message' => 'Investment request submitted', 'investment' => $investment]);
    }
    // Farm Owner Approves Investment
    public function updateStatus(Request $request, $id)
    {
        $investment = Investment::findOrFail($id);

        $request->validate([
            'status' => 'required|in:approved,rejected,completed',
        ]);

        $status = $request->status;



        // Update the status
        $investment->update(['invest_status' => $status]);

        return response()->json(['status' => true, 'message' => "Investment marked as {$status}", 'investment' => $investment]);
    }
    //get list all investment
    public function getInvestment()
    {
        $invest_list = Investment::with('investor:id,name', 'farm:id,farm_name,location,farmer_id', 'farm.farmer:id,name')->paginate(10);

        return response()->json([
            'status'  => $invest_list->isNotEmpty(),
            'message' => $invest_list->isNotEmpty() ? 'Farm list fetched successfully!' : 'No data found',
            'data'    => $invest_list,
        ], 200);

    }
    public function detailsInvestment($id)
    {
        $invest_details = Investment::with('investor:id,name', 'farm:id,farm_name,location,farmer_id', 'farm.farmer:id,name')->find($id);

        if (! $invest_details) {
            return response()->json([
                'status' => false, 'message' => 'No data found', 'data' => null], 200);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Investment Details fetched successfully!',
            'data'    => $invest_details,
        ], 200);
    }
    public function deleteInvestment($id)
    {
        $invest = Investment::find($id);

        if (! $invest) {
            return response()->json(['message' => 'Farm not found!'], 400);
        }

        $invest->delete();

        return response()->json([
            'message' => 'Investment deleted successfully!',
        ], 200);
    }

}
