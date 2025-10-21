<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ✅ Tambah kolom ke tabel pembelians
        Schema::table('pembelians', function (Blueprint $table) {
            if (!Schema::hasColumn('pembelians', 'status')) {
                $table->string('status', 20)
                    ->default('aktif')
                    ->after('total');
            }

            if (!Schema::hasColumn('pembelians', 'revisi_dari_id')) {
                $table->foreignId('revisi_dari_id')
                    ->nullable()
                    ->after('status')
                    ->constrained('pembelians')
                    ->nullOnDelete();
            }
        });

        // ✅ Tambah kolom ke tabel penjualans
        Schema::table('penjualans', function (Blueprint $table) {
            if (!Schema::hasColumn('penjualans', 'status')) {
                $table->string('status', 20)
                    ->default('aktif')
                    ->after('total');
            }

            if (!Schema::hasColumn('penjualans', 'revisi_dari_id')) {
                $table->foreignId('revisi_dari_id')
                    ->nullable()
                    ->after('status')
                    ->constrained('penjualans')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        // 🔄 Rollback
        Schema::table('pembelians', function (Blueprint $table) {
            if (Schema::hasColumn('pembelians', 'revisi_dari_id')) {
                $table->dropForeign(['revisi_dari_id']);
                $table->dropColumn('revisi_dari_id');
            }

            if (Schema::hasColumn('pembelians', 'status')) {
                $table->dropColumn('status');
            }
        });

        Schema::table('penjualans', function (Blueprint $table) {
            if (Schema::hasColumn('penjualans', 'revisi_dari_id')) {
                $table->dropForeign(['revisi_dari_id']);
                $table->dropColumn('revisi_dari_id');
            }

            if (Schema::hasColumn('penjualans', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
