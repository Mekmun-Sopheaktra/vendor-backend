<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Basket\BasketBuyRequest;
use App\Http\Requests\Basket\BasketRequest;
use App\Http\Resources\Basket\BasketResource;
use App\Models\Basket;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\Vendor;
use App\Traits\BaseApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BasketController extends Controller
{
    use BaseApiResponse;

    public function index(): JsonResponse
    {
        $delivery_fee = 2.5;
        $baskets = auth()->user()->baskets()
            ->where('status', 'created')
            ->with('product')->get();

        // Group baskets by vendor
        $groupedBaskets = $baskets->groupBy(function ($basket) {
            return $basket->product->vendor_id;
        });

        // Calculate the total cart value
        $subtotal = $baskets->sum(function ($basket) {
            return $basket->product->price * $basket->count;
        });

        // Transform grouped data
        $groupedData = $groupedBaskets->map(function ($baskets, $vendorId) {
            return [
                'vendor' => Vendor::query()->find($vendorId),
                'products' => BasketResource::collection($baskets),
            ];
        })->values(); // Reset keys for cleaner JSON output

        return response()->json([
            'data' => $groupedData,
            'summary' => [
                'subtotal' => $subtotal,
                'delivery_fee' => $delivery_fee,
                'total' => $subtotal ? $subtotal + $delivery_fee : 0,
            ],
            'status' => true,
            'alert' => [
                'title' => 'success',
                'message' => 'Cart grouped by vendor successfully.'
            ]
        ]);
    }

    //all cart for admin
    public function all(): JsonResponse
    {
        $baskets = Basket::query()
            ->where('status', 'created')
            ->with('product')
            ->get();

        return $this->success(BasketResource::collection($baskets), 'success', 'Product successfully.');
    }

    public function add(BasketRequest $request): JsonResponse
    {
        $userId = auth()->user()->id;

        $existingBasket = Basket::query()
            ->where('status', 'created')
            ->where('user_id', $userId)
            ->where('product_id', $request['product'])
            ->first();

        if ($existingBasket) {
            $existingBasket->count += $request['count'];
            $existingBasket->save();
        } else {
            Basket::query()->create([
                'user_id' => $userId,
                'product_id' => $request['product'],
                'count' => $request['count'],
            ]);
        }

        auth()->user()->notifications()->create([
            'title' => 'Product added to the basket',
            'description' => 'Product added to the basket successfully',
        ]);

        return $this->success(null, 'success', 'Product added to the basket successfully.');
    }

    public function delete($id): JsonResponse
    {
        $basketItem = Basket::query()->where('id', '=', $id)
            ->where('user_id', auth()->user()->id)
            ->first();

        if (!$basketItem) {
            return $this->failed(null, 'error', 'Basket item not found or you do not have permission to delete it.');
        }

        $basketItem->delete();

        return $this->success(null, 'success', 'Item removed from the basket.');
    }

    //checkout
    public function checkout(Request $request)
    {
        $products_id = $request->input('products_id');

        // Validate input
        if (empty($products_id) || !is_array($products_id)) {
            return $this->failed(null, 'error', 'Invalid products_id input.');
        }

        // Check if all products belong to the same vendor
        $vendors = Product::query()
            ->whereIn('id', $products_id)
            ->pluck('vendor_id')
            ->toArray();

        if (count(array_unique($vendors)) > 1) {
            return $this->failed(null, 'error', 'You can only checkout products from the same vendor.');
        }

        // Calculate total price
        $total = Basket::query()
            ->whereIn('id', $products_id)
            ->get();

        // Return success response
        return $this->success($total, 'Success', 'Checkout successful');
    }

    public function buy(BasketBuyRequest $request): JsonResponse
    {
        $validated = $request->validated();
        if (auth()->user()->baskets()->where('status', 'created')->count() == 0) {
            return $this->success(null, 'Empty', 'Your shopping cart is empty');
        }
        $products = auth()->user()->baskets()->where('status', 'created')->get();

        auth()->user()->baskets()->where('status', 'created')->update([
            'status' => 'paid',
        ]);
        $order = Order::query()->create([
            'code' => rand(),
            'user_id' => auth()->user()->id,
            "address" => $validated['address'],
            "transaction_method" => $validated['transaction_method'],
            "transaction_id" => $validated['transaction_id'],
            "amount" => $validated['amount'],
        ]);

        foreach ($products as $product) {
            OrderProduct::query()->create([
                'order_id' => $order->id,
                'product_id' => $product->product_id,
                'count' => $product->count,
            ]);
        }

        return $this->success(null, 'Success', 'Your purchase was successful');
    }
}
