<?php

namespace App\Livewire\Pembelian;

use Livewire\Component;
use App\Models\Supplier;
use App\Models\Pembelian;
use Livewire\WithPagination;
use App\Services\RevisiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;

class Laporan extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind'; // ✅ cocok sama Flowbite/Tailwind

    public $tanggal_awal;
    public $tanggal_akhir;
    public $supplier_id;
    public $filter_pajak = ''; // ✅ filter baru
    public $search_no_faktur = '';
    public $selectedPembelians = [];
    public $selectAll = false;
    // Untuk detail pembelian
    public $detailPembelianId = null;
    public $alamat_id = null;
    public $showRevisiModal = false;
    public $editId = null;

    public $form = [
        'supplier_id' => '',
        'tanggal' => '',
        'total' => '',
        'kena_pajak' => false,
        'items' => [],
        'akun_kas_id' => 1,
        'kategori_id' => null,
    ];

    // 🔄 Auto update total saat item berubah
    public function updatedForm()
    {
        $this->form['total'] = collect($this->form['items'])
            ->sum(fn($i) => ($i['qty'] ?? 0) * ($i['harga_beli'] ?? 0));
    }

    public function openRevisi($id)
    {
        $pb = Pembelian::with(['items'])->findOrFail($id);

        $this->editId = $pb->id;
        $this->showRevisiModal = true;

        // isi form dengan data lama
        $this->form['supplier_id'] = $pb->supplier_id;
        $this->form['tanggal'] = $pb->tanggal->format('Y-m-d');
        $this->form['total'] = $pb->total;
        $this->form['kena_pajak'] = $pb->kena_pajak;
        $this->form['items'] = $pb->items->map(fn($i) => [
            'produk_id' => $i->produk_id,
            'harga_beli' => $i->harga_beli,
            'qty' => $i->qty,
            'kena_pajak' => $i->kena_pajak,
        ])->toArray();
    }

    public function simpanRevisi()
    {
        $pbLama = Pembelian::with('items')->findOrFail($this->editId);
        RevisiService::revisiTransaksi('pembelian', $pbLama, $this->form);

        $this->showRevisiModal = false;
        $this->dispatch('notify', message: 'Revisi pembelian berhasil disimpan.');
        $this->reset('form', 'editId');
    }
    public function updatedSelectAll($value)
    {
        if ($value) {
            // Centang semua id di halaman ini
            $this->selectedPembelians = Pembelian::query()
                ->when($this->tanggal_awal, fn($q) => $q->whereDate('tanggal', '>=', $this->tanggal_awal))
                ->when($this->tanggal_akhir, fn($q) => $q->whereDate('tanggal', '<=', $this->tanggal_akhir))
                ->when($this->supplier_id, fn($q) => $q->where('supplier_id', $this->supplier_id))
                ->when($this->filter_pajak !== null && $this->filter_pajak !== '', fn($q) => $q->where('kena_pajak', $this->filter_pajak))
                ->when($this->search_no_faktur, fn($q) => $q->where('no_faktur', 'like', "%{$this->search_no_faktur}%"))
                ->pluck('id')
                ->toArray();
        } else {
            $this->selectedPembelians = [];
        }
    }

    // di dalam class Laporan (Livewire)
    public function updated($property)
    {
        if (in_array($property, [
            'tanggal_awal',
            'tanggal_akhir',
            'supplier_id',
            'filter_pajak',
            'search_no_faktur',
        ])) {
            // Reset semua pilihan kalau filter berubah
            $this->reset(['selectedPembelians', 'selectAll']);
        }
    }


    public function updatedSelectedPembelians()
    {
        $pembelianIds = Pembelian::query()
            ->when($this->tanggal_awal, fn($q) => $q->whereDate('tanggal', '>=', $this->tanggal_awal))
            ->when($this->tanggal_akhir, fn($q) => $q->whereDate('tanggal', '<=', $this->tanggal_akhir))
            ->when($this->supplier_id, fn($q) => $q->where('supplier_id', $this->supplier_id))
            ->when($this->filter_pajak !== null && $this->filter_pajak !== '', fn($q) => $q->where('kena_pajak', $this->filter_pajak))
            ->when($this->search_no_faktur, fn($q) => $q->where('no_faktur', 'like', "%{$this->search_no_faktur}%"))
            ->pluck('id')
            ->toArray();

        $this->selectAll = !array_diff($pembelianIds, $this->selectedPembelians);
    }

    public function toggleSelect($id)
    {
        if (in_array($id, $this->selectedPembelians)) {
            $this->selectedPembelians = array_diff($this->selectedPembelians, [$id]);
        } else {
            $this->selectedPembelians[] = $id;
        }
    }

    public function bulkDownload()
    {
        $alamatList = config('alamat');
        $alamat = $alamatList[$this->alamat_id] ?? reset($alamatList);

        foreach ($this->selectedPembelians as $id) {
            $pembelian = Pembelian::with(['items.produk', 'supplier'])->findOrFail($id);

            $pdf = Pdf::loadView('pdf.pembelian', [
                'pembelian' => $pembelian,
                'alamat'    => $alamat,
            ]);

            // trigger browser download untuk tiap file
            $this->dispatch(
                'open-pdf',
                content: base64_encode($pdf->output()),
                filename: "pembelian-{$pembelian->no_faktur}.pdf",
            );
        }
        return $this->reset('alamat_id', 'selectedPembelians');
    }

    public function setAlamat($id)
    {
        $this->alamat_id = $id;
    }
    public function showDetail($id)
    {
        $this->detailPembelianId = $id;
    }

    public function closeDetail()
    {
        $this->detailPembelianId = null;
    }

    public function downloadPdf($id)
    {
        $pembelian = Pembelian::with(['items.produk', 'supplier'])->findOrFail($id);

        $alamatList = config('alamat');
        $alamat = $alamatList[$this->alamat_id] ?? reset($alamatList);

        $pdf = Pdf::loadView('pdf.pembelian', [
            'pembelian' => $pembelian,
            'alamat'    => $alamat,
        ]);

        $this->reset('alamat_id');


        return response()->streamDownload(
            fn() => print($pdf->output()),
            "pembelian-{$pembelian->no_faktur}.pdf"
        );
    }

    public function render()
    {
        // 🔎 Query dasar untuk pembelian aktif / hasil revisi saja
        $baseQuery = Pembelian::with('supplier')
            ->whereIn('status', ['aktif', 'revisi']) // tampilkan hanya transaksi aktif & hasil revisi
            ->when($this->tanggal_awal, fn($q) => $q->whereDate('tanggal', '>=', $this->tanggal_awal))
            ->when($this->tanggal_akhir, fn($q) => $q->whereDate('tanggal', '<=', $this->tanggal_akhir))
            ->when($this->supplier_id, fn($q) => $q->where('supplier_id', $this->supplier_id))
            ->when($this->filter_pajak !== '', fn($q) => $q->where('kena_pajak', $this->filter_pajak))
            ->when($this->search_no_faktur, fn($q) => $q->where('no_faktur', 'like', "%{$this->search_no_faktur}%"))
            ->orderByDesc('tanggal')
            ->orderByDesc('id');

        // 🔹 Pagination 10 per halaman
        $pembelians = $baseQuery->paginate(10);

        // 🔹 Hitung total dari query yang sama (tanpa pagination)
        $total = (clone $baseQuery)->sum('total');

        // 🔹 Ambil data master supplier
        $suppliers = Supplier::orderBy('nama')->get();

        // 🔹 Detail pembelian (jika sedang dibuka)
        $detail = null;
        if ($this->detailPembelianId) {
            $detail = Pembelian::with(['items.produk', 'supplier'])
                ->find($this->detailPembelianId);
        }

        return view('livewire.pembelian.laporan', [
            'pembelians' => $pembelians,
            'suppliers'  => $suppliers,
            'total'      => $total,
            'detail'     => $detail,
        ]);
    }
}
