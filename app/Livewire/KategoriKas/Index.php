<?php

namespace App\Livewire\KategoriKas;

use Livewire\Component;
use App\Models\KategoriKas;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;

    // Form fields
    public $kategoriId, $nama, $tipe;

    protected $paginationTheme = 'tailwind';
    protected $listeners = ['delete'];

    protected $rules = [
        'nama' => 'required|string|max:255',
        'tipe' => 'required|in:masuk,keluar',
    ];

    // Reset pagination ketika search berubah
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $kategoriKas = KategoriKas::query()
            ->where('nama', 'like', "%{$this->search}%")
            ->orWhere('tipe', 'like', "%{$this->search}%")
            ->orderBy('updated_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.kategori-kas.index', [
            'kategoriKas' => $kategoriKas
        ]);
    }

    // Open modal untuk tambah/edit
    public function openModal($id = null)
    {
        if ($id) {
            $kategori = KategoriKas::findOrFail($id);
            $this->kategoriId = $kategori->id;
            $this->nama = $kategori->nama;
            $this->tipe = $kategori->tipe;
        } else {
            $this->resetForm();
        }

        $this->dispatch('open-modal');
    }

    public function save()
    {
        $this->validate();

        KategoriKas::updateOrCreate(
            ['id' => $this->kategoriId],
            [
                'nama' => $this->nama,
                'tipe' => $this->tipe,
            ]
        );

        $this->dispatch('close-modal');
        $this->dispatch(
            'toast',
            type: 'success',
            message: $this->kategoriId ? 'Kategori Kas berhasil diupdate!' : 'Kategori Kas berhasil ditambahkan!'
        );

        $this->resetForm();
    }

    public function confirmDelete($id)
    {
        $this->dispatch(
            'confirm',
            id: $id,
            title: 'Hapus kategori?',
            text: 'Data yang sudah dihapus tidak bisa dikembalikan.',
            icon: 'warning',
        );
    }

    public function delete($id)
    {
        KategoriKas::findOrFail($id)->delete();

        $this->dispatch(
            'toast',
            type: 'success',
            message: 'Kategori Kas berhasil dihapus!'
        );
    }

    private function resetForm()
    {
        $this->kategoriId = null;
        $this->nama = '';
        $this->tipe = '';
    }
}
