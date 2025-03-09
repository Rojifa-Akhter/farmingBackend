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
        $validator = Validator::make($request->all(), [
            'product_id'     => 'required|exists:products,id',
            'quantity'       => 'required|string',
            'unit'           => 'required|in:kg,ton,liters,pieces',
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
            'product_status' => $request->product_status ?? 'pending',
        ]);

        return response()->json(['status' => true, 'message' => 'Product added to marketplace', 'data' => $marketplace]);
    }
    // Update a marketplace product
    public function updateMarketplaceProduct(Request $request, $id)
    {
        $marketplace = Marketplace::with('farmer:id,name,image', 'product:id,name')->find($id);

        if (! $marketplace) {
            return response()->json(['status' => false, 'message' => 'Marketplace product not found'], 404);
        }

        if ($marketplace->farmer_id !== Auth::id()) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'quantity'       => 'sometimes|required|string',
            'unit'           => 'sometimes|required|in:kg,ton,liters,pieces',
            'product_status' => 'sometimes|required|in:available,sold,pending',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 400);
        }

        $marketplace->update($validator->validated());

        return response()->json(['status' => true, 'message' => 'Marketplace product updated', 'data' => $marketplace]);
    }
    // Delete a marketplace product
    public function deleteMarketplaceProduct($id)
    {
        $marketplace = Marketplace::find($id);

        if (! $marketplace) {
            return response()->json(['status' => false, 'message' => 'Marketplace product not found'], 200);
        }

        if ($marketplace->farmer_id !== Auth::id()) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $marketplace->delete();

        return response()->json(['status' => true, 'message' => 'Marketplace product deleted']);
    }

    //Get all marketplace products
    public function getMarketplaceProducts()
    {
        $products = Marketplace::with(['farmer:id,name,image', 'product:id,name'])->paginate(10);
        return response()->json(['status' => true, 'data' => $products]);
    }

}
