<?php

namespace App\Http\Controllers\api\v1;

use App\Constants\OrderConstants;
use App\Constants\RoleConstants;
use App\Http\Controllers\Controller;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Traits\BaseApiResponse;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    use BaseApiResponse;

    public function index()
    {
        try {
            $orders = $this->getOrdersByStatus();

            return $this->success($orders, 'Order', 'Order list completed');
        } catch (\Exception $e) {
            Log::error('Error retrieving orders: '.$e->getMessage());

            return $this->failed(null, 'Error', 'An error occurred while fetching orders.');
        }
    }

    protected function getOrdersByStatus()
    {
        return auth()->user()->orders()
            ->with('products')
            ->get()
            ->map(function ($order) {
                return [
                    'code' => $order->code,
                    'products' => $this->mapProducts($order->products),
                    'status' => $order->status,
                    'address' => $order->address,
                    'created_at' => $order->created_at,
                    'amount' => $order->amount,
                ];
            });
    }

    protected function mapProducts($products)
    {
        // Get products from order_products table
        $products = OrderProduct::query()
            ->whereIn('product_id', $products->pluck('id'))
            ->get();

        // Get the original products
        $originalProducts = Product::query()
            ->whereIn('id', $products->pluck('product_id'))
            ->get()
            ->keyBy('id'); // Key by ID for quick lookup

        return $products->map(function ($product) use ($originalProducts) {
            $originalProduct = $originalProducts->get($product->product_id); // Match with originalProducts by ID
            $price = $product->final_price ?? $originalProduct->price; // Get final_price from order_products or price from originalProducts
            $amount = $price * $product->count;
            return [
                'id' => $product->product_id,
                'product_code' => $originalProduct ? $originalProduct->product_code : null, // Get product_code from originalProducts
                'title' => $originalProduct ? $originalProduct->title : null, // Get title from originalProducts
                'unit_price' => $price, // Get final_price from order_products or price from originalProducts
                'count' => $product->count,
                'total' => $amount,
                'discount' => $originalProduct ? $originalProduct->discount : null, // Get discount from originalProducts
            ];
        });
    }

    protected function mapAddress($address)
    {
        return [
            'address' => $address->address,
            'city' => $address->city,
            'county' => $address->county,
            'state' => $address->state,
        ];
    }

    protected function convertShippingType($type)
    {
        switch ($type) {
            case 'economy':
                return 0;
            case 'regular':
                return 1;
            case 'cargo':
                return 2;
            case 'express':
                return 3;
            default:
                return 0;
        }
    }
}
