<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Hutang extends Model
{
    /** @use HasFactory<\Database\Factories\HutangFactory> */
    use HasFactory, SoftDeletes;

     protected $table = 'hutangs';

    protected $fillable = [
        'pembelian_id',
        'supplier_id',
        'total_tagihan',
        'total_terbayar',
        'sisa_tagihan',
        'jatuh_tempo',
        'status',
    ];

    protected static $logAttributes = [
        'pembelian_id', 'supplier_id', 'total_tagihan',
        'total_terbayar', 'sisa_tagihan', 'status',
    ];

    protected static $logOnlyDirty = true;
    protected static $logName = 'hutang';

    // 🔗 Relasi
    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class)->withTrashed();
    }

    public function pembayaran()
    {
        return $this->hasMany(PembayaranHutang::class);
    }

    // ⚙️ Event model: update status otomatis
    protected static function booted(): void
    {
        static::saving(function (Hutang $hutang) {
            $hutang->sisa_tagihan = $hutang->total_tagihan - $hutang->total_terbayar;
            $hutang->status = $hutang->sisa_tagihan <= 0 ? 'lunas' : 'belum_lunas';
        });
    }

    // 🧮 Helper: tambah pembayaran
    public function tambahPembayaran(float $jumlah): void
    {
        $this->increment('total_terbayar', $jumlah);
        $this->refresh();
    }
}
