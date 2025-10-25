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
        Schema::create('piutangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penjualan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->decimal('total_tagihan', 15, 2);
            $table->decimal('total_terbayar', 15, 2)->default(0);
            $table->decimal('sisa_tagihan', 15, 2);
            $table->date('jatuh_tempo')->nullable();
            $table->enum('status', ['belum_lunas', 'lunas', 'direvisi'])->default('belum_lunas');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('piutangs');
    }
};
