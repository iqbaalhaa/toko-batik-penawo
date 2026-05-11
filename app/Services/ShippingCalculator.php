<?php

namespace App\Services;

use App\Models\SiteSetting;

/**
 * Kalkulator ongkos kirim berbasis wilayah administratif & berat.
 *
 * Sumber tarif (urutan prioritas):
 *   1. RajaOngkir (Komerce) bila API key dikonfigurasi (RAJAONGKIR_API_KEY)
 *      — tarif berbasis jarak dari kurir riil, hasil termurah dipakai.
 *   2. Fallback kalkulator zona lokal di bawah ini — tarif deterministik
 *      yang dipakai untuk testing & saat API tidak tersedia.
 *
 * Aturan zona lokal (kunci hierarkis: provinsi → kota/kab → kecamatan):
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
     * @param  array  $storeAddress  Wilayah toko: minimal province_id, city_id, district_id (+ *_name untuk RajaOngkir).
     * @param  array  $buyerAddress  Wilayah pembeli: minimal province_id, city_id, district_id (+ *_name untuk RajaOngkir).
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

        // 2) Validasi berat. Untuk RajaOngkir kita kirim gram aktual supaya tier
        //    pembulatan per-kurir (mis. JNT EZ: 0–999 g & 1000 g sama-sama dianggap 1 kg)
        //    diputuskan oleh RajaOngkir/kurir, bukan diputuskan di sini. Kalkulator
        //    zona lokal tetap pakai pembulatan ke kg utuh agar tarifnya dapat dijelaskan.
        $weightKgRaw = max(0.0, $totalWeightKg);
        if ($weightKgRaw <= 0) {
            return self::failure('outside_province', 0, $baseKg, $zones,
                'Total berat barang harus lebih dari 0 kg.');
        }
        $weightGrams = (int) ceil($weightKgRaw * 1000); // gram presisi untuk RajaOngkir
        $weightKg    = (int) ceil($weightKgRaw);        // kg utuh untuk kalkulator lokal

        // 3) Tentukan zona secara hierarkis (untuk label & fallback).
        $zone   = self::resolveZone($storeAddress, $buyerAddress);
        $tariff = $zones[$zone];

        // 4) Coba RajaOngkir lebih dulu — tarif berbasis jarak dari kurir riil.
        $api = self::tryRajaOngkir($storeAddress, $buyerAddress, $weightKgRaw, $weightGrams, $zone, $tariff, $baseKg);
        if ($api !== null) {
            return $api;
        }

        // 5) Fallback kalkulator zona lokal: base_fee + kelebihan kg × extra_fee_per_kg.
        $extraKg      = max(0, $weightKg - $baseKg);
        $shippingCost = $tariff['base_fee'] + $extraKg * $tariff['extra_fee_per_kg'];

        return [
            'available'        => true,
            'source'           => 'local',
            'zone'             => $zone,
            'zone_label'       => $tariff['label'],
            'base_fee'         => $tariff['base_fee'],
            'base_weight_kg'   => $baseKg,
            'extra_fee_per_kg' => $tariff['extra_fee_per_kg'],
            'total_weight_kg'  => $weightKg,
            'shipping_cost'    => $shippingCost,
            'courier_code'     => null,
            'courier_name'     => null,
            'service_name'     => null,
            'service_desc'     => null,
            'etd'              => null,
            'message'          => sprintf(
                'Ongkir %s: %d kg × tarif %s.',
                $tariff['label'],
                $weightKg,
                self::formatRupiah($shippingCost),
            ),
        ];
    }

    /**
     * Coba ambil tarif dari RajaOngkir. Return null bila tidak available, mapping
     * gagal, atau API error — caller akan fallback ke kalkulator zona lokal.
     *
     * Berat dikirim sebagai gram aktual (`$weightGrams`) — RajaOngkir/kurir yang
     * menentukan tier pembulatan per kurir (mis. JNT EZ menaikkan tier setiap
     * tambahan 1000 g, tidak per fraksi kg).
     */
    private static function tryRajaOngkir(array $store, array $buyer, float $weightKgRaw, int $weightGrams, string $zone, array $tariff, int $baseKg): ?array
    {
        try {
            $svc = new RajaOngkirService();
            if (! $svc->enabled()) return null;

            $originId = $svc->resolveDistrictId(
                $store['province_name'] ?? null,
                $store['city_name']     ?? null,
                $store['district_name'] ?? null,
            );
            $destId = $svc->resolveDistrictId(
                $buyer['province_name'] ?? null,
                $buyer['city_name']     ?? null,
                $buyer['district_name'] ?? null,
            );
            if ($originId === null || $destId === null) return null;

            $best = $svc->calculate($originId, $destId, $weightGrams);
            if ($best === null) return null;

            // Bulatkan berat tampilan ke 2 desimal — bisa decimal (mis. 1.5 kg)
            // saat barang di bawah 1 kg utuh; angka biaya datang utuh dari API.
            $displayWeight = round($weightKgRaw, 2);

            return [
                'available'        => true,
                'source'           => 'rajaongkir',
                'zone'             => $zone,
                'zone_label'       => $tariff['label'],
                // Field tarif zona dipertahankan untuk kompat — tidak relevan via API.
                'base_fee'         => (int) $best['cost'],
                'base_weight_kg'   => $baseKg,
                'extra_fee_per_kg' => 0,
                'total_weight_kg'  => $displayWeight,
                'weight_grams'     => $weightGrams,
                'shipping_cost'    => (int) $best['cost'],
                'courier_code'     => $best['code']        ?: null,
                'courier_name'     => $best['name']        ?: null,
                'service_name'     => $best['service']     ?: null,
                'service_desc'     => $best['description'] ?: null,
                'etd'              => $best['etd']         ?: null,
                'message'          => sprintf(
                    'Ongkir %s · %s untuk %s g: %s%s.',
                    $best['name'] !== '' ? $best['name'] : 'Kurir',
                    $best['service'] !== '' ? $best['service'] : '-',
                    number_format($weightGrams, 0, ',', '.'),
                    self::formatRupiah((int) $best['cost']),
                    $best['etd'] !== '' ? ' (estimasi ' . $best['etd'] . ')' : '',
                ),
            ];
        } catch (\Throwable $e) {
            // Container/Cache/Http tidak tersedia (mis. unit test) → fallback lokal.
            return null;
        }
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
            'source'           => 'local',
            'zone'             => $zone,
            'zone_label'       => $tariff['label'],
            'base_fee'         => $tariff['base_fee'],
            'base_weight_kg'   => $baseKg,
            'extra_fee_per_kg' => $tariff['extra_fee_per_kg'],
            'total_weight_kg'  => $weightKg,
            'shipping_cost'    => 0,
            'courier_code'     => null,
            'courier_name'     => null,
            'service_name'     => null,
            'service_desc'     => null,
            'etd'              => null,
            'message'          => $message,
        ];
    }

    private static function formatRupiah(int $amount): string
    {
        return 'Rp' . number_format($amount, 0, ',', '.');
    }
}
