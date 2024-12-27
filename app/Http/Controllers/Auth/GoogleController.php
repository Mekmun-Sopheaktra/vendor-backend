<?php

namespace App\Http\Controllers\Auth;

use App\Constants\RoleConstants;
use App\Traits\BaseApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class GoogleController extends Controller
{
    use BaseApiResponse;
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            $name = $googleUser->getName();
            $email = $googleUser->getEmail();
            $password = Hash::make($googleUser->getId() . $googleUser->getEmail() . $googleUser->getName());

            $user = User::where('email', $email)->first();

            if (!$user) {
                // User doesn't exist, create a new one
                $request = [
                    'name' => $name,
                    'email' => $email,
                    'password' => $password,
                    'email_verified_at' => now(), // Automatically verify the email
                ];
                $user = User::query()->create($request);
            } else {
                // Optionally, mark the email as verified if user exists
                if (is_null($user->email_verified_at)) {
                    $user->email_verified_at = now(); // Automatically verify the email
                    $user->save();
                }
            }

            Auth::login($user);

            return redirect()->route('home');
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors('Unable to login, please try again.');
        }
    }

    //handleGoogleCode($code)
    public function handleGoogleCode(Request $request)
    {
        $code = $request->code;
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            $name = $googleUser->getName();
            $email = $googleUser->getEmail();
            $password = Hash::make($googleUser->getId() . $googleUser->getEmail() . $googleUser->getName());

            $user = User::where('email', $email)->first();

            if (!$user) {
                // User doesn't exist, create a new one
                $request = [
                    'name' => $name,
                    'email' => $email,
                    'password' => $password,
                    'email_verified_at' => now(), // Automatically verify the email
                ];
                $user = User::query()->create($request);
            } else {
                // Optionally, mark the email as verified if user exists
                if (is_null($user->email_verified_at)) {
                    $user->email_verified_at = now(); // Automatically verify the email
                    $user->save();
                }
            }

            Auth::login($user);

            $token = $user->createToken('token_base_name')->plainTextToken;

            $google = [
                'user' => $user,
                'token' => $token,
            ];

            return $this->successLogin($google, RoleConstants::USER , 'Login', 'Login successful');

        } catch (\Exception $e) {
            return $this->failed($e->getMessage(), 'Error', 'Error form server');
        }
    }
}
