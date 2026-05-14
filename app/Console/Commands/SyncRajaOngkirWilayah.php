<?php

namespace App\Console\Commands;

use App\Models\Address;
use App\Models\District;
use App\Models\Province;
use App\Models\Regency;
use App\Models\User;
use App\Services\RajaOngkirService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Isi kolom `rajaongkir_id` di tabel provinces/regencies/districts dengan
 * mencocokkan nama wilayah Kemendagri ke katalog RajaOngkir.
 *
 * Strategi default = hierarkis (1 API call per provinsi/kota — di-cache 30 hari
 * oleh service, jadi run kedua hampir nol hit). Cocok untuk Komerce free tier
 * yang dibatasi 100 hits/hari, terutama dikombinasikan dengan `--from-users`
 * supaya hanya wilayah yang benar-benar dipakai pelanggan/toko yang di-sync.
 */
class SyncRajaOngkirWilayah extends Command
{
    protected $signature = 'rajaongkir:sync-wilayah
        {--from-users : Hanya proses wilayah yang dipakai user/alamat aktif (hemat kuota)}
        {--limit=0 : Batasi jumlah kecamatan diproses (0 = tanpa batas)}
        {--force : Timpa rajaongkir_id yang sudah terisi}
        {--sleep=600 : Delay antar request ke RajaOngkir, dalam milidetik}
        {--max-fails=10 : Auto-stop setelah N kegagalan resolusi berturut-turut (deteksi kuota habis)}';

    protected $description = 'Sync ID wilayah RajaOngkir ke kolom rajaongkir_id di tabel provinces/regencies/districts';

    public function handle(RajaOngkirService $svc): int
    {
        if (! $svc->enabled()) {
            $this->error('RAJAONGKIR_API_KEY belum dikonfigurasi. Set di .env dulu.');
            return self::FAILURE;
        }

        $force      = (bool) $this->option('force');
        $sleepMs    = max(0, (int) $this->option('sleep'));
        $maxFails   = max(1, (int) $this->option('max-fails'));
        $limit      = max(0, (int) $this->option('limit'));
        $fromUsers  = (bool) $this->option('from-users');

        // Subset kecamatan yang dipakai pelanggan atau toko default — fokuskan
        // kuota API ke wilayah yang aktif.
        $activeDistrictIds = null;
        if ($fromUsers) {
            $activeDistrictIds = $this->collectActiveDistrictIds();
            $this->line(sprintf('Mode --from-users: %d kecamatan aktif yang akan diproses.', count($activeDistrictIds)));
        }

        $this->info('Sync wilayah RajaOngkir dimulai…');
        $stats = ['province' => 0, 'regency' => 0, 'district' => 0, 'failed' => 0];

        // --- LEVEL 1: PROVINSI ---
        $provQuery = Province::query();
        if (! $force) {
            $provQuery->whereNull('rajaongkir_id');
        }
        $provinces = $provQuery->get();
        $this->line(sprintf('Provinsi: %d entri diproses…', $provinces->count()));

        $apiProvinces = $svc->listProvinces() ?? [];
        if (empty($apiProvinces)) {
            $reason = $svc->lastErrorReason() ?: 'tidak ada detail tambahan.';
            $this->error('Gagal mengambil daftar provinsi dari RajaOngkir:');
            $this->line('  → ' . $reason);
            // Hint khusus cURL SSL — biang kerok paling sering di Windows.
            if (stripos($reason, 'ssl') !== false || stripos($reason, 'cert') !== false || stripos($reason, 'curl error 60') !== false) {
                $this->newLine();
                $this->warn('Sepertinya error sertifikat SSL (cURL error 60). Solusi cepat untuk WAMP/XAMPP di Windows:');
                $this->line('  1. Download cacert.pem dari https://curl.se/ca/cacert.pem');
                $this->line('  2. Simpan, mis. C:\\wamp64\\bin\\php\\cacert.pem');
                $this->line('  3. Edit php.ini (CLI & Apache):');
                $this->line('       curl.cainfo     = "C:\\wamp64\\bin\\php\\cacert.pem"');
                $this->line('       openssl.cafile  = "C:\\wamp64\\bin\\php\\cacert.pem"');
                $this->line('  4. Restart WAMP/Apache, jalankan ulang command ini.');
                $this->line('  (Alternatif sementara: APP_ENV=local di .env — service akan skip SSL verify.)');
            }
            return self::FAILURE;
        }
        $provincesMap = $this->mapByNormalizedName($apiProvinces);

        $apiProvinceIdByLocalId = []; // local cuid → RajaOngkir province ID, untuk traversal level berikutnya.
        foreach ($provinces as $p) {
            $key = $this->normalize($p->name);
            $hit = $provincesMap[$key] ?? null;
            if ($hit) {
                $p->rajaongkir_id = (string) $hit['id'];
                $p->save();
                $apiProvinceIdByLocalId[$p->id] = (int) $hit['id'];
                $stats['province']++;
            } else {
                $this->warn("  provinsi tidak match: {$p->name}");
                $stats['failed']++;
            }
        }
        // Untuk provinsi yang sudah ter-sync (--force off, di-skip), tetap muat mapping ID-nya.
        $alreadySynced = Province::whereNotNull('rajaongkir_id')->get(['id', 'rajaongkir_id']);
        foreach ($alreadySynced as $p) {
            $apiProvinceIdByLocalId[$p->id] = (int) $p->rajaongkir_id;
        }

        // --- LEVEL 2: KAB/KOTA ---
        $regQuery = Regency::query();
        if (! $force) $regQuery->whereNull('rajaongkir_id');
        $regencies = $regQuery->get();
        $this->line(sprintf('Kab/Kota: %d entri diproses…', $regencies->count()));

        $apiRegencyIdByLocalId = [];
        $consecutiveFails = 0;
        foreach ($regencies as $r) {
            $apiProvinceId = $apiProvinceIdByLocalId[$r->province_id] ?? null;
            if (! $apiProvinceId) {
                $this->warn("  skip {$r->name} — provinsi induknya belum di-sync");
                $stats['failed']++;
                continue;
            }
            $apiCities = $svc->listCities($apiProvinceId) ?? [];
            if ($sleepMs > 0) usleep($sleepMs * 1000);
            if (empty($apiCities)) {
                $consecutiveFails++;
                if ($consecutiveFails >= $maxFails) {
                    $this->error("Gagal $maxFails kali berturut-turut — kuota mungkin habis. Stop.");
                    return self::FAILURE;
                }
                continue;
            }
            $consecutiveFails = 0;
            $citiesMap = $this->mapByNormalizedName($apiCities);
            $key = $this->normalizeCity($r->name);
            $hit = $citiesMap[$key] ?? null;
            if ($hit) {
                $r->rajaongkir_id = (string) $hit['id'];
                $r->save();
                $apiRegencyIdByLocalId[$r->id] = (int) $hit['id'];
                $stats['regency']++;
            } else {
                $this->warn("  kab/kota tidak match: {$r->name}");
                $stats['failed']++;
            }
        }
        $alreadyRegencies = Regency::whereNotNull('rajaongkir_id')->get(['id', 'rajaongkir_id']);
        foreach ($alreadyRegencies as $r) {
            $apiRegencyIdByLocalId[$r->id] = (int) $r->rajaongkir_id;
        }

        // --- LEVEL 3: KECAMATAN ---
        $distQuery = District::query();
        if (! $force) $distQuery->whereNull('rajaongkir_id');
        if ($activeDistrictIds !== null) {
            if (empty($activeDistrictIds)) {
                $this->line('Tidak ada kecamatan aktif yang perlu di-sync.');
                $this->printSummary($stats);
                return self::SUCCESS;
            }
            $distQuery->whereIn('id', $activeDistrictIds);
        }
        if ($limit > 0) $distQuery->limit($limit);

        $districts = $distQuery->get();
        $this->line(sprintf('Kecamatan: %d entri diproses…', $districts->count()));

        $consecutiveFails = 0;
        $bar = $this->output->createProgressBar($districts->count());
        $bar->start();
        foreach ($districts as $d) {
            $apiRegencyId = $apiRegencyIdByLocalId[$d->regency_id] ?? null;
            if (! $apiRegencyId) {
                $stats['failed']++;
                $bar->advance();
                continue;
            }
            $apiDistricts = $svc->listDistricts($apiRegencyId) ?? [];
            if ($sleepMs > 0) usleep($sleepMs * 1000);
            if (empty($apiDistricts)) {
                $consecutiveFails++;
                if ($consecutiveFails >= $maxFails) {
                    $bar->finish();
                    $this->newLine();
                    $this->error("Gagal $maxFails kali berturut-turut — kuota mungkin habis. Stop.");
                    $this->printSummary($stats);
                    return self::FAILURE;
                }
                $bar->advance();
                continue;
            }
            $consecutiveFails = 0;
            $districtsMap = $this->mapByNormalizedName($apiDistricts);
            $key = $this->normalize($d->name);
            $hit = $districtsMap[$key] ?? null;
            if ($hit) {
                $d->rajaongkir_id = (string) $hit['id'];
                $d->save();
                $stats['district']++;
            } else {
                $stats['failed']++;
            }
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();

        $this->printSummary($stats);
        return self::SUCCESS;
    }

    private function printSummary(array $stats): void
    {
        $this->info(sprintf(
            'Selesai. Provinsi: %d · Kab/Kota: %d · Kecamatan: %d · Tidak match: %d',
            $stats['province'], $stats['regency'], $stats['district'], $stats['failed'],
        ));
    }

    /**
     * Kumpulkan ID kecamatan yang dipakai user (kolom embedded lama) atau
     * tabel addresses (alamat multi).
     *
     * @return array<int,string>
     */
    private function collectActiveDistrictIds(): array
    {
        $ids = collect();
        try {
            $ids = $ids->merge(User::query()->whereNotNull('district_id')->pluck('district_id'));
        } catch (\Throwable $e) { /* tabel users belum ada */ }
        try {
            $ids = $ids->merge(Address::query()->whereNotNull('district_id')->pluck('district_id'));
        } catch (\Throwable $e) { /* tabel addresses belum ada */ }
        // Plus alamat toko default dari site_settings — biasanya wajib di-sync.
        try {
            $storeDistrict = DB::table('site_settings')->where('key', 'store_district_id')->value('value');
            if (! empty($storeDistrict)) $ids->push($storeDistrict);
        } catch (\Throwable $e) { /* belum ada */ }

        return $ids->filter()->unique()->values()->all();
    }

    /**
     * Ubah list API jadi map: nama-ternormalisasi → row.
     */
    private function mapByNormalizedName(array $rows): array
    {
        $out = [];
        foreach ($rows as $r) {
            $name = $this->normalize((string) ($r['name'] ?? ''));
            if ($name !== '') $out[$name] = $r;
        }
        return $out;
    }

    /** Normalisasi nama untuk pencocokan (UPPERCASE + collapse whitespace). */
    private function normalize(?string $name): string
    {
        $name = mb_strtoupper(trim((string) $name));
        return preg_replace('/\s+/', ' ', $name) ?? '';
    }

    /** Strip prefix "KABUPATEN "/"KOTA " sebelum match — RajaOngkir tidak memakai prefix. */
    private function normalizeCity(?string $name): string
    {
        $name = $this->normalize($name);
        foreach (['KABUPATEN ', 'KAB. ', 'KAB ', 'KOTA ADMINISTRASI ', 'KOTA ADM. ', 'KOTA '] as $prefix) {
            if (str_starts_with($name, $prefix)) {
                return trim(substr($name, strlen($prefix)));
            }
        }
        return $name;
    }
}
