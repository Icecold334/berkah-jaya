<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Pembelian extends Model
{
    protected $table = 'pembelians';
    protected $fillable = ['supplier_id', 'tanggal', 'total', 'keterangan'];

    /** 🔗 Relasi **/

    // Pembelian milik 1 supplier
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    // Pembelian punya banyak item
    public function items()
    {
        return $this->hasMany(ItemPembelian::class, 'pembelian_id');
    }

    // Pembelian terkait ke transaksi kas (polymorphic)
    public function transaksiKas(): MorphOne
    {
        return $this->morphOne(TransaksiKas::class, 'sumber');
    }

    // Pembelian terkait ke pergerakan stok (polymorphic)
    public function pergerakanStok()
    {
        return $this->morphMany(PergerakanStok::class, 'sumber');
    }

    /** ⚙️ Helper Method **/

    // Hitung ulang total dari item
    public function hitungTotal()
    {
        $this->total = $this->items()->sum(DB::raw('harga_beli * qty'));
        $this->save();
    }
}
