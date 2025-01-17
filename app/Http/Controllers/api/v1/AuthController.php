<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserAuth\LoginRequest;
use App\Http\Requests\UserAuth\RegisterRequest;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use App\Traits\BaseApiResponse;
use App\Traits\CheckUserPermission;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    use BaseApiResponse, CheckUserPermission;

    public function Login(LoginRequest $request): JsonResponse
    {
        try {
            $user = User::query()
                ->select('id', 'email', 'password')
                //status 1 is active user
                ->where('status', 1)
                ->where('email', $request->input('email'))
                ->first();

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
            return $this->failed($exception->getMessage(), 'Error', 'Error from server');
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

    //forgotPassword of user email send new password to user email and update password in database with new password and send email to user
    public function resetPassword(Request $request)
    {
        // Validate the email
        $request->validate(['email' => 'required|email']);

        try {
            // Check if the user exists
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return $this->failed(null, 'Fail', 'User not found', 404);
            }

            // Generate a secure random password
            $newPassword = Str::random(12); // Customize complexity as needed

            // Hash and update the password
            $user->password = $newPassword;
            $user->save();

            // Send the new password to the user
            $user->notify(new ResetPasswordNotification($newPassword));

            // Return success response
            return $this->success(null, 'Success', 'Password reset successful. Please check your email.');

        } catch (Exception $exception) {
            // Log the error for debugging purposes
            \Log::error("Password reset error: " . $exception->getMessage());

            return $this->failed($exception->getMessage(), 'Error', 'Error resetting password');
        }
    }

    //Logout
    public function Logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return $this->success(null, 'Logout', 'Logout successful');
    }
}
