<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriKas extends Model
{
    protected $table = 'kategori_kas';
    protected $fillable = ['tipe', 'nama'];

    /** 🔗 Relasi **/

    // KategoriKas bisa dipakai di banyak transaksi kas
    public function transaksiKas()
    {
        return $this->hasMany(TransaksiKas::class, 'kategori_id');
    }

    /** ⚙️ Helper Method **/

    // Cek apakah kategori ini tipe masuk
    public function getIsMasukAttribute()
    {
        return $this->tipe === 'masuk';
    }

    // Cek apakah kategori ini tipe keluar
    public function getIsKeluarAttribute()
    {
        return $this->tipe === 'keluar';
    }
}
