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
        Schema::create('pergerakan_stoks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained('produks');
            $table->date('tanggal');
            $table->enum('tipe', ['masuk', 'keluar', 'penyesuaian']);
            $table->integer('qty');
            $table->morphs('sumber'); // sumber_type, sumber_id
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pergerakan_stoks');
    }
};
