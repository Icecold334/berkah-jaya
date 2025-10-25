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

    public $showMetodeModal = false;
    public $showKreditModal = false;
    public $metodeBayar = 'cash'; // default
    public $jatuh_tempo;
    public $keterangan_kredit;

    public function konfirmasiSimpan()
    {
        $this->resetErrorBag();

        // Hitung total sebelum modal metode muncul
        $this->totalPreview = collect($this->items)->sum(function ($i) {
            $harga = preg_replace('/[^0-9]/', '', $i['harga_beli']);
            $qty   = (int) $i['qty'];
            return ((int) $harga) * $qty;
        });

        // ✅ Pastikan semua modal tertutup dulu
        $this->showConfirmModal = false;
        $this->showKreditModal = false;

        // ✅ Baru buka modal metode pembayaran
        $this->showMetodeModal = true;
    }

    public function pilihMetode($metode)
    {
        $this->metodeBayar = $metode;

        // Tutup modal metode pembayaran
        $this->showMetodeModal = false;
        $this->showConfirmModal = false;
        $this->showKreditModal = false;

        // ✅ Buka modal sesuai pilihan user
        if ($metode === 'cash') {
            $this->showConfirmModal = true;
        } else {
            $this->showKreditModal = true;
        }
    }

    public function simpanFinal()
    {
        // Jalankan simpan
        $this->simpan();

        // Tutup semua modal setelah simpan
        $this->showConfirmModal = false;
        $this->showMetodeModal = false;
        $this->showKreditModal = false;
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
                // Pastikan hanya angka
                $this->items[$i]['harga_beli'] = (int) preg_replace('/[^0-9]/', '', $item['harga_beli']);
            }
        }

        $this->validate([
            'supplier_id' => 'required|exists:suppliers,id',
        ]);

        DB::beginTransaction();

        try {
            $noFaktur = Pembelian::generateNoFaktur();

            // Hitung total pembelian
            $totalPembelian = collect($this->items)->sum(fn($i) => $i['harga_beli'] * $i['qty']);

            // Buat record pembelian utama
            $pembelian = Pembelian::create([
                'no_faktur'   => $noFaktur,
                'supplier_id' => $this->supplier_id,
                'tanggal'     => $this->tanggal,
                'kena_pajak'  => collect($this->items)->contains(fn($i) => $i['kena_pajak']),
                'total'       => $totalPembelian,
                'keterangan'  => $this->keterangan,
            ]);

            // Simpan detail item & stok
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
                        'kena_pajak'  => $item['kena_pajak'] ?? false,
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
                    'keterangan'         => 'Pembelian Barang',
                ]);
            }

            // ==============================
            // CASE 1: PEMBAYARAN TUNAI (CASH)
            // ==============================
            if ($this->metodeBayar === 'cash') {
                $kategoriPembelian = KategoriKas::where('nama', 'pembelian')->first();

                TransaksiKas::create([
                    'akun_kas_id' => 1, // pastikan akun kas ada di tabel
                    'tanggal'     => $this->tanggal,
                    'tipe'        => 'keluar',
                    'kategori_id' => $kategoriPembelian?->id,
                    'jumlah'      => $pembelian->total,
                    'keterangan'  => 'Pembelian #' . $pembelian->no_faktur,
                    'sumber_type' => Pembelian::class,
                    'sumber_id'   => $pembelian->id,
                ]);
            }

            // ==============================
            // CASE 2: PEMBAYARAN KREDIT
            // ==============================
            if ($this->metodeBayar === 'kredit') {
                Hutang::create([
                    'pembelian_id'  => $pembelian->id,
                    'supplier_id'   => $this->supplier_id,
                    'total_tagihan' => $pembelian->total,
                    'total_terbayar' => 0,
                    'sisa_tagihan'   => $pembelian->total,
                    'jatuh_tempo'    => $this->jatuh_tempo ?: now()->addDays(30),
                    'status'         => 'belum_lunas',
                    'keterangan'     => $this->keterangan_kredit,
                ]);
            }

            DB::commit();

            $this->resetForm();

            $this->dispatch('toast', type: 'success', message: 'Pembelian berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();

            // Debug error kalau ada masalah
            report($e);

            $this->dispatch('toast', type: 'error', message: 'Terjadi kesalahan: ' . $e->getMessage());
        }
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
