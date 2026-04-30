<?php

namespace App\Services;

/**
 * Kalkulator simulasi ongkos kirim berbasis wilayah administratif & berat.
 *
 * Tidak memanggil API kurir eksternal (RajaOngkir, JNE, J&T, dsb). Semua
 * perhitungan dilakukan lokal — cocok untuk simulasi marketplace hasil tani
 * lokal yang membutuhkan tarif deterministik dan dapat dijelaskan.
 *
 * Aturan zona (kunci hierarkis: provinsi → kota/kab → kecamatan):
 *  - same_district    : kec sama          → 10.000 + max(0, kg-5) × 2.000
 *  - same_city        : kab sama, kec ≠   → 20.000 + max(0, kg-5) × 3.000
 *  - same_province    : prov sama, kab ≠  → 30.000 + max(0, kg-5) × 4.000
 *  - outside_province : prov ≠            → 40.000 + max(0, kg-5) × 5.000
 *
 * Berat dasar (base_weight_kg) selalu 5 kg. Berat di atas itu dikenakan
 * tarif tambahan per kilogram (sudah dibulatkan ke atas / ceil).
 */
final class ShippingCalculator
{
    public const BASE_WEIGHT_KG = 5;

    /**
     * Definisi tarif per zona. Dipisah supaya mudah dites dan disesuaikan
     * tanpa menyentuh logika alir. Untuk membuat zona menjadi "tidak melayani",
     * set kedua tarif = 0 dan ubah `available` di calculate() — atau cukup
     * naikkan tarif sangat tinggi.
     */
    public const ZONES = [
        'same_district' => [
            'label'            => 'Satu Kecamatan',
            'base_fee'         => 10000,
            'extra_fee_per_kg' => 2000,
        ],
        'same_city' => [
            'label'            => 'Satu Kota/Kabupaten',
            'base_fee'         => 20000,
            'extra_fee_per_kg' => 3000,
        ],
        'same_province' => [
            'label'            => 'Satu Provinsi',
            'base_fee'         => 30000,
            'extra_fee_per_kg' => 4000,
        ],
        'outside_province' => [
            'label'            => 'Luar Provinsi',
            'base_fee'         => 40000,
            'extra_fee_per_kg' => 5000,
        ],
    ];

    /**
     * Hitung ongkir untuk sebuah pengiriman dari satu toko ke satu pembeli.
     *
     * @param  array  $storeAddress  Wilayah toko: minimal city_id & district_id.
     * @param  array  $buyerAddress  Wilayah pembeli: minimal city_id & district_id.
     * @param  float  $totalWeightKg Total berat barang dalam kg (boleh desimal — akan dibulatkan ke atas).
     * @return array  Lihat docblock kelas untuk format. Field `available=false`
     *                berarti pengiriman tidak bisa diproses (alamat tidak lengkap
     *                atau zona outside_city).
     */
    public static function calculate(array $storeAddress, array $buyerAddress, float $totalWeightKg): array
    {
        // 1) Validasi alamat — kedua sisi wajib punya province_id, city_id, district_id.
        if (! self::hasRegion($storeAddress)) {
            return self::failure('outside_province', 0,
                'Alamat toko belum lengkap (butuh province_id, city_id, district_id).');
        }
        if (! self::hasRegion($buyerAddress)) {
            return self::failure('outside_province', 0,
                'Alamat pengiriman belum dipilih atau belum lengkap.');
        }

        // 2) Bulatkan berat ke atas — tarif dihitung per kg utuh.
        $weightKg = (int) ceil(max(0.0, $totalWeightKg));
        if ($weightKg <= 0) {
            return self::failure('outside_province', 0,
                'Total berat barang harus lebih dari 0 kg.');
        }

        // 3) Tentukan zona secara hierarkis: provinsi → kota/kab → kecamatan.
        $zone = self::resolveZone($storeAddress, $buyerAddress);

        // 4) Hitung tarif: base_fee + kelebihan kg × extra_fee_per_kg.
        $tariff       = self::ZONES[$zone];
        $extraKg      = max(0, $weightKg - self::BASE_WEIGHT_KG);
        $shippingCost = $tariff['base_fee'] + $extraKg * $tariff['extra_fee_per_kg'];

        return [
            'available'        => true,
            'zone'             => $zone,
            'zone_label'       => $tariff['label'],
            'base_fee'         => $tariff['base_fee'],
            'base_weight_kg'   => self::BASE_WEIGHT_KG,
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

    private static function failure(string $zone, int $weightKg, string $message): array
    {
        $tariff = self::ZONES[$zone];
        return [
            'available'        => false,
            'zone'             => $zone,
            'zone_label'       => $tariff['label'],
            'base_fee'         => $tariff['base_fee'],
            'base_weight_kg'   => self::BASE_WEIGHT_KG,
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
