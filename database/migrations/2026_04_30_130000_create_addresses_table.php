<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('label', 30);                        // contoh: "Rumah", "Kantor"
            $table->string('province_id', 30);
            $table->string('province_name', 80);
            $table->string('city_id', 30);
            $table->string('city_name', 80);
            $table->string('district_id', 30);
            $table->string('district_name', 80);
            $table->text('full_address');
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'is_default']);
        });

        // Data migration: salin alamat embedded di users (yang sudah lengkap)
        // menjadi satu Address default per user. Aman untuk dijalankan sekali —
        // jika user belum punya wilayah lengkap, dilewat.
        DB::table('users')
            ->whereNotNull('city_id')->where('city_id', '!=', '')
            ->whereNotNull('district_id')->where('district_id', '!=', '')
            ->whereNotNull('province_id')->where('province_id', '!=', '')
            ->select('id', 'province_id', 'province_name', 'city_id', 'city_name',
                     'district_id', 'district_name', 'full_address', 'address')
            ->orderBy('id')
            ->each(function ($u) {
                DB::table('addresses')->insert([
                    'user_id'       => $u->id,
                    'label'         => 'Alamat Utama',
                    'province_id'   => $u->province_id,
                    'province_name' => $u->province_name ?: '',
                    'city_id'       => $u->city_id,
                    'city_name'     => $u->city_name ?: '',
                    'district_id'   => $u->district_id,
                    'district_name' => $u->district_name ?: '',
                    'full_address'  => $u->full_address ?: ($u->address ?: ''),
                    'is_default'    => true,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
