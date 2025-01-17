<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateAddressRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\Profile\AddressResource;
use App\Notifications\ProfileUpdated;
use App\Traits\BaseApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    use BaseApiResponse;

    public function index(): JsonResponse
    {
        return $this->success([
            'email' => auth()->user()->email,
            'mobile' => auth()->user()->mobile,
            'name' => auth()->user()->name,
            'image' => secure_asset('storage/'.auth()->user()->image) ?? config('image.avatar_url'),
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
            $user->image = $request->file('image')->store('image', 'public');
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
            'image' => asset('storage/'.$user->image) ?? config('image.avatar_url'),
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

    //changePassword
    public function changePassword(Request $request)
    {
        // Validate request: old password, new password, and confirmation
        $request->validate([
            'old_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Get authenticated user
        $user = auth()->user();

        // Check if old password matches
        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Old password is incorrect'
            ], 400);
        }

        // Update password
        $user->password = $request->password;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Password changed successfully'
        ]);
    }
}
