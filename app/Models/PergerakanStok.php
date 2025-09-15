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
        'sumber_type',
        'produk_supplier_id',
        'sumber_id',
        'keterangan'
    ];

    /** 🔗 Relasi **/

    // Pergerakan stok milik 1 produk
    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    // Pergerakan stok bisa berasal dari pembelian, penjualan, atau lainnya
    public function sumber()
    {
        return $this->morphTo();
    }

    /** ⚙️ Helper Method **/

    // Cek apakah stok masuk
    public function getIsMasukAttribute()
    {
        return $this->tipe === 'masuk';
    }

    // Cek apakah stok keluar
    public function getIsKeluarAttribute()
    {
        return $this->tipe === 'keluar';
    }

    // Nilai qty dengan tanda (positif untuk masuk, negatif untuk keluar)
    public function getQtySignedAttribute()
    {
        return $this->is_masuk ? $this->qty : -$this->qty;
    }
}
