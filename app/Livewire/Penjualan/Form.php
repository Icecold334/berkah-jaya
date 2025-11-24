<?php

namespace App\Livewire\Penjualan;

use App\Models\Produk;
use App\Models\Piutang;
use App\Models\Setting;
use Livewire\Component;
use App\Models\Customer;
use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\KategoriKas;
use Illuminate\Support\Str;
use App\Models\TransaksiKas;
use App\Models\ItemPenjualan;
use App\Models\PergerakanStok;
use App\Models\PembayaranKredit;
use Illuminate\Support\Facades\DB;

class Form extends Component
{
    public $search = '';
    public $produkList = [];
    public $cart = [];
    public $tanggal;
    public $customer_id; // opsional, bisa null
    public $showConfirmModal = false;
    public $showFinalConfirmModal = false;
    public $metodeBayar = null;
    public $totalPreview;
    public $customerInput = ''; // input teks customer
    public $customerList = [];  // hasil pencarian suggestion

    public function updatedCustomerInput()
    {
        // Reset dulu ID setiap kali user ubah input manual
        $this->customer_id = null;

        $input = trim($this->customerInput);

        // Kalau input kosong / terlalu pendek → kosongkan list
        if (strlen($input) < 2) {
            $this->customerList = [];
            return;
        }

        // 🔹 Ambil daftar suggestion customer (autocomplete)
        $this->customerList = Customer::select('id', 'nama')
            ->where('nama', 'like', '%' . $input . '%')
            ->orderBy('nama')
            ->limit(10)
            ->get()
            ->toArray();

        // 🔹 Cek apakah input cocok persis dengan customer lama (by slug)
        //    — slug lebih aman untuk pencocokan nama unik & konsisten
        $slug = Str::slug($input);
        $match = Customer::where('slug', $slug)->first();

        if ($match) {
            $this->customer_id = $match->id; // ✅ auto set kalau slug cocok
        }
    }

    public function pilihCustomer($id)
    {
        $customer = Customer::find($id);
        if ($customer) {
            $this->customer_id = $customer->id;
            $this->customerInput = $customer->nama;
            $this->customerList = [];
        }
    }

    protected function resolveCustomer()
    {
        // Kalau kosong, boleh null
        if (empty($this->customerInput)) {
            return null;
        }

        // Buat slug unik
        $slug = Str::slug($this->customerInput);

        $customer = Customer::firstOrCreate(
            ['slug' => $slug],
            ['nama' => trim($this->customerInput)]
        );

        return $customer->id;
    }
    public function konfirmasiSimpan()
    {
        if (empty($this->cart)) {
            return $this->dispatch('toast', type: 'error', message: 'Keranjang masih kosong!');
        }

        $this->totalPreview = $this->getTotalProperty();
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
        try {
            $this->simpan();
            $this->dispatch('toast', type: 'success', message: 'Penjualan berhasil disimpan!');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', type: 'error', message: 'Terjadi kesalahan saat menyimpan penjualan!');
        }

        $this->showFinalConfirmModal = false;
    }

    public function focusSearch()
    {
        $this->updatedSearch();
    }
    public function updatedSearch()
    {
        // if (strlen($this->search) < 2) {
        //     $this->produkList = [];
        //     return;
        // }

        $this->produkList = Produk::select('produks.id', 'produks.nama', 'produks.kode_barang')
            ->join('pergerakan_stoks as ps', 'ps.produk_id', '=', 'produks.id')
            ->join('produk_suppliers as psu', 'psu.produk_id', '=', 'produks.id') // ✅ pastikan ada harga
            ->selectRaw('SUM(CASE WHEN ps.tipe="masuk" THEN ps.qty ELSE -ps.qty END) as stok')
            ->groupBy('produks.id', 'produks.nama', 'produks.kode_barang')
            ->having('stok', '>', 1)
            ->where(function ($q) {
                $q->where('produks.nama', 'like', '%' . $this->search . '%')
                    ->orWhere('produks.kode_barang', 'like', '%' . $this->search . '%');
            })
            ->limit(10)
            ->get()
            ->unique('id') // kalau ada supplier lebih dari 1, hilangkan duplikat
            ->toArray();
    }


    public function pilihProduk($id)
    {
        $produk = Produk::find($id);

        if (!$produk || $produk->stok < 1) return;

        // cek apakah sudah ada di cart
        $index = collect($this->cart)->search(fn($item) => $item['id'] == $id);

        if ($index !== false) {
            if ($this->cart[$index]['qty'] < $produk->stok) {
                $this->cart[$index]['qty'] += 1;
            }
        } else {
            $this->cart[] = [
                'id' => $produk->id,
                'kode_barang' => $produk->kode_barang,
                'nama' => $produk->nama,
                'qty' => 1,
                'stok' => $produk->stok,
                'harga' => $produk->harga_jual_default,
            ];
        }

        $this->search = '';
        $this->produkList = [];
    }

    protected function normalisasiHarga($nilai)
    {
        return (int) preg_replace('/[^0-9]/', '', $nilai);
    }
    public function removeItem($index)
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart); // reindex biar loop tetap rapi
    }

    public function updatedCart($value, $key)
    {
        // cek kalau field yang diupdate adalah harga
        if (str_contains($key, '.harga')) {
            $indexes = explode('.', $key); // contoh: "3.harga"
            $i = $indexes[0];
            $this->cart[$i]['harga'] = $this->normalisasiHarga($this->cart[$i]['harga']);
        }
    }


    public function getTotalProperty()
    {
        if (empty($this->cart)) {
            return 0;
        }
        return collect($this->cart)->sum(fn($item) => $item['qty'] * $item['harga']);
    }

    public function simpan()
    {
        if (empty($this->cart)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'cart' => 'Tidak ada item dalam penjualan',
            ]);
        }

        DB::transaction(function () {
            $this->customer_id = $this->resolveCustomer();
            // 🔹 Kelompokkan hasil cart berdasarkan status pajak
            $grouped = [
                'pajak' => [],
                'non_pajak' => [],
            ];

            foreach ($this->cart as $item) {
                $produk = Produk::find($item['id']);
                if (!$produk) continue;
                $qty = $item['qty'];
                $hargaJual = $item['harga'];

                // 🔸 Cari stok FIFO dari pergerakan_stoks (tipe=masuk)
                $stokMasuk = PergerakanStok::whereHasMorph('sumber', [Penjualan::class, Pembelian::class], function ($query) {
                    return $query->where('status', '!=', 'direvisi');
                })->where('produk_id', $produk->id)
                    ->orderBy('tanggal')
                    ->get();


                foreach ($stokMasuk as $stok) {
                    if ($qty <= 0) break;
                    // $stokTersisa = $stok->qty - PergerakanStok::whereHasMorph('sumber', [Penjualan::class, Pembelian::class], function ($query) {
                    //     return $query->where('status', '!=', 'direvisi');
                    // })->where('produk_id', $stok->produk_id)
                    //     ->where('produk_supplier_id', $stok->produk_supplier_id)
                    //     ->where('tipe', 'keluar')
                    //     ->sum('qty');
                    $stokTersisa = $this->hitungStok($produk->id, $stok->sumber->kena_pajak);
                    if ($stokTersisa <= 0) continue;
                    $ambil = min($qty, $stokTersisa);
                    $qty -= $ambil;

                    $kenaPajak = $stok->sumber->kena_pajak ? 'pajak' : 'non_pajak';
                    $grouped[$kenaPajak][] = [
                        'produk_id' => $produk->id,
                        'produk_supplier_id' => $stok->produk_supplier_id,
                        'qty' => $ambil,
                        'harga' => $hargaJual,
                        'subtotal' => $ambil * $hargaJual,
                        'tanggal' => $this->tanggal ?? now(),
                    ];
                }
            }


            // 🔹 Proses tiap kelompok (pajak / non pajak)
            foreach ($grouped as $tipe => $items) {
                if (empty($items)) continue;

                // 1️⃣ Buat Penjualan
                $penjualan = Penjualan::create([
                    'customer_id' => $this->customer_id,
                    'tanggal' => $this->tanggal ?? now(),
                    'total' => collect($items)->sum('subtotal'),
                    'kena_pajak' => $tipe === 'pajak',
                ]);

                // 2️⃣ Simpan item & pergerakan stok keluar
                foreach ($items as $item) {
                    ItemPenjualan::create([
                        'penjualan_id' => $penjualan->id,
                        'produk_id' => $item['produk_id'],
                        'produk_supplier_id' => $item['produk_supplier_id'],
                        'harga_jual' => $item['harga'],
                        'qty' => $item['qty'],
                        'kena_pajak' => $tipe === 'pajak',
                    ]);

                    PergerakanStok::create([
                        'produk_id' => $item['produk_id'],
                        'produk_supplier_id' => $item['produk_supplier_id'],
                        'tanggal' => $item['tanggal'],
                        'tipe' => 'keluar',
                        'qty' => $item['qty'],
                        'sumber_type' => Penjualan::class,
                        'sumber_id' => $penjualan->id,
                        'kena_pajak' => $tipe === 'pajak',
                        'keterangan' => 'Penjualan #' . $penjualan->no_struk,
                    ]);
                }

                // 3️⃣ Tergantung metode pembayaran
                if ($this->metodeBayar === 'cash') {
                    $kategori = KategoriKas::where('nama', 'Penjualan')->first();
                    $akunKasId = Setting::getValue('akun_penjualan', 1);

                    TransaksiKas::create([
                        'akun_kas_id' => $akunKasId,
                        'tanggal' => $this->tanggal ?? now(),
                        'tipe' => 'masuk',
                        'kategori_id' => $kategori?->id,
                        'jumlah' => $penjualan->total,
                        'keterangan' => 'Penjualan #' . $penjualan->no_struk,
                        'sumber_type' => Penjualan::class,
                        'sumber_id' => $penjualan->id,
                    ]);
                }
            }
        });

        // 🔹 Reset form
        $this->cart = [];
        $this->search = '';
        $this->produkList = [];
        $this->customer_id = null;
        $this->tanggal = null;
    }

    protected function generateNoStruk()
    {
        $tanggal = now()->format('Ymd');
        $countToday = Penjualan::whereDate('tanggal', now())->count() + 1;
        return 'INV' . $tanggal . str_pad($countToday, 3, '0', STR_PAD_LEFT);
    }

    private function hitungStok($produkId, $pajak = true)
    {
        $produk = Produk::with([
            'itemPembelians.pembelian',
            'itemPenjualans.penjualan',
        ])->find($produkId);

        $stok = 0;

        // stok masuk dari pembelian
        foreach (
            $produk->itemPembelians()->whereHas('pembelian', function ($pembelian) {
                return $pembelian->where('status', '!=', 'direvisi');
            })->get() as $item
        ) {
            if ($item->pembelian && $item->pembelian->kena_pajak == $pajak) {
                $stok += $item->qty; // pembelian = stok masuk
            }
        }
        // stok keluar dari penjualan
        foreach (
            $produk->itemPenjualans()->whereHas('penjualan', function ($penjualan) {
                return $penjualan->where('status', '!=', 'direvisi');
            })->get() as $item
        ) {
            if ($item->penjualan && $item->penjualan->kena_pajak == $pajak) {
                $stok -= $item->qty; // penjualan = stok keluar
            }
        }

        return $stok;
    }

    public function render()
    {
        return view('livewire.penjualan.form');
    }
}
