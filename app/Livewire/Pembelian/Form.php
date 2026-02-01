<?php

namespace App\Livewire\Pembelian;

use Carbon\Carbon;
use App\Models\Hutang;
use App\Models\Produk;
use Livewire\Component;
use App\Models\Supplier;
use App\Models\Pembelian;
use App\Models\KategoriKas;
use Illuminate\Support\Str;
use App\Models\TransaksiKas;
use App\Models\ItemPembelian;
use App\Models\PergerakanStok;
use App\Models\ProdukSupplier;
use App\Models\PembayaranKredit;
use Illuminate\Support\Facades\DB;

class Form extends Component
{
    public $supplier_id;
    public $tanggal;
    public $keterangan;
    public $kenaPajak = false;

    public $items = [];
    public $searchResults = [];

    public $showConfirmModal = false;
    public $showFinalConfirmModal = false;
    public $metodeBayar = null;
    public $jatuh_tempo;
    public $keterangan_kredit;

    public $totalPreview;

    public function updatedItems($value, $key)
    {
        // deteksi field mana yang berubah, misal items.0.nama
        if (Str::endsWith($key, '.nama')) {
            $index = explode('.', $key)[0];
            $nama = $this->items[$index]['nama'] ?? '';

            $slug = Str::slug($nama);
            if (strlen($nama)) {
                $this->searchResults[$index] = Produk::where('slug', 'like', "%{$slug}%")
                    ->limit(5)
                    ->pluck('nama')
                    ->toArray();
            } else {
                $this->searchResults[$index] = [];
            }
        }
    }
    public function pilihProduk($index, $nama)
    {
        $this->items[$index]['nama'] = $nama;
        $this->searchResults[$index] = []; // tutup dropdown setelah pilih
    }
    public function mount()
    {
        $this->tanggal = now()->format('Y-m-d');
        $this->addItem();
    }

    public function addItem()
    {
        $this->items[] = ['nama' => '', 'qty' => 1, 'harga_beli' => 0, 'kena_pajak' => $this->kenaPajak];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function konfirmasiSimpan()
    {
        $this->resetErrorBag();
        $this->totalPreview = collect($this->items)->sum(fn($i) => (int) preg_replace('/[^0-9]/', '', $i['harga_beli']) * (int) $i['qty']);
        $this->showConfirmModal = true;
    }

    public function pilihMetode($metode)
    {
        $this->metodeBayar = $metode;
        $this->showConfirmModal = false;
        $this->showFinalConfirmModal = true;
    }

    public function simpanFinal()
    {
        $this->simpan();
        $this->showFinalConfirmModal = false;
    }

    public function togglePajak()
    {
        $this->kenaPajak = !$this->kenaPajak;

        // ✅ optional: update semua row item
        foreach ($this->items as $i => $item) {
            $this->items[$i]['kena_pajak'] = $this->kenaPajak;
        }
    }

    public function simpan()
    {
        foreach ($this->items as $i => $item) {
            $this->items[$i]['harga_beli'] = (int) preg_replace('/[^0-9]/', '', $item['harga_beli']);
        }

        $this->validate(['supplier_id' => 'required|exists:suppliers,id']);

        DB::beginTransaction();
        try {
            $pembelian = Pembelian::create([
                'no_faktur' => Pembelian::generateNoFaktur(),
                'supplier_id' => $this->supplier_id,
                'tanggal' => $this->tanggal,
                'kena_pajak' => collect($this->items)->contains(fn($i) => $i['kena_pajak']),
                'total' => collect($this->items)->sum(fn($i) => $i['harga_beli'] * $i['qty']),
                'keterangan' => $this->keterangan,
            ]);

            // === Simpan item pembelian & stok
            foreach ($this->items as $item) {
                $produk = Produk::firstOrCreate(
                    ['slug' => Str::slug($item['nama'])],
                    ['nama' => $item['nama'], 'kode_barang' => fake()->unique()->numerify('BJ#####')]
                );

                // $produkSupplier = ProdukSupplier::updateOrCreate(
                //     ['produk_id' => $produk->id, 'supplier_id' => $this->supplier_id],
                //     ['harga_beli' => $item['harga_beli'], 'tanggal_pembelian_terakhir' => $this->tanggal]
                // );

                ItemPembelian::create([
                    'pembelian_id' => $pembelian->id,
                    'produk_id' => $produk->id,
                    'harga_beli' => $item['harga_beli'],
                    'qty' => $item['qty'],
                    'kena_pajak' => $item['kena_pajak'],
                ]);

                $produkSupplier = ProdukSupplier::firstOrCreate(
                    [
                        'produk_id' => $produk->id,
                        'supplier_id' => $this->supplier_id,
                        'kena_pajak' => $item['kena_pajak'] ?? false,
                    ],
                    [
                        'harga_beli' => 0,
                        'tanggal_pembelian_terakhir' => $this->tanggal,
                    ]
                );


                PergerakanStok::create([
                    'produk_id' => $produk->id,
                    'tanggal' => $this->tanggal,
                    'produk_supplier_id' => $produkSupplier->id,
                    'tipe' => 'masuk',
                    'qty' => $item['qty'],
                    'kena_pajak' => $item['kena_pajak'],
                    'sumber_type' => Pembelian::class,
                    'sumber_id' => $pembelian->id,
                    'keterangan' => 'Pembelian Barang',
                ]);

                if ($produkSupplier->harga_beli < $item['harga_beli']) {
                    $produkSupplier->harga_beli = $item['harga_beli'];
                    $produkSupplier->update();
                }
            }

            // === PEMBAYARAN ===
            // Tunai → buat transaksi kas
            // Kredit → tidak buat transaksi kas
            if ($this->metodeBayar === 'cash') {
                $kategori = KategoriKas::where('nama', 'Pembelian')->first();

                TransaksiKas::create([
                    'akun_kas_id' => 1,
                    'tanggal' => $this->tanggal,
                    'tipe' => 'keluar',
                    'kategori_id' => $kategori?->id,
                    'jumlah' => $pembelian->total,
                    'keterangan' => 'Pembelian #' . $pembelian->no_faktur,
                    'sumber_type' => Pembelian::class,
                    'sumber_id' => $pembelian->id,
                ]);
            }

            DB::commit();
            $this->resetForm();
            $this->dispatch('toast', type: 'success', message: 'Pembelian berhasil disimpan!');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            $this->dispatch('toast', type: 'error', message: 'Gagal simpan: ' . $e->getMessage());
        }
    }


    private function resetForm()
    {
        $this->supplier_id = '';
        $this->items = [];
        $this->keterangan = '';
        $this->kenaPajak = false;
        $this->addItem();
    }

    public function render()
    {
        return view('livewire.pembelian.form', [
            'suppliers' => Supplier::all(),
        ]);
    }
}
