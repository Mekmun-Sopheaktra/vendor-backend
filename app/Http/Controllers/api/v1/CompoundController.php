<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\Compound;
use App\Models\CompoundProduct;
use App\Models\Product;
use App\Traits\BaseApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompoundController extends Controller
{
    use BaseApiResponse;
    //index
    public function index(Request $request)
    {
        try {
            $perPage = $request->query('per_page', env('PAGINATION_PER_PAGE', 10));
            $userId = auth()->id(); // Assuming user_id is passed as a query parameter

            if (!$userId) {
                return $this->failed(null, 'User ID is required', 400);
            }

            // Filter compounds by user_id
            $compounds = Compound::with('products')
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
                'image' => 'nullable|string',
                'volume' => 'nullable',
                'product_code' => 'nullable|string',
                'manufacturing_date' => 'nullable|date',
                'fragrance_family' => 'nullable|string',
                'expire_date' => 'nullable|date',
                'gender' => 'nullable|string',
                'discount' => 'nullable|numeric',
                'priority' => 'nullable|string',
                'compound_products' => 'required|array',  // Compound products must be an array
                'compound_products.*.product_id' => 'required|integer|exists:products,id',  // Ensure product_id is valid
            ]);

            // Step 2: Create the product record first and get product_id
            $product = Product::create([
                'user_id' => $userId, // Use authenticated user's ID
                'vendor_id' => $vendorId, // Use authenticated user's ID
                'title' => $validatedData['title'],
                'slug' => $validatedData['slug'],
                'description' => $validatedData['description'],
                'price' => $validatedData['price'],
                'image' => $validatedData['image'],
                'volume' => $validatedData['volume'],
                'product_code' => $validatedData['product_code'],
                'manufacturing_date' => $validatedData['manufacturing_date'],
                'fragrance_family' => $validatedData['fragrance_family'],
                'expire_date' => $validatedData['expire_date'],
                'gender' => $validatedData['gender'],
                'discount' => $validatedData['discount'],
                'priority' => $validatedData['priority'],
                'is_compound_product' => true, // Mark as compound product
            ]);

            // Step 3: Create the compound record using the created product_id
            $compound = Compound::create([
                'user_id' => $userId, // Use authenticated user's ID
                'vendor_id' => $vendorId, // Use authenticated user's ID
                'title' => $validatedData['title'],
                'description' => $validatedData['description'],
                'price' => $validatedData['price'],
                'product_id' => $product->id, // Reference to the created product
            ]);

            // Step 4: Validate and store data in the compound_products table
            $syncData = [];

            // Fetch all product IDs belonging to the current vendor
            $vendorProducts = Product::where('vendor_id', $vendorId)->pluck('id')->toArray();

            // Check that each product_id in compound_products belongs to the vendor
            foreach ($validatedData['compound_products'] as $productData) {
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
                'title' => 'nullable|string|max:255',
                'slug' => 'nullable|string|max:255|unique:products,slug,' . $compound->product_id,  // Ensure unique slug but ignore current product slug
                'description' => 'nullable|string',
                'price' => 'nullable|numeric',
                'image' => 'nullable|string',
                'volume' => 'nullable|numeric',
                'product_code' => 'nullable|string',
                'manufacturing_date' => 'nullable|date',
                'fragrance_family' => 'nullable|string',
                'expire_date' => 'nullable|date',
                'gender' => 'nullable|string',
                'discount' => 'nullable|numeric',
                'priority' => 'nullable|string',
                'compound_products' => 'nullable|array',  // compound_products is optional
                'compound_products.*.product_id' => 'required|integer|exists:products,id',  // Validating product_id
            ]);

            // Update the compound itself
            $compound->update($validatedData);

            // Update the product associated with the compound
            $product->update($validatedData);

            // Handle compound products data if it's provided
            if (!empty($validatedData['compound_products'])) {
                $compoundProductsData = $validatedData['compound_products'];

                $syncData = [];
                // Fetch vendor products for validation
                $vendorProducts = Product::where('vendor_id', $vendorId)->pluck('id')->toArray();

                // Check that each product_id in compound_products belongs to the vendor
                foreach ($validatedData['compound_products'] as $productData) {
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
