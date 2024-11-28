<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\api\Exception;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Traits\BaseApiResponse;
use App\Traits\CheckUserPermission;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use BaseApiResponse, CheckUserPermission;

    public function Login(LoginRequest $request): JsonResponse
    {
        try {
            $user = User::query()->select('id', 'email', 'password')->where('email', $request->input('email'))->first();

            if (! $user || ! Hash::check($request->input('password'), $user->password)) {
                return $this->failed(null, 'Invalid', 'Invalid credentials', 401);
            }

            $token = $user->createToken('token_base_name')->plainTextToken;
            $permission = $this->userPermissionRole($user);

            return $this->successLogin($token, $permission,'Login', 'Login successful');
        } catch (Exception $exception) {
            return $this->failed($exception->getMessage(), 'Error', 'Error form server');
        }
    }

    public function Register(RegisterRequest $request): JsonResponse
    {
        // Check if the user has already registered recently
        $user = User::query()->where('email', $request->input('email'))->first();
        if ($user) {
            return $this->failed(null, 'Fail', 'User already exists', 409);
        }

        try {
            $user = User::query()->create($request->all());
            $token = $user->createToken('token_base_name')->plainTextToken;

            $user->sendEmailVerificationNotification(); // This sends the email

            session(['registered_time' => now()]); // Store the time of registration

            return $this->success($token, 'Registration', 'Registration successful', 201);
        } catch (Exception $exception) {
            return $this->failed($exception->getMessage(), 'Error', 'Error from server');
        }
    }

    public function Permission(): JsonResponse
    {
        $auth = auth()->user();
        if (!$auth) {
            $permissions = $this->userPermissionRole($auth);
            return $this->success($permissions, 'Permission', 'Public permissions found');
        }

        try {
            $permissions = $this->userPermissionRole($auth);
            $role = $this->userPermission($auth);
            return $this->success($permissions, 'Permission '.$role, 'Permissions found');

        } catch (Exception $exception) {
            return $this->failed($exception->getMessage(), 'Error', 'Error from server');
        }
    }

}
