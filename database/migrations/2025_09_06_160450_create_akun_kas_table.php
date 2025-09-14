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
        Schema::create('akun_kas', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // contoh: Kas Toko, Bank BCA, Dana
            $table->string('tipe')->default('tunai'); // tunai, bank, e-wallet
            $table->decimal('saldo_awal', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('akun_kas');
    }
};
