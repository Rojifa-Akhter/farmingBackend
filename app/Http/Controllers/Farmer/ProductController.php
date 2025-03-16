<?php
namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function addProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id'  => 'required|exists:product_categories,id',
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string',
            'price'        => 'required|numeric|min:0',
            'harvest_date' => 'required|date',
            'image'        => 'nullable|image',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 400);
        }

        $new_name = null;
        if ($request->has('image')) {
            $image     = $request->file('image');
            $extension = $image->getClientOriginalExtension();
            $new_name  = time() . '.' . $extension;
            $path      = $image->move(public_path('uploads/product_images'), $new_name);
        }
        $product = Product::create([
            'farmer_id'    => Auth::id(),
            'category_id'  => $request->category_id,
            'name'         => $request->name,
            'description'  => $request->description,
            'price'        => $request->price,
            'harvest_date' => $request->harvest_date,
            'image'        => $new_name,
        ]);
        $product->save();

        return response()->json(['status' => true, 'message' => 'Product created successfully', 'data' => $product]);
    }
    //Update a product
    public function updateProduct(Request $request, $id)
    {
        $product = Product::find($id);

        if (! $product) {
            return response()->json(['status' => false, 'message' => 'Product not found'], 422);
        }

        if ($product->farmer_id !== Auth::id()) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'category_id'  => 'nullable|exists:product_categories,id',
            'name'         => 'nullable|string|max:255',
            'description'  => 'nullable|string',
            'price'        => 'nullable|numeric|min:0',
            'harvest_date' => 'nullable',
            'image'        => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 400);
        }

        // Update non-image fields
        $validatedData = $validator->validated();

        $product->category_id  = $validatedData['category_id'] ?? $product->category_id;
        $product->name         = $validatedData['name'] ?? $product->name;
        $product->description  = $validatedData['description'] ?? $product->description;
        $product->price        = $validatedData['price'] ?? $product->price;
        $product->harvest_date = $validatedData['harvest_date'] ?? $product->harvest_date;

        // Handle image upload
        if ($request->hasFile('image')) {
            $existingImage = $product->image;

            if ($existingImage) {
                $oldImagePath = public_path('uploads/product_images/' . $existingImage);
                if (file_exists($oldImagePath) && is_file($oldImagePath)) {
                    unlink($oldImagePath); // Delete the existing image
                }
            }

            $image        = $request->file('image');
            $newImageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/product_images'), $newImageName);

            $product->image = $newImageName;
        }

        $product->save();

        return response()->json(['status' => true, 'message' => 'Product updated successfully', 'data' => $product]);
    }
    public function deleteProduct($id)
    {
        $product = Product::find($id);

        if (! $product) {
            return response()->json(['status' => false, 'message' => 'Product not found'], 404);
        }

        if ($product->farmer_id !== Auth::id()) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($product->image) {
            $oldImagePath = public_path('uploads/products/' . $product->image);
            if (file_exists($oldImagePath) && is_file($oldImagePath)) {
                unlink($oldImagePath);
            }
        }

        $product->delete();

        return response()->json(['status' => true, 'message' => 'Product deleted successfully']);
    }

    // Get all products
    public function getProduct(Request $request)
    {
        $products = Product::with(['farmer:id,name', 'category:id,name,icon'])
                            ->when($request->name, function($query, $name) {
                                return $query->where('name', 'like', '%' . $name . '%');
                            })
                            ->when($request->id, function($query, $id) {
                                return $query->where('id', $id);
                            })
                            ->paginate(10);

        return response()->json([
            'status'  => $products->isNotEmpty(),
            'message' => $products->isNotEmpty() ? 'Product list fetched successfully!' : 'No data found',
            'data'    => $products,
        ], 200);
    }


    //Get a single product
    public function detailsProduct($id)
    {
        $product = Product::with(['farmer:id,name', 'category:id,name,icon'])->find($id);

        if ($product) {
            return response()->json([
                'status'  => true,
                'message' => 'Product fetched successfully!',
                'data'    => $product,
            ], 200);
        } else {
            return response()->json([
                'status'  => false,
                'message' => 'No data found',
                'data'    => null,
            ], 200);
        }
    }

}
