<?php

namespace App\Http\Controllers\vendor;

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
                ->with('products')
                ->firstOrFail(); // Using firstOrFail for better error handling

            // Fetch the orders associated with the vendor's products
            $orderIds = Product::where('vendor_id', $store->id)
                ->with('orders')
                ->get()
                ->flatMap(fn($product) => $product->orders->pluck('order_id'));

            // Retrieve the orders based on the collected order IDs
            $orders = Order::whereIn('id', $orderIds)
                //status is created
                ->where('status', 'created')
                ->with(['products', 'user', 'address'])
                ->get();

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
                ->with('products')
                ->firstOrFail(); // Using firstOrFail for better error handling

            // Fetch the orders associated with the vendor's products
            $orderIds = Product::where('vendor_id', $store->id)
                ->with('orders')
                ->get()
                ->flatMap(fn($product) => $product->orders->pluck('order_id'));

            // Retrieve the orders based on the collected order IDs
            $orders = Order::whereIn('id', $orderIds)
                //where status is not created
                ->where('status', '!=', 'created')
                ->with(['products', 'user', 'address'])
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
            $order->status = 'success';
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
