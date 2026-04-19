<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin & Staff
        $staff = [
            [
                'name'     => 'Admin Penawo',
                'email'    => 'admin@penawo.id',
                'password' => Hash::make('admin123'),
                'role'     => 'admin',
                'phone'    => '081234567890',
                'status'   => 'aktif',
            ],
            [
                'name'     => 'Operator Gudang',
                'email'    => 'gudang@penawo.id',
                'password' => Hash::make('gudang123'),
                'role'     => 'staff',
                'phone'    => '081234567891',
                'status'   => 'aktif',
            ],
        ];

        foreach ($staff as $u) {
            User::updateOrCreate(['email' => $u['email']], $u);
        }

        // Pelanggan
        $pelanggan = [
            ['name' => 'Siti Nurhaliza',    'email' => 'siti@example.com',     'phone' => '082100000001'],
            ['name' => 'Budi Santoso',      'email' => 'budi.s@example.com',   'phone' => '082100000002'],
            ['name' => 'Dewi Kartika',      'email' => 'dewi.k@example.com',   'phone' => '082100000003'],
            ['name' => 'Ahmad Fauzi',       'email' => 'ahmadf@example.com',   'phone' => '082100000004'],
            ['name' => 'Rina Melati',       'email' => 'rina@example.com',     'phone' => '082100000005'],
            ['name' => 'Prasetyo Wibowo',   'email' => 'prasetyo@example.com', 'phone' => '082100000006'],
            ['name' => 'Lisa Anggraeni',    'email' => 'lisa.a@example.com',   'phone' => '082100000007', 'status' => 'nonaktif'],
            ['name' => 'Haris Maulana',     'email' => 'harism@example.com',   'phone' => '082100000008'],
        ];

        foreach ($pelanggan as $p) {
            User::updateOrCreate(
                ['email' => $p['email']],
                array_merge($p, [
                    'password' => Hash::make('password123'),
                    'role'     => 'pelanggan',
                    'status'   => $p['status'] ?? 'aktif',
                ])
            );
        }
    }
}
