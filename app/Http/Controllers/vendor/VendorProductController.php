<?php

namespace App\Http\Controllers\vendor;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tag;
use App\Traits\BaseApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class VendorProductController extends Controller
{
    use BaseApiResponse;
    //index products table and vendor table with pagination that this vendor has
    public function index()
    {
        //get vendor id from user table
        $vendor_id = auth()->user()?->vendor?->id;
        if (!$vendor_id) {
            return $this->failed(null, 'Vendor not found', 'Vendor not found', 404);
        }
        //search
        $search = request()->query('search');
        $products = Product::query()
            ->where('vendor_id', $vendor_id)
            ->where('title', 'like', '%' . $search . '%')
            ->paginate(10);

        return $this->success($products, 'Products retrieved successfully');
    }

    public function create()
    {
        return response()->json([
            'message' => 'Display product creation form.',
            'categories' => Category::all(),
            'tags' => Tag::all(),
        ]);
    }

    //store product
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'slug' => 'required|string|max:255|unique:products',
                'description' => 'nullable',
                'price' => 'required',
                'image' => 'nullable', // Validate image type and size
                'volume' => 'nullable',
                'product_code' => 'nullable',
                'manufacturing_date' => 'nullable',
                'fragrance_family' => 'nullable',
                'expire_date' => 'nullable',
                'gender' => 'nullable',
                'inventory' => 'nullable',
                'view_count' => 'nullable',
                'discount' => 'nullable',
                'priority' => 'nullable',
            ]);

            //check if title and slug is unique
            $product = Product::query()
                ->where('title', $validatedData['title'])
                ->orWhere('slug', $validatedData['slug'])
                ->first();

            if ($product) {
                return $this->failed(null, 'Product Title already exists', 'Product Title already exists', 409);
            }

            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = $image->getClientOriginalName();
                $imagePath = 'uploads/products/' . $imageName;

                // Prevent duplicate uploads using Storage
                if (!Storage::disk('public')->exists($imagePath)) {
                    $image->storeAs('uploads/products', $imageName, 'public');
                }

                $validatedData['image'] = $imagePath;
            }

            // Check if the authenticated user has a vendor
            if (!auth()->user()->vendor) {
                return $this->failed(null, 'Vendor not found', 'Vendor not found', 404);
            }

            // Add user and vendor IDs to validated data
            $validatedData['user_id'] = auth()->user()->id;
            $validatedData['vendor_id'] = auth()->user()->vendor->id;

            // Create the product
            $product = Product::query()->create($validatedData);

            // Attach relationships if necessary
            if ($request->has('categories')) {
                $product->categories()->attach($request->categories);
            }

            if ($request->has('tags')) {
                $product->tags()->attach($request->tags);
            }

            return $this->success($product, 'Product created successfully');
        } catch (\Exception $e) {
            // Handle other exceptions
            return $this->failed(null, 'An error occurred', $e->getMessage());
        }
    }

    // show product
    public function show(Request $requestt)
    {
        //get vendor id from user table
        $vendor_id = auth()->user()?->vendor?->id;
        if (!$vendor_id) {
            return $this->failed(null, 'Vendor not found', 'Vendor not found', 404);
        }
        $product = Product::query()
            ->where('vendor_id', $vendor_id)
            ->where('id', $requestt->product)
            ->first();

        if (!$product) {
            return $this->failed(null, 'Product not found', 'Product not found', 404);
        }

        return $this->success($product, 'Product retrieved successfully');
    }

    //update product
    public function update(Request $request, Product $product)
    {
        try {
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'slug' => 'required|string|max:255',
                'description' => 'nullable',
                'price' => 'required',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validate image type and size
                'volume' => 'nullable',
                'product_code' => 'nullable',
                'manufacturing_date' => 'nullable',
                'fragrance_family' => 'nullable',
                'expire_date' => 'nullable',
                'gender' => 'nullable',
                'inventory' => 'nullable',
                'view_count' => 'nullable',
                'discount' => 'nullable',
                'priority' => 'nullable',
            ]);

            $vendor_id = auth()->user()?->vendor?->id;
            if (!$vendor_id) {
                return $this->failed(null, 'Vendor not found', 'Vendor not found', 404);
            }

            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = $image->getClientOriginalName();
                $imagePath = 'uploads/products/' . $imageName;

                // Prevent duplicate uploads using Storage
                if (!Storage::disk('public')->exists($imagePath)) {
                    // Store the image if it does not exist
                    $image->storeAs('uploads/products', $imageName, 'public');
                    $validatedData['image'] = $imagePath;
                } else {
                    // Use the existing image path
                    $validatedData['image'] = $imagePath;
                }
            }
            $validatedData['user_id'] = auth()->user()->id;
            $validatedData['vendor_id'] = auth()->user()->vendor->id;

            // Update the product with the validated data
            $product->update($validatedData);

            // Attach relationships if necessary
            if ($request->has('categories')) {
                $product->categories()->sync($request->categories);
            }

            if ($request->has('tags')) {
                $product->tags()->sync($request->tags);
            }

            return $this->success($product, 'Product updated successfully');
        } catch (Exception $e) {
            return $this->failed($e->getMessage(), 'Error', 'Product updated Error from server');
        }
    }

    //destroy
    public function destroy(Product $product)
    {
        try {
            $product->delete();
            return $this->success(null, 'Product deleted successfully');
        } catch (Exception $e) {
            return $this->failed($e->getMessage(), 'Error', 'Product delete Error from server');
        }
    }

    //get product for select Options
    public function getOptions()
    {
        logger(auth()->user()?->vendor?->id);
        $products = Product::query()
            ->where('is_compound_product', false)
            ->where('vendor_id', auth()->user()?->vendor?->id)
            ->get(['id', 'title']);

        return $this->success($products, 'Products retrieved successfully');
    }
}
