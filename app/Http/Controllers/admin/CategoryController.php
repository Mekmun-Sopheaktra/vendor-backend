<?php

namespace App\Http\Controllers\admin;

use App\Constants\ProductPriority;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Traits\BaseApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    use BaseApiResponse;

    public function index(Request $request)
    {
        $perPage = $request->query('per_page', env('PAGINATION_PER_PAGE', 15));
        $search = $request->query('search');

        $categories = Category::query()
            ->where('name', 'like', "%$search%")
            ->paginate($perPage);
        return $this->success($categories);
    }

    // Show category and its products
    public function show($id, Request $request)
    {
        $perPage = $request->query('per_page', env('PAGINATION_PER_PAGE', 15));

        $category = Category::find($id);
        if (!$category) {
            return $this->error('Category not found', 404);
        }

        $products = $category->products()->paginate($perPage);

        return $this->success([
            'category' => $category,
            'products' => $products,
        ]);
    }

    // Store a new category
    public function store(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validate image type and size
            'description' => 'nullable|string',
            'parent' => 'nullable|string',
        ]);

        // Handle the file upload for 'icon'
        $iconPath = null;
        if ($request->hasFile('icon')) {
            $iconPath = $request->file('icon')->store('icons', 'public'); // Save image in 'storage/app/public/icons'
        }

        // Construct the full URL for the icon
        $fullIconUrl = $iconPath ? asset('storage/' . $iconPath) : null; // Generate full URL if icon exists

        // Create a new category with the icon path (or null if no icon was uploaded)
        $category = Category::create([
            'name' => $request->input('name'),
            'icon' => $fullIconUrl, // Save the full URL path in the database
            'description' => $request->input('description'),
            'parent' => $request->input('parent'),
        ]);

        return $this->success($category, 'Category created successfully');
    }

    //update
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable',
            'description' => 'nullable|string',
            'parent' => 'nullable|string',
        ]);

        // Find the category to update
        $category = Category::find($id);
        if (!$category) {
            return $this->failed(null, 'Category not found', 404);
        }

        $iconPath = $category->icon;
        if ($request->hasFile('icon')) {
            // Delete the old image if it exists
            if ($iconPath) {
                Storage::disk('public')->delete($iconPath);
            }
            // Store the new icon and save the relative path
            $iconPath = $request->file('icon')->store('icons', 'public');
        }

        // Update the category with the correct icon path
        $category->update([
            'name' => $request->input('name'),
            'icon' => $iconPath, // Store relative path
            'description' => $request->input('description'),
            'parent' => $request->input('parent'),
        ]);

        // Return a success response
        return $this->success($category, 'Category updated successfully');
    }

    // Delete a category
    public function destroy($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return $this->failed(null ,'Category not found', 404);
        }

        // Check if the category has associated products
        if ($category->products()->count() > 0) {
            return $this->failed(null,'Category cannot be deleted because it has associated products', 400);
        }

        // Delete the category
        $category->delete();

        return $this->success(null, 'Category deleted successfully');
    }
}

