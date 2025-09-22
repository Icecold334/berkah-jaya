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
                @foreach($suppliers as $s)
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
            @if(count($selectedPembelians) > 0)
            <div class="flex items-center gap-2">
                <select wire:model.live="alamat_id"
                    class="text-sm rounded border-gray-300 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">Pilih Alamat</option>
                    @foreach(config('alamat') as $key => $a)
                    <option value="{{ $key }}">{{ $a['label'] }}</option>
                    @endforeach
                </select>

                <button wire:click="bulkDownload" @disabled(empty($alamat_id))
                    @class([ 'bg-success-100 text-success-800 text-sm font-medium px-3 py-1 rounded shadow-sm'
                    , 'cursor-not-allowed opacity-50'=> empty($alamat_id),
                    ])>
                    <i class="fa-solid fa-file-pdf mr-1"></i>
                    Download Nota ({{ count($selectedPembelians) }})
                </button>
            </div>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-center text-gray-700">
                <thead class="text-xs text-gray-700 uppercase bg-primary-50">
                    <tr>
                        <th class="px-3 py-2 w-1/12">
                            <input type="checkbox" wire:model.live="selectAll"
                                class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        </th>
                        <th class="px-4 py-2">No Faktur</th>
                        <th class="px-4 py-2">Tanggal</th>
                        <th class="px-4 py-2">Supplier</th>
                        <th class="px-4 py-2">Total</th>
                        <th class="px-4 py-2">Pajak</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pembelians as $pb)
                    <tr class="odd:bg-white even:bg-gray-50 border-b">
                        <td class="px-3 py-2">
                            <input type="checkbox" value="{{ $pb->id }}" wire:model.live="selectedPembelians"
                                class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        </td>
                        <td class="px-4 py-2 font-semibold text-primary-700">{{ $pb->no_faktur }}</td>
                        <td class="px-4 py-2">{{ $pb->tanggal->format('d/m/Y') }}</td>
                        <td class="px-4 py-2">{{ $pb->supplier->nama }}</td>
                        <td class="px-4 py-2 text-right">Rp {{ number_format($pb->total, 0, ',', '.') }}</td>
                        <td class="px-4 py-2">
                            @if($pb->kena_pajak)
                            <span
                                class="bg-success-100 text-success-800 text-xs font-medium px-2.5 py-0.5 rounded">Pajak</span>
                            @else
                            <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded">Non
                                Pajak</span>
                            @endif
                        </td>
                        <td class="px-4 py-2">
                            <button type="button" wire:click="showDetail({{ $pb->id }})"
                                class="bg-info-100 text-info-800 text-xs font-medium px-3 py-1 rounded shadow-sm">
                                Detail
                            </button>
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
    @if($detail)
    <div class="fixed inset-0 bg-gray-900/50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-2/3">
            <div class="flex justify-between items-center border-b px-6 py-3">
                <h2 class="font-bold text-lg">Detail Pembelian #{{ $detail->no_faktur }}</h2>
                <div class="flex items-center gap-3">
                    <select wire:model.live="alamat_id"
                        class="rounded border-gray-300 text-sm focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Pilih Alamat</option>
                        @foreach(config('alamat') as $key => $a)
                        <option value="{{ $key }}">{{ $a['label'] }}</option>
                        @endforeach
                    </select>

                    <button wire:click="downloadPdf({{ $detail->id }})" @disabled(empty($alamat_id))
                        @class([ 'bg-success-100 text-success-800 text-sm font-medium px-3 py-1 rounded shadow-sm'
                        , 'cursor-not-allowed opacity-50'=> empty($alamat_id),
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
                                @if($detail->kena_pajak)
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
                            @foreach($detail->items as $item)
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

</div>