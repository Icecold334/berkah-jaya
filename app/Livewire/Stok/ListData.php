<?php

namespace App\Livewire\Stok;

use App\Models\Produk;
use Livewire\Component;
use App\Models\Pembelian;
use App\Models\Penjualan;
use Livewire\WithPagination;
use App\Models\ItemPembelian;
use App\Models\ItemPenjualan;
use App\Models\PergerakanStok;
use Illuminate\Support\Facades\DB;

class ListData extends Component
{
    use WithPagination;



    protected $paginationTheme = 'tailwind';
    public $detailProdukId = null;
    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }




    public function showDetail($id)
    {
        $this->detailProdukId = $id;
    }

    public function closeDetail()
    {
        $this->detailProdukId = null;
    }


    private function hitungStok($produkId, $pajak = true)
    {
        $produk = \App\Models\Produk::with([
            'itemPembelians.pembelian',
            'itemPenjualans.penjualan',
        ])->find($produkId);

        $stok = 0;

        // stok masuk dari pembelian
        foreach ($produk->itemPembelians as $item) {
            if ($item->pembelian && $item->pembelian->kena_pajak == $pajak) {
                $stok += $item->qty; // pembelian = stok masuk
            }
        }

        // stok keluar dari penjualan
        foreach ($produk->itemPenjualans as $item) {
            if ($item->penjualan && $item->penjualan->kena_pajak == $pajak) {
                $stok -= $item->qty; // penjualan = stok keluar
            }
        }

        return $stok;
    }



    private function hitungHargaJual($produk)
    {
        $maxHarga = $produk->suppliers()->max('harga_beli');
        if (!$maxHarga) return 0;

        $kenaPajak = $produk->suppliers()
            ->where('harga_beli', $maxHarga)
            ->value('kena_pajak');

        return $kenaPajak ? $maxHarga * 1.02 : $maxHarga;
    }
    public function render()
    {
        $query = Produk::query()
            ->when(
                $this->search,
                fn($q) =>
                $q->where('nama', 'like', "%{$this->search}%")
                    ->orWhere('kode_barang', 'like', "%{$this->search}%")
            );

        $produks = $query->paginate(10)->through(function ($produk) {
            $produk->stok_pajak = $this->hitungStok($produk->id, true);
            $produk->stok_non_pajak = $this->hitungStok($produk->id, false);
            $produk->stok_total = $produk->stok_pajak + $produk->stok_non_pajak;
            $produk->harga_jual = $this->hitungHargaJual($produk);
            return $produk;
        });

        $detail = null;
        $pergerakan = collect();
        if ($this->detailProdukId) {
            $detail = Produk::find($this->detailProdukId);

            $pergerakan = PergerakanStok::with('sumber')
                ->where('produk_id', $this->detailProdukId)
                ->orderByDesc('id')
                ->paginate(10, ['*'], 'pergerakanPage');
        }

        return view('livewire.stok.list_data', [
            'produks' => $produks,
            'detail' => $detail,
            'pergerakan' => $pergerakan,
        ]);
    }
}
