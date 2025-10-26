<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Pembelian extends Model
{
    protected $table = 'pembelians';
    protected $fillable = ['no_faktur', 'supplier_id', 'tanggal', 'total', 'keterangan', 'kena_pajak', 'status', 'revisi_dari_id'];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->status ??= 'aktif';
            if (empty($model->no_faktur)) {
                $model->no_faktur = self::generateNoFaktur();
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
        return $this->belongsTo(Pembelian::class, 'revisi_dari_id')->withTrashed();
    }

    public function revisiAnak()
    {
        return $this->hasOne(Pembelian::class, 'revisi_dari_id')->withTrashed();
    }


    // Pembelian milik 1 supplier
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id')->withTrashed();
    }

    // Pembelian punya banyak item
    public function items()
    {
        return $this->hasMany(ItemPembelian::class, 'pembelian_id');
    }

    // Pembelian terkait ke transaksi kas (polymorphic)
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

    public static function generateNoFaktur()
    {
        $prefix = 'IN' . now()->format('Ymd');

        $last = self::where('no_faktur', 'like', $prefix . '%')
            ->orderBy('no_faktur', 'desc')
            ->first();

        $number = $last
            ? intval(substr($last->no_faktur, -3)) + 1
            : 1;

        return $prefix . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}
