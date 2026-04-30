<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Wilayah administratif terstruktur (untuk perhitungan ongkir berbasis ID wilayah).
            // Kolom legacy `address`, `city`, `province`, `postal_code` dibiarkan untuk back-compat.
            $table->string('province_id', 20)->nullable()->after('postal_code');
            $table->string('province_name', 80)->nullable()->after('province_id');
            $table->string('city_id', 20)->nullable()->after('province_name');
            $table->string('city_name', 80)->nullable()->after('city_id');
            $table->string('district_id', 20)->nullable()->after('city_name');
            $table->string('district_name', 80)->nullable()->after('district_id');
            $table->text('full_address')->nullable()->after('district_name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'province_id', 'province_name',
                'city_id', 'city_name',
                'district_id', 'district_name',
                'full_address',
            ]);
        });
    }
};
