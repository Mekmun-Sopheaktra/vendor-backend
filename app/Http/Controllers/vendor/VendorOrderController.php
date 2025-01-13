<?php

namespace App\Http\Controllers\vendor;

use App\Constants\OrderConstants;
use App\Constants\RoleConstants;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\Vendor;
use App\Traits\BaseApiResponse;
use Illuminate\Support\Facades\Log;

class VendorOrderController extends Controller
{
    use BaseApiResponse;

    public function index()
    {
        try {
            // Get the authenticated vendor
            $vendor = auth()->user();

            // Retrieve the vendor's store along with its products
            $store = Vendor::where('user_id', $vendor->id)
                ->with('products') // Ensure you have a 'products' relationship in the Vendor model
                ->firstOrFail(); // Using firstOrFail for better error handling

            // Fetch the orders associated with the vendor's products
            $orders = Order::whereHas('products', function ($query) use ($store) {
                // Filter products by vendor's products
                $query->whereIn('product_id', $store->products->pluck('id'));
            })
                ->where('status', 'created')
                ->with(['products', 'user', 'address']) // Eager load related products, user, and address
                ->get();

            // Format the orders with pivot data (e.g., count of products)
            $orders->each(function ($order) {
                $order->products->each(function ($product) {
                    // Access pivot data like count, etc.
                    $product->pivot->count = $product->pivot->count; // Add this field to the result if needed
                });
            });

            return $this->success($orders, 'Order', 'Order list completed');
        } catch (\Exception $e) {
            Log::error('Error retrieving orders: ' . $e->getMessage());

            return $this->failed(null, 'Error', 'An error occurred while fetching orders.');
        }
    }

    //history
    public function history()
    {
        try {
            // Get the authenticated vendor
            $vendor = auth()->user();

            // Retrieve the vendor's store along with its products
            $store = Vendor::where('user_id', $vendor->id)
                ->with('products') // Eager load the products of the vendor
                ->firstOrFail(); // Using firstOrFail for better error handling

            // Fetch the orders associated with the vendor's products (excluding 'created' status)
            $orders = Order::whereHas('products', function ($query) use ($store) {
                // Filter by vendor's products
                $query->whereIn('product_id', $store->products->pluck('id'));
            })
                ->where('status', '!=', 'created') // Orders that are not in 'created' status
                ->with(['products', 'user', 'address']) // Eager load related products, user, and address
                ->get();

            return $this->success($orders, 'Order', 'Order list completed');
        } catch (\Exception $e) {
            Log::error('Error retrieving orders: ' . $e->getMessage());

            return $this->failed(null, 'Error', 'An error occurred while fetching orders.');
        }
    }

    //show
    public function show(Order $order)
    {
        try {
            // Get the authenticated vendor
            $vendor = auth()->user();

            // Retrieve the vendor's store along with its products
            $store = Vendor::where('user_id', $vendor->id)
                ->with('products')
                ->firstOrFail(); // Using firstOrFail for better error handling

            // Check if the order is associated with the vendor's products
            $orderProduct = OrderProduct::where('order_id', $order->id)
                ->whereIn('product_id', $store->products->pluck('id'))
                ->first();

            if (!$orderProduct) {
                return $this->failed(null, 'Error', 'The order is not associated with your products.');
            }

            // Retrieve the order details
            $order = Order::where('id', $order->id)
                ->with(['products', 'user', 'address'])
                ->first();

            return $this->success($order, 'Order', 'Order details retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Error retrieving order: ' . $e->getMessage());

            return $this->failed(null, 'Error', 'An error occurred while fetching the order.');
        }
    }

    //approve order
    public function approveOrder(Order $order)
    {
        try {
            // Get the authenticated vendor
            $vendor = auth()->user();

            // Retrieve the vendor's store along with its products
            $store = Vendor::where('user_id', $vendor->id)
                ->with('products')
                ->firstOrFail(); // Using firstOrFail for better error handling

            // Check if the order is associated with the vendor's products
            $orderProduct = OrderProduct::where('order_id', $order->id)
                ->whereIn('product_id', $store->products->pluck('id'))
                ->first();

            if (!$orderProduct) {
                return $this->failed(null, 'Error', 'The order is not associated with your products.');
            }

            // Update the order status to approved
            $order->status = OrderConstants::SUCCESS;
            $order->save();

            return $this->success($order, 'Order', 'Order approved successfully');
        } catch (\Exception $e) {
            Log::error('Error approving order: ' . $e->getMessage());

            return $this->failed(null, 'Error', 'An error occurred while approving the order.');
        }
    }

    //rejectOrder
    public function rejectOrder(Order $order)
    {
        try {
            // Get the authenticated vendor
            $vendor = auth()->user();

            // Retrieve the vendor's store along with its products
            $store = Vendor::where('user_id', $vendor->id)
                ->with('products')
                ->firstOrFail(); // Using firstOrFail for better error handling

            // Check if the order is associated with the vendor's products
            $orderProduct = OrderProduct::where('order_id', $order->id)
                ->whereIn('product_id', $store->products->pluck('id'))
                ->first();

            if (!$orderProduct) {
                return $this->failed(null, 'Error', 'The order is not associated with your products.');
            }

            // Update the order status to rejected
            $order->status = 'cancelled';
            $order->save();

            return $this->success($order, 'Order', 'Order rejected successfully');
        } catch (\Exception $e) {
            Log::error('Error rejecting order: ' . $e->getMessage());

            return $this->failed(null, 'Error', 'An error occurred while rejecting the order.');
        }
    }
}
