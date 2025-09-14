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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('tabel');                // nama tabel yang diubah
            $table->unsignedBigInteger('record_id'); // id record yg diubah
            $table->string('aksi');                 // created, updated, deleted
            $table->json('data_lama')->nullable();  // sebelum perubahan
            $table->json('data_baru')->nullable();  // sesudah perubahan
            $table->foreignId('user_id')->nullable()->constrained('users'); // siapa yg ubah
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
