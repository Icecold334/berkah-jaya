<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Tambahkan kolom slug (unik, nullable dulu biar migrasi aman)
            $table->string('slug')->nullable()->unique()->after('nama');
        });

        // Optional: isi slug untuk data existing (hindari null)
        \App\Models\Customer::whereNull('slug')->chunkById(100, function ($customers) {
            foreach ($customers as $customer) {
                $slug = \Illuminate\Support\Str::slug($customer->nama);

                // Pastikan unik
                $original = $slug;
                $i = 1;
                while (\App\Models\Customer::where('slug', $slug)->exists()) {
                    $slug = $original . '-' . $i++;
                }

                $customer->slug = $slug;
                $customer->saveQuietly();
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
