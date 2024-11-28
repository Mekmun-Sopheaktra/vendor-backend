<?php

namespace App\Http\Requests\Basket;

use App\Traits\CheckUserPermission;
use Illuminate\Foundation\Http\FormRequest;

class BasketBuyRequest extends FormRequest
{
    use CheckUserPermission;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shipping_type' => 'required|in:0,1,2,3',
            'address_id' => 'required',
        ];
    }
}
