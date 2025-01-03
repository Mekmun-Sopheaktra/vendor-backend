<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\Compound;
use App\Models\CompoundProduct;
use App\Models\Product;
use App\Traits\BaseApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CompoundController extends Controller
{
    use BaseApiResponse;
    //index
    public function index(Request $request)
    {
        try {
            $perPage = $request->query('per_page', env('PAGINATION_PER_PAGE', 10));
            $search = $request->query('search');
            $userId = auth()->user()->id;

            if (!$userId) {
                return $this->failed(null, 'User ID is required', 400);
            }

            // Filter compounds by user_id
            $compounds = Compound::with('products')
                ->when($search, function ($query, $search) {
                    return $query->where('title', 'like', '%' . $search . '%');
                })
                ->where('user_id', $userId)
                ->paginate($perPage);

            return $this->success($compounds, 'Compounds retrieved successfully');
        } catch (\Exception $e) {
            return $this->failed(null, 'Error', $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        $userId = auth()->user()->id;
        $vendorId = auth()->user()->vendor->id;

        try {
            // Validate the incoming data
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'slug' => 'required|string|max:255|unique:products,slug',
                'description' => 'nullable|string',
                'price' => 'required|numeric',
                'image' => 'nullable',
                'volume' => 'nullable',
                'product_code' => 'nullable|string|unique:products,product_code',
                'manufacturing_date' => 'nullable|date',
                'fragrance_family' => 'nullable|string',
                'expire_date' => 'nullable|date',
                'gender' => 'nullable|string',
                'discount' => 'nullable|numeric',
                'priority' => 'nullable|string',
                'status' => 'nullable|boolean',
                'compound_products' => 'required',  // Compound products must be an array
                'compound_products.*.product_id' => 'required|integer|exists:products,id',  // Ensure product_id is valid
            ]);

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

            // Step 2: Create the product record first and get product_id
            $product = Product::create([
                'user_id' => $userId, // Use authenticated user's ID
                'vendor_id' => $vendorId, // Use authenticated user's ID
                'title' => $validatedData['title'],
                'slug' => $validatedData['slug'],
                'description' => $validatedData['description'],
                'price' => $validatedData['price'],
                'image' => $validatedData['image'],
                'product_code' => $validatedData['product_code'],
                'gender' => $validatedData['gender'],
                'status' => $validatedData['status'],
                'is_compound_product' => true, // Mark as compound product
            ]);

            // Step 3: Create the compound record using the created product_id
            $compound = Compound::create([
                'user_id' => $userId, // Use authenticated user's ID
                'vendor_id' => $vendorId, // Use authenticated user's ID
                'title' => $validatedData['title'],
                'slug' => $validatedData['slug'],
                'image' => $validatedData['image'],
                'price' => $validatedData['price'],
                'description' => $validatedData['description'],
                'product_code' => $validatedData['product_code'],
                'gender' => $validatedData['gender'],
                'status' => $validatedData['status'],
                'product_id' => $product->id, // Reference to the created product
            ]);

            // Step 4: Validate and store data in the compound_products table
            $syncData = [];

            // Fetch all product IDs belonging to the current vendor
            $vendorProducts = Product::where('vendor_id', $vendorId)->pluck('id')->toArray();

            $compoundProducts = is_array($validatedData['compound_products']) ? $validatedData['compound_products'] : json_decode($validatedData['compound_products'], true);
            // Check that each product_id in compound_products belongs to the vendor
            foreach ($compoundProducts as $productData) {
                if (!in_array($productData['product_id'], $vendorProducts)) {
                    $product = Product::find($productData['product_id']);
                    throw new \Exception("Product ID {$product->title} does not belong to the vendor.");
                }

                // Remove inventory from sync data
                $syncData[$productData['product_id']] = [];
            }

            // Attach products to the compound using the compound_products pivot table
            $compound->products()->sync($syncData);

            DB::commit(); // Commit the transaction

            return $this->success($compound->load('products'), 'Compound created successfully');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction in case of failure
            return $this->failed(null, 'Error', $e->getMessage());
        }
    }

    //show
    public function show($id)
    {
        try {
            $vendorId = auth()->user()->vendor->id;

            // Fetch the compound record that matches the ID and belongs to the authenticated user
            $compound = Compound::with('products')
                ->where('id', $id)
                ->where('vendor_id', $vendorId) // Filter by the authenticated user's ID
                ->first();

            if (!$compound) {
                return $this->failed(null, 'Error', 'Compound not found or access denied');
            }

            return $this->success($compound, 'Compound retrieved successfully');
        } catch (\Exception $e) {
            return $this->failed(null, 'Error', $e->getMessage());
        }
    }

    //update
    public function update(Request $request, $id)
    {
        DB::beginTransaction();  // Start a database transaction
        try {
            $userId = auth()->user()->id;
            $vendorId = auth()->user()->vendor->id;

            // Find the compound by ID and ensure it belongs to the authenticated user
            $compound = Compound::where('id', $id)
                ->where('user_id', auth()->id())
                ->first();

            if (!$compound) {
                return $this->failed(null, 'Error', 'Compound not found');
            }

            $product = Product::find($compound->product_id);

            // Validate the incoming data for compound fields
            $validatedData = $request->validate([
                'title' => 'nullable|max:255',
                'slug' => 'nullable',  // Ensure unique slug but ignore current product slug
                'description' => 'nullable|string',
                'price' => 'nullable|numeric',
                'image' => 'nullable',
                'volume' => 'nullable|numeric',
                'product_code' => 'nullable',  // Ensure unique product_code but ignore current product code
                'manufacturing_date' => 'nullable|date',
                'fragrance_family' => 'nullable|string',
                'expire_date' => 'nullable|date',
                'gender' => 'nullable|string',
                'discount' => 'nullable|numeric',
                'priority' => 'nullable|string',
                'status' => 'nullable|boolean',
                'compound_products' => 'nullable',  // compound_products is optional
                'compound_products.*.product_id' => 'required|integer|exists:products,id',  // Validating product_id
            ]);

            //check if duplicate product_code and slug
            if ($validatedData['product_code'] && Product::where('product_code', $validatedData['product_code'])->where('id', '!=', $compound->product_id)->exists()) {
                return $this->failed(null, 'Error', 'Product code already exists');
            }

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

            // Update the compound itself
            $compound->update($validatedData);

            // Update the product associated with the compound
            $product->update($validatedData);

            // Handle compound products data if it's provided
            if (!empty($validatedData['compound_products'])) {
                //check if array or json
                $compoundProducts = is_array($validatedData['compound_products']) ? $validatedData['compound_products'] : json_decode($validatedData['compound_products'], true);

                $syncData = [];
                // Fetch vendor products for validation
                $vendorProducts = Product::where('vendor_id', $vendorId)->pluck('id')->toArray();

                // Check that each product_id in compound_products belongs to the vendor
                foreach ($compoundProducts as $productData) {
                    if (!in_array($productData['product_id'], $vendorProducts)) {
                        $product = Product::find($productData['product_id']);
                        throw new \Exception("Product ID {$product->title} does not belong to the vendor.");
                    }

                    // Remove inventory handling, no longer syncing inventory
                    $syncData[$productData['product_id']] = [];
                }

                // Sync the products with the compound
                $compound->products()->sync($syncData);
            }

            DB::commit();  // Commit the transaction

            // Return success response with the updated compound and related products
            return $this->success($compound->load('products'), 'Compound updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();  // Rollback transaction if an error occurs
            return $this->failed(null, 'Error', $e->getMessage());
        }
    }

    //destroy
    public function destroy($id)
    {
        DB::beginTransaction(); // Start a transaction

        try {
            $vendorId = auth()->user()->vendor->id;

            // Find the compound by ID
            $compound = Compound::where('id', $id)
                ->where('vendor_id', $vendorId)
                ->first();

            if (!$compound) {
                return $this->failed(null, 'Error', 'Compound not found');
            }

            // Find the product associated with the compound
            $product = Product::find($compound->product_id);

            // Optionally, detach associated products first (if necessary)
            $compound->products()->detach();

            // Delete the compound itself
            $compound->delete();

            // Delete the product if it exists
            if ($product) {
                $product->delete();
            }

            DB::commit(); // Commit the transaction

            return $this->success(null, 'Compound deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction if an error occurs
            return $this->failed(null, 'Error', $e->getMessage());
        }
    }

    //addProduct
    public function addProduct(Request $request, $id)
    {
        DB::beginTransaction(); // Start a transaction

        try {
            // Find the compound by ID
            $compound = Compound::where('id', $id)
                ->where('user_id', auth()->id())
                ->first();
            if (!$compound) {
                return $this->failed(null, 'Error', 'Compound not found');
            }

            // Validate the incoming request data
            $validatedData = $request->validate([
                'product_id' => 'required|integer|exists:products,id',
                'inventory' => 'required|integer|min:0',
            ]);

            // Attach the product with inventory to the compound
            $compound->products()->attach(
                $validatedData['product_id'],
                ['inventory' => $validatedData['inventory']]
            );

            DB::commit(); // Commit transaction

            // Return the updated compound with its products
            return $this->success($compound->load('products'), 'Product added to compound successfully');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on failure
            return $this->failed(null, 'Error', $e->getMessage());
        }
    }

    //removeProduct
    public function removeProduct(Request $request, $id)
    {
        DB::beginTransaction(); // Start a transaction

        try {
            // Find the compound by ID
            $compound = Compound::where('id', $id)
                ->where('user_id', auth()->id())
                ->first();
            if (!$compound) {
                return $this->failed(null, 'Error', 'Compound not found');
            }

            // Validate the incoming request data
            $validatedData = $request->validate([
                'product_id' => 'required|integer|exists:products,id',
            ]);

            // Check if the product is associated with the compound
            $isAttached = $compound->products()->where('product_id', $validatedData['product_id'])->exists();
            if (!$isAttached) {
                return $this->failed(null, 'Error', 'Product is not associated with this compound');
            }

            // Detach the product from the compound
            $compound->products()->detach($validatedData['product_id']);

            DB::commit(); // Commit transaction

            // Return the updated compound with its products
            return $this->success($compound->load('products'), 'Product removed from compound successfully');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on failure
            return $this->failed(null, 'Error', $e->getMessage());
        }
    }

    //updateProduct
    public function updateProduct(Request $request, $id)
    {
        DB::beginTransaction(); // Start a transaction

        try {
            // Find the compound by ID
            $compound = Compound::where('id', $id)
                ->where('user_id', auth()->id())
                ->first();
            if (!$compound) {
                return $this->failed(null, 'Error', 'Compound not found');
            }

            // Validate the incoming data
            $validatedData = $request->validate([
                'product_id' => 'required|integer|exists:products,id',
                'inventory' => 'required|integer|min:0',
            ]);

            $productId = $validatedData['product_id'];
            $inventory = $validatedData['inventory'];

            // Check if the product is associated with the compound
            $isAttached = $compound->products()->where('product_id', $productId)->exists();
            if (!$isAttached) {
                return $this->failed(null, 'Error', 'Product is not associated with this compound');
            }

            // Update the product inventory in the compound
            $compound->products()->updateExistingPivot($productId, ['inventory' => $inventory]);

            DB::commit(); // Commit transaction

            return $this->success($compound->load('products'), 'Product inventory updated successfully');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on failure
            return $this->failed(null, 'Error', $e->getMessage());
        }
    }

    //showProduct
}
