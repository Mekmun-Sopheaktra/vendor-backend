<?php

namespace App\Http\Controllers\admin;

use App\Constants\RoleConstants;
use App\Http\Controllers\Controller;
use App\Http\Controllers\api\Exception;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Traits\BaseApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use BaseApiResponse;

    public function Login(LoginRequest $request): JsonResponse
    {
        try {
            $user = User::query()->select('id', 'email', 'password')
                ->where('email', $request->input('email'))
                ->where(function ($query) {
                    $query->where('is_superuser', 1)
                        ->where('is_vendor', 0)
                        ->orWhere(function ($query) {
                            $query->where('is_superuser', 0)
                                ->where('is_vendor', 1);
                        });
                })
                ->first();

            if (! $user || ! Hash::check($request->input('password'), $user->password)) {
                return $this->failed(null, 'Invalid', 'Invalid credentials', 401);
            }

            $token = $user->createToken('token_base_name')->plainTextToken;

            $vendor = [
                'user' => $user,
                'token' => $token,
            ];

            return $this->successLogin($vendor, RoleConstants::VENDOR , 'Login', 'Login successful');
        } catch (Exception $exception) {
            return $this->failed($exception->getMessage(), 'Error', 'Error form server');
        }
    }

    public function Register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = User::query()
                ->select('id')
                ->create($request->all());

            $token = $user->createToken('token_base_name')->plainTextToken;

            return $this->success($token, 'Registration', 'Registration successful', 201);
        } catch (Exception $exception) {
            return $this->failed($exception->getMessage(), 'Error', 'Error form server');
        }
    }
}
