<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdukSupplier extends Model
{
    protected $table = 'produk_suppliers';
    protected $fillable = [
        'produk_id',
        'supplier_id',
        'harga_beli',
        'kena_pajak',
        'tanggal_pembelian_terakhir'
    ];

    /** 🔗 Relasi **/

    // Relasi ke produk
    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    // Relasi ke supplier
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /** ⚙️ Helper Method **/

    // Format harga beli ke rupiah
    public function getHargaBeliFormatAttribute()
    {
        return 'Rp ' . number_format($this->harga_beli, 0, ',', '.');
    }

    // Status pajak dalam teks
    public function getStatusPajakAttribute()
    {
        return $this->kena_pajak ? 'Kena Pajak' : 'Non Pajak';
    }
}
