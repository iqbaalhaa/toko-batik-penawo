<?php

namespace Tests\Feature;

use App\Services\RajaOngkirService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Unit/integration tests untuk RajaOngkirService.
 *
 * Pakai `Http::fake()` agar tidak menyentuh API beneran. Cache di-clear sebelum
 * tiap test supaya hierarki/tarif tidak bocor antar-skenario.
 */
class RajaOngkirServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();

        // Konfigurasi minimal yang dipakai service.
        config([
            'services.rajaongkir.key'       => 'test-key',
            'services.rajaongkir.base_url'  => 'https://rajaongkir.komerce.id/api/v1',
            'services.rajaongkir.couriers'  => 'jne:jnt:pos',
            'services.rajaongkir.timeout'   => 10,
            'services.rajaongkir.cache_ttl' => 60,
        ]);
    }

    public function test_disabled_when_api_key_empty(): void
    {
        config(['services.rajaongkir.key' => '']);
        $svc = new RajaOngkirService();
        $this->assertFalse($svc->enabled());
    }

    public function test_enabled_when_api_key_set(): void
    {
        $svc = new RajaOngkirService();
        $this->assertTrue($svc->enabled());
    }

    public function test_resolve_district_id_returns_id_when_all_levels_match(): void
    {
        Http::fake([
            'rajaongkir.komerce.id/api/v1/destination/province' => Http::response([
                'data' => [['id' => 12, 'name' => 'JAWA TENGAH']],
            ]),
            'rajaongkir.komerce.id/api/v1/destination/city/12' => Http::response([
                'data' => [['id' => 591, 'name' => 'BANYUMAS']],
            ]),
            'rajaongkir.komerce.id/api/v1/destination/district/591' => Http::response([
                'data' => [
                    ['id' => 6131, 'name' => 'PURWOKERTO BARAT'],
                    ['id' => 6136, 'name' => 'BANYUMAS'],
                ],
            ]),
        ]);

        $svc = new RajaOngkirService();
        $id  = $svc->resolveDistrictId('JAWA TENGAH', 'KABUPATEN BANYUMAS', 'PURWOKERTO BARAT');
        $this->assertSame(6131, $id);
    }

    public function test_resolve_district_id_returns_null_when_province_not_found(): void
    {
        Http::fake([
            'rajaongkir.komerce.id/api/v1/destination/province' => Http::response([
                'data' => [['id' => 12, 'name' => 'JAWA TENGAH']],
            ]),
        ]);

        $svc = new RajaOngkirService();
        $this->assertNull($svc->resolveDistrictId('PROVINSI NGAWUR', 'KOTA X', 'KECAMATAN Y'));
    }

    public function test_resolve_district_id_strips_kabupaten_prefix(): void
    {
        // "KABUPATEN BANYUMAS" (lokal) harus cocok dengan "BANYUMAS" (RajaOngkir).
        Http::fake([
            'rajaongkir.komerce.id/api/v1/destination/province' => Http::response([
                'data' => [['id' => 12, 'name' => 'JAWA TENGAH']],
            ]),
            'rajaongkir.komerce.id/api/v1/destination/city/12' => Http::response([
                'data' => [['id' => 591, 'name' => 'BANYUMAS']],
            ]),
            'rajaongkir.komerce.id/api/v1/destination/district/591' => Http::response([
                'data' => [['id' => 6131, 'name' => 'PURWOKERTO BARAT']],
            ]),
        ]);

        $svc = new RajaOngkirService();
        $this->assertSame(6131, $svc->resolveDistrictId('JAWA TENGAH', 'KABUPATEN BANYUMAS', 'PURWOKERTO BARAT'));
    }

    public function test_calculate_picks_cheapest_service_across_couriers(): void
    {
        Http::fake([
            'rajaongkir.komerce.id/api/v1/calculate/district/domestic-cost' => Http::response([
                'data' => [
                    ['name' => 'JNE',      'code' => 'jne', 'service' => 'REG', 'description' => 'Reguler', 'cost' => 22000, 'etd' => '1-2 day'],
                    ['name' => 'JNE',      'code' => 'jne', 'service' => 'OKE', 'description' => 'Ekonomis', 'cost' => 19000, 'etd' => '2-3 day'],
                    ['name' => 'J&T',      'code' => 'jnt', 'service' => 'EZ',  'description' => 'Reguler', 'cost' => 17000, 'etd' => '2 day'],
                ],
            ]),
        ]);

        $svc  = new RajaOngkirService();
        $best = $svc->calculate(6131, 6136, 1500);
        $this->assertNotNull($best);
        $this->assertSame(17000, $best['cost']);
        $this->assertSame('jnt', $best['code']);
        $this->assertSame('EZ', $best['service']);
    }

    public function test_calculate_returns_null_on_5xx(): void
    {
        Http::fake([
            'rajaongkir.komerce.id/api/v1/calculate/district/domestic-cost' => Http::response('Internal Error', 500),
        ]);
        $svc = new RajaOngkirService();
        $this->assertNull($svc->calculate(6131, 6136, 1500));
    }

    public function test_calculate_returns_null_when_data_empty(): void
    {
        Http::fake([
            'rajaongkir.komerce.id/api/v1/calculate/district/domestic-cost' => Http::response(['data' => []]),
        ]);
        $svc = new RajaOngkirService();
        $this->assertNull($svc->calculate(6131, 6136, 1500));
    }

    public function test_calculate_cache_hit_prevents_duplicate_http_call(): void
    {
        Http::fake([
            'rajaongkir.komerce.id/api/v1/calculate/district/domestic-cost' => Http::response([
                'data' => [
                    ['name' => 'JNE', 'code' => 'jne', 'service' => 'REG', 'description' => '-', 'cost' => 9000, 'etd' => '1 day'],
                ],
            ]),
        ]);

        $svc = new RajaOngkirService();
        $svc->calculate(6131, 6136, 1500);
        $svc->calculate(6131, 6136, 1500); // cached

        Http::assertSentCount(1);
    }

    public function test_hierarchy_list_cache_hit_prevents_duplicate_http_call(): void
    {
        Http::fake([
            'rajaongkir.komerce.id/api/v1/destination/province' => Http::response([
                'data' => [['id' => 12, 'name' => 'JAWA TENGAH']],
            ]),
        ]);

        $svc = new RajaOngkirService();
        $svc->listProvinces();
        $svc->listProvinces(); // di-cache 30 hari

        Http::assertSentCount(1);
    }

    public function test_min_weight_clamped_to_1_gram(): void
    {
        // Pastikan request POST tetap punya weight valid (>=1) walau pemanggil
        // mengirim 0 atau negatif.
        Http::fake([
            'rajaongkir.komerce.id/api/v1/calculate/district/domestic-cost' => Http::response([
                'data' => [['name' => 'JNE', 'code' => 'jne', 'service' => 'REG', 'description' => '-', 'cost' => 8000, 'etd' => '1 day']],
            ]),
        ]);
        $svc = new RajaOngkirService();
        $svc->calculate(6131, 6136, 0);

        Http::assertSent(function ($request) {
            $body = $request->body();
            return str_contains($body, 'weight=1');
        });
    }

    public function test_calculate_sends_form_encoded_with_key_header(): void
    {
        Http::fake([
            'rajaongkir.komerce.id/api/v1/calculate/district/domestic-cost' => Http::response([
                'data' => [['name' => 'JNE', 'code' => 'jne', 'service' => 'REG', 'description' => '-', 'cost' => 1, 'etd' => '1']],
            ]),
        ]);
        $svc = new RajaOngkirService();
        $svc->calculate(6131, 6136, 2000);

        Http::assertSent(function ($request) {
            return $request->method() === 'POST'
                && $request->header('key')[0] === 'test-key'
                && str_starts_with($request->header('Content-Type')[0], 'application/x-www-form-urlencoded');
        });
    }
}
