<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Klien RajaOngkir (Komerce) — hanya untuk hitung ongkir berbasis jarak.
 *
 * Hierarki resolusi destinasi: provinsi → kab/kota → kecamatan → calculate-cost.
 * RajaOngkir punya ID sendiri (numerik kecil); kita memetakan dari nama wilayah
 * Kemendagri yang dipakai aplikasi. Hasil mapping di-cache lama (data referensi
 * relatif statis) sementara hasil tarif di-cache singkat.
 */
final class RajaOngkirService
{
    /** Mapping hierarki (provinsi/kota/kecamatan) — referensi statis. */
    private const HIERARCHY_TTL = 60 * 60 * 24 * 30; // 30 hari

    /** Hasil tarif — ringan untuk di-refresh agar harga tetap aktual. */
    private const COST_TTL = 60 * 60; // 1 jam

    private string $baseUrl;
    private string $apiKey;
    private string $defaultCouriers;
    private int    $timeout;

    public function __construct()
    {
        $cfg                   = (array) config('services.rajaongkir', []);
        $this->baseUrl         = rtrim((string) ($cfg['base_url'] ?? 'https://rajaongkir.komerce.id/api/v1'), '/');
        $this->apiKey          = (string) ($cfg['key']      ?? '');
        $this->defaultCouriers = (string) ($cfg['couriers'] ?? 'jne:jnt:pos');
        $this->timeout         = (int)    ($cfg['timeout']  ?? 10);
    }

    /** True bila API key sudah dikonfigurasi (mis. RAJAONGKIR_API_KEY=…). */
    public function enabled(): bool
    {
        return $this->apiKey !== '';
    }

    /**
     * Resolve ID kecamatan RajaOngkir dari nama wilayah Kemendagri.
     * Mengembalikan null bila salah satu level gagal di-match.
     */
    public function resolveDistrictId(?string $provinceName, ?string $cityName, ?string $districtName): ?int
    {
        $provinceName = self::normalize($provinceName);
        $cityName     = self::normalizeCity($cityName);
        $districtName = self::normalize($districtName);

        if ($provinceName === '' || $cityName === '' || $districtName === '') {
            return null;
        }

        $provinceId = $this->findProvinceId($provinceName);
        if ($provinceId === null) return null;

        $cityId = $this->findCityId($provinceId, $cityName);
        if ($cityId === null) return null;

        return $this->findDistrictId($cityId, $districtName);
    }

    /**
     * Hitung ongkir untuk pasangan ID kecamatan RajaOngkir + berat (gram).
     * Mengembalikan opsi termurah dari kurir yang dikonfigurasi, atau null bila
     * API gagal / tidak ada layanan tersedia.
     *
     * @return array{name:string,code:string,service:string,description:string,cost:int,etd:string}|null
     */
    public function calculate(int $originDistrictId, int $destDistrictId, int $weightGrams, ?string $couriers = null): ?array
    {
        $couriers    = $couriers !== null && $couriers !== '' ? $couriers : $this->defaultCouriers;
        // Kirim gram aktual; RajaOngkir/kurir yang menetapkan tier pembulatannya.
        // Minimal 1 g — API menerima nilai sub-1kg dan kurir akan menagih sesuai tarif terendah.
        $weightGrams = max(1, $weightGrams);

        $cacheKey = sprintf(
            'rajaongkir:cost:%d:%d:%d:%s',
            $originDistrictId,
            $destDistrictId,
            $weightGrams,
            md5($couriers),
        );

        try {
            return Cache::remember($cacheKey, self::COST_TTL, function () use ($originDistrictId, $destDistrictId, $weightGrams, $couriers) {
                $resp = $this->postForm('/calculate/district/domestic-cost', [
                    'origin'      => (string) $originDistrictId,
                    'destination' => (string) $destDistrictId,
                    'weight'      => (string) $weightGrams,
                    'courier'     => $couriers,
                    'price'       => 'lowest',
                ]);
                if ($resp === null) return null;

                $rows = (array) ($resp['data'] ?? []);
                $best = null;
                foreach ($rows as $row) {
                    if (! isset($row['cost'])) continue;
                    $cost = (int) $row['cost'];
                    if ($cost <= 0) continue;
                    if ($best === null || $cost < (int) $best['cost']) {
                        $best = [
                            'name'        => (string) ($row['name']        ?? ''),
                            'code'        => (string) ($row['code']        ?? ''),
                            'service'     => (string) ($row['service']     ?? ''),
                            'description' => (string) ($row['description'] ?? ''),
                            'cost'        => $cost,
                            'etd'         => (string) ($row['etd']         ?? ''),
                        ];
                    }
                }
                return $best;
            });
        } catch (\Throwable $e) {
            Log::warning('RajaOngkir calculate failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function findProvinceId(string $normalizedName): ?int
    {
        try {
            $list = Cache::remember('rajaongkir:provinces', self::HIERARCHY_TTL, function () {
                $resp = $this->get('/destination/province');
                return $resp['data'] ?? [];
            });
        } catch (\Throwable $e) {
            return null;
        }
        foreach ((array) $list as $row) {
            if (self::normalize((string) ($row['name'] ?? '')) === $normalizedName) {
                return (int) $row['id'];
            }
        }
        return null;
    }

    private function findCityId(int $provinceId, string $normalizedName): ?int
    {
        try {
            $list = Cache::remember('rajaongkir:cities:' . $provinceId, self::HIERARCHY_TTL, function () use ($provinceId) {
                $resp = $this->get('/destination/city/' . $provinceId);
                return $resp['data'] ?? [];
            });
        } catch (\Throwable $e) {
            return null;
        }
        foreach ((array) $list as $row) {
            if (self::normalize((string) ($row['name'] ?? '')) === $normalizedName) {
                return (int) $row['id'];
            }
        }
        return null;
    }

    private function findDistrictId(int $cityId, string $normalizedName): ?int
    {
        try {
            $list = Cache::remember('rajaongkir:districts:' . $cityId, self::HIERARCHY_TTL, function () use ($cityId) {
                $resp = $this->get('/destination/district/' . $cityId);
                return $resp['data'] ?? [];
            });
        } catch (\Throwable $e) {
            return null;
        }
        foreach ((array) $list as $row) {
            if (self::normalize((string) ($row['name'] ?? '')) === $normalizedName) {
                return (int) $row['id'];
            }
        }
        return null;
    }

    private function get(string $path): ?array
    {
        $resp = Http::withHeaders(['key' => $this->apiKey, 'Accept' => 'application/json'])
            ->timeout($this->timeout)
            ->get($this->baseUrl . $path);

        if (! $resp->ok()) {
            Log::warning('RajaOngkir GET failed', ['path' => $path, 'status' => $resp->status()]);
            return null;
        }
        return $resp->json();
    }

    private function postForm(string $path, array $body): ?array
    {
        $resp = Http::withHeaders(['key' => $this->apiKey, 'Accept' => 'application/json'])
            ->asForm()
            ->timeout($this->timeout)
            ->post($this->baseUrl . $path, $body);

        if (! $resp->ok()) {
            Log::warning('RajaOngkir POST failed', ['path' => $path, 'status' => $resp->status(), 'body' => $resp->body()]);
            return null;
        }
        return $resp->json();
    }

    /** UPPERCASE + trim + collapse spasi. Aman untuk null. */
    private static function normalize(?string $name): string
    {
        $name = mb_strtoupper(trim((string) $name));
        return preg_replace('/\s+/', ' ', $name) ?? '';
    }

    /**
     * Kemendagri menyimpan kab/kota dengan prefiks "KABUPATEN "/"KOTA ", sedangkan
     * RajaOngkir hanya pakai nama (mis. "BANYUMAS"). Strip prefiks sebelum match.
     */
    private static function normalizeCity(?string $name): string
    {
        $name = self::normalize($name);
        foreach (['KABUPATEN ', 'KAB. ', 'KAB ', 'KOTA ADMINISTRASI ', 'KOTA ADM. ', 'KOTA '] as $prefix) {
            if (str_starts_with($name, $prefix)) {
                return trim(substr($name, strlen($prefix)));
            }
        }
        return $name;
    }
}
