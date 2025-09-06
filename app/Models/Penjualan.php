<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Penjualan extends Model
{
    protected $table = 'penjualans';
    protected $fillable = ['customer_id', 'tanggal', 'total'];

    /** 🔗 Relasi **/

    // Penjualan milik 1 customer (opsional)
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    // Penjualan punya banyak item
    public function items()
    {
        return $this->hasMany(ItemPenjualan::class, 'penjualan_id');
    }

    // Penjualan terkait ke transaksi kas (polymorphic)
    public function transaksiKas(): MorphOne
    {
        return $this->morphOne(TransaksiKas::class, 'sumber');
    }

    // Penjualan terkait ke pergerakan stok (polymorphic)
    public function pergerakanStok()
    {
        return $this->morphMany(PergerakanStok::class, 'sumber');
    }

    /** ⚙️ Helper Method **/

    // Hitung ulang total dari item
    public function hitungTotal()
    {
        $this->total = $this->items()->sum('subtotal');
        $this->save();
    }
}
