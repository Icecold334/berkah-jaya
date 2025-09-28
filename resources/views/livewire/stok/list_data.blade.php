<div class="p-6 space-y-6">

    <div class="mb-4">
        <input type="text" wire:model.live.debounce.500ms="search" placeholder="Cari nama / kode barang..."
            class="w-1/3 text-sm rounded border-gray-300 focus:border-primary-500 focus:ring focus:ring-primary-200" />
    </div>

    {{-- 📑 Tabel Stok --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
        <div class="flex justify-between items-center px-4 py-2 border-b">
            <h3 class="font-semibold text-gray-700 text-sm">Daftar Stok</h3>
        </div>



        <div class="overflow-x-auto">
            <table class="w-full text-sm text-center text-gray-900">
                <thead class="text-xs text-gray-700 uppercase bg-primary-50">
                    <tr>
                        <th class="px-4 py-2 text-left">Nama Barang</th>
                        <th class="px-4 py-2">Harga Jual</th>
                        <th class="px-4 py-2">Stok Pajak</th>
                        <th class="px-4 py-2">Stok Non Pajak</th>
                        <th class="px-4 py-2">Total</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($produks as $p)
                        <tr class="odd:bg-white even:bg-gray-50 border-b">
                            <td class="px-4 py-2 text-left font-medium">{{ $p->nama }}</td>
                            <td class="px-4 py-2 text-right">Rp {{ number_format($p->harga_jual, 0, ',', '.') }}</td>
                            <td class="px-4 py-2 text-right">{{ $p->stok_pajak }}</td>
                            <td class="px-4 py-2 text-right">{{ $p->stok_non_pajak }}</td>
                            <td class="px-4 py-2 text-right font-semibold">{{ $p->stok_total }}</td>
                            <td class="px-4 py-2">
                                <button type="button" wire:click="showDetail({{ $p->id }})"
                                    class="bg-info-100 text-info-800 text-xs font-medium px-3 py-1 rounded shadow-sm">
                                    Detail
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-4 text-gray-500">Tidak ada data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex justify-between items-center px-4 py-2 border-t">
            {{ $produks->links() }}
        </div>
    </div>

    {{-- 🔍 Modal Detail Produk --}}
    @if ($detail)
        <div class="fixed inset-0 bg-gray-900/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg w-3/4">
                <div class="flex justify-between items-center border-b px-6 py-3">
                    <h2 class="font-bold text-lg">Riwayat Pergerakan Stok – {{ $detail->nama }}</h2>
                    <button wire:click="closeDetail" class="text-gray-500 hover:text-gray-700">&times;</button>
                </div>

                <div class="p-6 space-y-4">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-gray-700">
                            <thead class="bg-primary-50">
                                <tr>
                                    <th class="px-4 py-2 text-left">Tanggal</th>
                                    <th class="px-4 py-2">Tipe</th>
                                    <th class="px-4 py-2">Qty</th>
                                    <th class="px-4 py-2">Sumber</th>
                                    <th class="px-4 py-2">Pajak</th> {{-- ✅ kolom baru --}}
                                    {{-- <th class="px-4 py-2">Keterangan</th> --}}
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pergerakan as $pg)
                                    <tr class="border-b">
                                        <td class="px-4 py-2">{{ $pg->tanggal->format('d/m/Y') }}</td>
                                        <td class="px-4 py-2 text-center">
                                            @if ($pg->tipe === 'masuk')
                                                <span class="text-success-600 font-semibold">Masuk</span>
                                            @else
                                                <span class="text-danger-600 font-semibold">Keluar</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 text-right">{{ $pg->qty }}</td>
                                        <td class="px-4 py-2">
                                            @if ($pg->sumber instanceof App\Models\Pembelian)
                                                {{ $pg->sumber->no_faktur }}
                                            @elseif($pg->sumber instanceof App\Models\Penjualan)
                                                {{ $pg->sumber->no_struk }}
                                            @else
                                                Manual / Opname
                                            @endif
                                        </td>

                                        {{-- ✅ status pajak --}}
                                        <td class="px-4 py-2">
                                            @if ($pg->sumber instanceof App\Models\Pembelian || $pg->sumber instanceof App\Models\Penjualan)
                                                @if ($pg->sumber->kena_pajak)
                                                    <span
                                                        class="bg-success-100 text-success-800 text-xs font-medium px-2.5 py-0.5 rounded">Pajak</span>
                                                @else
                                                    <span
                                                        class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded">Non
                                                        Pajak</span>
                                                @endif
                                            @else
                                                <span
                                                    class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded">-</span>
                                            @endif
                                        </td>

                                        {{-- <td class="px-4 py-2">{{ $pg->keterangan }}</td> --}}
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-4 text-gray-500 text-center">Belum ada pergerakan
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="mt-3">
                            {{ $pergerakan->links() }}
                        </div>
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
