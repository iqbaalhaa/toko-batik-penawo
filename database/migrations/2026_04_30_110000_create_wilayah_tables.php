<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Referensi wilayah administratif Indonesia (Kemendagri).
        // Primary key memakai `id` cuid dari sumber data; `code` adalah kode
        // numerik standar (mis. 11, 1101, 1101010) yang digunakan di antarmuka.

        Schema::create('provinces', function (Blueprint $table) {
            $table->string('id', 30)->primary();
            $table->string('code', 10)->unique();
            $table->string('name', 100);
        });

        Schema::create('regencies', function (Blueprint $table) {
            $table->string('id', 30)->primary();
            $table->string('code', 10)->unique();
            $table->string('name', 100);
            $table->string('province_id', 30);
            $table->foreign('province_id')->references('id')->on('provinces')->cascadeOnDelete();
            $table->index('province_id');
        });

        Schema::create('districts', function (Blueprint $table) {
            $table->string('id', 30)->primary();
            $table->string('code', 10)->unique();
            $table->string('name', 100);
            $table->string('regency_id', 30);
            $table->foreign('regency_id')->references('id')->on('regencies')->cascadeOnDelete();
            $table->index('regency_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('districts');
        Schema::dropIfExists('regencies');
        Schema::dropIfExists('provinces');
    }
};
