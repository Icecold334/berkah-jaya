<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class ReturPembelian extends Model
{
    protected $table = 'retur_pembelians';
    protected $fillable = ['pembelian_id', 'tanggal', 'total', 'keterangan'];

    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class, 'pembelian_id');
    }

    public function items()
    {
        return $this->hasMany(ItemReturPembelian::class, 'retur_pembelian_id');
    }

    public function transaksiKas()
    {
        return $this->morphOne(TransaksiKas::class, 'sumber');
    }

    public function pergerakanStok()
    {
        return $this->morphMany(PergerakanStok::class, 'sumber');
    }

    public function hitungTotal()
    {
        $this->total = $this->items()->sum(DB::raw('harga_beli * qty'));
        $this->save();
    }
}
