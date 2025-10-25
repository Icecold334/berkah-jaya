<div class="">
    <div class="grid grid-cols-12 gap-4">
        <div class="col-span-10">
            <select wire:model.live="supplier_id"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 shadow-md">
                <option value="">Pilih Supplier</option>
                @foreach ($suppliers as $supplier)
                <option value="{{ $supplier->id }}">{{ $supplier->nama }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-span-2">
            <button type="button" wire:click="togglePajak" @disabled(empty($supplier_id))
                @class([ 'w-full font-medium rounded-lg text-sm px-5 py-2.5 text-center shadow-md' , $kenaPajak
                ? 'bg-primary-700 text-white focus:ring-primary-300'
                : 'border border-primary-700 text-primary-700 focus:ring-primary-300' , empty($supplier_id)
                ? 'cursor-not-allowed opacity-50' : '' , ])>
                {{ $kenaPajak ? 'Pakai Pajak' : 'Tanpa Pajak' }}
            </button>
        </div>
    </div>

    <div class="mt-4">
        {{-- <div class="relative overflow-x-auto shadow-md sm:rounded-lg"> --}}
            <table class="w-full text-sm text-center text-gray-900 border border-gray-200 rounded-md shadow-md">
                <thead class="text-xs text-gray-700 uppercase bg-primary-50">
                    <tr>
                        <th class="px-6 py-3 w-1/12">#</th>
                        <th class="px-6 py-3">Nama Barang</th>
                        <th class="px-6 py-3">Jumlah</th>
                        <th class="px-6 py-3">Harga Satuan</th>
                        <th class="px-6 py-3 w-1/12"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $index => $item)
                    <tr class="odd:bg-white even:bg-gray-50 border-b border-gray-200">
                        <td class="px-6 py-4 font-medium">{{ $index + 1 }}</td>
                        <td class="px-6 py-4 text-left relative" x-data="{ open: false }">
                            <input type="text" wire:model.live="items.{{ $index }}.nama" placeholder="Nama Barang"
                                @focus="open = true" @blur="setTimeout(() => open = false, 200)"
                                @disabled(empty($supplier_id))
                                @class([ 'bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2'
                                , empty($supplier_id) ? 'cursor-not-allowed opacity-50' : '' , ])>

                            {{-- Dropdown hasil pencarian produk --}}
                            @if (!empty($searchResults[$index]))
                            <ul x-show="open" x-transition
                                class="absolute z-50 bg-white border border-gray-200 rounded-md shadow-lg max-h-48 overflow-y-auto text-left w-full">
                                @foreach ($searchResults[$index] as $namaProduk)
                                <li wire:click="pilihProduk({{ $index }}, '{{ addslashes($namaProduk) }}')"
                                    class="px-3 py-2 cursor-pointer hover:bg-primary-100">
                                    {{ $namaProduk }}
                                </li>
                                @endforeach
                            </ul>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-left">
                            <input type="number" wire:model.live="items.{{ $index }}.qty" min="1"
                                @disabled(empty($supplier_id))
                                @class([ 'bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2'
                                , empty($supplier_id) ? 'cursor-not-allowed opacity-50' : '' , ])>
                        </td>
                        <td class="px-6 py-4 text-left">
                            <input type="text" x-data x-init="if ($el.value) { $el.value = formatRupiah($el.value) }"
                                x-on:input="$el.value = formatRupiah($el.value)"
                                wire:model.live="items.{{ $index }}.harga_beli" placeholder="Rp 0"
                                @disabled(empty($supplier_id))
                                @class([ 'bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2'
                                , empty($supplier_id) ? 'cursor-not-allowed opacity-50' : '' , ])>
                        </td>
                        <td class="px-6 py-4 text-center flex justify-center items-center gap-3">
                            {{-- Tombol Hapus --}}
                            @if (count($items) > 1)
                            <button type="button" wire:click="removeItem({{ $index }})" @disabled(empty($supplier_id))
                                @class([ 'bg-danger-100 text-danger-800 text-sm font-medium px-2.5 py-1 rounded-sm' ,
                                empty($supplier_id) ? 'cursor-not-allowed opacity-50' : '' , ])>
                                <i class="fa-solid fa-trash"></i>
                            </button>
                            @endif

                            {{-- Tombol Tambah --}}
                            @if (
                            $loop->last &&
                            !empty($item['nama']) &&
                            !empty($item['qty']) &&
                            $item['qty'] > 0 &&
                            !empty($item['harga_beli']) &&
                            $item['harga_beli'] > 0)
                            <button type="button" wire:click="addItem" @disabled(empty($supplier_id))
                                @class([ 'bg-primary-100 text-primary-800 text-sm font-medium px-2.5 py-1 rounded-sm' ,
                                empty($supplier_id) ? 'cursor-not-allowed opacity-50' : '' , ])>
                                <i class="fa-solid fa-plus"></i>
                            </button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            {{--
        </div> --}}
    </div>

    @php
    $isValid = collect($items)->every(
    fn($i) => !empty($i['nama']) &&
    !empty($i['qty']) &&
    $i['qty'] > 0 &&
    !empty($i['harga_beli']) &&
    $i['harga_beli'] > 0,
    );
    @endphp

    @if (count($items) >= 1 && $isValid)
    <div class="mt-4 flex justify-end">
        <button wire:click="konfirmasiSimpan" type="button" @disabled(empty($supplier_id))
            @class([ 'text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center shadow-md'
            , empty($supplier_id) ? 'cursor-not-allowed opacity-50' : '' , ])>
            Simpan
        </button>
    </div>
    @endif

    {{-- ✅ Modal Konfirmasi Pembelian --}}
    <div x-data="{ open: @entangle('showConfirmModal') }" x-show="open" x-transition.opacity.duration.200ms
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">

        <div x-show="open" x-transition.scale.origin.center.duration.200ms
            class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6 relative border border-gray-200">

            {{-- Header --}}
            <div class="flex justify-between items-center border-b pb-3 mb-4">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <i class="fa-solid fa-file-invoice text-primary-600"></i>
                    Konfirmasi Pembelian
                </h2>
                <button @click="open = false" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            {{-- Body --}}
            <div class="space-y-3 text-sm text-gray-700">
                <div class="flex justify-between">
                    <span class="font-medium text-gray-600">Supplier</span>
                    <span class="text-gray-900 font-semibold">
                        {{ optional(App\Models\Supplier::find($supplier_id))->nama ?? '-' }}
                    </span>
                </div>

                <div class="flex justify-between items-center">
                    <span class="font-medium text-gray-600">Status Pajak</span>
                    <span @class([ 'px-2 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wide' , $kenaPajak
                        ? 'bg-green-100 text-green-800 border border-green-200'
                        : 'bg-gray-100 text-gray-700 border border-gray-200' , ])>
                        {{ $kenaPajak ? 'Pakai Pajak' : 'Tanpa Pajak' }}
                    </span>
                </div>

                <div class="flex justify-between">
                    <span class="font-medium text-gray-600">Total</span>
                    <span class="font-bold text-gray-900 text-base">
                        Rp {{ number_format($totalPreview, 0, ',', '.') }}
                    </span>
                </div>

                <div class="flex justify-between">
                    <span class="font-medium text-gray-600">Tanggal</span>
                    <span class="text-gray-800">{{ $tanggal }}</span>
                </div>
            </div>

            {{-- Divider --}}
            <div class="border-t my-5"></div>

            {{-- Footer Buttons --}}
            <div class="flex justify-end gap-3">
                <button type="button" @click="open = false"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 focus:ring-2 focus:ring-gray-300">
                    Batal
                </button>

                <button type="button" wire:click="simpanFinal"
                    class="px-4 py-2 text-sm font-semibold text-white bg-primary-600 rounded-lg hover:bg-primary-700 focus:ring-4 focus:ring-primary-300">
                    Ya, Simpan
                </button>
            </div>
        </div>
    </div>
</div>