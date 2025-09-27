<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Penjualan;
use App\Models\Pembelian;
use App\Models\TransaksiKas;
use Carbon\Carbon;

class Dashboard extends Component
{
    public $chartData = [];
    public $summary = [];

    public function mount()
    {
        $this->loadSummary();
        $this->loadChartData();
    }

    private function loadSummary()
    {
        $today = Carbon::today();

        $this->summary = [
            'penjualan_hari_ini' => Penjualan::whereDate('tanggal', $today)->sum('total'),
            'pembelian_hari_ini' => Pembelian::whereDate('tanggal', $today)->sum('total'),
            'kas_masuk_hari_ini' => TransaksiKas::whereDate('tanggal', $today)->where('tipe', 'masuk')->sum('jumlah'),
            'kas_keluar_hari_ini' => TransaksiKas::whereDate('tanggal', $today)->where('tipe', 'keluar')->sum('jumlah'),
        ];
    }

    private function loadChartData()
    {
        $dates = collect(range(0, 6))
            ->map(fn($i) => Carbon::now()->subDays($i)->format('Y-m-d'))
            ->reverse();

        $penjualan = Penjualan::selectRaw('DATE(tanggal) as tgl, SUM(total) as total')
            ->whereBetween('tanggal', [now()->subDays(6), now()])
            ->groupBy('tgl')->pluck('total', 'tgl');

        $pembelian = Pembelian::selectRaw('DATE(tanggal) as tgl, SUM(total) as total')
            ->whereBetween('tanggal', [now()->subDays(6), now()])
            ->groupBy('tgl')->pluck('total', 'tgl');

        $kasMasuk = TransaksiKas::selectRaw('DATE(tanggal) as tgl, SUM(jumlah) as total')
            ->where('tipe', 'masuk')
            ->whereBetween('tanggal', [now()->subDays(6), now()])
            ->groupBy('tgl')->pluck('total', 'tgl');

        $kasKeluar = TransaksiKas::selectRaw('DATE(tanggal) as tgl, SUM(jumlah) as total')
            ->where('tipe', 'keluar')
            ->whereBetween('tanggal', [now()->subDays(6), now()])
            ->groupBy('tgl')->pluck('total', 'tgl');

        $this->chartData = [
            'labels' => $dates->values(),
            'penjualan' => $dates->map(fn($d) => $penjualan[$d] ?? 0)->values(),
            'pembelian' => $dates->map(fn($d) => $pembelian[$d] ?? 0)->values(),
            'kas_masuk' => $dates->map(fn($d) => $kasMasuk[$d] ?? 0)->values(),
            'kas_keluar' => $dates->map(fn($d) => $kasKeluar[$d] ?? 0)->values(),
        ];
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
