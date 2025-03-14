<?php
namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use App\Models\Investment;
use App\Models\User;
use App\Notifications\InvestmentStatusNotification;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;


class InvestmentController extends Controller
{ //add invest
    public function addInvest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'farm_id'     => 'required|exists:farms,id',
            'amount'      => 'required|numeric|min:1',

        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }

        $investment = Investment::create([
            'investor_id'   => $request->user()->id,
            'farm_id'       => $request->farm_id,
            'amount'        => $request->amount,
            'invest_status' => 'pending',
        ]);

        return response()->json([
            'status' => true, 'message' => 'Investment created successfully', 'investment' => $investment], 201);
    }

    /**
     * Update Investment Status (Approve / Reject)
     */
    public function updateStatus(Request $request, $id)
    {
        $investment = Investment::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,rejected',

        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }

        $investment                = Investment::findOrFail($id);
        $investment->invest_status = $request->status;

        if ($request->status == 'approved') {
            $investment->profit_share = 10; // 10% profit share
        } else {
            $investment->profit_share = null; // No profit share if rejected
        }

        $investment->save();

        // Notify the investor
        $investor = User::findOrFail($investment->investor_id);
        $investor->notify(new InvestmentStatusNotification($investment));

        return response()->json([
            'message'    => 'Investment status updated successfully!',
            'investment' => $investment,
        ], 200);

    }

    /**
     * Get All Investments with Relations
     */
    public function getInvestment()
    {
        $invest_list = Investment::with([
            'investor:id,name',
            'farm:id,farm_name,location,farmer_id',
            'farm.farmer:id,name',
        ])->paginate(10);

        return response()->json([
            'status'  => $invest_list->isNotEmpty(),
            'message' => $invest_list->isNotEmpty() ? 'Investment list fetched successfully!' : 'No data found',
            'data'    => $invest_list,
        ], 200);
    }

    /**
     * Get Investment Details
     */
    public function detailsInvestment($id)
    {
        $invest_details = Investment::with([
            'investor:id,name',
            'farm:id,farm_name,location,farmer_id',
            'farm.farmer:id,name',
        ])->find($id);

        if (! $invest_details) {
            return response()->json([
                'status'  => false,
                'message' => 'No data found',
                'data'    => null,
            ], 200);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Investment Details fetched successfully!',
            'data'    => $invest_details,
        ], 200);
    }

    /**
     * Delete Investment
     */
    public function deleteInvestment($id)
    {
        $invest = Investment::find($id);

        if (! $invest) {
            return response()->json(['message' => 'Investment not found!'], 200);
        }

        $invest->delete();

        return response()->json([
            'message' => 'Investment deleted successfully!',
        ], 200);
    }
}
