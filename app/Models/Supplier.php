<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;
    protected $table = 'suppliers';
    protected $fillable = ['nama', 'alamat', 'telepon', 'npwp'];

    /** 🔗 Relasi **/

    // Supplier bisa punya banyak produk (relasi pivot)
    public function produks()
    {
        return $this->belongsToMany(Produk::class, 'produk_suppliers')
            ->withPivot(['harga_beli', 'kena_pajak', 'tanggal_pembelian_terakhir'])
            ->withTimestamps();
    }

    // Supplier punya banyak pembelian
    public function pembelians()
    {
        return $this->hasMany(Pembelian::class, 'supplier_id');
    }

    /** ⚙️ Helper Method **/

    // Ambil produk terakhir yang dibeli dari supplier ini
    public function produkTerakhir()
    {
        return $this->produks()
            ->orderByPivot('tanggal_pembelian_terakhir', 'desc')
            ->first();
    }

    // Total transaksi pembelian dengan supplier ini
    public function getTotalPembelianAttribute()
    {
        return $this->pembelians()->sum('total');
    }
}
