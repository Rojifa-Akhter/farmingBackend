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
            'name' => 'required|string|max:255',
            'icon' => 'required',
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
            'farmer_id' => Auth::id(),
            'name'      => $request->name,
            'icon'      => $new_name,
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

        if ($category->farmer_id !== Auth::id()) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'icon' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 400);
        }

        // Update
        if ($request->filled('name')) {
            $category->name = $request->name;
        }

        // Check if a new icon is uploaded
        if ($request->hasFile('icon')) {
            $existingIcon = $category->icon;

            if (! empty($existingIcon)) {
                $oldIconPath = public_path('uploads/product_icons/' . $existingIcon);
                if (file_exists($oldIconPath) && is_file($oldIconPath)) {
                    unlink($oldIconPath);
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

        if ($category->farmer_id !== Auth::id()) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $category->delete();

        return response()->json(['status' => true, 'message' => 'Category deleted']);
    }
    // Get All Categories
    public function getCategories()
    {
        $categories = ProductCategory::with('farmer:id,name')->get();
        return response()->json(['status' => true, 'data' => $categories]);
    }

    // Get Single Category
    public function detailsCategory($id)
    {
        $category = ProductCategory::with('farmer:id,name')->find($id);

        if (! $category) {
            return response()->json(['status' => false, 'message' => 'Category not found'], 404);
        }

        return response()->json(['status' => true, 'data' => $category]);
    }
}
