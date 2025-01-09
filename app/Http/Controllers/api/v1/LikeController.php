<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Product\wishListCollection;
use App\Models\Discount;
use App\Models\LikeProducts;
use App\Models\Product;
use App\Traits\BaseApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class LikeController extends Controller
{
    use BaseApiResponse;

    public function likeProduct(Product $product)
    {
        $userId = auth()->id();

        $existingLike = LikeProducts::query()->where('product_id', $product->id)
            ->where('user_id', $userId)
            ->first();

        $product = Product::query()->where('id', $product->id)->first();

        if ($existingLike) {
            $existingLike->delete();

            return $this->success($product, 'Success', 'Product unliked successfully');
        }

        $like = LikeProducts::create([
            'product_id' => $product->id,
            'user_id' => $userId,
        ]);

        //query product to get the updated likes count

        return $this->success($like, 'Success', 'Product liked successfully');
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

        // Fetch active discounts keyed by product_id
        $discounts = Discount::query()
            ->where('status', 1)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->get()
            ->keyBy('product_id');

        // Log the keys of the discounts collection to verify if product_id matches
        // Paginate the liked products
        $paginatedProducts = $productsQuery->paginate($perPage);

        // Transform the products collection
        $paginatedProducts->getCollection()->transform(function ($item) use ($discounts) {
            $product = $item->product; // Nested product object

            // Check if a discount exists for the product
            if ($discounts->has($product->id)) {
                $discount = $discounts->get($product->id); // Get the individual discount
                $product->discount = $discount->discount;
                $product->final_price = $product->price - ($product->price * $discount->discount / 100);
            } else {
                $product->discount = null;
                $product->final_price = $product->price;
            }

            return $item;
        });

        $data = [
            'products' => new wishListCollection($paginatedProducts),
            'pagination' => [
                'page_number' => $paginatedProducts->currentPage(),
                'total_rows' => $paginatedProducts->total(),
                'total_pages' => $paginatedProducts->lastPage(),
                'has_previous_page' => $paginatedProducts->previousPageUrl() !== null,
                'has_next_page' => $paginatedProducts->nextPageUrl() !== null,
            ],
        ];

        return $this->success($data);
    }

    //unlikeProduct
    public function unlikeProduct(Product $product)
    {
        $userId = auth()->id();

        $existingLike = LikeProducts::query()->where('product_id', $product->id)
            ->where('user_id', $userId)
            ->first();

        if ($existingLike) {
            $existingLike->delete();

            return $this->success($product, 'Success', 'Product unliked successfully');
        }

        return $this->error('Product not found', 404);
    }
}
