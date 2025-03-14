<?php
namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Models\Marketplace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MarketController extends Controller
{
    // Add a product to the marketplace
    public function addProductToMarketplace(Request $request)
    {
        if (! Auth::check()) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'product_id'     => 'required|exists:products,id',
            'quantity'       => 'required|string',
            'unit'           => 'required|in:kg,ton,liters,pieces',
            'minimum_bid'    => 'required|numeric|min:200', // Added missing validation
            'product_status' => 'nullable|in:available,sold,pending',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 400);
        }

        $marketplace = Marketplace::create([
            'farmer_id'      => Auth::id(),
            'product_id'     => $request->product_id,
            'quantity'       => $request->quantity,
            'unit'           => $request->unit,
            'minimum_bid'    => $request->minimum_bid, // Now stored in DB
            'product_status' => $request->product_status ?? 'pending',
        ]);

        return response()->json(['status' => true, 'message' => 'Product added to marketplace', 'data' => $marketplace], 201);
    }

    // Update a marketplace product
    public function updateMarketplaceProduct(Request $request, $id)
    {
        if (! Auth::check()) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $marketplace = Marketplace::where('id', $id)->with(['farmer:id,name', 'product:id,name'])->first();

        if (! $marketplace) {
            return response()->json(['status' => false, 'message' => 'Marketplace product not found'], 404);
        }

        if ($marketplace->farmer_id !== Auth::id()) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'quantity'       => 'sometimes|required|string',
            'unit'           => 'sometimes|required|in:kg,ton,liters,pieces',
            'minimum_bid'    => 'sometimes|required|numeric|min:0', // Added validation for updates
            'product_status' => 'sometimes|required|in:available,sold,pending',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 400);
        }

        $marketplace->update($validator->validated());

        return response()->json(['status' => true, 'message' => 'Marketplace product updated', 'data' => $marketplace], 200);
    }

    // Delete a marketplace product
    public function deleteMarketplaceProduct($id)
    {
        if (! Auth::check()) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $marketplace = Marketplace::find($id);

        if (! $marketplace) {
            return response()->json(['status' => false, 'message' => 'Marketplace product not found'], 200);
        }

        if ($marketplace->farmer_id !== Auth::id()) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $marketplace->delete();

        return response()->json(['status' => true, 'message' => 'Marketplace product deleted'], 200);
    }

    // Get all marketplace products
    public function getMarketplaceProducts()
    {
        $products = Marketplace::with(['farmer:id,name', 'product:id,name'])->paginate(10);

        return response()->json([
            'status'  => $products->isNotEmpty(),
            'message' => $products->isNotEmpty() ? 'Marketplace products fetched successfully' : 'No products found',
            'data'    => $products,
        ], 200);
    }
}
