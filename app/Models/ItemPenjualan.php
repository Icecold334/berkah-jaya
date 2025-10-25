<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemPenjualan extends Model
{
    protected $table = 'item_penjualans';

    protected $fillable = [
        'penjualan_id',
        'produk_id',
        'harga_jual',
        'qty',
        // 'subtotal',
    ];

    // Relasi ke Penjualan
    public function penjualan(): BelongsTo
    {
        return $this->belongsTo(Penjualan::class);
    }

    // Relasi ke Produk
    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class);
    }
}
