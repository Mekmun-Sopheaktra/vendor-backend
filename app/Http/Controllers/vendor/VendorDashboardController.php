<?php

namespace App\Http\Controllers\vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Search\SearchRequest;
use App\Http\Resources\Product\ProductCollection;
use App\Http\Resources\Profile\AddressResource;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Compound;
use App\Models\LikeProducts;
use App\Models\Order;
use App\Models\Product;
use App\Traits\BaseApiResponse;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class VendorDashboardController extends Controller
{
    use BaseApiResponse;

    public function index(): JsonResponse
    {
        try {
            // Current authenticated user
            $userId = auth()->id();
            //get vendor id
            $vendorId = auth()->user()->vendor->id;
            $total_order = Order::query()->where('user_id', $userId)->count();

            $total_revenue = Order::query()->where('user_id', $userId)->sum('amount');

            $total_product = Product::query()->where('user_id', $userId)->count();
            $total_compound = Compound::query()->where('user_id', $userId)->count();

            return $this->success([
                'total_order' => $total_order,
                'total_revenue' => $total_revenue,
                'total_product' => $total_product,
                'total_compound' => $total_compound,
            ], 'Dashboard', 'Dashboard fetched successfully');
        } catch (Exception $exception) {
            return $this->failed($exception->getMessage(), 'Error', 'Error from server');
        }
    }


    public function search(Request $request): JsonResponse
    {
        try {
            // Get the authenticated user's vendor ID
            $vendorId = auth()->user()?->vendor?->id;

            // If the vendor ID is not found, return an error response
            if (!$vendorId) {
                return $this->failed(null, 'Vendor not found', 'Vendor not found', 404);
            }

            // Get the search term from the request, defaulting to an empty string if not provided
            $searchTerm = $request->query('title', '');

            // Initialize the query for products and filter by vendor ID
            $query = Product::query()->where('vendor_id', $vendorId);

            // Apply the title filter if the search term is not empty
            if (!empty($searchTerm)) {
                $query->whereRaw('LOWER(title) LIKE ?', ['%' . strtolower($searchTerm) . '%']); // Case-insensitive search
            }

            // Get the filtered products
            $products = $query->get();

            return $this->success([
                'products' => $products,
            ], 'Search Results', 'Products fetched successfully');
        } catch (\Exception $exception) {
            return $this->failed($exception->getMessage(), 'Error', 'Error from server');
        }
    }



    public function filter(SearchRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        $productsQuery = Product::query();

        if ($request->has('categories_id')) {
            $productsQuery->whereIn('id', explode(',', $validatedData['categories_id']));
        }

        if ($request->has('min_price')) {
            $productsQuery->where('price', '>=', $validatedData['min_price']);
        }

        if ($request->has('max_price')) {
            $productsQuery->where('price', '<=', $validatedData['max_price']);
        }

        if ($request->has('sort')) {
            if ($validatedData['sort'] == '0') {
                $productsQuery->orderBy('created_at', 'asc');
            } elseif ($validatedData['sort'] == '1') {
                $productsQuery->orderBy('created_at', 'desc');
            } elseif ($validatedData['sort'] == '2') {
                $productsQuery->orderBy('price', 'desc');
            } elseif ($validatedData['sort'] == '3') {
                $productsQuery->orderBy('price', 'asc');
            } elseif ($validatedData['sort'] == '4') {
                $productsQuery->orderBy('view_count', 'desc');
            } elseif ($validatedData['sort'] == '5') {
                $productsQuery->orderBy('view_count', 'asc');
            }
        }

        $perPage = $request->input('per_page', 15);
        $currentPage = $request->input('page', 1);

        Paginator::currentPageResolver(fn () => $currentPage);

        $products = $productsQuery->paginate((int) $perPage);

        $paginationData = [
            'page_number' => $products->currentPage(),
            'total_rows' => $products->total(),
            'total_pages' => $products->lastPage(),
            'has_previous_page' => $products->previousPageUrl() !== null,
            'has_next_page' => $products->nextPageUrl() !== null,
        ];

        return $this->success([
            'products' => new ProductCollection($products),
            'pagination' => $paginationData,
        ]);
    }

    private function calculateLikesForProduct($productId): int
    {
        return LikeProducts::query()->where('product_id', $productId)->count();
    }

    private function isProductLiked($productId): bool
    {
        if (!auth()->check()) {
            return false;
        }
        return LikeProducts::query()->where('product_id', $productId)
            ->where('user_id', auth()->user()->id)
            ->exists();
    }

    private function calculateRateForProduct($productId): float
    {
        return 3.5;
    }
}
