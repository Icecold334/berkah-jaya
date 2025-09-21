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
                ? 'cursor-not-allowed opacity-50' : '' ])>
                {{ $kenaPajak ? 'Pakai Pajak' : 'Tanpa Pajak' }}
            </button>
        </div>
    </div>

    <div class="mt-4">
        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
            <table class="w-full text-sm text-center text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-primary-50">
                    <tr>
                        <th class="px-6 py-3 w-1/12">#</th>
                        <th class="px-6 py-3">Nama Barang</th>
                        <th class="px-6 py-3">Jumlah</th>
                        <th class="px-6 py-3">Harga</th>
                        <th class="px-6 py-3 w-1/12"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $index => $item)
                    <tr class="odd:bg-white even:bg-gray-50 border-b border-gray-200">
                        <td class="px-6 py-4 font-medium">{{ $index + 1 }}</td>
                        <td class="px-6 py-4 text-left">
                            <input type="text" wire:model.live="items.{{ $index }}.nama" placeholder="Nama Barang"
                                @disabled(empty($supplier_id))
                                @class([ 'bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2'
                                , empty($supplier_id) ? 'cursor-not-allowed opacity-50' : '' ])>
                        </td>
                        <td class="px-6 py-4 text-left">
                            <input type="number" wire:model.live="items.{{ $index }}.qty" min="1"
                                @disabled(empty($supplier_id))
                                @class([ 'bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2'
                                , empty($supplier_id) ? 'cursor-not-allowed opacity-50' : '' ])>
                        </td>
                        <td class="px-6 py-4 text-left">
                            <input type="text" x-data x-init="if ($el.value) { $el.value = formatRupiah($el.value) }"
                                x-on:input="$el.value = formatRupiah($el.value)"
                                wire:model.live="items.{{ $index }}.harga_beli" placeholder="Rp 0"
                                @disabled(empty($supplier_id))
                                @class([ 'bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2'
                                , empty($supplier_id) ? 'cursor-not-allowed opacity-50' : '' ])>
                        </td>
                        <td class="px-6 py-4 text-center flex justify-center items-center gap-3">
                            {{-- Tombol Hapus --}}
                            @if (count($items) > 1)
                            <button type="button" wire:click="removeItem({{ $index }})" @disabled(empty($supplier_id))
                                @class([ 'bg-danger-100 text-danger-800 text-sm font-medium px-2.5 py-1 rounded-sm' ,
                                empty($supplier_id) ? 'cursor-not-allowed opacity-50' : '' ])>
                                <i class="fa-solid fa-trash"></i>
                            </button>
                            @endif

                            {{-- Tombol Tambah --}}
                            @if ($loop->last && !empty($item['nama']) && !empty($item['qty']) && $item['qty'] > 0 &&
                            !empty($item['harga_beli']) && $item['harga_beli'] > 0)
                            <button type="button" wire:click="addItem" @disabled(empty($supplier_id))
                                @class([ 'bg-primary-100 text-primary-800 text-sm font-medium px-2.5 py-1 rounded-sm' ,
                                empty($supplier_id) ? 'cursor-not-allowed opacity-50' : '' ])>
                                <i class="fa-solid fa-plus"></i>
                            </button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @php
    $isValid = collect($items)->every(fn($i) => !empty($i['nama']) && !empty($i['qty']) && $i['qty'] > 0 &&
    !empty($i['harga_beli']) && $i['harga_beli'] > 0);
    @endphp

    @if (count($items) >= 1 && $isValid)
    <div class="mt-4 flex justify-end">
        <button wire:click="simpan" type="button" @disabled(empty($supplier_id))
            @class([ 'text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center shadow-md'
            , empty($supplier_id) ? 'cursor-not-allowed opacity-50' : '' ])>
            Simpan
        </button>
    </div>
    @endif
</div>