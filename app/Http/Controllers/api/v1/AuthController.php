<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserAuth\LoginRequest;
use App\Http\Requests\UserAuth\RegisterRequest;
use App\Models\User;
use App\Traits\BaseApiResponse;
use App\Traits\CheckUserPermission;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
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

            $user = [
                'user' => $user,
                'token' => $token,
            ];

            return $this->successLogin($user, $permission,'Login', 'Login successful');
        } catch (Exception $exception) {
            return $this->failed($exception->getMessage(), 'Error', 'Error form server');
        }
    }

    public function Register(RegisterRequest $request): JsonResponse
    {
        // Check if the user has already registered recently
        $existingUser = User::query()->where('email', $request->input('email'))->first();
        if ($existingUser) {
            return $this->failed(null, 'Fail', 'User already exists', 409);
        }

        DB::beginTransaction();

        try {
            // Create the user
            $user = User::query()->create($request->all());

            // Generate a token for the user
            $token = $user->createToken('token_base_name')->plainTextToken;

            // Send email verification notification
            $user->sendEmailVerificationNotification();

            // Store registration time in session
            session(['registered_time' => now()]);

            // Commit the transaction
            DB::commit();

            // Prepare user and token response
            $response = [
                'user' => $user,
                'token' => $token,
            ];

            return $this->success($response, 'Registration', 'Registration successful', 201);
        } catch (Exception $exception) {
            // Rollback the transaction in case of error
            DB::rollBack();

            return $this->failed($exception->getMessage(), 'Error', 'Error from server');
        }
    }

    public function me(): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return $this->failed(null, 'Unauthorized', 'Unauthorized', 401);
        }

        return $this->success($user, 'User', 'User found');
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
