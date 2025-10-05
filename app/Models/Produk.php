<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    protected $table = 'produks';
    protected $fillable = ['nama', 'slug', 'kode_barang'];

    /** 🔗 Relasi **/
    // Produk bisa punya banyak supplier (pivot: produk_supplier)
    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'produk_suppliers')
            ->withPivot(['harga_beli', 'kena_pajak', 'tanggal_pembelian_terakhir'])
            ->withTimestamps();
    }

    // Produk muncul di banyak item pembelian
    public function itemPembelians()
    {
        return $this->hasMany(ItemPembelian::class, 'produk_id');
    }

    // Produk muncul di banyak item penjualan
    public function itemPenjualans()
    {
        return $this->hasMany(ItemPenjualan::class, 'produk_id');
    }

    // Produk punya banyak pergerakan stok
    public function pergerakanStok()
    {
        return $this->hasMany(PergerakanStok::class, 'produk_id');
    }

    /** ⚙️ Helper Method **/
    // Hitung stok realtime
    public function getStokAttribute()
    {
        return $this->pergerakanStok()
            ->selectRaw("SUM(CASE WHEN tipe = 'masuk' THEN qty ELSE -qty END) as total")
            ->value('total') ?? 0;
    }

    // Harga beli tertinggi dari relasi supplier
    public function getHargaBeliTertinggiAttribute()
    {
        $harga = $this->suppliers()->max('produk_suppliers.harga_beli');
        return $harga;
    }

    // Harga jual default (aturan: harga beli tertinggi + pajak jika ada)
    public function getHargaJualDefaultAttribute()
    {
        $presentase = Setting::getValue('presentase') ?? 0;
        $presentase = $presentase / 100;

        // Ambil semua harga dari pivot
        $hargaList = $this->suppliers()
            ->get(['produk_suppliers.harga_beli', 'produk_suppliers.kena_pajak']);

        $hargaJualList = $hargaList->map(function ($row) use ($presentase) {
            $harga = $row->pivot->harga_beli;
            if ($row->pivot->kena_pajak) {
                $harga += $harga * $presentase;
            }
            return $harga;
        });


        return $hargaJualList->max() ?? 0;
    }
}
