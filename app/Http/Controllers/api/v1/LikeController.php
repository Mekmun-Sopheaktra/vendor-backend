<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\LikeProducts;
use App\Models\Product;
use App\Traits\BaseApiResponse;

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
}
