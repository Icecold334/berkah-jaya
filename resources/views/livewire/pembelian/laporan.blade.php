<div class="p-6 space-y-6">


    {{-- 🔎 Filter --}}
    <div class="grid grid-cols-12 gap-3">
        <div class="col-span-2">
            <input type="date" wire:model.live="tanggal_awal"
                class="w-full text-sm rounded border-gray-300 focus:border-primary-500 focus:ring focus:ring-primary-200" />
        </div>
        <div class="col-span-2">
            <input type="date" wire:model.live="tanggal_akhir"
                class="w-full text-sm rounded border-gray-300 focus:border-primary-500 focus:ring focus:ring-primary-200" />
        </div>
        <div class="col-span-3">
            <select wire:model.live="supplier_id"
                class="w-full text-sm rounded border-gray-300 focus:border-primary-500 focus:ring focus:ring-primary-200">
                <option value="">Semua Supplier</option>
                @foreach ($suppliers as $s)
                    <option value="{{ $s->id }}">{{ $s->nama }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-span-2">
            <select wire:model.live="filter_pajak"
                class="w-full text-sm rounded border-gray-300 focus:border-primary-500 focus:ring focus:ring-primary-200">
                <option value="">Semua Pajak</option>
                <option value="1">Kena Pajak</option>
                <option value="0">Non Pajak</option>
            </select>
        </div>
        <div class="col-span-3">
            <input type="text" wire:model.live="search_no_faktur" placeholder="Cari No Faktur..."
                class="w-full text-sm rounded border-gray-300 focus:border-primary-500 focus:ring focus:ring-primary-200" />
        </div>
    </div>

    {{-- 📑 Tabel --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
        <div class="flex justify-between items-center px-4 py-2 border-b">
            <h3 class="font-semibold text-gray-700 text-sm">Daftar Pembelian</h3>

            {{-- 🔄 Bulk Download moved here --}}
            @if (count($selectedPembelians) > 0)
                <div class="flex items-center gap-2">
                    <select wire:model.live="alamat_id"
                        class="text-sm rounded border-gray-300 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Pilih Alamat</option>
                        @foreach (config('alamat') as $key => $a)
                            <option value="{{ $key }}">{{ $a['label'] }}</option>
                        @endforeach
                    </select>

                    <button wire:click="bulkDownload" @disabled(empty($alamat_id)) @class([
                        'bg-success-100 text-success-800 text-sm font-medium px-3 py-1 rounded shadow-sm',
                        'cursor-not-allowed opacity-50' => empty($alamat_id),
                    ])>
                        <i class="fa-solid fa-file-pdf mr-1"></i>
                        Download Nota ({{ count($selectedPembelians) }})
                    </button>
                </div>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-center text-gray-900">
                <thead class="text-xs text-gray-700 uppercase bg-primary-50">
                    <tr>
                        <th class="px-3 py-2 w-1/12">
                            <input type="checkbox" wire:model.live="selectAll"
                                class="h-5 w-5 rounded border-gray-400 text-primary-600 focus:ring-primary-500 
           checked:border-primary-600 checked:bg-primary-600 hover:border-primary-500" />
                        </th>
                        <th class="px-4 py-2">No Faktur</th>
                        <th class="px-4 py-2">Tanggal</th>
                        <th class="px-4 py-2">Supplier</th>
                        <th class="px-4 py-2">Total</th>
                        <th class="px-4 py-2">Pajak</th>
                        <th class="px-4 py-2">Status</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pembelians as $pb)
                        <tr class="odd:bg-white even:bg-gray-50 border-b">
                            <td class="px-3 py-2">
                                <input type="checkbox" value="{{ $pb->id }}" wire:model.live="selectedPembelians"
                                    class="h-5 w-5 rounded border-gray-400 text-primary-600 focus:ring-primary-500 
           checked:border-primary-600 checked:bg-primary-600 hover:border-primary-500" />
                            </td>
                            <td class="px-4 py-2 font-semibold text-primary-700">{{ $pb->no_faktur }}</td>
                            <td class="px-4 py-2">{{ $pb->tanggal->format('d/m/Y') }}</td>
                            <td class="px-4 py-2">{{ $pb->supplier->nama }}</td>
                            <td class="px-4 py-2 text-right">Rp {{ number_format($pb->total, 0, ',', '.') }}</td>
                            <td class="px-4 py-2">
                                @if ($pb->kena_pajak)
                                    <span
                                        class="bg-success-100 text-success-800 text-xs font-medium px-2.5 py-0.5 rounded">Pajak</span>
                                @else
                                    <span
                                        class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded">Non
                                        Pajak</span>
                                @endif
                            </td>

                            <td class="px-4 py-2 relative">
                                @if ($pb->is_lunas)
                                    <span
                                        class="bg-success-100 text-success-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                                        Lunas
                                    </span>
                                @else
                                    <div x-data="{ show: false }" class="inline-block relative">
                                        <span @mouseenter="show = true" @mouseleave="show = false"
                                            class="bg-warning-100 text-warning-800 text-xs font-semibold px-2.5 py-0.5 rounded cursor-help">
                                            Belum Lunas
                                        </span>

                                        {{-- Tooltip tampil saat hover --}}
                                        <div x-show="show" x-transition.opacity.duration.150ms
                                            class="absolute left-1/2 -translate-x-1/2 mt-2 w-max bg-gray-800 text-white text-[11px] rounded px-2 py-1 shadow-lg z-50 whitespace-nowrap">
                                            Sisa: Rp {{ number_format($pb->sisa_bayar, 0, ',', '.') }}
                                        </div>
                                    </div>
                                @endif
                            </td>

                            <td class="px-4 py-2">
                                <button type="button" wire:click="showDetail({{ $pb->id }})"
                                    class="bg-info-100 text-info-800 text-xs font-medium px-3 py-1 rounded shadow-sm">
                                    Detail
                                </button>

                                @if (!$pb->is_lunas)
                                    <button type="button" wire:click="openBayarModal({{ $pb->id }})"
                                        class="bg-success-100 text-success-800 text-xs font-medium px-3 py-1 rounded shadow-sm ml-1">
                                        Bayar
                                    </button>
                                @endif

                                @if ($pb->status === 'aktif')
                                    <button type="button" wire:click="openRevisi({{ $pb->id }})"
                                        class="bg-warning-100 text-warning-800 text-xs font-medium px-3 py-1 rounded shadow-sm ml-1">
                                        Revisi
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-4 text-gray-500">Tidak ada data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- 📍 Pagination + Total moved here --}}
        <div class="flex justify-between items-center px-4 py-2 border-t">
            {{ $pembelians->links() }}

            <span class="text-sm text-gray-600">
                Total: <strong>Rp {{ number_format($total, 0, ',', '.') }}</strong>
            </span>
        </div>
    </div>

    {{-- 🔍 Modal Detail Pembelian --}}
    @if ($detail)
        <div class="fixed inset-0 bg-gray-900/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg w-2/3">
                <div class="flex justify-between items-center border-b px-6 py-3">
                    <h2 class="font-bold text-lg">Detail Pembelian #{{ $detail->no_faktur }}</h2>
                    <div class="flex items-center gap-3">
                        <select wire:model.live="alamat_id"
                            class="rounded border-gray-300 text-sm focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Pilih Alamat</option>
                            @foreach (config('alamat') as $key => $a)
                                <option value="{{ $key }}">{{ $a['label'] }}</option>
                            @endforeach
                        </select>

                        <button wire:click="downloadPdf({{ $detail->id }})" @disabled(empty($alamat_id))
                            @class([
                                'bg-success-100 text-success-800 text-sm font-medium px-3 py-1 rounded shadow-sm',
                                'cursor-not-allowed opacity-50' => empty($alamat_id),
                            ])>
                            <i class="fa-solid fa-file-pdf mr-1"></i> Download Nota
                        </button>

                        <button wire:click="closeDetail" class="text-gray-500 hover:text-gray-700">&times;</button>
                    </div>
                </div>
                <div class="p-6 space-y-4">
                    {{-- Info utama --}}
                    <table class="w-full text-sm text-gray-700">
                        <tbody>
                            <tr>
                                <td class="font-semibold w-1/4 py-1">Tanggal</td>
                                <td class="py-1">{{ $detail->tanggal->translatedFormat('l, d F Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td class="font-semibold py-1">Supplier</td>
                                <td class="py-1">{{ $detail->supplier->nama }}</td>
                            </tr>
                            <tr>
                                <td class="font-semibold py-1">Total</td>
                                <td class="py-1">Rp {{ number_format($detail->total, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="font-semibold py-1">Status Pajak</td>
                                <td class="py-1">
                                    @if ($detail->kena_pajak)
                                        <span class="text-success-600 font-semibold">Pajak</span>
                                    @else
                                        <span class="text-gray-600 font-semibold">Non Pajak</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    {{-- Item pembelian --}}
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-gray-700">
                            <thead class="bg-primary-50">
                                <tr>
                                    <th class="px-4 py-2 text-left">Produk</th>
                                    <th class="px-4 py-2 text-right">Qty</th>
                                    <th class="px-4 py-2 text-right">Harga Satuan</th>
                                    <th class="px-4 py-2 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($detail->items as $item)
                                    <tr class="border-b">
                                        <td class="px-4 py-2">{{ $item->produk->nama }}</td>
                                        <td class="px-4 py-2 text-right">{{ $item->qty }}</td>
                                        <td class="px-4 py-2 text-right">
                                            Rp {{ number_format($item->harga_beli, 0, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-2 text-right">
                                            Rp {{ number_format($item->qty * $item->harga_beli, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            {{-- 🔻 TOTAL SUBTOTAL --}}
                            <tfoot>
                                <tr class="bg-gray-50 font-semibold">
                                    <td colspan="3" class="px-4 py-2 text-right">Total</td>
                                    <td class="px-4 py-2 text-right">
                                        Rp
                                        {{ number_format($detail->items->sum(fn($i) => $i->qty * $i->harga_beli), 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="flex justify-end border-t px-6 py-3">
                    <button wire:click="closeDetail"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm font-medium px-4 py-2 rounded">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if ($showRevisiModal)
        <div class="fixed inset-0 bg-gray-900/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl mx-4">
                {{-- 🔹 HEADER --}}
                <div class="flex justify-between items-center border-b px-6 py-4">
                    <h2 class="font-bold text-lg text-gray-800">
                        ✏️ Revisi Transaksi Pembelian
                        <span class="text-primary-600 font-semibold">
                            #{{ $editId }}
                        </span>
                    </h2>
                    <button wire:click="$set('showRevisiModal', false)"
                        class="text-gray-400 hover:text-gray-700 transition">
                        <i class="fa-solid fa-times text-lg"></i>
                    </button>
                </div>

                {{-- 🔹 BODY --}}
                <div class="p-6 space-y-5 text-sm text-gray-800">
                    {{-- INFO SUPPLIER & TANGGAL --}}
                    {{-- INFO SUPPLIER, TANGGAL, & STATUS PAJAK --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        {{-- Supplier --}}
                        <div>
                            <label class="block text-gray-600 font-medium mb-1">Supplier</label>
                            <input type="text"
                                value="{{ \App\Models\Supplier::find($form['supplier_id'])->nama ?? '-' }}"
                                class="w-full bg-gray-50 border border-gray-300 rounded text-sm px-3 py-2" readonly>
                        </div>

                        {{-- Tanggal --}}
                        <div>
                            <label class="block text-gray-600 font-medium mb-1">Tanggal Transaksi</label>
                            <input type="date" wire:model="form.tanggal"
                                class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>

                        {{-- Pajak --}}
                        {{-- Pajak --}}
                        <div class="flex flex-col justify-center">
                            <label class="block text-gray-600 font-medium mb-1">Status Pajak</label>

                            <button type="button" wire:click="togglePajak" @disabled(empty($form['supplier_id']))
                                @class([
                                    'w-full font-medium rounded-lg text-sm px-5 py-2.5 text-center shadow-md transition',
                                    $form['kena_pajak']
                                        ? 'bg-primary-700 text-white hover:bg-primary-800 focus:ring-primary-300'
                                        : 'border border-primary-700 text-primary-700 hover:bg-primary-50 focus:ring-primary-300',
                                    empty($form['supplier_id']) ? 'cursor-not-allowed opacity-50' : '',
                                ])>
                                {{ $form['kena_pajak'] ? 'Pakai Pajak' : 'Tanpa Pajak' }}
                            </button>
                        </div>
                    </div>

                    {{-- DAFTAR ITEM --}}
                    <div>
                        <label class="block text-gray-600 font-medium mb-2">Daftar Item</label>

                        <div class="border border-gray-200 rounded-md divide-y max-h-72 overflow-y-auto">
                            <div class="grid grid-cols-12 bg-gray-50 text-xs font-semibold text-gray-600 px-3 py-2">
                                <div class="col-span-5 text-left">Produk</div>
                                <div class="col-span-2 text-right">Qty</div>
                                <div class="col-span-3 text-right">Harga</div>
                                <div class="col-span-2 text-right">Subtotal</div>
                            </div>

                            @foreach ($form['items'] as $index => $item)
                                <div class="grid grid-cols-12 gap-2 items-center px-3 py-2">
                                    <div class="col-span-5">
                                        <input type="text"
                                            value="{{ \App\Models\Produk::find($item['produk_id'])->nama ?? 'Produk #' . $item['produk_id'] }}"
                                            class="w-full border border-gray-200 bg-gray-50 rounded px-2 py-1 text-sm"
                                            readonly>
                                    </div>

                                    <div class="col-span-2">
                                        <input type="number" wire:model.live="form.items.{{ $index }}.qty"
                                            class="w-full border-gray-300 rounded text-sm text-right px-2 py-1 focus:ring-primary-500 focus:border-primary-500"
                                            placeholder="Qty">
                                    </div>

                                    <div x-data="{
                                        formatted: '',
                                        raw: @entangle('form.items.' . $index . '.harga_beli').live,
                                        formatRupiah(angka) {
                                            if (!angka) return '';
                                            return 'Rp ' + angka.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                                        },
                                        unformat(str) {
                                            return str.replace(/[^0-9]/g, '');
                                        },
                                        updateValue(e) {
                                            this.raw = this.unformat(e.target.value);
                                            this.formatted = this.formatRupiah(this.raw);
                                        }
                                    }" x-init="formatted = formatRupiah(raw);
                                    $watch('raw', val => formatted = formatRupiah(val));" class="col-span-3">
                                        <input type="text" x-model="formatted" x-on:input="updateValue($event)"
                                            inputmode="numeric" placeholder="Rp 0"
                                            class="w-full border-gray-300 rounded text-sm text-right px-2 py-1 focus:ring-primary-500 focus:border-primary-500 font-medium tracking-wide">
                                    </div>


                                    <div class="col-span-2 text-right text-gray-700 font-medium">
                                        Rp
                                        {{ number_format(($item['qty'] ?? 0) * ($item['harga_beli'] ?? 0), 0, ',', '.') }}
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- TOTAL REVISI --}}
                        <div class="flex justify-between items-center mt-4 border-t pt-2 text-sm font-medium">
                            <span class="text-gray-700">Total Revisi</span>
                            <span class="text-primary-700 text-base font-semibold">
                                Rp
                                {{ number_format(
                                    collect($form['items'])->sum(fn($i) => ($i['qty'] ?? 0) * ($i['harga_beli'] ?? 0)),
                                    0,
                                    ',',
                                    '.',
                                ) }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- 🔹 FOOTER --}}
                <div class="flex justify-end items-center border-t bg-gray-50 px-6 py-3 gap-3">
                    <button wire:click="$set('showRevisiModal', false)"
                        class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium">
                        Batal
                    </button>
                    <button wire:click="simpanRevisi"
                        class="px-5 py-2 rounded bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium shadow">
                        Simpan Revisi
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if ($showBayarModal)
        <div class="fixed inset-0 bg-gray-900/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
                <div class="flex justify-between items-center border-b px-6 py-3">
                    <h2 class="font-semibold text-lg text-gray-800">💰 Pembayaran Pembelian</h2>
                    <button wire:click="$set('showBayarModal', false)" class="text-gray-400 hover:text-gray-600">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>

                <div class="p-6 space-y-5">

                    {{-- 💵 Input Pembayaran --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Tanggal Pembayaran</label>
                        <input type="date" wire:model="bayarForm.tanggal"
                            class="w-full border-gray-300 rounded text-sm focus:ring-primary-500 focus:border-primary-500">
                    </div>

                    <div x-data="{
                        formatted: '',
                        raw: @entangle('bayarForm.jumlah').live,
                        formatRupiah(angka) {
                            if (!angka) return '';
                            return 'Rp ' + angka.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                        },
                        unformat(str) {
                            return str.replace(/[^0-9]/g, '');
                        },
                        updateValue(e) {
                            this.raw = this.unformat(e.target.value);
                            this.formatted = this.formatRupiah(this.raw);
                        }
                    }" x-init="formatted = formatRupiah(raw);
                    $watch('raw', val => formatted = formatRupiah(val));" class="relative">
                        <label class="block text-sm font-medium text-gray-600 mb-1">Nominal Pembayaran</label>

                        <input type="text" x-model="formatted" x-on:input="updateValue($event)"
                            inputmode="numeric" placeholder="Rp 0"
                            class="w-full border-gray-300 rounded text-sm focus:ring-primary-500 focus:border-primary-500 text-right font-medium tracking-wide">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Keterangan</label>
                        <textarea wire:model="bayarForm.keterangan"
                            class="w-full border-gray-300 rounded text-sm focus:ring-primary-500 focus:border-primary-500" rows="2"></textarea>
                    </div>

                    {{-- 🧾 Riwayat Pembayaran --}}
                    @if (count($riwayatPembayaran) > 0)
                        <div class="border-t pt-3">
                            <h3 class="text-sm font-semibold text-gray-700 mb-2">Riwayat Pembayaran Sebelumnya</h3>
                            <div class="max-h-40 overflow-y-auto border rounded-md">
                                <table class="w-full text-xs text-gray-700">
                                    <thead class="bg-gray-50 text-gray-600 uppercase">
                                        <tr>
                                            <th class="px-3 py-2 text-left">Tanggal</th>
                                            <th class="px-3 py-2 text-right">Jumlah</th>
                                            <th class="px-3 py-2 text-left">Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($riwayatPembayaran as $r)
                                            <tr class="border-b last:border-0">
                                                <td class="px-3 py-2">{{ $r['tanggal'] }}</td>
                                                <td class="px-3 py-2 text-right">Rp
                                                    {{ number_format($r['jumlah'], 0, ',', '.') }}</td>
                                                <td class="px-3 py-2">{{ $r['keterangan'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="flex justify-end gap-3 border-t bg-gray-50 px-6 py-3">
                    <button wire:click="$set('showBayarModal', false)"
                        class="px-4 py-2 text-sm bg-gray-200 rounded hover:bg-gray-300">Batal</button>
                    <button wire:click="simpanPembayaran"
                        class="px-4 py-2 text-sm bg-primary-600 text-white rounded hover:bg-primary-700">
                        Simpan Pembayaran
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
