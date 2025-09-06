<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditObserver
{
    public function created($model)
    {
        $this->log('created', $model, null, $model->getAttributes());
    }

    public function updated($model)
    {
        $this->log('updated', $model, $model->getOriginal(), $model->getAttributes());
    }

    public function deleted($model)
    {
        $this->log('deleted', $model, $model->getOriginal(), null);
    }

    protected function log($aksi, $model, $lama, $baru)
    {
        AuditLog::create([
            'tabel' => $model->getTable(),
            'record_id' => $model->id ?? 0,
            'aksi' => $aksi,
            'data_lama' => $lama,
            'data_baru' => $baru,
            'user_id' => Auth::id()
        ]);
    }
}
