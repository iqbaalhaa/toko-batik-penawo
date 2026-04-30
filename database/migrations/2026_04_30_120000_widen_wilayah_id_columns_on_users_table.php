<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cuid dari tabel referensi wilayah berukuran 25 karakter, sedangkan
        // kolom awal hanya varchar(20). Lebarkan supaya tidak ter-truncate
        // saat user memilih dari dropdown wilayah.
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE users MODIFY province_id VARCHAR(30) NULL');
            DB::statement('ALTER TABLE users MODIFY city_id VARCHAR(30) NULL');
            DB::statement('ALTER TABLE users MODIFY district_id VARCHAR(30) NULL');
        }
        // SQLite tidak peduli ukuran VARCHAR (semua TEXT), jadi tidak perlu ALTER.
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE users MODIFY province_id VARCHAR(20) NULL');
            DB::statement('ALTER TABLE users MODIFY city_id VARCHAR(20) NULL');
            DB::statement('ALTER TABLE users MODIFY district_id VARCHAR(20) NULL');
        }
    }
};
