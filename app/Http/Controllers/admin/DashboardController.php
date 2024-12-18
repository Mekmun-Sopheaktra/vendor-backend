<?php

namespace App\Http\Controllers\admin;

use App\Constants\VendorStatusConstants;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use App\Traits\BaseApiResponse;

class DashboardController extends Controller
{
    use BaseApiResponse;

    //index
    public function index()
    {
        // Total users
        $totalUsers = Vendor::query()->where('status', VendorStatusConstants::ACTIVE)->count();

        // Total orders from Model Order
        $totalOrders = Order::count();

        // Total products
        $totalProducts = Product::count();

        // Stats: Get top 5 products from order_products with count and group by product_id and product_name
        $stats = Product::select('products.id', 'products.title', \DB::raw('SUM(order_products.count) as total'))
            ->join('order_products', 'products.id', '=', 'order_products.product_id')
            ->groupBy('products.id', 'products.title')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        return $this->success([
            'total_vendors' => $totalUsers,
            'total_orders' => $totalOrders,
            'total_products' => $totalProducts,
            'top_products' => $stats
        ]);
    }
}
