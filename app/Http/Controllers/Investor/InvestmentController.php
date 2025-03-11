<?php
namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use App\Models\Investment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Stripe\PaymentIntent;
use Stripe\Refund;
use Stripe\Stripe;

class InvestmentController extends Controller
{ //add invest
    public function addInvest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'farm_id'        => 'required|exists:farms,id',
            'amount'         => 'required|numeric|min:1',
            'payment_method' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }

        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));

            // Create a Stripe PaymentIntent
            $paymentIntent = PaymentIntent::create([
                'amount'                    => $request->amount * 100,
                'currency'                  => 'usd',
                'payment_method'            => $request->payment_method,
                'confirm'                   => true, // Confirm payment immediately
                'automatic_payment_methods' => [
                    'enabled' => true,
                    // 'allow_redirects' => 'never', // Disable redirect-based methods
                ],
            ]);

            if ($paymentIntent->status !== 'succeeded') {
                return response()->json(['status' => false, 'message' => 'Payment failed'], 400);
            }

            $investment = Investment::create([
                'investor_id'       => Auth::id(),
                'farm_id'           => $request->farm_id,
                'amount'            => $request->amount,
                'invest_status'     => 'pending', // Initially pending
                'payment_intent_id' => $paymentIntent->id,
                'payment_status'    => $paymentIntent->status,
            ]);

            return response()->json([
                'status'     => true,
                'message'    => 'Investment request submitted, payment successful',
                'investment' => $investment,
                'payment'    => $paymentIntent,
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Payment failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update Investment Status (Approve / Reject)
     */
    public function updateStatus(Request $request, $id)
    {
        $investment = Investment::findOrFail($id);

        $request->validate([
            'status' => 'required|in:approved,rejected,completed',
        ]);

        // If investment is rejected, issue a refund
        if ($request->status === 'rejected' && $investment->payment_intent_id) {
            try {
                Stripe::setApiKey(env('STRIPE_SECRET'));

                // Issue a refund
                Refund::create([
                    'payment_intent' => $investment->payment_intent_id,
                ]);

                // Update the investment status to rejected
                $investment->update(['invest_status' => 'rejected']);

                return response()->json([
                    'status'     => true,
                    'message'    => "Investment rejected and refund issued successfully.",
                    'investment' => $investment,
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'status'  => false,
                    'message' => "Failed to issue refund: " . $e->getMessage(),
                ], 500);
            }
        }

        // Approve or Complete investment
        $investment->update(['invest_status' => $request->status]);

        return response()->json([
            'status'     => true,
            'message'    => "Investment marked as {$request->status}",
            'investment' => $investment,
        ]);
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
            return response()->json(['message' => 'Investment not found!'], 400);
        }

        $invest->delete();

        return response()->json([
            'message' => 'Investment deleted successfully!',
        ], 200);
    }
}
