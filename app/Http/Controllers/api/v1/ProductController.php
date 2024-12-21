<?php

namespace App\Http\Controllers\api\v1;

use App\Constants\ProductPriority;
use App\Http\Controllers\Controller;
use App\Http\Requests\Search\SearchRequest;
use App\Http\Resources\Comment\CommentResource;
use App\Http\Resources\Product\wishListCollection;
use App\Models\Category;
use App\Models\LikeProducts;
use App\Models\Product;
use App\Models\Tag;
use App\Traits\BaseApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class ProductController extends Controller
{
    use BaseApiResponse;

    public function index(SearchRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $productsQuery = Product::query();

        // Apply category filter
        if (!empty($validatedData['categories_id'])) {
            $productsQuery->whereIn('id', explode(',', $validatedData['categories_id']));
        }

        // Apply price range filters
        if (!empty($validatedData['min_price'])) {
            $productsQuery->where('price', '>=', $validatedData['min_price']);
        }

        if (!empty($validatedData['max_price'])) {
            $productsQuery->where('price', '<=', $validatedData['max_price']);
        }

        // Apply sorting
        if (!empty($validatedData['sort'])) {
            switch ($validatedData['sort']) {
                case '0':
                    $productsQuery->orderBy('created_at', 'asc');
                    break;
                case '1':
                    $productsQuery->orderBy('created_at', 'desc');
                    break;
                case '2':
                    $productsQuery->orderBy('price', 'desc');
                    break;
                case '3':
                    $productsQuery->orderBy('price', 'asc');
                    break;
                case '4':
                    $productsQuery->orderBy('view_count', 'desc');
                    break;
                case '5':
                    $productsQuery->orderBy('view_count', 'asc');
                    break;
            }
        }

        $perPage = $request->query('per_page', env('PAGINATION_PER_PAGE', 10));
        $page = $request->query('page', 1);

        $products = $productsQuery->with(['categories', 'tags', 'galleries'])
            ->paginate($perPage, ['*'], 'page', $page);

        return $this->success($products);
    }

    public function show($id): JsonResponse
    {
        // Find the product by ID, including related categories, tags, and galleries
        $product = Product::with(['categories', 'tags', 'galleries'])->find($id);

        // Return an error if the product is not found
        if (!$product) {
            return $this->failed(null,'Product not found', 404);
        }

        // Retrieve related products
        $relatedProducts = Product::whereHas('categories', function ($query) use ($product) {
            // Find products in the same categories as the current product
            $query->whereIn('categories.id', $product->categories->pluck('id'));
        })
            ->where('id', '!=', $id) // Exclude the current product
            ->limit(5) // Limit the number of related products
            ->get();

        // Add related products to the product response
        $product->related_products = $relatedProducts;

        // Return the product with related products
        return $this->success($product);
    }

    public function create()
    {
        return response()->json([
            'message' => 'Display product creation form.',
            'categories' => Category::all(),
            'tags' => Tag::all(),
        ]);
    }
    public function edit(Product $product)
    {
        $product->load(['categories', 'tags']);
        return response()->json([
            'product' => $product,
            'categories' => Category::all(),
            'tags' => Tag::all(),
        ]);
    }
    //store
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|integer',
            'brand_id' => 'required',
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:products,slug',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'image' => 'nullable|string', // Validate image type and size
            'volume' => 'nullable|string',
            'product_code' => 'nullable|string',
            'manufacturing_date' => 'nullable|date',
            'fragrance_family' => 'nullable|string',
            'expire_date' => 'nullable|date',
            'gender' => 'nullable|string',
            'inventory' => 'nullable|integer',
            'view_count' => 'nullable|integer',
            'discount' => 'nullable|numeric',
            'priority' => 'nullable',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Generate a unique filename and store the image
            $imagePath = $request->file('image')->store('uploads/products', 'public');
            $validatedData['image'] = $imagePath;
        }


        // Create the product with the validated data
        $product = Product::create($validatedData);

        // Attach relationships if necessary
        if ($request->has('categories')) {
            $product->categories()->attach($request->categories);
        }

        if ($request->has('tags')) {
            $product->tags()->attach($request->tags);
        }

        return $this->success($product, 'Product created successfully');
    }

    public function update(Request $request, Product $product)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:products,slug,' . $product->id,
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'image' => 'nullable|string',
            'volume' => 'nullable|string',
            'product_code' => 'nullable|string',
            'manufacturing_date' => 'nullable|date',
            'fragrance_family' => 'nullable|string',
            'expire_date' => 'nullable|date',
            'gender' => 'nullable|string',
            'inventory' => 'nullable|integer',
            'view_count' => 'nullable|integer',
            'is_compound_product' => 'nullable|boolean',
            'discount' => 'nullable|numeric',
            'priority' => 'nullable|integer',
        ]);

        $product->update($validatedData);

        // Update relationships if necessary
        if ($request->has('categories')) {
            $product->categories()->sync($request->categories);
        }

        if ($request->has('tags')) {
            $product->tags()->sync($request->tags);
        }

        return $this->success($product, 'Product updated successfully');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully.',
        ]);
    }

    public function wishlist(Request $request): JsonResponse
    {
        $productsQuery = auth()->user()->likedProducts()->with('product');

        if ($request->has('category_id')) {
            $productsQuery->whereHas('product', function ($query) use ($request) {
                $query->where('id', $request->input('category_id'));
            });
        }

        $perPage = $request->has('per_page') ? (int) $request->input('per_page') : 15;
        $currentPage = $request->has('page') ? (int) $request->input('page') : 1;

        Paginator::currentPageResolver(function () use ($currentPage) {
            return $currentPage;
        });

        $products = $productsQuery->paginate($perPage);

        $data = [
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
        $products = Product::orderBy('created_at', $sortOrder)->limit(5)->get();

        return $this->success($products);
    }

    public function relatedProducts($id)
    {
        // Find the product by ID
        $product = Product::find($id);

        // Return an error if the product is not found
        if (!$product) {
            return $this->failed(null.'Product not found', 404);
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
        $popular = $request->query('popular', 'desc'); // Default to descending order
        $perPage = $request->query('per_page', 10); // Default to 10 items per page

        // Initialize query
        $query = Product::query()->orderBy('priority', 'desc');

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
