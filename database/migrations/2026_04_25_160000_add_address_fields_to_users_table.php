<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('address')->nullable()->after('phone');
            $table->string('city', 80)->nullable()->after('address');
            $table->string('province', 80)->nullable()->after('city');
            $table->string('postal_code', 10)->nullable()->after('province');
            $table->date('birth_date')->nullable()->after('postal_code');
            $table->enum('gender', ['pria', 'wanita'])->nullable()->after('birth_date');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['address', 'city', 'province', 'postal_code', 'birth_date', 'gender']);
        });
    }
};
