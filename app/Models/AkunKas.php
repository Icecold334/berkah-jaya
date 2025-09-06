<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AkunKas extends Model
{
    protected $table = 'akun_kas';
    protected $fillable = ['nama', 'tipe', 'saldo_awal'];

    /** 🔗 Relasi **/
    public function transaksiKas()
    {
        return $this->hasMany(TransaksiKas::class, 'akun_kas_id');
    }

    /** ⚙️ Helper Method **/
    public function getSaldoAttribute()
    {
        $mutasi = $this->transaksiKas()
            ->selectRaw("SUM(CASE WHEN tipe='masuk' THEN jumlah ELSE -jumlah END) as saldo")
            ->value('saldo');

        return $this->saldo_awal + ($mutasi ?? 0);
    }
}
