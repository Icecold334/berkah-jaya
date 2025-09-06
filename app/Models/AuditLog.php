<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'audit_logs';
    protected $fillable = [
        'tabel',
        'record_id',
        'aksi',
        'data_lama',
        'data_baru',
        'user_id'
    ];

    protected $casts = [
        'data_lama' => 'array',
        'data_baru' => 'array',
    ];

    // relasi ke user
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
