<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Akun pelanggan: profil, daftar pesanan, pengaturan (preferensi, password,
 * hapus akun).
 */
class AccountController extends Controller
{
    public function profil()
    {
        $authUser = session('auth_user');
        if (! $authUser) {
            return redirect()->route('login')->withErrors(['email' => 'Silakan masuk untuk mengakses profil.']);
        }
        $user      = User::findOrFail($authUser['id']);
        $addresses = $user->addresses;
        return view('home.akun.profil', compact('user', 'addresses'));
    }

    public function updateProfil(Request $request)
    {
        $authUser = session('auth_user');
        if (! $authUser) {
            return redirect()->route('login');
        }
        $user = User::findOrFail($authUser['id']);

        // Alamat pengiriman dipindah ke tabel `addresses` dan ubah password
        // dipindah ke halaman Pengaturan — profil hanya mengelola data pribadi.
        $data = $request->validate([
            'name'        => 'required|string|min:2|max:60',
            'email'       => 'required|email|unique:users,email,' . $user->id,
            'phone'       => 'nullable|string|max:25',
            'gender'      => 'nullable|in:pria,wanita',
        ]);

        $user->name        = $data['name'];
        $user->email       = $data['email'];
        $user->phone       = $data['phone']       ?? null;
        $user->gender      = $data['gender']      ?? null;

        if (! empty($data['new_password'])) {
            if (empty($data['current_password']) || ! Hash::check($data['current_password'], $user->password)) {
                return back()->withErrors(['current_password' => 'Password saat ini salah.'])->withInput();
            }
            $user->password = Hash::make($data['new_password']);
        }

        $user->save();

        // Sinkronisasi session
        session(['auth_user' => [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'role'  => $user->role,
        ]]);

        return redirect()->route('akun.profil')->with('status', 'Profil berhasil diperbarui.');
    }

    public function pesanan()
    {
        $authUser = session('auth_user');
        if (! $authUser) {
            return redirect()->route('login')->withErrors(['email' => 'Silakan masuk untuk melihat pesanan.']);
        }
        $orders = Order::with('items')
            ->where(function ($q) use ($authUser) {
                $q->where('user_id', $authUser['id'])->orWhere('customer_email', $authUser['email']);
            })
            ->latest()
            ->paginate(10);
        return view('home.akun.pesanan', compact('orders'));
    }

    public function pengaturan()
    {
        $authUser = session('auth_user');
        if (! $authUser) {
            return redirect()->route('login')->withErrors(['email' => 'Silakan masuk untuk mengakses pengaturan.']);
        }
        $user = User::findOrFail($authUser['id']);
        return view('home.akun.pengaturan', compact('user'));
    }

    public function updatePengaturan(Request $request)
    {
        $authUser = session('auth_user');
        if (! $authUser) return redirect()->route('login');
        $user = User::findOrFail($authUser['id']);

        // Checkbox: tidak terkirim saat unchecked → cast manual.
        $user->notify_order_updates = $request->boolean('notify_order_updates');
        $user->notify_promo         = $request->boolean('notify_promo');
        $user->save();

        return redirect()->route('akun.pengaturan')->with('status', 'Preferensi berhasil disimpan.');
    }

    // Ubah password — terpisah dari pengaturan utama, validasi password lama wajib.
    public function updatePassword(Request $request)
    {
        $authUser = session('auth_user');
        if (! $authUser) return redirect()->route('login');
        $user = User::findOrFail($authUser['id']);

        $data = $request->validate([
            'current_password'          => 'required|string',
            'new_password'              => 'required|string|min:6|confirmed',
        ]);

        if (! Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini salah.']);
        }

        $user->password = Hash::make($data['new_password']);
        $user->save();

        return redirect()->route('akun.pengaturan')
            ->with('status', 'Password berhasil diperbarui. Gunakan password baru di login berikutnya.');
    }

    // Hapus akun — wajib konfirmasi password + checkbox understanding.
    public function hapusAkun(Request $request)
    {
        $authUser = session('auth_user');
        if (! $authUser) return redirect()->route('login');
        $user = User::findOrFail($authUser['id']);

        $data = $request->validate([
            'password'      => 'required|string',
            'confirm_phrase' => 'required|in:HAPUS AKUN SAYA',
        ], [
            'confirm_phrase.in' => 'Frasa konfirmasi tidak sesuai. Ketik persis "HAPUS AKUN SAYA".',
        ]);

        if (! Hash::check($data['password'], $user->password)) {
            return back()->withErrors(['password' => 'Password salah. Akun tidak dihapus.']);
        }

        // Blokir admin menghapus akun lewat sini supaya tidak ada yang sengaja
        // ngosongin sistem; admin harus dikelola via panel admin.
        if ($user->role === 'admin') {
            return back()->withErrors(['password' => 'Akun admin tidak dapat dihapus dari sini.']);
        }

        // Soft delete: baris users hanya ditandai deleted_at. Alamat dan
        // pesanan dibiarkan utuh supaya akun dapat dipulihkan admin bila perlu.
        $user->delete();

        // Logout total.
        session()->forget('auth_user');
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('home')
            ->with('status', 'Akun Anda telah dihapus. Hubungi admin jika ingin memulihkan akun. Terima kasih sudah berbelanja di Batik Penawuo.');
    }
}
