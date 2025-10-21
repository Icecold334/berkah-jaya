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
        Schema::table('item_penjualans', function (Blueprint $table) {
            if (Schema::hasColumn('item_penjualans', 'subtotal')) {
                $table->dropColumn('subtotal');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_penjualans', function (Blueprint $table) {
            $table->decimal('subtotal', 15, 2)->after('qty')->nullable();
        });
    }
};
