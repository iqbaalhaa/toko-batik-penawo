<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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

    // ---- Lupa kata sandi ----

    public function showLupaPassword()
    {
        return view('home.lupa-password');
    }

    public function kirimLinkReset(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        // Selalu tampilkan pesan sukses meski email tidak ada — hindari email enumeration.
        if ($user) {
            $token = Str::random(64);

            DB::table('password_reset_tokens')->upsert(
                ['email' => $user->email, 'token' => hash('sha256', $token), 'created_at' => now()],
                ['email'],
                ['token', 'created_at']
            );

            $resetUrl = url(route('reset-password.show', ['token' => $token, 'email' => $user->email], false));

            Mail::to($user->email)->send(new ResetPasswordMail($resetUrl, $user->name));
        }

        return back()->with('status', 'Jika email terdaftar, tautan reset kata sandi telah dikirim. Periksa kotak masuk (dan folder spam) Anda.');
    }

    public function showResetPassword(Request $request, string $token)
    {
        $email = $request->query('email', '');

        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (! $record || ! hash_equals($record->token, hash('sha256', $token))) {
            return redirect()->route('lupa-password')
                ->withErrors(['email' => 'Tautan reset tidak valid atau sudah kedaluwarsa.']);
        }

        // Token kedaluwarsa setelah 60 menit.
        if (now()->diffInMinutes($record->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return redirect()->route('lupa-password')
                ->withErrors(['email' => 'Tautan reset sudah kedaluwarsa. Silakan minta tautan baru.']);
        }

        return view('home.reset-password', compact('token', 'email'));
    }

    public function prosesReset(Request $request)
    {
        $data = $request->validate([
            'token'    => 'required|string',
            'email'    => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $data['email'])->first();

        if (! $record || ! hash_equals($record->token, hash('sha256', $data['token']))) {
            return back()->withErrors(['password' => 'Tautan reset tidak valid atau sudah kedaluwarsa.']);
        }

        if (now()->diffInMinutes($record->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $data['email'])->delete();
            return redirect()->route('lupa-password')
                ->withErrors(['email' => 'Tautan reset sudah kedaluwarsa. Silakan minta tautan baru.']);
        }

        $user = User::where('email', $data['email'])->first();
        if (! $user) {
            return back()->withErrors(['password' => 'Akun tidak ditemukan.']);
        }

        $user->password = Hash::make($data['password']);
        $user->save();

        DB::table('password_reset_tokens')->where('email', $data['email'])->delete();

        return redirect()->route('login')
            ->with('status', 'Kata sandi berhasil diperbarui. Silakan masuk dengan kata sandi baru Anda.');
    }
}
