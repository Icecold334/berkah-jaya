<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class BaseModel extends Model
{
    protected static function booted()
    {
        static::created(function ($model) {
            AuditLog::create([
                'tabel'     => $model->getTable(),
                'record_id' => $model->getKey(),
                'aksi'      => 'created',
                'data_baru' => $model->toJson(),
                'user_id'   => Auth::id(),
            ]);
        });

        static::updated(function ($model) {
            AuditLog::create([
                'tabel'     => $model->getTable(),
                'record_id' => $model->getKey(),
                'aksi'      => 'updated',
                'data_lama' => json_encode($model->getOriginal()),
                'data_baru' => $model->toJson(),
                'user_id'   => Auth::id(),
            ]);
        });

        static::deleted(function ($model) {
            AuditLog::create([
                'tabel'     => $model->getTable(),
                'record_id' => $model->getKey(),
                'aksi'      => 'deleted',
                'data_lama' => $model->toJson(),
                'user_id'   => Auth::id(),
            ]);
        });
    }
}
