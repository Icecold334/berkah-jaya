<?php

namespace App\Livewire\Pembelian;

use Carbon\Carbon;
use App\Models\Produk;
use Livewire\Component;
use App\Models\Supplier;
use App\Models\Pembelian;
use Illuminate\Support\Str;
use App\Models\TransaksiKas;
use App\Models\ItemPembelian;
use App\Models\KategoriKas;
use App\Models\PergerakanStok;
use App\Models\ProdukSupplier;
use Illuminate\Support\Facades\DB;

class Form extends Component
{
    public $supplier_id;
    public $tanggal;
    public $keterangan;
    public $kenaPajak = false; // ✅ konsisten dengan blade

    public $items = []; // {nama, qty, harga_beli, kena_pajak}
    public $showConfirmModal = false; // untuk buka/tutup modal
    public $totalPreview;             // untuk tampilkan total di modal

    // Tambahkan di dalam class Form

    public $searchResults = []; // untuk simpan hasil pencarian produk
    public function konfirmasiSimpan()
    {
        // hitung total & status pajak sebelum modal muncul
        $this->totalPreview = collect($this->items)->sum(function ($i) {
            $harga = preg_replace('/[^0-9]/', '', $i['harga_beli']); // hapus semua non-angka
            $qty   = (int) $i['qty'];
            return ((int) $harga) * $qty;
        });

        $this->showConfirmModal = true;
    }

    public function simpanFinal()
    {
        // panggil logika simpan asli
        $this->simpan();
        $this->showConfirmModal = false;
    }
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
        $this->addItem(); // default 1 row
    }

    public function addItem()
    {
        $this->items[] = [
            'nama' => '',
            'qty' => 1,
            'harga_beli' => 0,
            'kena_pajak' => $this->kenaPajak,
        ];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
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
            if (isset($item['harga_beli'])) {
                $this->items[$i]['harga_beli'] = preg_replace('/[^0-9]/', '', $item['harga_beli']);
            }
        }

        $this->validate([
            'supplier_id' => 'required|exists:suppliers,id',
        ]);

        DB::transaction(function () {
            $noFaktur = Pembelian::generateNoFaktur();

            $pembelian = Pembelian::create([
                'no_faktur'   => $noFaktur,
                'supplier_id' => $this->supplier_id,
                'tanggal'     => $this->tanggal,
                // ✅ kalau ada item kena pajak, otomatis pembelian kena pajak
                'kena_pajak'  => collect($this->items)->contains(fn($i) => $i['kena_pajak']),
                'total'       => collect($this->items)->sum(fn($i) => $i['harga_beli'] * $i['qty']),
                'keterangan'  => $this->keterangan,
            ]);

            foreach ($this->items as $item) {
                $produk = Produk::firstOrCreate(
                    ['slug' => Str::slug($item['nama'])],
                    [
                        'nama'        => $item['nama'],
                        'kode_barang' => fake()->unique()->numerify('BJ#####'),
                    ]
                );

                ItemPembelian::create([
                    'pembelian_id' => $pembelian->id,
                    'produk_id'    => $produk->id,
                    'harga_beli'   => $item['harga_beli'],
                    'qty'          => $item['qty'],
                    'kena_pajak'   => $item['kena_pajak'] ?? false,
                ]);

                $produkSupplier = ProdukSupplier::updateOrCreate(
                    [
                        'produk_id'   => $produk->id,
                        'supplier_id' => $this->supplier_id,
                        'kena_pajak'                 => $item['kena_pajak'] ?? false,
                    ],
                    [
                        'harga_beli'                 => $item['harga_beli'],
                        'tanggal_pembelian_terakhir' => $this->tanggal,
                    ]
                );

                PergerakanStok::create([
                    'produk_id'          => $produk->id,
                    'tanggal'            => $this->tanggal,
                    'produk_supplier_id' => $produkSupplier->id,
                    'tipe'               => 'masuk',
                    'qty'                => $item['qty'],
                    'kena_pajak'         => $item['kena_pajak'] ?? false,
                    'sumber_type'        => Pembelian::class,
                    'sumber_id'          => $pembelian->id,
                    'keterangan'         => 'Pembelian',
                ]);
            }
            $pembelianId = KategoriKas::where('nama', 'pembelian')->first()->id;
            TransaksiKas::create([
                'akun_kas_id' => 1,
                'tanggal'     => $this->tanggal,
                'tipe'        => 'keluar',
                'kategori_id' => $pembelianId,
                'jumlah'      => $pembelian->total,
                'keterangan'  => 'Pembelian #' . $pembelian->no_faktur,
                'sumber_type' => Pembelian::class,
                'sumber_id'   => $pembelian->id,
            ]);
        });

        $this->resetForm();

        return $this->dispatch(
            'toast',
            type: 'success',
            message: 'Pembelian berhasil disimpan!'
        );
    }

    private function resetForm()
    {
        $this->supplier_id = '';
        $this->keterangan = '';
        $this->kenaPajak = false; // reset
        $this->items = [];
        $this->addItem();
    }

    public function render()
    {
        return view('livewire.pembelian.form', [
            'suppliers' => Supplier::all(),
        ]);
    }
}
