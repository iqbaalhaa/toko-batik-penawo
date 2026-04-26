<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_product', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->primary(['product_id', 'category_id']);
        });

        // Backfill data lama (one-to-many) ke pivot many-to-many
        if (Schema::hasColumn('products', 'category_id')) {
            DB::statement('INSERT INTO category_product (product_id, category_id) SELECT id, category_id FROM products WHERE category_id IS NOT NULL');

            Schema::table('products', function (Blueprint $table) {
                $table->dropForeign(['category_id']);
                $table->dropColumn('category_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        // Pulihkan satu kategori per produk (yang pertama ditemukan di pivot)
        DB::statement('UPDATE products SET category_id = (SELECT category_id FROM category_product WHERE product_id = products.id LIMIT 1)');

        Schema::dropIfExists('category_product');
    }
};
