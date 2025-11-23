<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class EnsureAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $userId = $request->session()->get('auth_user');
        if (!$userId) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu');
        }

        // Load user and share
        $user = User::find($userId);
        if ($user) {
            $request->attributes->set('currentUser', $user);
            view()->share('currentUser', $user);
        }

        return $next($request);
    }
}
