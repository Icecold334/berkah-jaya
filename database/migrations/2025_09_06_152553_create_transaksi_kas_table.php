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
        Schema::create('transaksi_kas', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->enum('tipe', ['masuk', 'keluar']);
            $table->foreignId('kategori_id')->nullable()->constrained('kategori_kas');
            $table->foreignId('akun_kas_id')->constrained('akun_kas');
            $table->decimal('jumlah', 15, 2);
            $table->text('keterangan')->nullable();
            $table->nullableMorphs('sumber'); // sumber_type, sumber_id
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_kas');
    }
};
