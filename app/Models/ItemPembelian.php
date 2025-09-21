<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemPembelian extends Model
{
    protected $table = 'item_pembelians';

    protected $fillable = [
        'pembelian_id',
        'produk_id',
        'harga_beli',
        'qty',
    ];

    // Relasi ke Pembelian
    public function pembelian(): BelongsTo
    {
        return $this->belongsTo(Pembelian::class);
    }

    // Relasi ke Produk
    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class);
    }
}
