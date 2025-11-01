<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customers';
    protected $fillable = ['nama', 'telepon', 'alamat', 'slug'];

    /** 🔗 Relasi **/

    // Customer punya banyak penjualan
    public function penjualans()
    {
        return $this->hasMany(Penjualan::class, 'customer_id');
    }

    /** ⚙️ Helper Method **/

    // Hitung total transaksi penjualan customer ini
    public function getTotalTransaksiAttribute()
    {
        return $this->penjualans()->sum('total');
    }

    // Ambil penjualan terakhir customer ini
    public function penjualanTerakhir()
    {
        return $this->penjualans()->latest('tanggal')->first();
    }
}
