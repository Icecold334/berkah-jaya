<?php

namespace App\Livewire\AkunKas;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AkunKas;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;

    // Form field
    public $akunKasId, $nama;

    protected $paginationTheme = 'tailwind';
    protected $listeners = ['delete'];

    protected $rules = [
        'nama' => 'required|string|max:255',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $akunKas = AkunKas::query()
            ->where('nama', 'like', "%{$this->search}%")
            ->orderBy('updated_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.akun-kas.index', [
            'akunKas' => $akunKas,
        ]);
    }

    // Open modal untuk tambah/edit
    public function openModal($id = null)
    {
        if ($id) {
            $akun = AkunKas::findOrFail($id);
            $this->akunKasId = $akun->id;
            $this->nama = $akun->nama;
        } else {
            $this->resetForm();
        }

        $this->dispatch('open-modal');
    }

    public function save()
    {
        $this->validate();

        AkunKas::updateOrCreate(
            ['id' => $this->akunKasId],
            ['nama' => $this->nama]
        );

        $this->dispatch('close-modal');
        $this->dispatch(
            'toast',
            type: 'success',
            message: $this->akunKasId ? 'Akun Kas berhasil diupdate!' : 'Akun Kas berhasil ditambahkan!'
        );

        $this->resetForm();
    }

    public function confirmDelete($id)
    {
        $this->dispatch(
            'confirm',
            id: $id,
            title: 'Hapus akun kas?',
            text: 'Data yang sudah dihapus tidak bisa dikembalikan.',
            icon: 'warning',
        );
    }

    public function delete($id)
    {
        AkunKas::findOrFail($id)->delete();

        $this->dispatch(
            'toast',
            type: 'success',
            message: 'Akun Kas berhasil dihapus!'
        );
    }

    private function resetForm()
    {
        $this->akunKasId = null;
        $this->nama = '';
    }
}
