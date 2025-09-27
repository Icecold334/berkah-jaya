<?php

namespace App\Livewire\Kas;

use Carbon\Carbon;
use App\Models\AkunKas;
use Livewire\Component;
use App\Models\KategoriKas;
use App\Models\TransaksiKas;
use Livewire\WithPagination;

class ListData extends Component
{
    use WithPagination;

    public $tanggal_awal, $tanggal_akhir, $akun_kas_id, $kategori_id, $search_keterangan;

    // 🪟 Modal
    public $showModal = false;
    public $form = [
        'tanggal' => '',
        'akun_kas_id' => '',
        'tipe' => '',
        'kategori_id' => '',
        'jumlah' => '',
        'keterangan' => '',
    ];

    protected $rules = [
        'form.tanggal' => 'required|date',
        'form.akun_kas_id' => 'required|exists:akun_kas,id',
        'form.tipe' => 'required|in:masuk,keluar',
        'form.kategori_id' => 'nullable|exists:kategori_kas,id',
        'form.jumlah' => 'required|numeric|min:1',
        'form.keterangan' => 'nullable|string|max:255',
    ];


    public function openModal()
    {
        $this->reset('form');
        $this->form['tanggal'] = Carbon::today()->toDateString(); // format YYYY-MM-DD
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }


    public function save()
    {
        $this->validate();
        TransaksiKas::create([
            'tanggal' => $this->form['tanggal'],
            'akun_kas_id' => $this->form['akun_kas_id'],
            'tipe' => $this->form['tipe'],
            'kategori_id' => $this->form['kategori_id'],
            'jumlah' => $this->form['jumlah'],
            'keterangan' => $this->form['keterangan'],
            'sumber_type' => null,
            'sumber_id' => null,
        ]);

        $this->closeModal();
        session()->flash('success', 'Transaksi kas berhasil ditambahkan ✅');
    }

    // ⏩ Pagination
    protected $paginationTheme = 'tailwind';


    // Reset pagination saat filter berubah
    public function updating($field)
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = TransaksiKas::with(['akunKas', 'kategori'])
            ->when($this->tanggal_awal, fn($q) => $q->whereDate('tanggal', '>=', $this->tanggal_awal))
            ->when($this->tanggal_akhir, fn($q) => $q->whereDate('tanggal', '<=', $this->tanggal_akhir))
            ->when($this->akun_kas_id, fn($q) => $q->where('akun_kas_id', $this->akun_kas_id))
            ->when($this->kategori_id, fn($q) => $q->where('kategori_id', $this->kategori_id))
            ->when(
                $this->search_keterangan,
                fn($q) =>
                $q->where('keterangan', 'like', '%' . $this->search_keterangan . '%')
            )
            ->orderByDesc('id');

        $transaksiKas = $query->paginate(15);

        // Hitung saldo akhir dari seluruh query (bukan cuma halaman ini)
        $saldoAkhir = (clone $query)->get()->reduce(function ($carry, $item) {
            return $carry + ($item->tipe === 'masuk' ? $item->jumlah : -$item->jumlah);
        }, 0);

        return view('livewire.kas.list_data', [
            'transaksiKas' => $transaksiKas,
            'saldoAkhir'   => $saldoAkhir,
            'akunKas'      => AkunKas::all(),
            'kategoriKas'  => KategoriKas::all(),
        ]);
    }
}
