<?php

namespace App\Services;

use App\Models\Product;
use App\Models\SiteSetting;

/**
 * Orchestrator perhitungan ongkir saat checkout.
 *
 * - Mengelompokkan baris keranjang berdasarkan `store_id` produk.
 * - Menghitung total berat per toko (qty × weight_kg).
 * - Memanggil ShippingCalculator untuk tiap toko.
 * - Mengagregasi subtotal, total ongkir, dan grand total.
 *
 * Saat ini sistem masih single-tenant (Batik Penawo) sehingga semua produk
 * dikelompokkan di bawah toko default yang dikonfigurasi via SiteSetting
 * (`store_*`). Saat skema multi-toko/petani ditambahkan, cukup ganti
 * `resolveStoreFor()` untuk membaca dari relasi `$product->store`.
 */
class CheckoutShippingService
{
    /**
     * @param  array<int,array{product:Product,qty:int,unit_price:int,name?:string,size?:?string,color?:?string,image_url?:?string,cart_key?:string}>  $lines
     * @param  array|null  $buyerAddress  Hasil User::shippingAddress(); null jika belum dipilih.
     */
    public function summary(array $lines, ?array $buyerAddress): array
    {
        $errors = [];

        // Validasi awal: alamat pembeli wajib.
        if ($buyerAddress === null) {
            $errors[] = 'Alamat pengiriman pembeli belum dipilih atau belum lengkap (kota & kecamatan wajib).';
        }

        $stores = [];
        foreach ($lines as $line) {
            $product = $line['product'];
            $qty     = (int) $line['qty'];

            // Validasi: produk wajib punya berat numerik > 0.
            $weightKg = (float) ($product->weight_kg ?? 0);
            if ($weightKg <= 0) {
                $errors[] = sprintf('Produk "%s" belum memiliki berat (weight_kg). Hubungi penjual.', $product->name);
                continue;
            }

            // Resolve toko penjual untuk produk ini.
            $store = $this->resolveStoreFor($product);
            $key   = $store['store_id'];

            if (! isset($stores[$key])) {
                $stores[$key] = [
                    'store_id'        => $store['store_id'],
                    'store_name'      => $store['store_name'],
                    'store_address'   => $store['address'],
                    'items'           => [],
                    'total_weight_kg' => 0.0,
                    'subtotal'        => 0,
                ];
            }

            $itemSubtotal = $qty * (int) $line['unit_price'];
            $stores[$key]['items'][] = [
                'cart_key'   => $line['cart_key']  ?? null,
                'name'       => $line['name']      ?? $product->name,
                'qty'        => $qty,
                'unit_price' => (int) $line['unit_price'],
                'subtotal'   => $itemSubtotal,
                'size'       => $line['size']      ?? null,
                'color'      => $line['color']     ?? null,
                'image_url'  => $line['image_url'] ?? null,
                'weight_kg'  => $weightKg,
            ];
            $stores[$key]['total_weight_kg'] += $qty * $weightKg;
            $stores[$key]['subtotal']        += $itemSubtotal;
        }

        // Hitung ongkir per toko.
        $subtotalProducts = 0;
        $shippingTotal    = 0;
        $allAvailable     = $errors === [];   // jika sudah ada error pra-validasi, blokir checkout
        $storesOut        = [];

        foreach ($stores as $store) {
            $shipping = ShippingCalculator::calculate(
                $store['store_address'],
                $buyerAddress ?? [],
                $store['total_weight_kg'],
            );

            if (! $shipping['available']) {
                $allAvailable = false;
                $errors[]     = sprintf('Toko %s: %s', $store['store_name'], $shipping['message']);
            }

            $subtotalProducts += $store['subtotal'];
            $shippingTotal    += $shipping['shipping_cost'];

            $storesOut[] = array_merge($store, [
                // Bulatkan untuk display: backend pakai nilai ceiled dari calculator.
                'total_weight_kg_display' => $shipping['total_weight_kg'],
                'shipping'                => $shipping,
            ]);
        }

        $grandTotal = $subtotalProducts + $shippingTotal;

        return [
            'stores'             => $storesOut,
            'subtotal_products'  => $subtotalProducts,
            'shipping_total'     => $shippingTotal,
            'grand_total'        => $grandTotal,
            'all_available'      => $allAvailable,
            'errors'             => $errors,
            'buyer_address'      => $buyerAddress,
        ];
    }

    /**
     * Resolve "toko" untuk sebuah produk.
     *
     * Implementasi saat ini: semua produk berasal dari toko tunggal yang
     * alamatnya disimpan di SiteSetting (`store_*`). Saat ada tabel petani/
     * stores, ganti method ini untuk membaca dari `$product->store`.
     */
    protected function resolveStoreFor(Product $product): array
    {
        // Future-proof hook: jika produk sudah punya kolom store_id, gunakan itu.
        if (isset($product->store_id) && method_exists($product, 'store') && $product->store) {
            return [
                'store_id'   => (int) $product->store_id,
                'store_name' => $product->store->name,
                'address'    => $product->store->shippingAddress(),
            ];
        }

        return self::defaultStore();
    }

    /**
     * Toko default yang dibaca dari SiteSetting. Dipublikasikan supaya dapat
     * digunakan oleh halaman admin untuk pratinjau.
     */
    public static function defaultStore(): array
    {
        return [
            'store_id'   => 'default',
            'store_name' => SiteSetting::get('store_name') ?? config('app.name', 'Toko'),
            'address'    => [
                'province_id'   => SiteSetting::get('store_province_id'),
                'province_name' => SiteSetting::get('store_province_name'),
                'city_id'       => SiteSetting::get('store_city_id'),
                'city_name'     => SiteSetting::get('store_city_name'),
                'district_id'   => SiteSetting::get('store_district_id'),
                'district_name' => SiteSetting::get('store_district_name'),
                'full_address'  => SiteSetting::get('store_full_address')
                    ?? SiteSetting::get('contact_address'),
            ],
        ];
    }
}
