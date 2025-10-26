<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PergerakanStok extends Model
{
    protected $table = 'pergerakan_stoks';

    protected $fillable = [
        'produk_id',
        'tanggal',
        'tipe',
        'qty',
        'produk_supplier_id',
        'sumber_type',
        'sumber_id',
        'keterangan'
    ];

    protected $casts = [
        'tanggal' => 'date'
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    // morph ke ItemPembelian / ItemPenjualan / lainnya
    public function sumber()
    {
        return $this->morphTo();
    }

    /** helper */
    public function getIsMasukAttribute()
    {
        return $this->tipe === 'masuk';
    }

    public function getIsKeluarAttribute()
    {
        return $this->tipe === 'keluar';
    }

    public function getQtySignedAttribute()
    {
        return $this->is_masuk ? $this->qty : -$this->qty;
    }
}
