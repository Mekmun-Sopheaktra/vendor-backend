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
            'vendor_id' => 'required',
            'address' => 'required|string',
            'phone' => 'required|string',
            'transaction_method' => 'required|in:cod,paypal',
            'transaction_id' => 'required_if:transaction_method,paypal|string',
            'amount' => 'required_if:transaction_method,paypal|numeric',
        ];
    }
}
