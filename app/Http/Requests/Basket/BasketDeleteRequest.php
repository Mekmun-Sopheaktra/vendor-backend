<?php

namespace App\Http\Requests\Basket;

use App\Traits\CheckUserPermission;
use Illuminate\Foundation\Http\FormRequest;

class BasketDeleteRequest extends FormRequest
{
    use CheckUserPermission;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product' => ['required', 'exists:products,id'],
        ];
    }
}
