<?php

namespace App\Http\Controllers\api\v1;

use App\Constants\ProductPriority;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Discount;
use App\Models\Product;
use App\Traits\BaseApiResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use BaseApiResponse;

    public function index(Request $request)
    {
        $perPage = $request->query('per_page', env('PAGINATION_PER_PAGE', 10));
        $search = $request->query('search');
        $categories = Category::query()
            ->where('name', 'like', '%' . $search . '%')
            ->paginate($perPage);
        return $this->success($categories);
    }

    //show
    public function show($slug, Request $request)
    {
        // Get the per_page value from the request, default to 10 if not provided
        $perPage = $request->query('per_page', env('PAGINATION_PER_PAGE', 10));
        // Get the search query from the request
        $search = $request->query('search');

        // Find the category, if not found return an error
        $category = Category::query()->where('slug', $slug)->first();
        if (!$category) {
            return $this->failed('Category not found', 404);
        }

        // Query products, apply search if the search term is provided
        $productsQuery = $category->products()->withCount('tags'); // Add withCount to count related tags

        if ($search) {
            $productsQuery->where('title', 'like', '%' . $search . '%');
        }

        // Sort by tags_count in descending order
        $productsQuery->orderBy('tags_count', 'desc');

        $products = $productsQuery->paginate($perPage);
        $products->load(['tags', 'category']);

        $discounts = Discount::query()
            ->where('status', 1)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->get()
            ->keyBy('product_id');

        $products->map(function ($product) use ($discounts) {
            $product->discount = $discounts->get($product->id);
            // If the product has a discount, calculate the final_price
            if ($product->discount) {
                $product->final_price = $product->price - ($product->price * $product->discount->percentage / 100);
            }
            //discount->discount
            if ($product->discount) {
                $product->discount = $product->discount->discount;
            }

            return $product;
        });

        return $this->success([
            'category' => $category,
            'products' => $products,
        ]);
    }

    public function highlightProducts($categoryId, Request $request)
    {

        $inputPriority = $request->input('priority', 'hot');

        $category = Category::find($categoryId);
        if (!$category) {
            return $this->error('Category not found', 404);
        }

        $perPage = $request->input('per_page', 10);

        $highlightedProducts = $category->products()
            ->where('priority', $inputPriority)
            ->get();

        $allProducts = $category->products()
            ->get()
            ->sortBy(function ($product) {
                return ProductPriority::priorityLevel($product->priority);
            })->values(); // Sort by priority

        $mergedProducts = $highlightedProducts->merge($allProducts);

        $paginatedProducts = $this->paginateCollection($mergedProducts, $perPage, $request);

        return $this->success($paginatedProducts);
    }

    /**
     * Manually paginate a collection (useful when sorting happens after querying).
     */
    protected function paginateCollection($collection, $perPage, $request)
    {
        $page = $request->input('page', 1); // Get the current page or default to 1
        $offset = ($page - 1) * $perPage;

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $collection->slice($offset, $perPage)->values(),
            $collection->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

    public function listProductsInCategory($categoryId, Request $request)
    {
        $perPage = $request->query('per_page', env('PAGINATION_PER_PAGE', 10));

        $category = Category::find($categoryId);
        if (!$category) {
            return $this->error('Category not found', 404);
        }

        $products = $category->products()->paginate($perPage);

        $products->load('tags');

        return $this->success([
            'category' => $category,
            'products' => $products,
        ]);
    }
}
