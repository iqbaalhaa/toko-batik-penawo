<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Auth — session-based against users table. TIDAK memakai Laravel Auth guard;
 * status login disimpan sebagai array di session('auth_user').
 */
class AuthController extends Controller
{
    public function showLogin()
    {
        $authUser = session('auth_user');
        if ($authUser) {
            return redirect(($authUser['role'] ?? 'pelanggan') === 'admin' ? route('admin.dashboard') : route('home'));
        }
        return view('home.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            // Akun soft-deleted tidak ikut query default — beri pesan yang jelas.
            if (! $user && User::withTrashed()->where('email', $data['email'])->exists()) {
                return back()->withErrors(['email' => 'Akun ini telah dihapus. Hubungi admin untuk pemulihan akun.'])->withInput($request->only('email'));
            }
            return back()->withErrors(['email' => 'Email atau password salah.'])->withInput($request->only('email'));
        }

        if ($user->status !== 'aktif') {
            return back()->withErrors(['email' => 'Akun Anda nonaktif. Silakan hubungi admin.'])->withInput($request->only('email'));
        }

        session(['auth_user' => [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'role'  => $user->role,
        ]]);

        return redirect($user->role === 'admin' ? route('admin.dashboard') : route('home'));
    }

    public function showRegister()
    {
        if (session('auth_user')) {
            return redirect(route('home'));
        }
        return view('home.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|min:2|max:60',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => 'pelanggan',
            'status'   => 'aktif',
        ]);

        session(['auth_user' => [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'role'  => $user->role,
        ]]);

        return redirect(route('home'));
    }

    public function logout()
    {
        session()->forget('auth_user');
        return redirect(route('home'));
    }
}
