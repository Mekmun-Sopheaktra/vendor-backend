<?php

namespace App\Http\Controllers\vendor;

use App\Constants\RoleConstants;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserAuth\LoginRequest;
use App\Models\User;
use App\Traits\BaseApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class VendorAuthController extends Controller
{
    use BaseApiResponse;

    public function Login(LoginRequest $request): JsonResponse
    {
        try {
            $user = User::query()->select('id', 'email', 'password')
                //status 1 is active user
                ->where('status', 1)
                ->where('email', $request->input('email'))
                ->where('is_vendor', true)
                ->first();

            if (!$user || !Hash::check($request->input('password'), $user->password)) {
                return $this->failed(null, 'Invalid', 'Invalid credentials', 401);
            }

            $token = $user->createToken('token_base_name')->plainTextToken;

            $vendor = [
                'user' => $user,
                'token' => $token,
            ];

            return $this->successLogin($vendor, RoleConstants::VENDOR, 'Login', 'Login successful');
        } catch (Exception $exception) {
            return $this->failed($exception->getMessage(), 'Error', 'Error form server');
        }
    }
}
