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
use App\Models\PergerakanStok;
use App\Models\ProdukSupplier;
use Illuminate\Support\Facades\DB;

class Form extends Component
{
    public $supplier_id;
    public $tanggal;
    public $keterangan;
    public $tanpaPajak = true;

    public $items = []; // {nama, qty, harga_beli, kena_pajak}

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
            'kena_pajak' => $this->tanpaPajak,
        ];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function togglePajak()
    {
        $this->tanpaPajak = !$this->tanpaPajak;

        // update semua row
        foreach ($this->items as $i => $item) {
            $this->items[$i]['kena_pajak'] = $this->tanpaPajak;
        }
    }
    public function simpan()
    {
        // normalisasi semua harga_beli ke angka murni
        foreach ($this->items as $i => $item) {
            if (isset($item['harga_beli'])) {
                $this->items[$i]['harga_beli'] = preg_replace('/[^0-9]/', '', $item['harga_beli']);
            }
        }

        $this->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            // 'tanggal' => 'required|date',
            // 'items.*.nama' => 'required|string|min:2',
            // 'items.*.qty' => 'required|numeric|min:1',
            // 'items.*.harga_beli' => 'required|numeric|min:0',
        ]);
        DB::transaction(function () {
            $pembelian = Pembelian::create([
                'supplier_id' => $this->supplier_id,
                'tanggal' => $this->tanggal,
                'total' => collect($this->items)->sum(fn($i) => $i['harga_beli'] * $i['qty']),
                'keterangan' => $this->keterangan,
            ]);

            foreach ($this->items as $item) {
                $produk = Produk::firstOrCreate(
                    ['slug' => Str::slug($item['nama'])],
                    [
                        'nama' => $item['nama'],
                        'kode_barang' => fake()->unique()->numerify('BJ#####') // tambahkan unique
                    ]
                );

                ItemPembelian::create([
                    'pembelian_id' => $pembelian->id,
                    'produk_id' => $produk->id,
                    'harga_beli' => $item['harga_beli'],
                    'qty' => $item['qty'],
                    'kena_pajak' => $item['kena_pajak'] ?? false,
                ]);

                // ✅ Update relasi produk–supplier
                ProdukSupplier::updateOrCreate(
                    [
                        'produk_id' => $produk->id,
                        'supplier_id' => $this->supplier_id,
                    ],
                    [
                        'harga_beli' => $item['harga_beli'],
                        'kena_pajak' => $item['kena_pajak'] ?? false,
                        'tanggal_pembelian_terakhir' => $this->tanggal,
                    ]
                );

                PergerakanStok::create([
                    'produk_id' => $produk->id,
                    'tanggal' => $this->tanggal,
                    'produk_supplier_id' => $this->supplier_id,
                    'tipe' => 'masuk',
                    'qty' => $item['qty'],
                    'sumber_type' => Pembelian::class,
                    'sumber_id' => $pembelian->id,
                    'keterangan' => 'Pembelian',
                ]);
            }


            TransaksiKas::create([
                'akun_kas_id' => 1, // TODO: pilih akun kas di form
                'tanggal' => $this->tanggal,
                'tipe' => 'keluar',
                'kategori_id' => 2,
                'jumlah' => $pembelian->total,
                'keterangan' => 'Pembelian #' . $pembelian->id,
                'sumber_type' => Pembelian::class,
                'sumber_id' => $pembelian->id,
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
