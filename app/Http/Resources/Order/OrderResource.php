<?php

namespace App\Http\Resources\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'amount' => $this->amount, // Assuming a pivot table with 'price'
            'address' => $this->address, // Assuming an 'address' relationship in the Order model
            'phone' => $this->phone,
            'transaction_method' => $this->transaction_method,
            'transaction_id' => $this->transaction_id,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ],
            'products' => $this->products_details, // Assuming a 'products' relationship in the Order model
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
