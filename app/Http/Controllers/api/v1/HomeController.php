<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Search\SearchRequest;
use App\Http\Resources\Product\ProductCollection;
use App\Http\Resources\Profile\AddressResource;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Compound;
use App\Models\LikeProducts;
use App\Models\Product;
use App\Traits\BaseApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use MongoDB\Driver\Exception\EncryptionException;

class HomeController extends Controller
{
    use BaseApiResponse;

    public function index(): JsonResponse
    {
        try {
            $banners = Banner::query()
                ->select('id', 'banner')
                ->take(5)
                ->get();

            $products = Product::query()
                ->select('id', 'title', 'description', 'price', 'image')
                ->take(5)
                ->get();

            foreach ($products as $product) {
                $product->likes = $this->calculateLikesForProduct($product->id);
                $product->isLike = $this->isProductLiked($product->id);
                $product->rate = $this->calculateRateForProduct($product->id);
            }

            $categories = Category::query()
                ->select('id', 'name', 'parent', 'icon')
                ->take(5)
                ->get();

            $address = null;
            if (auth()->user()) {
                $address = auth()->user()->address()->first()
                    ? new AddressResource(auth()->user()->address()->first())
                    : null;
            }

            // Compound products
            $compounds = Compound::with(['products' => function ($query) {
                $query->take(5);
            }])->take(5)->get();

            return $this->success([
                'banners' => $banners,
                'categories' => $categories,
                'newest_product' => $products,
                'address' => $address,
                'compound' => $compounds,
                'flash_sale' => [
                    'expired_at' => Carbon::now()->addDays(5),
                    'products' => $products,
                ],
                'most_sale' => $products,
            ], 'Home', 'Home fetched successfully');
        } catch (EncryptionException $exception) {
            return $this->failed($exception->getMessage(), 'Error', 'Error from server');
        }
    }

    public function search(Request $request): JsonResponse
    {
        try {
            // Get the search term from the request, defaulting to an empty string if not provided
            $searchTerm = $request->query('title', '');

            // Query for minimum and maximum price
            $get_min_price = Product::query()->min('price');
            $get_max_price = Product::query()->max('price');

            // Initialize the query for products
            $query = Product::query();

            // Only apply the title filter if the search term is not empty
            if (!empty($searchTerm)) {
                // Case-insensitive partial search for product titles
                $query->whereRaw('LOWER(title) LIKE ?', ['%' . strtolower($searchTerm) . '%']); // Searching by product title, case-insensitive
            }

            // Get the filtered products
            $products = $query->get();

            return $this->success([
                'min_price' => $get_min_price,
                'max_price' => $get_max_price,
                'products' => $products,
            ]);
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
