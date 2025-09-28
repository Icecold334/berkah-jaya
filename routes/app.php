<?php

use App\Models\AkunKas;
use App\Livewire\Dashboard;
use App\Models\KategoriKas;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KasController;
use App\Http\Controllers\StokController;
use App\Http\Controllers\AkunKasController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\KategoriKasController;

Route::middleware(['auth'])->group(function () {
  Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

  Route::get('/setting', [SettingController::class, 'index'])->name('setting.index');
  Route::get('/akunkas', [AkunKasController::class, 'index'])->name('akun-kas.index');
  Route::get('/kategorikas', [KategoriKasController::class, 'index'])->name('kategori-kas.index');
  Route::get('/pembelian', [PembelianController::class, 'index'])->name('pembelian.index');
  Route::get('/penjualan', [PenjualanController::class, 'index'])->name('penjualan.index');
  Route::get('/supplier', [SupplierController::class, 'index'])->name('supplier.index');
  Route::get('/stok', [StokController::class, 'index'])->name('stok.index');
  Route::get('/kas', [KasController::class, 'index'])->name('kas.index');
  Route::get('/laporan/{type}', [LaporanController::class, 'index'])->name('laporan.index');
});
