<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranHutang extends Model
{
    /** @use HasFactory<\Database\Factories\PembayaranHutangFactory> */
    use HasFactory;

     protected $table = 'pembayaran_hutangs';

       protected $fillable = [
        'hutang_id',
        'transaksi_kas_id',
        'jumlah',
    ];

    protected static $logAttributes = ['hutang_id', 'transaksi_kas_id', 'jumlah'];
    protected static $logOnlyDirty = true;
    protected static $logName = 'pembayaran_hutang';

    // 🔗 Relasi
    public function hutang()
    {
        return $this->belongsTo(Hutang::class);
    }

    public function transaksiKas()
    {
        return $this->belongsTo(TransaksiKas::class);
    }

    // 🧠 Event: update total hutang otomatis
    protected static function booted(): void
    {
        static::created(function (PembayaranHutang $bayar) {
            $bayar->hutang->tambahPembayaran($bayar->jumlah);
        });
    }
}
