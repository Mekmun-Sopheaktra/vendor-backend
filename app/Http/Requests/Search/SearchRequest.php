<?php

namespace App\Http\Requests\Search;

use App\Traits\CheckUserPermission;
use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    use CheckUserPermission;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|gte:min_price',
            'categories_id' => 'nullable|regex:/^(\d+,)*\d+$/',
            'sort' => 'nullable|in:0,1,2,3',
            'page' => 'nullable|numeric|min:1',
            'title' => 'nullable|string|max:255',
            'volume' => 'nullable|string|max:255',
            'latest' => 'nullable|string|max:255',
        ];
    }
}
