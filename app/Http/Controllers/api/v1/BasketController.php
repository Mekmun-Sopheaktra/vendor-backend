<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Basket\BasketBuyRequest;
use App\Http\Requests\Basket\BasketRequest;
use App\Http\Resources\Basket\BasketResource;
use App\Models\Basket;
use App\Models\Discount;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\Vendor;
use App\Traits\BaseApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BasketController extends Controller
{
    use BaseApiResponse;

    public function index(): JsonResponse
    {
        $delivery_fee = 2.5;
        $baskets = auth()->user()->baskets()
            ->whereIn('status', ['created', 'pending_payment'])
            ->with('product')
            ->get();

        // Group baskets by vendor
        $groupedBaskets = $baskets->groupBy(function ($basket) {
            return $basket->product->vendor_id;
        });

        $discounts = Discount::query()
            ->where('status', 1)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->get()
            ->keyBy('product_id');

        //add final price to the product if discount is available
        $baskets->each(function ($basket) use ($discounts) {
            if ($discounts->has($basket->product_id)) {
                $discount = $discounts->get($basket->product_id);
                $basket->product->final_price = $basket->product->price - ($basket->product->price * $discount->discount / 100);
                $basket->product->discount = $discount;
            } else {
                $basket->product->final_price = $basket->product->price;
            }
        });

        //amount after discount for each product in the basket
        $baskets->each(function ($basket) {
            $basket->amount = $basket->product->final_price * $basket->count;
        });

        // Calculate the total cart value
        $subtotal = $baskets->sum(function ($basket) {
            return $basket->product->final_price * $basket->count;
        });

        // Transform grouped data
        $groupedData = $groupedBaskets->map(function ($baskets, $vendorId) {
            return [
                'summary' => [
                    'total' => $baskets->sum(function ($basket) {
                        return $basket->product->final_price * $basket->count;
                    }),
                ],
                'vendor' => Vendor::query()->find($vendorId),
                'products' => BasketResource::collection($baskets),
            ];
        })->values(); // Reset keys for cleaner JSON output

        return response()->json([
            'data' => $groupedData,
            'summary' => [
                'total' => $subtotal,
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
                'vendor_id' => Product::query()->find($request['product'])->vendor_id,
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
        $vendor_id = $request->input('vendor_id');

        // Validate vendor_id input
        if (empty($vendor_id)) {
            return $this->failed(null, 'error', 'Vendor ID is required.');
        }

        // Check if the vendor exists in the Vendor table
        $validVendor = Vendor::query()->where('id', $vendor_id)->exists();
        if (!$validVendor) {
            return $this->failed(null, 'error', 'Vendor not found.');
        }

        // Get all basket items for products belonging to the specified vendor
        $basketItems = Basket::query()
            ->whereIn('product_id', function ($query) use ($vendor_id) {
                $query->select('id')
                    ->from('products')
                    ->where('vendor_id', $vendor_id);
            })
            ->with('product') // Load product relationship
            ->get();

        // Check if the basket is empty for the selected vendor
        if ($basketItems->isEmpty()) {
            return $this->failed(null, 'error', 'Your basket does not contain products from the selected vendor.');
        }

        $discounts = Discount::query()
            ->where('status', 1)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->get()
            ->keyBy('product_id');

        // Update the status of the basket items to 'pending_payment'
        $basketItems->each(function ($item) {
            $item->status = 'pending_payment';
            $item->save();
        });

        // Calculate the total price from the products table
        $totalPrice = $basketItems->sum(function ($item) use ($discounts) {
            // Check if there's a discount for the product
            if ($discounts->has($item->product_id)) {
                $discount = $discounts->get($item->product_id);

                // Calculate the discounted price
                $discountedPrice = $item->product->price - ($item->product->price * $discount->discount / 100);

                // Set additional properties for final_price and discount
                $item->product->final_price = $discountedPrice;
                $item->product->discount = $discount;

                // Return the discounted total price for the item
                return $discountedPrice * $item->count;
            } else {
                // No discount: return the regular total price for the item
                return $item->product->price * $item->count;
            }
        });


        $data = [
            'amount' => $totalPrice,
            'vendor_id' => $vendor_id,
            'basket' => $basketItems,
        ];

        // Return a success response
        return $this->success($data, 'Success', 'Checkout successful.');
    }

    public function buy(BasketBuyRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $vendorId = $validated['vendor_id'];

        // Fetch all products in the basket for the user and the selected vendor
        $productIds = auth()->user()->baskets()
            ->where('status', 'pending_payment')
            ->where('vendor_id', $vendorId)
            ->pluck('product_id');

        //check products is already paid in the basket
        $paidProducts = auth()->user()->baskets()->whereIn('product_id', $productIds)->where('status', 'paid')->get();
        if (!$paidProducts->isEmpty()) {
            return $this->failed($paidProducts, 'Invalid Products', 'Some products are already paid.', 422);
        }

        // Fetch only products from the user's basket
        $userBasket = auth()->user()->baskets()->whereIn('product_id', $productIds)->get();

        if ($userBasket->isEmpty()) {
            return $this->failed($userBasket, 'Invalid Products', 'No valid products found in your basket', 422);
        }

        // Mark selected products as paid
        auth()->user()->baskets()->whereIn('product_id', $productIds)->update([
            'status' => 'paid',
        ]);

        // Create an order
        $order = Order::create([
            'code' => Str::uuid(), // Generate a unique order code
            'user_id' => auth()->id(),
            'vendor_id' => $vendorId,
            'address' => $validated['address'],
            'phone' => $validated['phone'],
            'transaction_method' => $validated['transaction_method'],
            'transaction_id' => $validated['transaction_id'],
            'amount' => $validated['amount'],
            'status' => 'created',
        ]);

        // Link products to the order
        foreach ($userBasket as $basketItem) {
            OrderProduct::create([
                'order_id' => $order->id,
                'product_id' => $basketItem->product_id,
                'count' => $basketItem->count, // Assuming count exists in the basket
            ]);
        }

        return $this->success(null, 'Success', 'Your purchase was successful');
    }
}
