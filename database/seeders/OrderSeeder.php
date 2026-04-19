<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $orders = [
            ['inv' => 'INV-2026-0412', 'cust' => 'Siti Nurhaliza',    'email' => 'siti@example.com',     'status' => 'dikirim',        'payment' => 'BCA Transfer', 'date' => '2026-04-19 09:15:00', 'skus' => [['BP-003', 3]]],
            ['inv' => 'INV-2026-0411', 'cust' => 'Budi Santoso',      'email' => 'budi.s@example.com',   'status' => 'diproses',       'payment' => 'GoPay',        'date' => '2026-04-19 11:30:00', 'skus' => [['BP-005', 1]]],
            ['inv' => 'INV-2026-0410', 'cust' => 'Dewi Kartika',      'email' => 'dewi.k@example.com',   'status' => 'selesai',        'payment' => 'Mandiri VA',   'date' => '2026-04-18 14:20:00', 'skus' => [['BP-002', 2], ['BP-008', 2]]],
            ['inv' => 'INV-2026-0409', 'cust' => 'Ahmad Fauzi',       'email' => 'ahmadf@example.com',   'status' => 'menunggu_bayar', 'payment' => 'OVO',          'date' => '2026-04-18 16:45:00', 'skus' => [['BP-001', 1]]],
            ['inv' => 'INV-2026-0408', 'cust' => 'Rina Melati',       'email' => 'rina@example.com',     'status' => 'selesai',        'payment' => 'BCA Transfer', 'date' => '2026-04-17 10:10:00', 'skus' => [['BP-007', 1], ['BP-006', 1]]],
            ['inv' => 'INV-2026-0407', 'cust' => 'Prasetyo Wibowo',   'email' => 'prasetyo@example.com', 'status' => 'dikirim',        'payment' => 'Dana',         'date' => '2026-04-17 13:25:00', 'skus' => [['BP-011', 1], ['BP-025', 1]]],
            ['inv' => 'INV-2026-0406', 'cust' => 'Lisa Anggraeni',    'email' => 'lisa.a@example.com',   'status' => 'dibatalkan',     'payment' => 'OVO',          'date' => '2026-04-16 09:40:00', 'skus' => [['BP-013', 1]]],
            ['inv' => 'INV-2026-0405', 'cust' => 'Haris Maulana',     'email' => 'harism@example.com',   'status' => 'selesai',        'payment' => 'BRI VA',       'date' => '2026-04-16 15:05:00', 'skus' => [['BP-021', 3], ['BP-024', 1], ['BP-029', 1]]],
        ];

        foreach ($orders as $o) {
            $user = User::where('email', $o['email'])->first();
            $items = [];
            $total = 0;

            foreach ($o['skus'] as [$sku, $qty]) {
                $product = Product::where('sku', $sku)->first();
                if (! $product) {
                    continue;
                }
                $items[] = [
                    'product_id'   => $product->id,
                    'product_name' => $product->name,
                    'qty'          => $qty,
                    'price'        => $product->price,
                ];
                $total += $product->price * $qty;
            }

            if (empty($items)) {
                continue;
            }

            $order = Order::updateOrCreate(
                ['invoice_number' => $o['inv']],
                [
                    'user_id'          => $user?->id,
                    'customer_name'    => $o['cust'],
                    'customer_email'   => $o['email'],
                    'total'            => $total,
                    'payment_method'   => $o['payment'],
                    'status'           => $o['status'],
                    'shipping_address' => 'Jl. Contoh No. 10, Yogyakarta',
                    'created_at'       => $o['date'],
                    'updated_at'       => $o['date'],
                ]
            );

            $order->items()->delete();
            foreach ($items as $item) {
                $order->items()->create($item);
            }
        }
    }
}
