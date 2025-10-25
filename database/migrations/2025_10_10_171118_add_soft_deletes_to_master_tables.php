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
        // ✅ Tambahkan kolom deleted_at (soft delete) ke tabel master
        Schema::table('suppliers', function (Blueprint $table) {
            if (!Schema::hasColumn('suppliers', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('produks', function (Blueprint $table) {
            if (!Schema::hasColumn('produks', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('akun_kas', function (Blueprint $table) {
            if (!Schema::hasColumn('akun_kas', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('kategori_kas', function (Blueprint $table) {
            if (!Schema::hasColumn('kategori_kas', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ✅ Hapus kolom deleted_at jika rollback
        Schema::table('suppliers', function (Blueprint $table) {
            if (Schema::hasColumn('suppliers', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });

        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });

        Schema::table('produks', function (Blueprint $table) {
            if (Schema::hasColumn('produks', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });

        Schema::table('akun_kas', function (Blueprint $table) {
            if (Schema::hasColumn('akun_kas', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });

        Schema::table('kategori_kas', function (Blueprint $table) {
            if (Schema::hasColumn('kategori_kas', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
