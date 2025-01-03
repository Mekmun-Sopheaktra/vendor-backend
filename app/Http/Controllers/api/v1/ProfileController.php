<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateAddressRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\Profile\AddressResource;
use App\Notifications\ProfileUpdated;
use App\Traits\BaseApiResponse;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    use BaseApiResponse;

    public function index(): JsonResponse
    {
        return $this->success([
            'email' => auth()->user()->email,
            'mobile' => auth()->user()->mobile,
            'name' => auth()->user()->name,
            'profile_url' => secure_asset('storage/'.auth()->user()->image) ?? config('image.avatar_url'),
            'age' => auth()->user()->age ?? 0,
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
            $user->profile_photo_path = $request->file('image')->store('image', 'public');
        }
        $user->save();

        // Set notification
        $user->notifications()->create([
            'title' => 'Profile Updated',
            'description' => 'Your profile has been updated successfully',
        ]);

        return $this->success([
            'name' => $user->name,
            'mobile' => $user->mobile,
            'profile_url' => asset('storage/'.$user->profile_photo_path) ?? config('image.avatar_url'),
            'age' => $user->age,
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
