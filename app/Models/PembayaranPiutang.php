<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranPiutang extends Model
{
    /** @use HasFactory<\Database\Factories\PembayaranPiutangFactory> */
    use HasFactory;

    protected $table = 'pembayaran_piutangs';

    protected $fillable = [
        'piutang_id',
        'transaksi_kas_id',
        'jumlah',
    ];

    protected static $logAttributes = ['piutang_id', 'transaksi_kas_id', 'jumlah'];
    protected static $logOnlyDirty = true;
    protected static $logName = 'pembayaran_piutang';

    // 🔗 Relasi
    public function piutang()
    {
        return $this->belongsTo(Piutang::class);
    }

    public function transaksiKas()
    {
        return $this->belongsTo(TransaksiKas::class);
    }

    // 🧠 Event: update total piutang otomatis
    protected static function booted(): void
    {
        static::created(function (PembayaranPiutang $bayar) {
            $bayar->piutang->tambahPembayaran($bayar->jumlah);
        });
    }
}
