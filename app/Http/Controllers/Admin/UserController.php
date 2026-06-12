<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        return view('admin.user', [
            'users' => User::withCount('orders')->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|min:2|max:60',
            'email'    => 'required|email|max:120|unique:users,email',
            'phone'    => 'nullable|string|max:30',
            'password' => 'required|string|min:6',
            'role'     => 'required|in:admin,staff,pelanggan',
            'status'   => 'required|in:aktif,nonaktif',
        ]);

        User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'phone'    => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'role'     => $data['role'],
            'status'   => $data['status'],
        ]);

        return redirect()->route('admin.user')->with('status', 'User baru berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => 'required|string|min:2|max:60',
            'email'    => ['required', 'email', 'max:120', Rule::unique('users', 'email')->ignore($user->id)],
            'phone'    => 'nullable|string|max:30',
            'password' => 'nullable|string|min:6',
            'role'     => 'required|in:admin,staff,pelanggan',
            'status'   => 'required|in:aktif,nonaktif',
        ]);

        // Cegah admin yang sedang login mengunci dirinya sendiri (demote/nonaktif).
        $authUser = session('auth_user');
        $isSelf   = $authUser && (int) $authUser['id'] === (int) $user->id;
        if ($isSelf && ($data['role'] !== 'admin' || $data['status'] !== 'aktif')) {
            return back()->withErrors([
                'role' => 'Anda tidak dapat menurunkan peran/menonaktifkan akun sendiri yang sedang login.',
            ])->withInput();
        }

        $user->name   = $data['name'];
        $user->email  = $data['email'];
        $user->phone  = $data['phone'] ?? null;
        $user->role   = $data['role'];
        $user->status = $data['status'];
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        return redirect()->route('admin.user')->with('status', 'Data user "' . $user->name . '" berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        // Tidak boleh hapus diri sendiri.
        $authUser = session('auth_user');
        if ($authUser && (int) $authUser['id'] === (int) $user->id) {
            return back()->withErrors(['user' => 'Anda tidak dapat menghapus akun sendiri.']);
        }
        // Jaga-jaga: minimal harus tersisa 1 admin aktif setelah penghapusan.
        if ($user->role === 'admin') {
            $remainingAdmins = User::where('role', 'admin')->where('status', 'aktif')->where('id', '!=', $user->id)->count();
            if ($remainingAdmins === 0) {
                return back()->withErrors(['user' => 'Tidak dapat menghapus admin terakhir yang aktif.']);
            }
        }
        $name = $user->name;
        $user->delete();
        return redirect()->route('admin.user')->with('status', 'User "' . $name . '" berhasil dihapus.');
    }
}
