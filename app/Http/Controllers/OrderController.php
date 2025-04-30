<?php
namespace App\Http\Controllers;

use App\Models\Order;
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
        $validator = Validator::make($request->all(), [
            'product_id'     => 'required|exists:products,id',
            'payment_method' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }

        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            $product = Product::find($request->product_id);
            if (! $product) {
                return response()->json(['status' => false, 'message' => 'Product not found.'], 404);
            }

            $amount = $product->price * 100; // Convert to cents

            $paymentIntent = PaymentIntent::create([
                'amount'         => $amount,
                'currency'       => 'usd',
                'payment_method' => $request->payment_method,
                // 'confirmation_method' => 'manual',
                'confirm'        => false,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Payment intent created successfully.',
                'data'    => $paymentIntent,
            ], 200);

        } catch (Exception $e) {
            Log::error('Stripe Payment Intent Error: ' . $e->getMessage());

            return response()->json([
                'status'  => false,
                'message' => 'Payment intent creation failed.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    //create order

    public function createOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id'     => 'required|exists:products,id',
            'transaction_id' => 'nullable|string',
            'quantity'       => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }

        try {
            $product = Product::findOrFail($request->product_id);

            // Calculate total price
            $total_price = $product->price * $request->quantity;

            $order = Order::create([
                'buyer_id'       => auth()->id(),
                'farmer_id'      => $product->farmer_id, // Assuming `farmer_id` exists in `products`
                'product_id'     => $request->product_id,
                'transaction_id' => $request->transaction_id,
                'quantity'       => $request->quantity,
                'total_price'    => $total_price,
                'payment_method' => $request->payment_method,
                'order_status'   => 'pending',
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Order created successfully.',
                'data'    => $order,
            ], 200);

        } catch (Exception $e) {
            Log::error($e->getMessage());

            return response()->json([
                'status'  => false,
                'message' => 'Failed to create order.',
            ], 500);
        }
    }
    public function updateOrder(Request $request, $id)
    {
        // Validate the status input
        $validator = Validator::make($request->all(), [
            'order_status' => 'required|in:pending,shipped,delivered,cancelled', // Ensure the status is one of the predefined statuses
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }

        try {
            // Find the order by ID
            $order = Order::findOrFail($id);

            // Update the order status
            $order->order_status = $request->order_status;
            $order->save();

            return response()->json([
                'status'  => true,
                'message' => 'Order status updated successfully.',
                'data'    => $order,
            ], 200);

        } catch (Exception $e) {
            Log::error('Order Update Error: ' . $e->getMessage());

            return response()->json([
                'status'  => false,
                'message' => 'Failed to update order status.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function orderList()
    {
        if (auth()->user()->role == 'super_admin') {
            $orders = Order::with(['buyer:id,name', 'farmer:id,name', 'product:id,name,price'])->paginate(10);
        }
        // User
        elseif (auth()->user()->role == 'user') {
            $orders = Order::with(['buyer:id,name', 'farmer:id,name', 'product:id,name,price'])
                ->where('buyer_id', auth()->id())
                ->paginate(10);
        }
        // User
        elseif (auth()->user()->role == 'investor') {
            $orders = Order::with(['buyer:id,name', 'farmer:id,name', 'product:id,name,price'])
                ->where('buyer_id', auth()->id())
                ->paginate(10);
        }
        // Farmer
        elseif (auth()->user()->role == 'farmer') {
            $orders = Order::with(['buyer:id,name', 'farmer:id,name', 'product:id,name,price'])
                ->where('farmer_id', auth()->id())
                ->paginate(10);
        } else {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'status'  => $orders->isNotEmpty(),
            'message' => $orders->isNotEmpty() ? 'Orders fetched successfully' : 'No orders found',
            'data'    => $orders,
        ], 200);
    }



    public function orderDetails($id)
    {
        $order = Order::with(['buyer:id,name', 'farmer:id,name', 'product:id,name,price'])->find($id);

        if ($order) {
            return response()->json([
                'status'  => true,
                'message' => 'Order details fetched successfully!',
                'data'    => $order,
            ], 200);
        } else {
            return response()->json([
                'status'  => false,
                'message' => 'Order not found',
                'data'    => null,
            ], 200);
        }
    }

}
