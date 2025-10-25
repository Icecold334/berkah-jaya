<?php

namespace App\Models;

use App\Models\Customer;
use App\Models\Penjualan;
use App\Models\PembayaranPiutang;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class Piutang extends Model
{
    /** @use HasFactory<\Database\Factories\PiutangFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'piutangs';

    protected $fillable = [
        'penjualan_id',
        'customer_id',
        'total_tagihan',
        'total_terbayar',
        'sisa_tagihan',
        'jatuh_tempo',
        'status',
    ];

    protected static $logAttributes = [
        'penjualan_id',
        'customer_id',
        'total_tagihan',
        'total_terbayar',
        'sisa_tagihan',
        'status',
    ];

    protected static $logOnlyDirty = true;
    protected static $logName = 'piutang';

    // 🔗 Relasi
    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function pembayaran()
    {
        return $this->hasMany(PembayaranPiutang::class);
    }

    // ⚙️ Event model: update status otomatis
    protected static function booted(): void
    {
        static::saving(function (Piutang $piutang) {
            $piutang->sisa_tagihan = $piutang->total_tagihan - $piutang->total_terbayar;
            $piutang->status = $piutang->sisa_tagihan <= 0 ? 'lunas' : 'belum_lunas';
        });
    }

    // 🧮 Helper: tambah pembayaran
    public function tambahPembayaran(float $jumlah): void
    {
        $this->increment('total_terbayar', $jumlah);
        $this->refresh();
    }
}
