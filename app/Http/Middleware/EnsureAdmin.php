<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $authUser = session('auth_user');

        if (! $authUser) {
            return redirect()->route('login')->withErrors([
                'email' => 'Silakan masuk sebagai admin terlebih dahulu.',
            ]);
        }

        if (($authUser['role'] ?? null) !== 'admin') {
            session()->flash('status', 'Akses ditolak. Halaman ini khusus admin.');
            return redirect()->route('home');
        }

        return $next($request);
    }
}
