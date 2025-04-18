<?php
namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProductCategoryController extends Controller
{
    // Add a Product Category
    public function addCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'icon'        => 'required',
            'description' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 400);
        }

        $new_name = null;
        if ($request->has('icon')) {
            $icon      = $request->file('icon');
            $extension = $icon->getClientOriginalExtension();
            $new_name  = time() . '.' . $extension;
            $path      = $icon->move(public_path('uploads/product_icons'), $new_name);
        }
        $category = ProductCategory::create([
            'user_id' => Auth::id(),
            'name'      => $request->name,
            'icon'      => $new_name,
            'description'      => $request->description,
        ]);

        return response()->json(['status' => true, 'message' => 'Category added successfully', 'data' => $category]);
    }

    // Update Category
    public function updateCategory(Request $request, $id)
    {
        $category = ProductCategory::find($id);

        if (! $category) {
            return response()->json(['status' => false, 'message' => 'Category not found'], 422);
        }

        if ($category->user_id !== Auth::id()) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name'        => 'nullable|string|max:255',
            'icon'        => 'nullable',
            'description' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 400);
        }

        // Update
        $validatedData = $validator->validated();

        $category->name        = $validatedData['name'] ?? $category->name;
        $category->description = $validatedData['description'] ?? $category->description;

        // Check if a new icon is uploaded
        if ($request->hasFile('icon')) {
            $existingIcon = $category->icon;

            if ($existingIcon) {
                $oldImage = parse_url($existingIcon);
                $filePath = ltrim($oldImage['path'], '/');
                if (file_exists($filePath)) {
                    unlink($filePath); // Delete the existing image
                }
            }

            $icon        = $request->file('icon');
            $newIconName = time() . '.' . $icon->getClientOriginalExtension();
            $icon->move(public_path('uploads/product_icons'), $newIconName);

            $category->icon = $newIconName;
        }

        $category->save();

        return response()->json(['status' => true, 'message' => 'Category updated', 'data' => $category]);
    }

    // Delete Category
    public function deleteCategory($id)
    {
        $category = ProductCategory::find($id);

        if (! $category) {
            return response()->json(['status' => false, 'message' => 'Category not found'], 404);
        }

        if ($category->user_id !== Auth::id()) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $category->delete();

        return response()->json(['status' => true, 'message' => 'Category deleted']);
    }
    // Get All Categories
    public function getCategories()
    {
        $categories = ProductCategory::paginate(10);
        return response()->json([
            'status'  => $categories->isNotEmpty(),
            'message' => $categories->isNotEmpty() ? 'Product Category list fetched successfully!' : 'No data found',
            'data'    => $categories,
        ], 200);

    }

    // Get Single Category
    public function detailsCategory($id)
    {
        $category = ProductCategory::find($id);

        if (! $category) {
            return response()->json(['status' => false, 'message' => 'Category not found'], 404);
        }

        return response()->json(['status' => true, 'data' => $category]);
    }
}
