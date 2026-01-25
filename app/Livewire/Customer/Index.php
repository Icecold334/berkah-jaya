<?php

namespace App\Livewire\Customer;

use App\Models\Produk;
use Livewire\Component;
use App\Models\Customer;
use App\Models\Penjualan;
use Illuminate\Support\Str;
use Livewire\WithPagination;
use App\Models\ItemPenjualan;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;

    // Form fields
    public $customerId, $nama, $telepon, $alamat;

    // Detail Modal
    public $detailCustomer, $daftarPembelian = [];

    public $detailPenjualan, $detailItems = [];

    protected $paginationTheme = 'tailwind';
    protected $listeners = ['delete'];

    protected $rules = [
        'nama' => 'required|string|max:255',
        'telepon' => 'nullable|string|max:50',
        'alamat' => 'nullable|string|max:500',
    ];

    public $daftarBarang = [];
    public $selectedProdukId;
    public $riwayatProduk = [];
    public $detailInvoice;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $customers = Customer::query()
            ->where('nama', 'like', "%{$this->search}%")
            ->orWhere('telepon', 'like', "%{$this->search}%")
            ->orWhere('alamat', 'like', "%{$this->search}%")
            ->orderBy('updated_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.customer.index', [
            'customers' => $customers,
        ]);
    }

    public function openModal($id = null)
    {
        if ($id) {
            $customer = Customer::findOrFail($id);
            $this->customerId = $customer->id;
            $this->nama = $customer->nama;
            $this->telepon = $customer->telepon;
            $this->alamat = $customer->alamat;
        } else {
            $this->resetForm();
        }

        $this->dispatch('open-modal');
    }

    public function save()
    {
        $this->validate();

        if ($this->customerId) {
            // UPDATE
            $customer = Customer::findOrFail($this->customerId);

            // Jika nama berubah, generate slug baru
            if ($customer->nama !== $this->nama) {
                $slug = $this->generateUniqueSlug($this->nama);
            } else {
                $slug = $customer->slug; // tetap pakai slug lama
            }

            $customer->update([
                'nama' => $this->nama,
                'telepon' => $this->telepon,
                'alamat' => $this->alamat,
                'slug' => $slug,
            ]);

            $message = 'Customer berhasil diupdate!';
        } else {
            // CREATE
            Customer::create([
                'nama' => $this->nama,
                'telepon' => $this->telepon,
                'alamat' => $this->alamat,
                'slug' => $this->generateUniqueSlug($this->nama),
            ]);

            $message = 'Customer berhasil ditambahkan!';
        }

        $this->dispatch('close-modal');
        $this->dispatch('toast', type: 'success', message: $message);
        $this->resetForm();
    }

    private function generateUniqueSlug($nama)
    {
        $slug = Str::slug($nama);
        $originalSlug = $slug;
        $counter = 1;

        // cek apakah sudah ada slug sama
        while (Customer::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    public function confirmDelete($id)
    {
        $this->dispatch(
            'confirm',
            id: $id,
            title: 'Hapus customer?',
            text: 'Data yang sudah dihapus tidak bisa dikembalikan.',
            icon: 'warning',
        );
    }

    public function delete($id)
    {
        Customer::findOrFail($id)->delete();

        $this->dispatch(
            'toast',
            type: 'success',
            message: 'Customer berhasil dihapus!'
        );
    }

    private function hitungHargaJualProduk($produkId)
    {
        $produk = Produk::with('suppliers')->find($produkId);
        if (!$produk) return 0;

        $supplier = $produk->suppliers()
            ->orderByDesc('harga_beli')
            ->orderByDesc('kena_pajak')
            ->first();

        if (!$supplier) return 0;

        $harga = $supplier->pivot->harga_beli;
        return $supplier->pivot->kena_pajak ? $harga * 1.02 : $harga;
    }


    /** 🔍 Lihat daftar pembelian customer */
    public function showDetail($id)
    {
        $this->detailCustomer = Customer::findOrFail($id);

        $items = ItemPenjualan::with(['produk', 'penjualan'])
            ->whereHas('penjualan', function ($q) use ($id) {
                $q->where('customer_id', $id)
                    ->where('status', 'aktif');
            })
            ->get();

        $grouped = $items->groupBy('produk_id');

        $this->daftarBarang = $grouped->map(function ($rows, $produkId) {
            $first = $rows->first();
            $produk = $first?->produk;

            // ambil item terbaru berdasarkan tanggal penjualan (fallback created_at)
            $latestItem = $rows->sortByDesc(function ($r) {
                return $r->penjualan?->tanggal ?? $r->created_at;
            })->first();

            return [
                'produk_id'     => $produkId,
                'nama_produk'   => $produk?->nama ?? '-',
                'harga_terbaru' => (int) $this->hitungHargaJualProduk($produkId), // ← FIX
                'total_qty'     => (int) $rows->sum('qty'),
            ];
        })->values()->toArray();

        $this->dispatch('open-detail-modal');
    }

    public function showDaftarPembelian($customerId)
    {
        $this->detailCustomer = Customer::findOrFail($customerId);

        $this->daftarPembelian = Penjualan::where('customer_id', $customerId)
            ->where('status', 'aktif')
            ->orderByDesc('tanggal')
            ->get()
            ->map(fn($p) => [
                'id'         => $p->id,
                'no_struk'   => $p->no_struk,
                'tanggal'    => optional($p->tanggal)->format('d/m/Y'),
                'total'      => (int) $p->total,
                'is_lunas'   => $p->is_lunas,
                'sisa_bayar' => (int) $p->sisa_bayar,
            ])
            ->toArray();

        $this->dispatch('open-daftar-pembelian-modal');
    }

    public function showItemDetail($penjualanId)
    {
        $this->detailInvoice = Penjualan::findOrFail($penjualanId);

        $this->detailItems = ItemPenjualan::with('produk')
            ->where('penjualan_id', $penjualanId)
            ->get()
            ->map(fn($i) => [
                'nama_produk' => $i->produk?->nama ?? '-',
                'qty' => (int) $i->qty,
                'harga' => (int) $i->harga_jual,
                'subtotal' => (int) ($i->qty * $i->harga_jual),
            ])
            ->toArray();

        $this->dispatch('open-item-modal');
    }

    private function resetForm()
    {
        $this->customerId = null;
        $this->nama = '';
        $this->telepon = '';
        $this->alamat = '';
    }
}
