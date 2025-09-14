<?php

use App\Http\Controllers\PembelianController;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth'])->group(function () {
  Route::get('/pembelian', [PembelianController::class, 'index'])->name('pembelian.index');
});
