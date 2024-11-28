<?php

namespace App\Http\Controllers\api\v1;

use App\Constants\ProductPriority;
use App\Http\Controllers\Controller;
use App\Http\Resources\Comment\CommentResource;
use App\Http\Resources\Product\wishListCollection;
use App\Models\Category;
use App\Models\LikeProducts;
use App\Models\Product;
use App\Traits\BaseApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class ProductController extends Controller
{
    use BaseApiResponse;

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', env('PAGINATION_PER_PAGE', 10));
        $products = Product::paginate($perPage);

        return $this->success($products);
    }

    public function show(Product $product): JsonResponse
    {
        $product->likes = $this->calculateLikesForProduct($product->id);
        $product->isLike = $this->isProductLiked($product->id);
        $product->rate = $this->calculateRateForProduct($product->id);
        $product->category = $product->category;
        $product->comments = CommentResource::collection($this->getComments($product));
        $product->gallery = $product->galleries()->pluck('image')->map(function ($item) {
            return secure_asset('storage/'.$item);
        });

        return $this->success($product);
    }

    public function wishlist(Request $request): JsonResponse
    {
        $productsQuery = auth()->user()->likedProducts()->with('product');

        if ($request->has('category_id')) {
            $productsQuery->whereHas('product', function ($query) use ($request) {
                $query->where('category_id', $request->input('category_id'));
            });
        }

        $perPage = $request->has('per_page') ? (int) $request->input('per_page') : 15;
        $currentPage = $request->has('page') ? (int) $request->input('page') : 1;

        Paginator::currentPageResolver(function () use ($currentPage) {
            return $currentPage;
        });

        $products = $productsQuery->paginate($perPage);

        $categories = Category::query()->select('id', 'name', 'parent', 'icon')->get();

        $data = [
            'categories' => $categories,
            'products' => new wishListCollection($products),
            'pagination' => [
                'page_number' => $products->currentPage(),
                'total_rows' => $products->total(),
                'total_pages' => $products->lastPage(),
                'has_previous_page' => $products->previousPageUrl() !== null,
                'has_next_page' => $products->nextPageUrl() !== null,
            ],
        ];

        return $this->success($data);
    }

    private function getComments($product)
    {
        return $product->comments()->select('id', 'comment', 'created_at', 'user_id')->with('user:id,name,profile_photo_path')->get();
    }

    private function calculateLikesForProduct($productId): int
    {
        return LikeProducts::query()->where('product_id', $productId)->count();
    }

    private function isProductLiked($productId): bool
    {
        return LikeProducts::query()->where('product_id', $productId)
            ->where('user_id', auth()->user()->id)
            ->exists();
    }

    private function calculateRateForProduct($productId): float
    {
        return 3.5;
    }


    public function latestProducts(Request $request)
    {
        $sortOrder = $request->query('order', 'desc'); // Default to descending
        $products = Product::orderBy('created_at', $sortOrder)->get();

        return $this->success($products);
    }

    public function relatedProducts($id)
    {
        // Find the product by ID
        $product = Product::find($id);

        // Return an error if the product is not found
        if (!$product) {
            return $this->error('Product not found', 404);
        }

        // Retrieve the related products through the pivot table (category_products)
        $relatedProducts = Product::whereHas('categories', function ($query) use ($product) {
            // Find products in the same categories as the current product
            $query->whereIn('categories.id', $product->categories->pluck('id'));
        })
            ->where('id', '!=', $id) // Exclude the current product
            ->limit(5) // Limit the number of related products to return
            ->get();

        // Return the related products in the response
        return $this->success($relatedProducts);
    }

    public function discountedProducts()
    {
        $discountedProducts = Product::where('discount', '>', 0) // Products with discounts
        ->orderBy('priority', 'desc') // Sort by priority
        ->get();

        return $this->success($discountedProducts);
    }

    public function filterProducts(Request $request)
    {
        // Get query parameters
        $minPrice = $request->query('min-price');
        $maxPrice = $request->query('max-price');
        $volume = $request->query('volume');
        $popular = $request->query('popular');
        $perPage = $request->query('per_page', 10); // Default to 10 items per page

        // Initialize query
        $query = Product::query();

        // Filter by min and max price
        if ($minPrice) {
            $query->where('price', '>=', $minPrice); // Filter by minimum price
        }

        if ($maxPrice) {
            $query->where('price', '<=', $maxPrice); // Filter by maximum price
        }

        // Filter by volume
        if ($volume) {
            $query->where('volume', $volume); // Filter by volume
        }

        // Sort by popularity if requested
        if ($popular) {
            $query->orderBy('view_count', $popular); // Sort by view_count (ascending or descending)
        }

        // Paginate the results
        $filteredProducts = $query->paginate($perPage);

        // Return the paginated results as a successful response
        return $this->success($filteredProducts);
    }

    public function newArrivals(Request $request)
    {
        // Retrieve pagination parameters from request or default to 10 per page
        $perPage = $request->input('per_page', 10);

        // Get all products, prioritize NEW_ARRIVAL products by ordering them to the top
        $newArrivalProducts = Product::select('products.*') // Select all fields from products
        ->leftJoin('priority', 'products.priority', '=', 'priority.name') // Join with the priority table
        ->orderByRaw("CASE WHEN priority.name = '" . ProductPriority::NEW_ARRIVAL . "' THEN 0 ELSE 1 END") // Prioritize NEW_ARRIVAL
        ->orderBy('created_at', 'desc') // Fallback order by creation date
        ->paginate($perPage);

        return $this->success($newArrivalProducts);
    }
}
