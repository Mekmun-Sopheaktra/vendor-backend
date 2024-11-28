<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\Compound;
use App\Models\CompoundProduct;
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

            // Eager load the 'products' relationship, including pivot data (inventory)
            $compounds = Compound::with('products') // This will load related products along with pivot data
            ->paginate($perPage);

            return $this->success($compounds);
        } catch (\Exception $e) {
            return $this->failed(null, 'Error', $e->getMessage());
        }
    }

    //store
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            // Create the compound
            $compound = Compound::create($request->only(['name', 'price', 'description']));

            // Validate and process compound_products data
            $compoundProductsData = $request->get('compound_products', []);
            if (!is_array($compoundProductsData) || empty($compoundProductsData)) {
                throw new \Exception('Compound products data is required and must be an array.');
            }

            $syncData = [];
            foreach ($compoundProductsData as $product) {
                if (empty($product['product_id']) || empty($product['inventory'])) {
                    throw new \Exception('Each compound product must have product id and inventory.');
                }

                // Add the product_id and inventory to the sync data array
                $syncData[$product['product_id']] = ['inventory' => $product['inventory']];
            }

            // Attach products with quantities to the compound
            $compound->products()->sync($syncData);

            DB::commit(); // Commit transaction
            return $this->success($compound->load('products'), 'Compound created successfully');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on failure
            return $this->failed(null, 'Error', $e->getMessage());
        }
    }

    //show
    public function show($id)
    {
        try {
            $compound = Compound::with('products')->find($id);
            if (!$compound) {
                return $this->failed(null, 'Error', 'Compound not found');
            }

            return $this->success($compound);
        } catch (\Exception $e) {
            return $this->failed(null, 'Error', $e->getMessage());
        }
    }

    //update
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $compound = Compound::find($id);
            if (!$compound) {
                return $this->failed(null, 'Error', 'Compound not found');
            }

            // Update the compound
            $compound->update($request->only(['name', 'price', 'description']));

            // Validate and process compound_products data
            $compoundProductsData = $request->get('compound_products', []);
            if (!is_array($compoundProductsData) || empty($compoundProductsData)) {
                throw new \Exception('Compound products data is required and must be an array.');
            }

            $syncData = [];
            foreach ($compoundProductsData as $product) {
                if (empty($product['product_id']) || empty($product['inventory'])) {
                    throw new \Exception('Each compound product must have product id and inventory.');
                }

                // Add the product_id and inventory to the sync data array
                $syncData[$product['product_id']] = ['inventory' => $product['inventory']];
            }

            // Attach products with quantities to the compound
            $compound->products()->sync($syncData);

            DB::commit(); // Commit transaction
            return $this->success($compound->load('products'), 'Compound updated successfully');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on failure
            return $this->failed(null, 'Error', $e->getMessage());
        }
    }

    //destroy
    public function destroy($id)
    {
        try {
            $compound = Compound::find($id);
            if (!$compound) {
                return $this->failed(null, 'Error', 'Compound not found');
            }

            $compound->delete();
            return $this->success(null, 'Compound deleted successfully');
        } catch (\Exception $e) {
            return $this->failed(null, 'Error', $e->getMessage());
        }
    }

    //addProduct
    public function addProduct(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $compound = Compound::find($id);
            if (!$compound) {
                return $this->failed(null, 'Error', 'Compound not found');
            }

            $productId = $request->get('product_id');
            $inventory = $request->get('inventory');

            if (!$productId || !$inventory) {
                return $this->failed(null, 'Error', 'Product id and inventory are required');
            }

            // Attach the product with inventory to the compound
            $compound->products()->attach($productId, ['inventory' => $inventory]);

            DB::commit(); // Commit transaction
            return $this->success($compound->load('products'), 'Product added to compound successfully');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on failure
            return $this->failed(null, 'Error', $e->getMessage());
        }
    }

    //removeProduct
    public function removeProduct(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $compound = Compound::find($id);
            if (!$compound) {
                return $this->failed(null, 'Error', 'Compound not found');
            }

            $productId = $request->get('product_id');
            if (!$productId) {
                return $this->failed(null, 'Error', 'Product id is required');
            }

            // Detach the product from the compound
            $compound->products()->detach($productId);

            DB::commit(); // Commit transaction
            return $this->success($compound->load('products'), 'Product removed from compound successfully');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on failure
            return $this->failed(null, 'Error', $e->getMessage());
        }
    }

    //updateProduct
    public function updateProduct(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $compound = Compound::find($id);
            if (!$compound) {
                return $this->failed(null, 'Error', 'Compound not found');
            }

            $productId = $request->get('product_id');
            $inventory = $request->get('inventory');

            if (!$productId || !$inventory) {
                return $this->failed(null, 'Error', 'Product id and inventory are required');
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
    public function showProduct($id, $productId)
    {
        try {
            $compound = Compound::with(['products' => function ($query) use ($productId) {
                $query->where('product_id', $productId);
            }])->find($id);

            if (!$compound) {
                return $this->failed(null, 'Error', 'Compound not found');
            }

            return $this->success($compound);
        } catch (\Exception $e) {
            return $this->failed(null, 'Error', $e->getMessage());
        }
    }

    //adjustInventory
    public function adjustInventory(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $compound = Compound::find($id);
            if (!$compound) {
                return $this->failed(null, 'Error', 'Compound not found');
            }

            $productId = $request->get('product_id');
            $inventory = $request->get('inventory');

            if (!$productId || !$inventory) {
                return $this->failed(null, 'Error', 'Product id and inventory are required');
            }

            // Update the product inventory in the compound
            $compound->products()->updateExistingPivot($productId, ['inventory' => $inventory]);

            DB::commit(); // Commit transaction
            return $this->success($compound->load('products'), 'Product inventory adjusted successfully');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on failure
            return $this->failed(null, 'Error', $e->getMessage());
        }
    }
}
