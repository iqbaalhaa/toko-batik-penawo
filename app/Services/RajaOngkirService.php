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
    /** Mapping hierarki (provinsi/kota/kecamatan) — referensi statis, jarang berubah. */
    private const HIERARCHY_TTL = 60 * 60 * 24 * 30; // 30 hari

    /** Default TTL hasil tarif — di-override oleh config `services.rajaongkir.cache_ttl`. */
    private const COST_TTL_DEFAULT = 60 * 60; // 1 jam

    private string $baseUrl;
    private string $apiKey;
    private string $defaultCouriers;
    private int    $timeout;
    private int    $costTtl;

    /** Penyebab kegagalan terakhir — diintip caller via lastErrorReason(). */
    private ?string $lastErrorReason = null;

    public function __construct()
    {
        $cfg                   = (array) config('services.rajaongkir', []);
        $this->baseUrl         = rtrim((string) ($cfg['base_url'] ?? 'https://rajaongkir.komerce.id/api/v1'), '/');
        $this->apiKey          = (string) ($cfg['key']      ?? '');
        $this->defaultCouriers = (string) ($cfg['couriers'] ?? 'jne:jnt:pos');
        $this->timeout         = (int)    ($cfg['timeout']  ?? 10);
        $this->costTtl         = (int)    ($cfg['cache_ttl'] ?? self::COST_TTL_DEFAULT);
    }

    /** True bila API key sudah dikonfigurasi (mis. RAJAONGKIR_API_KEY=…). */
    public function enabled(): bool
    {
        return $this->apiKey !== '';
    }

    /**
     * Penyebab gagal terakhir. Berguna untuk surface pesan API ke UI alih-alih
     * fallback diam-diam. Reset otomatis di awal tiap pemanggilan calculate*.
     */
    public function lastErrorReason(): ?string
    {
        return $this->lastErrorReason;
    }

    private function setError(string $reason): void
    {
        $this->lastErrorReason = $reason;
    }

    private function clearError(): void
    {
        $this->lastErrorReason = null;
    }

    /**
     * Resolve ID kecamatan RajaOngkir dari payload alamat lengkap.
     *
     * Strategi (hemat panggilan API):
     *   1. Cek kolom `districts.rajaongkir_id` via cuid Kemendagri (`district_id`)
     *      — kalau sudah di-sync, langsung return tanpa hit API.
     *   2. Fallback ke pencarian berbasis nama (resolveDistrictId) — pakai
     *      `province_name` / `city_name` / `district_name` di payload.
     *
     * Payload yang diharapkan: ['province_id','province_name','city_id',
     * 'city_name','district_id','district_name'] — sesuai User::shippingAddress().
     */
    public function resolveDistrictIdFromAddress(array $address): ?int
    {
        // 1) DB-first: cek mapping rajaongkir_id yang sudah di-sync.
        $districtId = $address['district_id'] ?? null;
        if (! empty($districtId)) {
            try {
                $district = \App\Models\District::find($districtId);
                if ($district && ! empty($district->rajaongkir_id) && is_numeric($district->rajaongkir_id)) {
                    return (int) $district->rajaongkir_id;
                }
            } catch (\Throwable $e) {
                // Tabel/model tidak siap (mis. test tanpa migrasi) → silent fallback.
            }
        }

        // 2) Fallback ke pencarian berbasis nama via hierarki RajaOngkir.
        $id = $this->resolveDistrictId(
            $address['province_name'] ?? null,
            $address['city_name']     ?? null,
            $address['district_name'] ?? null,
        );
        if ($id === null && $this->lastErrorReason === null) {
            // Bukan HTTP error — murni nama tidak cocok di katalog RajaOngkir.
            $this->setError(sprintf(
                'Wilayah "%s, %s, %s" tidak ditemukan di katalog RajaOngkir. Jalankan `php artisan rajaongkir:sync-wilayah --from-users` atau cek ejaan nama.',
                $address['district_name'] ?? '?',
                $address['city_name']     ?? '?',
                $address['province_name'] ?? '?',
            ));
        }
        return $id;
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
     * Mengembalikan SEMUA opsi layanan dari kurir yang dikonfigurasi, di-urut
     * dari termurah ke termahal. Null bila API gagal / tidak ada layanan.
     *
     * Hasil di-cache (per origin/dest/weight/courier-list).
     *
     * @return list<array{name:string,code:string,service:string,description:string,cost:int,etd:string}>|null
     */
    public function calculateAll(int $originDistrictId, int $destDistrictId, int $weightGrams, ?string $couriers = null): ?array
    {
        $this->clearError();
        $couriers    = $couriers !== null && $couriers !== '' ? $couriers : $this->defaultCouriers;
        $weightGrams = max(1, $weightGrams);

        $cacheKey = sprintf(
            'rajaongkir:cost-all:%d:%d:%d:%s',
            $originDistrictId,
            $destDistrictId,
            $weightGrams,
            md5($couriers),
        );

        // Note: pakai Cache::get + manual put → kegagalan TIDAK di-cache. Kalau pakai
        // Cache::remember dan callback return null, Laravel tetap cache null tsb,
        // sehingga error sementara ngendap di TTL panjang.
        try {
            $cached = Cache::get($cacheKey);
            if (is_array($cached)) return $cached;
        } catch (\Throwable $e) {
            // Cache tidak tersedia → langsung hit API.
        }

        try {
            $resp = $this->postForm('/calculate/district/domestic-cost', [
                'origin'      => (string) $originDistrictId,
                'destination' => (string) $destDistrictId,
                'weight'      => (string) $weightGrams,
                'courier'     => $couriers,
                'price'       => 'lowest',
            ]);
            if ($resp === null) {
                // setError() sudah di-set di postForm() saat HTTP gagal.
                return null;
            }

            $rows    = (array) ($resp['data'] ?? []);
            $options = [];
            foreach ($rows as $row) {
                if (! isset($row['cost'])) continue;
                $cost = (int) $row['cost'];
                if ($cost <= 0) continue;
                $options[] = [
                    'name'        => (string) ($row['name']        ?? ''),
                    'code'        => (string) ($row['code']        ?? ''),
                    'service'     => (string) ($row['service']     ?? ''),
                    'description' => (string) ($row['description'] ?? ''),
                    'cost'        => $cost,
                    'etd'         => (string) ($row['etd']         ?? ''),
                ];
            }
            if (empty($options)) {
                $this->setError('RajaOngkir tidak menemukan layanan kurir untuk rute ini.');
                return null;
            }
            usort($options, fn ($a, $b) => $a['cost'] <=> $b['cost']);

            try { Cache::put($cacheKey, $options, $this->costTtl); } catch (\Throwable $e) { /* cache tidak siap */ }
            return $options;
        } catch (\Throwable $e) {
            $this->setError('Exception: ' . $e->getMessage());
            Log::warning('RajaOngkir calculateAll failed', ['error' => $e->getMessage()]);
            return null;
        }
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
            return Cache::remember($cacheKey, $this->costTtl, function () use ($originDistrictId, $destDistrictId, $weightGrams, $couriers) {
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

    /**
     * Ambil seluruh provinsi RajaOngkir (cached 30 hari).
     * Dipakai oleh artisan sync-wilayah untuk mapping massal.
     *
     * @return list<array{id:int,name:string}>|null
     */
    public function listProvinces(): ?array
    {
        try {
            $list = Cache::remember('rajaongkir:provinces', self::HIERARCHY_TTL, function () {
                $resp = $this->get('/destination/province');
                return $resp['data'] ?? [];
            });
            return is_array($list) ? $list : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Ambil seluruh kota/kabupaten dalam satu provinsi RajaOngkir (cached 30 hari).
     *
     * @return list<array{id:int,name:string}>|null
     */
    public function listCities(int $provinceId): ?array
    {
        try {
            $list = Cache::remember('rajaongkir:cities:' . $provinceId, self::HIERARCHY_TTL, function () use ($provinceId) {
                $resp = $this->get('/destination/city/' . $provinceId);
                return $resp['data'] ?? [];
            });
            return is_array($list) ? $list : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Ambil seluruh kecamatan dalam satu kota/kabupaten RajaOngkir (cached 30 hari).
     *
     * @return list<array{id:int,name:string}>|null
     */
    public function listDistricts(int $cityId): ?array
    {
        try {
            $list = Cache::remember('rajaongkir:districts:' . $cityId, self::HIERARCHY_TTL, function () use ($cityId) {
                $resp = $this->get('/destination/district/' . $cityId);
                return $resp['data'] ?? [];
            });
            return is_array($list) ? $list : null;
        } catch (\Throwable $e) {
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
            $this->setError('HTTP ' . $resp->status() . ' dari RajaOngkir saat GET ' . $path);
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
            // Coba ekstrak message dari body Komerce: {"meta":{"message":"...","code":...}}.
            $body = $resp->json();
            $apiMsg = is_array($body) ? ($body['meta']['message'] ?? null) : null;
            $reason = 'HTTP ' . $resp->status();
            if ($apiMsg) $reason .= ' — ' . $apiMsg;
            $this->setError($reason);
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
