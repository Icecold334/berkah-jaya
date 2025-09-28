<?php

namespace App\Livewire\Supplier;

use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;

    // Form fields
    public $supplierId, $nama, $alamat, $telepon;

    protected $paginationTheme = 'tailwind';
    protected $listeners = ['delete'];

    protected $rules = [
        'nama' => 'required|string|max:255',
        'alamat' => 'nullable|string|max:500',
        'telepon' => 'nullable|string|max:20',
    ];

    // Reset pagination ketika search berubah
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $suppliers = Supplier::query()
            ->where('nama', 'like', "%{$this->search}%")
            ->orWhere('alamat', 'like', "%{$this->search}%")
            ->orWhere('telepon', 'like', "%{$this->search}%")
            ->orderBy('updated_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.supplier.index', [
            'suppliers' => $suppliers
        ]);
    }

    // Open modal untuk tambah/edit
    public function openModal($id = null)
    {
        if ($id) {
            $supplier = Supplier::findOrFail($id);
            $this->supplierId = $supplier->id;
            $this->nama = $supplier->nama;
            $this->alamat = $supplier->alamat;
            $this->telepon = $supplier->telepon;
        } else {
            $this->resetForm();
        }

        $this->dispatch('open-modal');
    }

    public function save()
    {
        $this->validate();

        Supplier::updateOrCreate(
            ['id' => $this->supplierId],
            [
                'nama' => $this->nama,
                'alamat' => $this->alamat,
                'telepon' => $this->telepon,
            ]
        );

        $this->dispatch('close-modal');
        $this->dispatch(
            'toast',
            type: 'success',
            message: $this->supplierId ? 'Supplier berhasil diupdate!' : 'Supplier berhasil ditambahkan!'
        );

        $this->resetForm();
    }

    public function confirmDelete($id)
    {
        $this->dispatch(
            'confirm',
            id: $id,
            title: 'Hapus supplier?',
            text: 'Data yang sudah dihapus tidak bisa dikembalikan.',
            icon: 'warning',
        );
    }

    public function delete($id)
    {
        Supplier::findOrFail($id)->delete();

        $this->dispatch(
            'toast',
            type: 'success',
            message: 'Supplier berhasil dihapus!'
        );
    }


    private function resetForm()
    {
        $this->supplierId = null;
        $this->nama = '';
        $this->alamat = '';
        $this->telepon = '';
    }
}
