<?php

namespace Tests\Unit;

use App\Services\ShippingCalculator;
use PHPUnit\Framework\TestCase;

class ShippingCalculatorTest extends TestCase
{
    /** Helper untuk membentuk alamat minimal dengan 4 level wilayah. */
    private function addr(string $provinceId, string $cityId, string $districtId): array
    {
        return [
            'province_id'   => $provinceId,
            'province_name' => 'Provinsi ' . $provinceId,
            'city_id'       => $cityId,
            'city_name'     => 'Kota ' . $cityId,
            'district_id'   => $districtId,
            'district_name' => 'Kecamatan ' . $districtId,
            'full_address'  => 'Jalan Mawar 1',
        ];
    }

    public function test_same_district_below_base_weight(): void
    {
        $r = ShippingCalculator::calculate(
            $this->addr('P1', 'C1', 'D1'),
            $this->addr('P1', 'C1', 'D1'),
            3.0,
        );

        $this->assertTrue($r['available']);
        $this->assertSame('same_district', $r['zone']);
        $this->assertSame('Satu Kecamatan', $r['zone_label']);
        $this->assertSame(10000, $r['base_fee']);
        $this->assertSame(2000, $r['extra_fee_per_kg']);
        $this->assertSame(3, $r['total_weight_kg']);
        // <= 5 kg → tarif dasar saja.
        $this->assertSame(10000, $r['shipping_cost']);
    }

    public function test_same_district_above_base_weight(): void
    {
        // 8 kg → 10000 + (8-5)*2000 = 16000.
        $r = ShippingCalculator::calculate(
            $this->addr('P1', 'C1', 'D1'),
            $this->addr('P1', 'C1', 'D1'),
            8.0,
        );
        $this->assertSame(16000, $r['shipping_cost']);
    }

    public function test_same_city_different_district(): void
    {
        // Provinsi & kota sama, kec. beda → 20000 + (7-5)*3000 = 26000.
        $r = ShippingCalculator::calculate(
            $this->addr('P1', 'C1', 'D1'),
            $this->addr('P1', 'C1', 'D2'),
            7.0,
        );
        $this->assertTrue($r['available']);
        $this->assertSame('same_city', $r['zone']);
        $this->assertSame('Satu Kota/Kabupaten', $r['zone_label']);
        $this->assertSame(26000, $r['shipping_cost']);
    }

    public function test_same_province_different_city(): void
    {
        // Provinsi sama, kota beda → 30000 + (6-5)*4000 = 34000.
        $r = ShippingCalculator::calculate(
            $this->addr('P1', 'C1', 'D1'),
            $this->addr('P1', 'C2', 'D9'),
            6.0,
        );
        $this->assertTrue($r['available']);
        $this->assertSame('same_province', $r['zone']);
        $this->assertSame('Satu Provinsi', $r['zone_label']);
        $this->assertSame(34000, $r['shipping_cost']);
    }

    public function test_outside_province(): void
    {
        // Beda provinsi → 40000 + (8-5)*5000 = 55000.
        $r = ShippingCalculator::calculate(
            $this->addr('P1', 'C1', 'D1'),
            $this->addr('P2', 'C9', 'D9'),
            8.0,
        );
        $this->assertTrue($r['available']);
        $this->assertSame('outside_province', $r['zone']);
        $this->assertSame('Luar Provinsi', $r['zone_label']);
        $this->assertSame(55000, $r['shipping_cost']);
    }

    public function test_outside_province_below_base_weight_is_just_base(): void
    {
        // Beda provinsi, 2 kg → 40000 (tarif dasar saja).
        $r = ShippingCalculator::calculate(
            $this->addr('P1', 'C1', 'D1'),
            $this->addr('P2', 'C9', 'D9'),
            2.0,
        );
        $this->assertSame(40000, $r['shipping_cost']);
    }

    public function test_decimal_weight_is_ceiled(): void
    {
        // 5.1 kg → ceil 6 → same_district 10000 + (6-5)*2000 = 12000.
        $r = ShippingCalculator::calculate(
            $this->addr('P1', 'C1', 'D1'),
            $this->addr('P1', 'C1', 'D1'),
            5.1,
        );
        $this->assertSame(6, $r['total_weight_kg']);
        $this->assertSame(12000, $r['shipping_cost']);
    }

    public function test_weight_at_exact_base_is_base_fee(): void
    {
        // 5 kg pas → kelebihan 0 kg → base_fee saja.
        $r = ShippingCalculator::calculate(
            $this->addr('P1', 'C1', 'D1'),
            $this->addr('P1', 'C1', 'D1'),
            5.0,
        );
        $this->assertSame(10000, $r['shipping_cost']);
    }

    public function test_zero_weight_rejected(): void
    {
        $r = ShippingCalculator::calculate(
            $this->addr('P1', 'C1', 'D1'),
            $this->addr('P1', 'C1', 'D1'),
            0.0,
        );
        $this->assertFalse($r['available']);
    }

    public function test_incomplete_buyer_address_rejected(): void
    {
        $buyer = ['province_id' => '', 'city_id' => '', 'district_id' => ''];
        $r = ShippingCalculator::calculate($this->addr('P1', 'C1', 'D1'), $buyer, 2.0);
        $this->assertFalse($r['available']);
        $this->assertStringContainsStringIgnoringCase('alamat pengiriman', $r['message']);
    }

    public function test_incomplete_store_address_rejected(): void
    {
        $r = ShippingCalculator::calculate(
            ['province_id' => null, 'city_id' => null, 'district_id' => null],
            $this->addr('P1', 'C1', 'D1'),
            2.0,
        );
        $this->assertFalse($r['available']);
        $this->assertStringContainsStringIgnoringCase('toko', $r['message']);
    }

    public function test_missing_province_id_treated_as_incomplete(): void
    {
        // Tanpa province_id, sekarang dianggap alamat tidak lengkap (bukan outside_province otomatis).
        $buyer = [
            'province_id' => null,
            'city_id'     => 'C1',
            'district_id' => 'D1',
        ];
        $r = ShippingCalculator::calculate($this->addr('P1', 'C1', 'D1'), $buyer, 2.0);
        $this->assertFalse($r['available']);
    }

    public function test_response_shape(): void
    {
        $r = ShippingCalculator::calculate(
            $this->addr('P1', 'C1', 'D1'),
            $this->addr('P1', 'C1', 'D2'),
            6.0,
        );
        foreach (['available','zone','zone_label','base_fee','base_weight_kg','extra_fee_per_kg','total_weight_kg','shipping_cost','message'] as $key) {
            $this->assertArrayHasKey($key, $r, "Field {$key} hilang dari response");
        }
        $this->assertSame(5, $r['base_weight_kg']);
    }
}
