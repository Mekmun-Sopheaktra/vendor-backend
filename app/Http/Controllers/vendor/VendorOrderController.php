<?php

namespace App\Http\Controllers\vendor;

use App\Constants\OrderConstants;
use App\Constants\RoleConstants;
use App\Http\Controllers\Controller;
use App\Http\Resources\Order\OrderResource;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\Vendor;
use App\Traits\BaseApiResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class VendorOrderController extends Controller
{
    use BaseApiResponse;

    public function index(Request $request)
    {
        try {
            // Get the authenticated vendor
            $vendor = auth()->user();
            $search = $request->get('search'); // Retrieve the 'search' query parameter
            $per_page = $request->get('per_page', 10); // Retrieve the 'per_page' query parameter

            // Retrieve the vendor's store along with its products
            $store = Vendor::where('user_id', $vendor->id)
                ->with('products') // Ensure you have a 'products' relationship in the Vendor model
                ->firstOrFail(); // Using firstOrFail for better error handling

            // Fetch the orders associated with the vendor's products with pagination
            $orders = Order::whereHas('products', function ($query) use ($store) {
                // Filter products by vendor's products
                $query->whereIn('product_id', $store->products->pluck('id'));
            })
                //search order id
                ->when($search, function ($query, $search) {
                    return $query->where('id', 'like', '%' . $search . '%');
                })
                ->where('status', OrderConstants::PENDING)
                ->with(['products', 'user']) // Eager load related products, user, and address
                ->paginate($per_page); // Paginate with 10 results per page

            // Format the orders with pivot data (e.g., count of products)
            $orders->getCollection()->each(function ($order) {
                $order->products->each(function ($product) {
                    // Access pivot data like count, etc.
                    $product->pivot->count = $product->pivot->count; // Add this field to the result if needed
                });
            });

            //total order count
            $total_order_count = Order::whereHas('products', function ($query) use ($store) {
                // Filter products by vendor's products
                $query->whereIn('product_id', $store->products->pluck('id'));
            })
                ->where('status', OrderConstants::PENDING)
                ->count();

            //add total order count to the response
            $orders->total_order_count = $total_order_count;

            return $this->success($orders, 'Order', 'Order list completed');
        } catch (\Exception $e) {
            Log::error('Error retrieving orders: ' . $e->getMessage());

            return $this->failed(null, 'Error', 'An error occurred while fetching orders.');
        }
    }

    //history
    public function history(Request $request)
    {
        try {
            // Get the authenticated vendor
            $vendor = auth()->user();
            $search = $request->input('search'); // Retrieve the 'search' query parameter
            $per_page = $request->input('per_page', 10); // Retrieve the 'per_page' query parameter

            // Retrieve the vendor's store along with its products
            $store = Vendor::where('user_id', $vendor->id)
                ->with('products') // Eager load the products of the vendor
                ->firstOrFail(); // Using firstOrFail for better error handling

            // Fetch the orders associated with the vendor's products (excluding 'pending' status) with pagination and search
            $orders = Order::whereHas('products', function ($query) use ($store) {
                // Filter by vendor's products
                $query->whereIn('product_id', $store->products->pluck('id'));
            })
                ->where('status', '!=', 'pending') // Exclude 'pending' status
                ->when($search, function ($query, $search) {
                    return $query->where('id', 'like', '%' . $search . '%');
                })
                ->with(['products', 'user']) // Eager load related products, user, and address
                ->paginate($per_page); // Paginate with 10 results per page

            //total order count
            $total_order_count = Order::whereHas('products', function ($query) use ($store) {
                // Filter products by vendor's products
                $query->whereIn('product_id', $store->products->pluck('id'));
            })
                ->where('status', '!=', 'pending')
                ->count();

            //add total order count to the response
            $orders->total_order_count = $total_order_count;

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
                ->with('products') // Eager load products of the vendor
                ->firstOrFail();

            // Check if the order is associated with the vendor's products
            $isOrderAssociated = OrderProduct::where('order_id', $order->id)
                ->whereIn('product_id', $store->products->pluck('id'))
                ->exists();

            if (!$isOrderAssociated) {
                return $this->failed(null, 'Error', 'The order is not associated with your products.');
            }

            // Retrieve the order details
            $order = Order::where('id', $order->id)
                ->with(['products', 'user']) // Eager load relationships
                ->firstOrFail();

            // Get order products from the order_products table
            $orderProducts = OrderProduct::where('order_id', $order->id)
                ->get()
                ->map(function ($orderProduct) {
                    return [
                        'id' => $orderProduct->id,
                        'product_id' => $orderProduct->product_id,
                        'product_title' => Product::find($orderProduct->product_id)->title ?? 'Unknown Product',
                        'price' => (float) $orderProduct->price,
                        'count' => (int) $orderProduct->count, // Assuming 'count' is the quantity field
                        'discount' => (float) $orderProduct->discount,
                        'total' => (float) $orderProduct->price * $orderProduct->count,
                    ];
                });

            // Include the order products in the order response
            $order->products_details = $orderProducts;

            // Format the response using a resource
            return $this->success(OrderResource::make($order), 'Order', 'Order details retrieved successfully');
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
