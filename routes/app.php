<?php

use App\Http\Controllers\PembelianController;
use App\Http\Controllers\PenjualanController;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth'])->group(function () {
  Route::get('/pembelian', [PembelianController::class, 'index'])->name('pembelian.index');
  Route::get('/penjualan', [PenjualanController::class, 'index'])->name('penjualan.index');
});
