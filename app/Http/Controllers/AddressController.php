<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Alamat pengiriman pelanggan: maks 3 per pelanggan (Address::MAX_PER_USER).
 */
class AddressController extends Controller
{
    private function rules(): array
    {
        return [
            'label'         => 'required|string|max:30',
            'province_id'   => 'required|string|max:30|exists:provinces,id',
            'province_name' => 'required|string|max:80',
            'city_id'       => 'required|string|max:30|exists:regencies,id',
            'city_name'     => 'required|string|max:80',
            'district_id'   => 'required|string|max:30|exists:districts,id',
            'district_name' => 'required|string|max:80',
            'full_address'  => 'required|string|max:500',
            'is_default'    => 'nullable|boolean',
        ];
    }

    // Helper: pastikan alamat target memang milik user yang sedang login.
    private function loadAuthAddress(int $id): Address
    {
        $authUser = session('auth_user');
        if (! $authUser) abort(403);
        $address = Address::where('id', $id)->where('user_id', $authUser['id'])->first();
        if (! $address) abort(404);
        return $address;
    }

    public function store(Request $request)
    {
        $authUser = session('auth_user');
        if (! $authUser) {
            return redirect()->route('login')->withErrors(['email' => 'Silakan masuk untuk menambah alamat.']);
        }

        // Whitelist tujuan redirect (menerima form dari profil maupun checkout).
        $redirectTo = in_array($request->input('redirect_to'), ['checkout.show'], true)
            ? 'checkout.show' : 'akun.profil';

        // Cap 3 alamat per pelanggan.
        $count = Address::where('user_id', $authUser['id'])->count();
        if ($count >= Address::MAX_PER_USER) {
            return redirect()->route($redirectTo)
                ->withErrors(['alamat' => 'Maksimal ' . Address::MAX_PER_USER . ' alamat tersimpan. Hapus salah satu untuk menambah.']);
        }

        $data = $request->validate($this->rules());
        $data['user_id']    = $authUser['id'];
        $data['is_default'] = (bool) ($data['is_default'] ?? false);

        // Pertama kali tambah → otomatis jadi default.
        if ($count === 0) {
            $data['is_default'] = true;
        }

        $created = null;
        DB::transaction(function () use (&$data, $authUser, &$created) {
            if ($data['is_default']) {
                Address::where('user_id', $authUser['id'])->update(['is_default' => false]);
            }
            $created = Address::create($data);
        });

        // Saat dipanggil dari checkout, langsung pre-select alamat yang baru dibuat.
        $params = $redirectTo === 'checkout.show' ? ['address_id' => $created->id] : [];
        return redirect()->route($redirectTo, $params)->with('status', 'Alamat baru berhasil ditambahkan.');
    }

    public function update(Request $request, int $address)
    {
        $model   = $this->loadAuthAddress($address);
        $data    = $request->validate($this->rules());
        $data['is_default'] = (bool) ($data['is_default'] ?? false);

        DB::transaction(function () use ($model, $data) {
            if ($data['is_default']) {
                Address::where('user_id', $model->user_id)
                    ->where('id', '!=', $model->id)
                    ->update(['is_default' => false]);
            }
            $model->update($data);
        });

        return redirect()->route('akun.profil')->with('status', 'Alamat berhasil diperbarui.');
    }

    public function destroy(int $address)
    {
        $model    = $this->loadAuthAddress($address);
        $wasDefault = $model->is_default;
        $userId   = $model->user_id;
        $model->delete();

        // Jika yang dihapus adalah default, promosikan alamat lain (yang paling lama) jadi default.
        if ($wasDefault) {
            $next = Address::where('user_id', $userId)->oldest()->first();
            if ($next) {
                $next->update(['is_default' => true]);
            }
        }

        return redirect()->route('akun.profil')->with('status', 'Alamat dihapus.');
    }

    public function setDefault(int $address)
    {
        $model = $this->loadAuthAddress($address);

        DB::transaction(function () use ($model) {
            Address::where('user_id', $model->user_id)->update(['is_default' => false]);
            $model->update(['is_default' => true]);
        });

        return redirect()->route('akun.profil')->with('status', 'Alamat utama diperbarui.');
    }
}
