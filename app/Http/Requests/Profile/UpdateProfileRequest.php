<?php

namespace App\Http\Requests\Profile;

use App\Traits\CheckUserPermission;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    use CheckUserPermission;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'age' => 'nullable|integer',
            'mobile' => 'nullable|string|max:255',
            'vendor_name' => 'nullable|string|max:255',
            'vendor_slug' => 'nullable|string|max:255',
            'vendor_address' => 'nullable|string|max:255',
            'vendor_description' => 'nullable|string|max:255',
            'vendor_logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'vendor_status' => 'nullable|string|max:255',
            'vendor_paypal_client_id' => 'nullable|string|max:255',
        ];
    }
}
