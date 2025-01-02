<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Product\wishListCollection;
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

        return $this->success($product, 'Success', 'Product liked successfully');
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
