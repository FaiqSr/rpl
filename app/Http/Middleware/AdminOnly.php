<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    /**
     * Handle an incoming request.
     * Allows only authenticated users with role 'admin'.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        if (!$user || ($user->role ?? 'visitor') !== 'admin') {
            return redirect()->route('home')->with('error', 'Akses ditolak: khusus admin.');
        }
        return $next($request);
    }
}
