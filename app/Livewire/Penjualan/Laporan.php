<?php

namespace App\Livewire\Penjualan;

use Livewire\Component;
use App\Models\Customer;
use App\Models\Penjualan;
use App\Models\KategoriKas;
use App\Models\TransaksiKas;
use Livewire\WithPagination;
use App\Services\RevisiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Laporan extends Component
{
    use WithPagination;

    public $showRevisiModal = false;
    public $editId = null;

    public $form = [
        'customer_id' => '',
        'tanggal' => '',
        'total' => '',
        'kena_pajak' => false,
        'items' => [],
        'akun_kas_id' => 1,
        'kategori_id' => null,
    ];

    protected $paginationTheme = 'tailwind';

    public $tanggal_awal;
    public $tanggal_akhir;
    public $customer_id;
    public $filter_pajak = '';
    public $search_no_struk = '';

    public $selectedPenjualans = [];
    public $selectAll = false;

    // Untuk detail penjualan
    public $detailPenjualanId = null;
    public $alamat_id = null;
    public $showBayarModal = false;
    public $bayarForm = [
        'penjualan_id' => null,
        'tanggal' => '',
        'jumlah' => '',
        'keterangan' => '',
    ];
    public $riwayatPembayaran = [];

    public function openBayarModal($id)
    {
        $penjualan = Penjualan::with('transaksiKas')->findOrFail($id);
        $sisa = $penjualan->sisa_bayar;

        $this->bayarForm = [
            'penjualan_id' => $penjualan->id,
            'tanggal' => now()->format('Y-m-d'),
            'jumlah' => null,
            'keterangan' => 'Pembayaran Penjualan #' . $penjualan->no_struk,
        ];

        $this->riwayatPembayaran = $penjualan->transaksiKas()
            ->orderBy('tanggal')
            ->get(['tanggal', 'jumlah', 'keterangan'])
            ->map(fn($t) => [
                'tanggal' => $t->tanggal->format('d/m/Y'),
                'jumlah' => $t->jumlah,
                'keterangan' => $t->keterangan,
            ])->toArray();

        $this->showBayarModal = true;
    }

    public function simpanPembayaran()
    {
        $this->validate([
            'bayarForm.penjualan_id' => 'required|exists:penjualans,id',
            'bayarForm.tanggal' => 'required|date',
            'bayarForm.jumlah' => 'required|numeric|min:1',
        ]);

        DB::beginTransaction();
        try {
            $penjualan = Penjualan::findOrFail($this->bayarForm['penjualan_id']);

            if ($this->bayarForm['jumlah'] > $penjualan->sisa_bayar) {
                throw ValidationException::withMessages([
                    'bayarForm.jumlah' => 'Nominal melebihi sisa piutang. Sisa bayar: Rp ' . number_format($penjualan->sisa_bayar, 0, ',', '.'),
                ]);
            }

            $kategori = KategoriKas::where('nama', 'Penjualan')->first();

            TransaksiKas::create([
                'akun_kas_id' => 1,
                'tanggal' => $this->bayarForm['tanggal'],
                'tipe' => 'masuk',
                'kategori_id' => $kategori?->id,
                'jumlah' => $this->bayarForm['jumlah'],
                'keterangan' => $this->bayarForm['keterangan'],
                'sumber_type' => Penjualan::class,
                'sumber_id' => $penjualan->id,
            ]);

            DB::commit();

            $this->dispatch('toast', type: 'success', message: 'Pembayaran berhasil disimpan!');
            $this->showBayarModal = false;
            $this->reset('bayarForm');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            $this->dispatch('toast', type: 'error', message: 'Gagal menyimpan pembayaran: ' . $e->getMessage());
        }
    }


    // ✅ Checkbox "select all"
    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedPenjualans = Penjualan::query()
                ->when($this->tanggal_awal, fn($q) => $q->whereDate('tanggal', '>=', $this->tanggal_awal))
                ->when($this->tanggal_akhir, fn($q) => $q->whereDate('tanggal', '<=', $this->tanggal_akhir))
                ->when($this->customer_id, fn($q) => $q->where('customer_id', $this->customer_id))
                ->when($this->filter_pajak !== '', fn($q) => $q->where('kena_pajak', $this->filter_pajak))
                ->when($this->search_no_struk, fn($q) => $q->where('no_struk', 'like', "%{$this->search_no_struk}%"))
                ->pluck('id')
                ->toArray();
        } else {
            $this->selectedPenjualans = [];
        }
    }

    // ✅ Buka modal revisi
    public function openRevisi($id)
    {
        $pj = Penjualan::with(['items'])->findOrFail($id);

        $this->editId = $pj->id;
        $this->showRevisiModal = true;

        $this->form['customer_id'] = $pj->customer_id;
        $this->form['tanggal'] = $pj->tanggal->format('Y-m-d');
        $this->form['total'] = $pj->total;
        $this->form['kena_pajak'] = $pj->kena_pajak;
        $this->form['items'] = $pj->items->map(fn($i) => [
            'produk_id' => $i->produk_id,
            'harga_jual' => (string) $i->harga_jual,
            'qty' => $i->qty,
            'kena_pajak' => $i->kena_pajak,
        ])->toArray();
    }

    // ✅ Simpan revisi penjualan
    public function simpanRevisi()
    {
        $pjLama = Penjualan::with(['items', 'transaksiKas'])
            ->findOrFail($this->editId);

        RevisiService::revisiTransaksi('penjualan', $pjLama, $this->form);

        $this->showRevisiModal = false;
        $this->dispatch('notify', message: 'Revisi penjualan berhasil disimpan.');
        $this->reset('form', 'editId');
    }

    // 🔄 Auto update total kalau item berubah
    public function updatedForm()
    {
        $this->form['total'] = collect($this->form['items'])
            ->sum(fn($i) => ($i['qty'] ?? 0) * ($i['harga_jual'] ?? 0));
    }


    // ✅ Reset pilihan kalau filter berubah
    public function updated($property)
    {
        if (in_array($property, [
            'tanggal_awal',
            'tanggal_akhir',
            'customer_id',
            'filter_pajak',
            'search_no_struk',
        ])) {
            $this->reset(['selectedPenjualans', 'selectAll']);
        }
    }

    // ✅ sinkronisasi select all per halaman
    public function updatedSelectedPenjualans()
    {
        $ids = Penjualan::query()
            ->when($this->tanggal_awal, fn($q) => $q->whereDate('tanggal', '>=', $this->tanggal_awal))
            ->when($this->tanggal_akhir, fn($q) => $q->whereDate('tanggal', '<=', $this->tanggal_akhir))
            ->when($this->customer_id, fn($q) => $q->where('customer_id', $this->customer_id))
            ->when($this->filter_pajak !== '', fn($q) => $q->where('kena_pajak', $this->filter_pajak))
            ->when($this->search_no_struk, fn($q) => $q->where('no_struk', 'like', "%{$this->search_no_struk}%"))
            ->pluck('id')
            ->toArray();

        $this->selectAll = !array_diff($ids, $this->selectedPenjualans);
    }

    public function toggleSelect($id)
    {
        if (in_array($id, $this->selectedPenjualans)) {
            $this->selectedPenjualans = array_diff($this->selectedPenjualans, [$id]);
        } else {
            $this->selectedPenjualans[] = $id;
        }
    }

    // ✅ Bulk download
    public function bulkDownload()
    {
        $alamatList = config('alamat');
        $alamat = $alamatList[$this->alamat_id] ?? reset($alamatList);

        foreach ($this->selectedPenjualans as $id) {
            $penjualan = Penjualan::with(['items.produk', 'customer'])->findOrFail($id);

            $pdf = Pdf::loadView('pdf.penjualan', [
                'penjualan' => $penjualan,
                'alamat'    => $alamat,
            ]);

            $this->dispatch(
                'open-pdf',
                content: base64_encode($pdf->output()),
                filename: "penjualan-{$penjualan->no_struk}.pdf",
            );
        }

        return $this->reset('alamat_id', 'selectedPenjualans', 'selectAll');
    }

    public function setAlamat($id)
    {
        $this->alamat_id = $id;
    }

    // ✅ Modal detail
    public function showDetail($id)
    {
        $this->detailPenjualanId = $id;
    }

    public function closeDetail()
    {
        $this->detailPenjualanId = null;
    }

    // ✅ Single download
    public function downloadPdf($id)
    {
        $penjualan = Penjualan::with(['items.produk', 'customer'])->findOrFail($id);

        $alamatList = config('alamat');
        $alamat = $alamatList[$this->alamat_id] ?? reset($alamatList);

        $pdf = Pdf::loadView('pdf.penjualan', [
            'penjualan' => $penjualan,
            'alamat'    => $alamat,
        ]);

        $this->reset('alamat_id');

        return response()->streamDownload(
            fn() => print($pdf->output()),
            "penjualan-{$penjualan->no_struk}.pdf"
        );
    }

    // ✅ Render
    public function render()
    {
        $baseQuery = Penjualan::with('customer')
            ->whereIn('status', ['aktif', 'revisi'])
            ->when($this->tanggal_awal, fn($q) => $q->whereDate('tanggal', '>=', $this->tanggal_awal))
            ->when($this->tanggal_akhir, fn($q) => $q->whereDate('tanggal', '<=', $this->tanggal_akhir))
            ->when($this->customer_id, fn($q) => $q->where('customer_id', $this->customer_id))
            ->when($this->filter_pajak !== '', fn($q) => $q->where('kena_pajak', $this->filter_pajak))
            ->when($this->search_no_struk, fn($q) => $q->where('no_struk', 'like', "%{$this->search_no_struk}%"))
            ->orderByDesc('tanggal')
            ->orderByDesc('id');


        $penjualans = (clone $baseQuery)
            ->orderBy('id', 'desc')
            ->paginate(10);

        $total = (clone $baseQuery)->sum('total');
        $customers = Customer::all();

        return view('livewire.penjualan.laporan', [
            'penjualans' => $penjualans,
            'customers'  => $customers,
            'total'      => $total,
            'detail'     => $this->detailPenjualanId
                ? Penjualan::with(['items.produk', 'customer'])->find($this->detailPenjualanId)
                : null,
        ]);
    }
}
