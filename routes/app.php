<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\PenjualanController;


Route::middleware(['auth'])->group(function () {
  Route::get('/pembelian', [PembelianController::class, 'index'])->name('pembelian.index');
  Route::get('/penjualan', [PenjualanController::class, 'index'])->name('penjualan.index');
  Route::get('/supplier', [SupplierController::class, 'index'])->name('supplier.index');
});
