<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Seeder;

class StockMovementSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        // Restok / barang masuk
        $incoming = [
            ['sku' => 'BP-001', 'qty' => 20, 'note' => 'Restok dari pengrajin Kerinci',  'ref' => 'PO-2026-041', 'date' => '2026-04-19 08:00:00'],
            ['sku' => 'BP-008', 'qty' => 15, 'note' => 'Produksi internal batch 4',    'ref' => 'PO-2026-040', 'date' => '2026-04-18 09:30:00'],
            ['sku' => 'BP-023', 'qty' => 12, 'note' => 'Restok supplier alas kaki',    'ref' => 'PO-2026-039', 'date' => '2026-04-17 10:15:00'],
            ['sku' => 'BP-024', 'qty' => 8,  'note' => 'Produksi internal',            'ref' => 'PO-2026-038', 'date' => '2026-04-16 14:00:00'],
            ['sku' => 'BP-011', 'qty' => 25, 'note' => 'Restok pengrajin Kerinci',     'ref' => 'PO-2026-037', 'date' => '2026-04-15 11:20:00'],
            ['sku' => 'BP-002', 'qty' => 10, 'note' => 'Restok batch reguler',         'ref' => 'PO-2026-036', 'date' => '2026-04-14 13:45:00'],
            ['sku' => 'BP-021', 'qty' => 18, 'note' => 'Restok supplier sutra',        'ref' => 'PO-2026-035', 'date' => '2026-04-13 09:00:00'],
            ['sku' => 'BP-029', 'qty' => 30, 'note' => 'Produksi internal bros',       'ref' => 'PO-2026-034', 'date' => '2026-04-12 15:30:00'],
        ];

        foreach ($incoming as $in) {
            $product = Product::where('sku', $in['sku'])->first();
            if (! $product) {
                continue;
            }

            StockMovement::create([
                'product_id'  => $product->id,
                'user_id'     => $admin?->id,
                'type'        => 'masuk',
                'qty'         => $in['qty'],
                'reference'   => $in['ref'],
                'note'        => $in['note'],
                'occurred_at' => $in['date'],
            ]);
        }

        // Barang keluar otomatis dari setiap order item (order yang sudah dikirim/selesai)
        $shippedOrders = Order::whereIn('status', ['dikirim', 'selesai'])->with('items')->get();
        foreach ($shippedOrders as $order) {
            foreach ($order->items as $item) {
                if (! $item->product_id) {
                    continue;
                }

                StockMovement::create([
                    'product_id'  => $item->product_id,
                    'user_id'     => $admin?->id,
                    'type'        => 'keluar',
                    'qty'         => $item->qty,
                    'reference'   => $order->invoice_number,
                    'note'        => 'Pesanan ' . $order->invoice_number,
                    'occurred_at' => $order->created_at,
                ]);
            }
        }
    }
}
