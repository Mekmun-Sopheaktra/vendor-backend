<?php

namespace App\Http\Resources\Basket;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BasketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'product_id' => $this->product_id,
            'count' => $this->count,
            'status' => $this->status,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            'product' => [
                'id' => $this->product->id,
                'user_id' => $this->product->user_id,
                'vendor_id' => $this->product->vendor_id,
                'brand_id' => $this->product->brand_id,
                'title' => $this->product->title,
                'slug' => $this->product->slug,
                'description' => $this->product->description,
                'price' => $this->product->price,
                'image' => $this->product->image,
                'volume' => $this->product->volume,
                'product_code' => $this->product->product_code,
                'manufacturing_date' => $this->product->manufacturing_date,
                'expire_date' => $this->product->expire_date,
                'fragrance_family' => $this->product->fragrance_family,
                'gender' => $this->product->gender,
                'inventory' => $this->product->inventory,
                'view_count' => $this->product->view_count,
                'is_compound_product' => $this->product->is_compound_product,
                'discount' => $this->product->discount,
                'priority' => $this->product->priority,
                'created_at' => $this->product->created_at->toISOString(),
                'updated_at' => $this->product->updated_at->toISOString(),
            ],
        ];
    }
}
