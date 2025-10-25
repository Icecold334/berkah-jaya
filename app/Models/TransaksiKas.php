<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiKas extends Model
{
    protected $table = 'transaksi_kas';
    protected $fillable = [
        'tanggal',
        'tipe',
        'kategori_id',
        'akun_kas_id',
        'jumlah',
        'keterangan',
        'sumber_type',
        'sumber_id'
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];
    /** 🔗 Relasi **/

    public function akunKas()
    {
        return $this->belongsTo(AkunKas::class, 'akun_kas_id');
    }


    // Transaksi kas milik satu kategori
    public function kategori()
    {
        return $this->belongsTo(KategoriKas::class, 'kategori_id');
    }

    // Transaksi kas bisa berasal dari berbagai sumber (polymorphic)
    public function sumber()
    {
        return $this->morphTo();
    }

    /** ⚙️ Helper Method **/

    // Cek apakah transaksi ini kas masuk
    public function getIsMasukAttribute()
    {
        return $this->tipe === 'masuk';
    }

    // Cek apakah transaksi ini kas keluar
    public function getIsKeluarAttribute()
    {
        return $this->tipe === 'keluar';
    }

    // Format nominal dalam rupiah
    public function getJumlahFormatAttribute()
    {
        return 'Rp ' . number_format($this->jumlah, 0, ',', '.');
    }

    public function pembayaranPiutang()
    {
        return $this->hasOne(PembayaranPiutang::class);
    }

    public function pembayaranHutang()
    {
        return $this->hasOne(PembayaranHutang::class);
    }
}
