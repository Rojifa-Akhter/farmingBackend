<?php
namespace App\Http\Controllers;

use App\Models\Product;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class OrderController extends Controller
{
    public function createPaymentIntent(Request $request)
    {
        // Validate the input
        $validator = Validator::make($request->all(), [
            'product_id'     => 'required|exists:products,id',
            'payment_method' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }

        // Set Stripe API Key
        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            // Find the product
            $product = Product::find($request->product_id);
            if (! $product) {
                return response()->json(['status' => false, 'message' => 'Product not found.'], 404);
            }

            // Get the price of the product
            $amount = $product->price;

            $paymentIntent = PaymentIntent::create([
                'amount'         => $amount * 100, // Convert to cents
                'currency'       => 'usd',
                'payment_method' => $request->payment_method,
                'confirm'        => false,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Payment intent created successfully.',
                'data'    => $paymentIntent,
            ], 200);

        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'Payment intent creation failed.',
            ], 500);
        }
    }
    //create order
    

}
