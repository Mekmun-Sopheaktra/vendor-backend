<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function verify($user_id, Request $request) {
        if (!$request->hasValidSignature()) {
            return response()->json(["msg" => "Invalid/Expired url provided."], 401);
        }

        $user = User::findOrFail($user_id);

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return redirect()->to('https://www.facebook.com/sotpanhnha.info');
    }

    public function resend() {
        if (auth()->user()->hasVerifiedEmail()) {
            return response()->json(["msg" => "Email already verified."], 400);
        }

        // Prevent multiple requests in a short period
        if (session('verification_sent', false)) {
            return response()->json(["msg" => "A verification email has already been sent. Please check your inbox."], 429);
        }

        auth()->user()->sendEmailVerificationNotification();
        session(['verification_sent' => true]);

        return response()->json(["msg" => "Email verification link sent to your email address."]);
    }


    //show
    public function show()
    {
        return view('mail.verify-user');
    }
}
