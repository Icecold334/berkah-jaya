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
        Schema::create('item_pembelians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembelian_id')->constrained('pembelians');
            $table->foreignId('produk_id')->constrained('produks');
            $table->decimal('harga_beli', 15, 2);
            $table->integer('qty');
            $table->boolean('kena_pajak')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_pembelians');
    }
};
