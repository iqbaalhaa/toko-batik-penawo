<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('snap_token')->nullable()->after('paid_at');
            $table->string('midtrans_transaction_id')->nullable()->after('snap_token');
            $table->string('midtrans_payment_type')->nullable()->after('midtrans_transaction_id');
            $table->string('midtrans_transaction_status')->nullable()->after('midtrans_payment_type');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'snap_token',
                'midtrans_transaction_id',
                'midtrans_payment_type',
                'midtrans_transaction_status',
            ]);
        });
    }
};
