<?php

namespace App\Http\Controllers\admin;

use App\Constants\RoleConstants;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserAuth\LoginRequest;
use App\Http\Requests\UserAuth\RegisterRequest;
use App\Models\User;
use App\Traits\BaseApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use BaseApiResponse;

    public function Login(LoginRequest $request): JsonResponse
    {
        try {
            $user = User::query()->select('id', 'email', 'password')
                ->where('email', $request->input('email'))
                ->where('is_superuser', 1)
                ->first();

            if (! $user || ! Hash::check($request->input('password'), $user->password)) {
                return $this->failed(null, 'Invalid', 'Invalid credentials', 401);
            }

            $token = $user->createToken('token_base_name')->plainTextToken;

            $vendor = [
                'user' => $user,
                'token' => $token,
            ];

            return $this->successLogin($vendor, RoleConstants::SUPERUSER , 'Login', 'Login successful');
        } catch (Exception $exception) {
            return $this->failed($exception->getMessage(), 'Error', 'Error form server');
        }
    }
    public function Register(RegisterRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $user = User::query()
                ->select('id')
                ->create($request->all());

            $token = $user->createToken('token_base_name')->plainTextToken;

            DB::commit();

            return $this->success($token, 'Registration', 'Registration successful', 201);
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->failed($exception->getMessage(), 'Error', 'Error from server');
        }
    }

    //Logout
    public function logout(): JsonResponse
    {
        try {
            auth()->user()->tokens->each(function ($token) {
                $token->delete();
            });

            return $this->success(null, 'Logout', 'Logout successful');
        } catch (Exception $exception) {
            return $this->failed($exception->getMessage(), 'Error', 'Error from server');
        }
    }
}
