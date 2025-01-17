<?php

namespace App\Http\Controllers\admin;

use App\Constants\VendorStatusConstants;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\Revenue;
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
        $totalVendors = Vendor::query()->where('status', 1)->count();

        // Total orders from Model Order
        $totalOrders = Order::count();

        // Total products
        $totalProducts = Product::count();

        //total users without vendor (is_vendor) and admin (is_superuser)
        $totalUsers = User::where('is_vendor', 0)->where('is_superuser', 0)->count();

        $revenue_per_month = Revenue::query()
            ->selectRaw('MONTH(date) as month_number, DATE_FORMAT(date, "%M") as month, SUM(revenue) as revenue')
            ->groupBy('month_number', 'month')
            ->orderBy('month_number')
            ->get();
        //make month name as key and revenue as value
        $revenue_per_month = $revenue_per_month->pluck('revenue', 'month');

        // Stats: Get top 5 products from order_products with count and group by product_id and product_name
        $stats = Product::select('products.id', 'products.title', \DB::raw('SUM(order_products.count) as total'))
            ->join('order_products', 'products.id', '=', 'order_products.product_id')
            ->groupBy('products.id', 'products.title')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        //total revenue
        $totalRevenue = Revenue::sum('revenue');

        return $this->success([
            'total_users' => $totalUsers,
            'total_vendors' => $totalVendors,
            'total_orders' => $totalOrders,
            'total_products' => $totalProducts,
            'total_revenue' => $totalRevenue,
            'revenue_per_month' => $revenue_per_month,
            'top_products' => $stats
        ]);
    }

    //Subscription Billing
    public function subscription()
    {
        //check if revenue already exists for this month then return error
        $revenue = Revenue::query()->whereMonth('date', now())->first();
        if ($revenue) {
            return $this->failed(null,'Revenue already exists for this month');
        }
        //total revenue = total vendor active * monthly_subscription_fee constant and save to revenue table with date now
        $totalVendors = Vendor::query()->where('status', VendorStatusConstants::ACTIVE)->count();
        $monthlySubscriptionFee = 15;
        $totalRevenue = $totalVendors * $monthlySubscriptionFee;

        $revenue = new Revenue();
        $revenue->date = now();
        $revenue->revenue = $totalRevenue;
        $revenue->monthly_subscription_fee = $monthlySubscriptionFee;
        $revenue->save();

        return $this->success($revenue);
    }
}
