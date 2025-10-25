<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Penjualan extends Model
{
    protected $table = 'penjualans';
    protected $fillable = ['customer_id', 'tanggal', 'total', 'no_struk', 'kena_pajak', 'status'];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->status ??= 'aktif';
            if (empty($model->no_struk)) {
                $model->no_struk = self::generateNoStruk();
            }
        });
    }


    protected $casts = [
        'tanggal' => 'date',
        'kena_pajak' => 'boolean',
    ];

    /** 🔗 Relasi **/

    public function revisiDari()
    {
        return $this->belongsTo(Penjualan::class, 'revisi_dari_id')->withTrashed();
    }

    public function revisiAnak()
    {
        return $this->hasOne(Penjualan::class, 'revisi_dari_id')->withTrashed();
    }


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
    public function transaksiKas()
    {
        return $this->hasOne(TransaksiKas::class, 'sumber_id')
            ->where('sumber_type', self::class);
    }

    /**
     * Total jumlah pembayaran yang sudah masuk ke kas
     */
    public function getTotalDibayarAttribute()
    {
        return $this->transaksiKas()->sum('jumlah');
    }

    /**
     * Status pelunasan (lunas jika total pembayaran >= total pembelian)
     */
    public function getIsLunasAttribute()
    {
        return $this->total_dibayar >= $this->total;
    }

    /**
     * Sisa hutang (belum dibayar)
     */
    public function getSisaBayarAttribute()
    {
        return max(0, $this->total - $this->total_dibayar);
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
        $this->total = $this->items()->sum(DB::raw('harga_jual * qty'));
        $this->save();
    }


    public static function generateNoStruk()
    {
        $prefix = 'INV' . now()->format('Ymd');

        $last = self::where('no_struk', 'like', $prefix . '%')
            ->orderBy('no_struk', 'desc')
            ->first();

        $number = $last
            ? intval(substr($last->no_struk, -3)) + 1
            : 1;

        return $prefix . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}
