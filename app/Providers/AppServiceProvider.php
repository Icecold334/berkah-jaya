<?php

namespace App\Providers;

use App\Models\Produk;
use App\Models\AkunKas;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\KategoriKas;
use App\Models\TransaksiKas;
use App\Models\PergerakanStok;
use App\Observers\AuditObserver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $models = [
            Pembelian::class,
            Penjualan::class,
            Produk::class,
            Supplier::class,
            Customer::class,
            AkunKas::class,
            KategoriKas::class,
            TransaksiKas::class,
            PergerakanStok::class,
        ];

        foreach ($models as $model) {
            $model::observe(AuditObserver::class);
        }
        Auth::loginUsingId(1);
    }
}
