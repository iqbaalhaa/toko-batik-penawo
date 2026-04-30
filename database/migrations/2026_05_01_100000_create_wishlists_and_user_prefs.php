<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            // Satu produk hanya boleh sekali per user (toggle dari sisi server).
            $table->unique(['user_id', 'product_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            // Preferensi notifikasi — default-nya nyala supaya aman secara UX.
            $table->boolean('notify_order_updates')->default(true)->after('gender');
            $table->boolean('notify_promo')->default(true)->after('notify_order_updates');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wishlists');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['notify_order_updates', 'notify_promo']);
        });
    }
};
