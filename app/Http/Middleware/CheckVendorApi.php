<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckVendorApi
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            $user = User::where('email', $request->email)->first();
            if ($user) {
                Auth::setUser($user);
            }
        }

        if (auth()->check() && (auth()->user()->is_superuser || auth()->user()->is_vendor)) {
            return $next($request);
        }

        return response()->json([
            'result' => null,
            'status' => false,
            'alert' => 'You are not authorized to access this resource',
        ], 403);
    }
}
