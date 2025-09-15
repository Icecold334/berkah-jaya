<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('item_penjualans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penjualan_id')->constrained('penjualans');
            $table->foreignId('produk_id')->constrained('produks');
            $table->unsignedBigInteger('produk_supplier_id')->nullable();
            $table->decimal('harga_jual', 15, 2);
            $table->integer('qty');
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();

            $table->foreign('produk_supplier_id')
                ->references('id')->on('produk_suppliers')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_penjualans');
    }
};
