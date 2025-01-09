<?php

namespace App\Http\Controllers\api\v1;

use App\Constants\ProductPriority;
use App\Http\Controllers\Controller;
use App\Http\Requests\Search\SearchRequest;
use App\Http\Resources\Comment\CommentResource;
use App\Http\Resources\Product\wishListCollection;
use App\Models\Category;
use App\Models\Discount;
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
        $productsQuery = Product::query()->where('status', true);

        // title
        if (!empty($validatedData['title'])) {
            $productsQuery->where('title', 'like', '%' . $validatedData['title'] . '%');
        }

        // volume
        if (!empty($validatedData['volume'])) {
            $productsQuery->where('volume', 'like', '%' . $validatedData['volume'] . '%');
        }

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

        $products = $productsQuery->with(['category', 'tags', 'galleries'])
            ->paginate($perPage, ['*'], 'page', $page);

        $discounts = Discount::query()
            ->where('status', 1)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->get()
            ->keyBy('product_id');

        $products->getCollection()->transform(function ($product) use ($discounts) {
            if ($discounts->has($product->id)) {
                $discount = $discounts->get($product->id);
                $product->final_price = $product->price - ($product->price * $discount->discount / 100);
                $product->discount = $discount;
            } else {
                $product->final_price = $product->price;
                $product->discount = null;
            }
            return $product;
        });

        return $this->success($products);
    }

    public function show($id): JsonResponse
    {
        // Find the product by ID, including related categories, tags, and galleries
        $product = Product::with(['category', 'tags', 'galleries'])->where('status', true)->find($id);

        // Return an error if the product is not found
        if (!$product) {
            return $this->failed(null,'Product not found', 404);
        }

        // Retrieve related products
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('discount', '>', 0)
            ->where('status', true)
            ->where('vendor_id', $product->vendor_id)
            ->where('id', '!=', $id) // Exclude the current product
            ->limit(5) // Limit the number of related products
            ->get();


        $discount = Discount::query()
            ->where('status', 1)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->where('product_id', $product->id)
            ->first();

        if ($discount) {
            $product->final_price = $product->price - ($product->price * $discount->discount / 100);
            $product->discount = $discount;
        }

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
        $products = Product::orderBy('created_at', $sortOrder)->where('status', true)->limit(4)->get();

        return $this->success($products);
    }

    public function relatedProducts($id)
    {
        // Find the product by ID and ensure its status is true
        $product = Product::where('id', $id)->where('status', true)->first();

        // Return an error if the product is not found
        if (!$product) {
            return $this->failed('Product not found', 404);
        }

        // Retrieve the related products through the pivot table (category_products)
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $id) // Exclude the current product
            ->limit(5) // Limit the number of related products
            ->get();

        // Return the related products in the response
        return $this->success($relatedProducts);
    }

    public function discountedProducts()
    {
        $discountedProducts = Product::where('discount', '>', 0) // Products with discounts
        ->where('status', true)
        ->orderBy('priority', 'desc') // Sort by priority
                //limit to 4 products
        ->limit(4)
        ->get();

        $discountedProducts->map(function ($product) {
            $product->final_price = $product->price - ($product->price * $product->discount / 100);
            return $product;
        });

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
        $query = Product::query()->where('status', true)->orderBy('priority', 'desc');

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
        ->where('status', true)
        ->paginate($perPage);

        return $this->success($newArrivalProducts);
    }
    public function promotionProducts(Request $request)
    {
        $perPage = $request->query('per_page', 10); // Default to 10 items per page
        $search = $request->query('search', null); // Search keyword
        $filter = $request->query('filter', 'all'); // Filter: all, discount, compound, or compound_discount

        // Initialize query
        $query = Product::query()
            ->where('status', true)
            ->with(['discount'])
            ->where(function ($query) {
                $query->where('discount', '>', 0) // Products with discounts
                ->orWhere('is_compound_product', true); // Or compound products
            });

        // Apply search filter
        if (!empty($search)) {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('title', 'like', '%' . $search . '%');
            });
        }

        // Apply product type filter
        if ($filter === 'discount') {
            $query->where('discount', '>', 0);
        } elseif ($filter === 'compound') {
            $query->where('is_compound_product', true);
        } elseif ($filter === 'compound_discount') {
            $query->where('discount', '>', 0)
                ->where('is_compound_product', true);
        }

        $query->orderBy('discount', 'desc'); // Sort by discount

        // Fetch the results and apply mapping
        $products = $query->get()->map(function ($product) {
            $product->final_price = $product->price - ($product->price * $product->discount / 100);

            // Determine product type
            if ($product->discount > 0 && $product->is_compound_product) {
                $product->product_type = 'compound_discount'; // Both discount and compound
            } elseif ($product->discount > 0) {
                $product->product_type = 'discount';
            } elseif ($product->is_compound_product) {
                $product->product_type = 'compound';
            } else {
                $product->product_type = 'none'; // Fallback, should not occur
            }

            return $product;
        });

        // Paginate the mapped results manually
        $paginatedProducts = $this->paginateCollection($products, $perPage);

        return $this->success($paginatedProducts);
    }

    /**
     * Paginate a collection manually.
     */
    protected function paginateCollection($collection, $perPage)
    {
        $page = request('page', 1); // Get the current page or default to 1
        $total = $collection->count();
        $results = $collection->slice(($page - 1) * $perPage, $perPage)->values();

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $results,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

}
