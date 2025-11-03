<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use App\Models\Customer;
use App\Models\Penjualan;
use Illuminate\Support\Str;
use Livewire\WithPagination;

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

    /** 🔍 Lihat daftar pembelian customer */
    public function showDetail($id)
    {
        $this->detailCustomer = Customer::with(['penjualans' => function ($q) {
            $q->orderBy('tanggal', 'desc');
        }])->findOrFail($id);

        $this->daftarPembelian = $this->detailCustomer->penjualans->map(function ($p) {
            return [
                'id' => $p->id,
                'no_struk' => $p->no_struk,
                'tanggal' => $p->tanggal?->format('d/m/Y'),
                'total' => $p->total,
                'status' => $p->status,
                'is_lunas' => $p->is_lunas,
                'sisa_bayar' => $p->sisa_bayar,
            ];
        });

        $this->dispatch('open-detail-modal');
    }

    public function showItemDetail($penjualanId)
    {
        $penjualan = Penjualan::with('items.produk')->findOrFail($penjualanId);

        $this->detailPenjualan = [
            'no_struk' => $penjualan->no_struk,
            'tanggal' => $penjualan->tanggal?->format('d/m/Y'),
            'total' => $penjualan->total,
        ];

        // Ambil daftar item
        $this->detailItems = $penjualan->items->map(function ($item) {
            return [
                'nama_produk' => $item->produk->nama ?? 'Tidak diketahui',
                'qty' => $item->qty,
                'harga' => $item->harga_jual,
                'subtotal' => $item->harga_jual * $item->qty,
            ];
        });

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
