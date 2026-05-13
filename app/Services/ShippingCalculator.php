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
    public static function calculate(array $storeAddress, array $buyerAddress, float $totalWeightKg, ?string $selectedOptionCode = null): array
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

        // 3) Tentukan zona secara hierarkis (untuk label & — bila API tidak aktif — fallback).
        $zone   = self::resolveZone($storeAddress, $buyerAddress);
        $tariff = $zones[$zone];

        // 4) Coba RajaOngkir — pakai satu instance agar `lastErrorReason()` bisa
        //    diintip kalau gagal. Konstruksi service dibungkus try/catch supaya
        //    test PHPUnit (tanpa container Laravel) tidak meledak; itu memang
        //    skenario "API tidak tersedia" → lanjut ke fallback lokal di bawah.
        $svc = null;
        try { $svc = new RajaOngkirService(); } catch (\Throwable $e) { /* container tidak siap */ }

        if ($svc !== null && $svc->enabled()) {
            $api = self::tryRajaOngkir(
                $svc, $storeAddress, $buyerAddress, $weightKgRaw, $weightGrams,
                $zone, $tariff, $baseKg, $selectedOptionCode,
            );
            if ($api !== null) {
                return $api;
            }
            // API enabled tapi gagal → JANGAN fallback diam-diam ke tarif lokal.
            // - Pesan untuk PELANGGAN: generik & sopan.
            // - Pesan teknis (HTTP code / mapping fail / dsb.) tetap masuk Log + di
            //   field `debug_reason` yang hanya dirender untuk admin / debug mode.
            $debug = $svc->lastErrorReason() ?: 'API RajaOngkir tidak merespons.';
            $fail  = self::failure(
                $zone, $weightKg, $baseKg, $zones,
                'Ongkir untuk alamat ini sedang tidak tersedia. Silakan coba beberapa saat lagi atau hubungi penjual.',
            );
            $fail['debug_reason'] = $debug;
            return $fail;
        }

        // 5) API tidak dikonfigurasi (RAJAONGKIR_API_KEY kosong) ATAU container
        //    Laravel tidak tersedia (test PHPUnit) → fallback ke kalkulator zona
        //    lokal. Ini cuma path test/dev tanpa API key.
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
            // Untuk fallback lokal hanya ada satu opsi (tarif zona) — diisi sebagai
            // daftar 1-elemen agar view picker bisa render seragam.
            'options'          => [[
                'code'         => 'local:' . $zone,
                'courier_code' => 'local',
                'courier_name' => 'Tarif Toko',
                'service_name' => $tariff['label'],
                'service_desc' => 'Tarif zona internal (fallback)',
                'cost'         => $shippingCost,
                'etd'          => '',
            ]],
            'option_code'      => 'local:' . $zone,
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
    private static function tryRajaOngkir(RajaOngkirService $svc, array $store, array $buyer, float $weightKgRaw, int $weightGrams, string $zone, array $tariff, int $baseKg, ?string $selectedOptionCode = null): ?array
    {
        try {
            // Pakai resolver berbasis payload alamat lengkap — DB-first lookup
            // via kolom `districts.rajaongkir_id` (kalau sudah di-sync via
            // `rajaongkir:sync-wilayah`), fallback ke pencarian berbasis nama.
            // lastErrorReason() di service otomatis terisi kalau resolve gagal —
            // caller (calculate) yang akan menampilkannya ke user. Kita cukup
            // bail out sini.
            $originId = $svc->resolveDistrictIdFromAddress($store);
            if ($originId === null) return null;
            $destId = $svc->resolveDistrictIdFromAddress($buyer);
            if ($destId === null) return null;

            // Ambil SEMUA opsi (sudah di-sort termurah dulu di service).
            $allOptions = $svc->calculateAll($originId, $destId, $weightGrams);
            if (empty($allOptions)) return null;

            // Build daftar opsi untuk picker — kode unik per opsi = "<courier>:<service>".
            $options = [];
            foreach ($allOptions as $opt) {
                $code = self::optionCode((string) $opt['code'], (string) $opt['service']);
                $options[] = [
                    'code'         => $code,
                    'courier_code' => (string) $opt['code'],
                    'courier_name' => (string) $opt['name'],
                    'service_name' => (string) $opt['service'],
                    'service_desc' => (string) $opt['description'],
                    'cost'         => (int)    $opt['cost'],
                    'etd'          => (string) $opt['etd'],
                ];
            }

            // Pilih opsi: prioritas kode dari user, fallback termurah (index 0).
            $picked = $options[0];
            if ($selectedOptionCode) {
                foreach ($options as $opt) {
                    if (strcasecmp($opt['code'], $selectedOptionCode) === 0) {
                        $picked = $opt;
                        break;
                    }
                }
            }

            // Bulatkan berat tampilan ke 2 desimal — bisa decimal (mis. 1.5 kg)
            // saat barang di bawah 1 kg utuh; angka biaya datang utuh dari API.
            $displayWeight = round($weightKgRaw, 2);

            return [
                'available'        => true,
                'source'           => 'rajaongkir',
                'zone'             => $zone,
                'zone_label'       => $tariff['label'],
                // Field tarif zona dipertahankan untuk kompat — tidak relevan via API.
                'base_fee'         => $picked['cost'],
                'base_weight_kg'   => $baseKg,
                'extra_fee_per_kg' => 0,
                'total_weight_kg'  => $displayWeight,
                'weight_grams'     => $weightGrams,
                'shipping_cost'    => $picked['cost'],
                'courier_code'     => $picked['courier_code'] ?: null,
                'courier_name'     => $picked['courier_name'] ?: null,
                'service_name'     => $picked['service_name'] ?: null,
                'service_desc'     => $picked['service_desc'] ?: null,
                'etd'              => $picked['etd']          ?: null,
                'options'          => $options,
                'option_code'      => $picked['code'],
                'message'          => sprintf(
                    'Ongkir %s · %s untuk %s g: %s%s.',
                    $picked['courier_name'] !== '' ? $picked['courier_name'] : 'Kurir',
                    $picked['service_name']  !== '' ? $picked['service_name']  : '-',
                    number_format($weightGrams, 0, ',', '.'),
                    self::formatRupiah($picked['cost']),
                    $picked['etd'] !== '' ? ' (estimasi ' . $picked['etd'] . ')' : '',
                ),
            ];
        } catch (\Throwable $e) {
            // Container/Cache/Http tidak tersedia (mis. unit test) → fallback lokal.
            return null;
        }
    }

    /** Format kode opsi seragam: "<courier_lower>:<service_upper>". */
    public static function optionCode(string $courier, string $service): string
    {
        return strtolower(trim($courier)) . ':' . strtoupper(trim($service));
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
            'options'          => [],
            'option_code'      => null,
            'message'          => $message,
        ];
    }

    private static function formatRupiah(int $amount): string
    {
        return 'Rp' . number_format($amount, 0, ',', '.');
    }
}
