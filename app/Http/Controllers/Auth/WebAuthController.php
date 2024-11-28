<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class WebAuthController extends Controller {
    public function showRegistrationForm() {
        return view('auth.register');
    }

    //register
    public function register(RegisterRequest $request) {
        // Check if the user has already registered recently

        // check if user already exists
        $user = User::query()->where('email', $request->input('email'))->first();
        if ($user) {
            //alert not return fail
            return redirect()->route('register.fail');
        }

        try {
            $user = User::query()->create($request->all());

            $user->sendEmailVerificationNotification(); // This sends the email

            session(['registered_time' => now()]); // Store the time of registration

            return redirect()->route('register.done'); // Or wherever you want to redirect
        } catch (Exception $exception) {
            return $this->failed($exception->getMessage(), 'Error', 'Error from server');
        }
    }

    public function showStatus()
    {
        // Ensure the user is logged in
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Check if the email is verified
        $isVerified = Auth::user()->hasVerifiedEmail();

        return view('auth.verification-status', compact('isVerified'));
    }

    //logout
    public function logout() {
        Auth::logout();
        return redirect()->route('login');
    }

    //home route check if user already login to redirect to dashboard or login page
    public function home() {
        if (Auth::check()) {
            return view('dashboard');
        }
        return view('auth.login');
    }
}
