<?php

namespace App\Http\Controllers\vendor;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tag;
use App\Traits\BaseApiResponse;
use Illuminate\Http\Request;

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
        $products = Product::query()
            ->where('vendor_id', $vendor_id)
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
        $validatedData = $request->validate([
            'brand_id' => 'required',
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
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

        // Handle image upload
        if ($request->hasFile('image')) {
            // Generate a unique filename and store the image
            $imagePath = $request->file('image')->store('uploads/products', 'public');
            $validatedData['image'] = $imagePath;
        }

        if (!auth()->user()->vendor) {
            return $this->failed(null, 'Vendor not found', 'Vendor not found', 404);
        }
        // user_id
        $validatedData['user_id'] = auth()->user()->id;
        $validatedData['vendor_id'] = auth()->user()->vendor->id;

        // Create the product with the validated data
        $product = Product::query()->create($validatedData);


        // Attach relationships if necessary
        if ($request->has('categories')) {
            $product->categories()->attach($request->categories);
        }

        if ($request->has('tags')) {
            $product->tags()->attach($request->tags);
        }

        return $this->success($product, 'Product created successfully');
    }

    // show product
    public function show(Request $requestt)
    {

    }
}
