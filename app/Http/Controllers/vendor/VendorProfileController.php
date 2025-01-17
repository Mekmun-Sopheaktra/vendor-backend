<?php

namespace App\Http\Controllers\vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateAddressRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\Profile\AddressResource;
use App\Traits\BaseApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorProfileController extends Controller
{
    use BaseApiResponse;

    public function index(): JsonResponse
    {
        $user = auth()->user();
        //get vendor data
        $vendor = $user->vendor;
        return $this->success([
            'email' =>$user->email,
            'mobile' => $user->mobile,
            'name' => $user->name,
            'image' => $user->image,
            'age' => $user->age ?? 0,
            'vendor_name' => $vendor->name,
            'vendor_slug' => $vendor->slug,
            'vendor_address' => $vendor->address,
            'vendor_description' => $vendor->description,
            'vendor_logo' => $vendor->logo,
            'vendor_status' => $vendor->status,
            'vendor_paypal_client_id' => $vendor->paypal_client_id,
        ]);
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = auth()->user();
        $user->name = $request->name;
        $user->age = $request->age;
        $user->mobile = $request->mobile;
        //store image
        if ($request->hasFile('image')) {
            $user->image = $request->file('image')->store('image', 'public');
        }
        $user->save();

        // Update vendor data
        $vendor = $user->vendor;
        $vendor->name = $request->vendor_name;
        $vendor->slug = $request->vendor_slug;
        $vendor->address = $request->vendor_address;
        $vendor->description = $request->vendor_description;
        $vendor->status = $request->vendor_status;
        $vendor->paypal_client_id = $request->vendor_paypal_client_id;
        //store image
        if ($request->hasFile('image')) {
            $vendor->logo = $request->file('image')->store('image', 'public');
        }
        $vendor->save();

        // Set notification
        $user->notifications()->create([
            'title' => 'Profile Updated',
            'description' => 'Your profile has been updated successfully',
        ]);

        return $this->success([
            'email' =>$user->email,
            'name' => $user->name,
            'mobile' => $user->mobile,
            'image' => $user->image,
            'age' => $user->age,
            'vendor_name' => $vendor->name,
            'vendor_slug' => $vendor->slug,
            'vendor_address' => $vendor->address,
            'vendor_description' => $vendor->description,
            'vendor_logo' => $vendor->logo,
            'vendor_status' => $vendor->status,
            'vendor_paypal_client_id' => $vendor->paypal_client_id,
        ]);
    }

    public function address()
    {
        return $this->success(AddressResource::collection(auth()->user()->address));
    }

    public function store_address(UpdateAddressRequest $request)
    {
        $address = auth()->user()->address()->create($request->validated());

        return $this->success(new AddressResource($address));
    }
}
