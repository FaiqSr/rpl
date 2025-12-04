<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $userId = $request->session()->get('auth_user');
        if (!$userId) {
            return redirect()->route('login')->with('error', 'Harus login sebagai admin');
        }
        $user = User::find($userId);
        if (!$user || $user->role !== 'admin') {
            return redirect()->route('home')->with('error', 'Akses ditolak');
        }
        $request->attributes->set('currentUser', $user);
        view()->share('currentUser', $user);
        return $next($request);
    }
}
