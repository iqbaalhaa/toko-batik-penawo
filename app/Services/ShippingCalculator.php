<?php

namespace App\Services;

use App\Models\SiteSetting;

/**
 * Kalkulator simulasi ongkos kirim berbasis wilayah administratif & berat.
 *
 * Tidak memanggil API kurir eksternal (RajaOngkir, JNE, J&T, dsb). Semua
 * perhitungan dilakukan lokal — cocok untuk simulasi marketplace hasil tani
 * lokal yang membutuhkan tarif deterministik dan dapat dijelaskan.
 *
 * Aturan zona (kunci hierarkis: provinsi → kota/kab → kecamatan):
 *  - same_district    : kec sama          → base + max(0, kg-base_kg) × extra
 *  - same_city        : kab sama, kec ≠
 *  - same_province    : prov sama, kab ≠
 *  - outside_province : prov ≠
 *
 * Tarif default ada pada konstanta `DEFAULT_ZONES`/`DEFAULT_BASE_WEIGHT_KG`,
 * namun nilai aktif yang dipakai checkout dapat di-override oleh admin via
 * tabel `site_settings` (key `shipping_*`). Lihat `zones()` dan `baseWeightKg()`.
 */
final class ShippingCalculator
{
    public const DEFAULT_BASE_WEIGHT_KG = 5;

    /**
     * Tarif default — dipakai ketika site_settings belum diisi atau saat
     * akses DB tidak tersedia (mis. unit test tanpa migrasi).
     */
    public const DEFAULT_ZONES = [
        'same_district' => [
            'label'            => 'Antar Desa (dalam 1 kecamatan)',
            'base_fee'         => 10000,
            'extra_fee_per_kg' => 2000,
        ],
        'same_city' => [
            'label'            => 'Antar Kecamatan (dalam 1 kab/kota)',
            'base_fee'         => 20000,
            'extra_fee_per_kg' => 3000,
        ],
        'same_province' => [
            'label'            => 'Antar Kabupaten/Kota (dalam 1 provinsi)',
            'base_fee'         => 30000,
            'extra_fee_per_kg' => 4000,
        ],
        'outside_province' => [
            'label'            => 'Antar Provinsi',
            'base_fee'         => 40000,
            'extra_fee_per_kg' => 5000,
        ],
    ];

    /**
     * Konfigurasi tarif aktif. Membaca DEFAULT_ZONES lalu meng-override
     * `base_fee` & `extra_fee_per_kg` per zona dari site_settings bila ada.
     */
    public static function zones(): array
    {
        $zones = self::DEFAULT_ZONES;
        try {
            foreach ($zones as $key => &$cfg) {
                $base  = SiteSetting::get('shipping_' . $key . '_base_fee');
                $extra = SiteSetting::get('shipping_' . $key . '_extra_fee');
                if ($base  !== null && is_numeric($base))  $cfg['base_fee']         = (int) $base;
                if ($extra !== null && is_numeric($extra)) $cfg['extra_fee_per_kg'] = (int) $extra;
            }
        } catch (\Throwable $e) {
            // DB / tabel site_settings tidak tersedia → fallback DEFAULT_ZONES.
        }
        return $zones;
    }

    /**
     * Berat dasar (kg) yang masih dikenai tarif `base_fee` saja.
     */
    public static function baseWeightKg(): int
    {
        try {
            $val = SiteSetting::get('shipping_base_weight_kg');
            if ($val !== null && is_numeric($val) && (int) $val > 0) {
                return (int) $val;
            }
        } catch (\Throwable $e) {
            // Fallback ke default.
        }
        return self::DEFAULT_BASE_WEIGHT_KG;
    }

    /**
     * Hitung ongkir untuk sebuah pengiriman dari satu toko ke satu pembeli.
     *
     * @param  array  $storeAddress  Wilayah toko: minimal province_id, city_id, district_id.
     * @param  array  $buyerAddress  Wilayah pembeli: minimal province_id, city_id, district_id.
     * @param  float  $totalWeightKg Total berat barang dalam kg (boleh desimal — akan dibulatkan ke atas).
     */
    public static function calculate(array $storeAddress, array $buyerAddress, float $totalWeightKg): array
    {
        $baseKg = self::baseWeightKg();
        $zones  = self::zones();

        // 1) Validasi alamat — kedua sisi wajib punya province_id, city_id, district_id.
        if (! self::hasRegion($storeAddress)) {
            return self::failure('outside_province', 0, $baseKg, $zones,
                'Alamat toko belum lengkap (butuh province_id, city_id, district_id).');
        }
        if (! self::hasRegion($buyerAddress)) {
            return self::failure('outside_province', 0, $baseKg, $zones,
                'Alamat pengiriman belum dipilih atau belum lengkap.');
        }

        // 2) Bulatkan berat ke atas — tarif dihitung per kg utuh.
        $weightKg = (int) ceil(max(0.0, $totalWeightKg));
        if ($weightKg <= 0) {
            return self::failure('outside_province', 0, $baseKg, $zones,
                'Total berat barang harus lebih dari 0 kg.');
        }

        // 3) Tentukan zona secara hierarkis: provinsi → kota/kab → kecamatan.
        $zone = self::resolveZone($storeAddress, $buyerAddress);

        // 4) Hitung tarif: base_fee + kelebihan kg × extra_fee_per_kg.
        $tariff       = $zones[$zone];
        $extraKg      = max(0, $weightKg - $baseKg);
        $shippingCost = $tariff['base_fee'] + $extraKg * $tariff['extra_fee_per_kg'];

        return [
            'available'        => true,
            'zone'             => $zone,
            'zone_label'       => $tariff['label'],
            'base_fee'         => $tariff['base_fee'],
            'base_weight_kg'   => $baseKg,
            'extra_fee_per_kg' => $tariff['extra_fee_per_kg'],
            'total_weight_kg'  => $weightKg,
            'shipping_cost'    => $shippingCost,
            'message'          => sprintf(
                'Ongkir %s: %d kg × tarif %s.',
                $tariff['label'],
                $weightKg,
                self::formatRupiah($shippingCost),
            ),
        ];
    }

    private static function hasRegion(array $address): bool
    {
        return ! empty($address['province_id'])
            && ! empty($address['city_id'])
            && ! empty($address['district_id']);
    }

    private static function resolveZone(array $store, array $buyer): string
    {
        // Beda provinsi → zona paling jauh.
        if ((string) $store['province_id'] !== (string) $buyer['province_id']) {
            return 'outside_province';
        }
        // Satu provinsi tapi beda kota/kab → zona regional.
        if ((string) $store['city_id'] !== (string) $buyer['city_id']) {
            return 'same_province';
        }
        // Satu kota/kab tapi beda kecamatan → zona kota.
        if ((string) $store['district_id'] !== (string) $buyer['district_id']) {
            return 'same_city';
        }
        // Persis satu kecamatan → tarif paling murah.
        return 'same_district';
    }

    private static function failure(string $zone, int $weightKg, int $baseKg, array $zones, string $message): array
    {
        $tariff = $zones[$zone];
        return [
            'available'        => false,
            'zone'             => $zone,
            'zone_label'       => $tariff['label'],
            'base_fee'         => $tariff['base_fee'],
            'base_weight_kg'   => $baseKg,
            'extra_fee_per_kg' => $tariff['extra_fee_per_kg'],
            'total_weight_kg'  => $weightKg,
            'shipping_cost'    => 0,
            'message'          => $message,
        ];
    }

    private static function formatRupiah(int $amount): string
    {
        return 'Rp' . number_format($amount, 0, ',', '.');
    }
}
