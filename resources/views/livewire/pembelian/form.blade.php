<div class="">
    {{-- ===================== --}}
    {{-- FORM PEMBELIAN --}}
    {{-- ===================== --}}
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

    {{-- ===================== --}}
    {{-- TABEL ITEM PEMBELIAN --}}
    {{-- ===================== --}}
    <div class="mt-4">
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

                    {{-- Input Nama Barang --}}
                    <td class="px-6 py-4 text-left relative" x-data="{ open: false }">
                        <input type="text" wire:model.live.debounce.300ms="items.{{ $index }}.nama"
                            placeholder="Nama Barang" @focus="open = true" @blur="setTimeout(() => open = false, 200)"
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

                    {{-- Input Qty --}}
                    <td class="px-6 py-4 text-left">
                        <input type="number" wire:model.live="items.{{ $index }}.qty" min="1"
                            @disabled(empty($supplier_id))
                            class="bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2">
                    </td>

                    <td class="px-6 py-4 text-left">
                        <div x-data="{
                                formatted: '',
                                raw: @entangle('items.' . $index . '.harga_beli').live,
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
                            $watch('raw', val => formatted = formatRupiah(val));">
                            <input type="text" x-model="formatted" x-on:input="updateValue($event)" inputmode="numeric"
                                placeholder="Rp 0" @disabled(empty($supplier_id))
                                class="bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2 text-right font-medium tracking-wide">
                        </div>
                    </td>


                    {{-- Tombol Tambah/Hapus --}}
                    <td class="px-6 py-4 flex justify-center items-center gap-3">
                        @if (count($items) > 1)
                        <button type="button" wire:click="removeItem({{ $index }})"
                            class="bg-danger-100 text-danger-800 text-sm font-medium px-2.5 py-1 rounded-sm">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                        @endif

                        @if ($loop->last && !empty($item['nama']) && $item['qty'] > 0 && $item['harga_beli'] > 0)
                        <button type="button" wire:click="addItem"
                            class="bg-primary-100 text-primary-800 text-sm font-medium px-2.5 py-1 rounded-sm">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- ===================== --}}
    {{-- TOMBOL SIMPAN --}}
    {{-- ===================== --}}
    @php
    $isValid = collect($items)->every(
    fn($i) => !empty($i['nama']) &&
    !empty($i['qty']) &&
    $i['qty'] > 0 &&
    !empty($i['harga_beli']) &&
    $i['harga_beli'] > 0,
    );
    @endphp

    @if ($isValid)
    <div class="mt-4 flex justify-end">
        <button wire:click="konfirmasiSimpan" type="button"
            class="text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center shadow-md">
            Simpan
        </button>
    </div>
    @endif

    {{-- ===================== --}}
    {{-- MODAL KONFIRMASI PEMBELIAN (Sekaligus Pilih Metode) --}}
    {{-- ===================== --}}
    <div x-data="{ open: @entangle('showConfirmModal') }" x-cloak x-show="open"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">

        <div x-show="open" x-transition.scale.origin.center.duration.200ms
            class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6 relative border border-gray-200">

            <h2 class="text-lg font-semibold text-gray-800 mb-4">Konfirmasi Pembelian</h2>

            <div class="space-y-2 text-sm text-gray-700">
                <div class="flex justify-between"><span>Supplier</span><span class="font-semibold">{{
                        optional(App\Models\Supplier::find($supplier_id))->nama ?? '-' }}</span>
                </div>
                <div class="flex justify-between"><span>Tanggal</span><span>{{ $tanggal }}</span></div>
                <div class="flex justify-between">
                    <span>Pajak</span><span>{{ $kenaPajak ? 'Pakai Pajak' : 'Tanpa Pajak' }}</span>
                </div>
                <div class="flex justify-between"><span>Total</span><span class="font-bold">Rp
                        {{ number_format($totalPreview, 0, ',', '.') }}</span></div>
            </div>

            <div class="mt-5 flex justify-end gap-3">
                <button type="button" @click="$wire.set('showConfirmModal', false)"
                    class="px-4 py-2 bg-gray-100 rounded-lg text-gray-700 hover:bg-gray-200">Batal</button>

                <button type="button" wire:click="pilihMetode('cash')"
                    class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">Tunai 💵</button>

                <button type="button" wire:click="pilihMetode('kredit')"
                    class="px-4 py-2 bg-secondary-600 text-white rounded-lg hover:bg-secondary-700">Kredit 📄</button>
            </div>
        </div>
    </div>

    {{-- ===================== --}}
    {{-- MODAL KONFIRMASI AKHIR (Yakin Simpan?) --}}
    {{-- ===================== --}}
    <div x-data="{ open: @entangle('showFinalConfirmModal') }" x-cloak x-show="open"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">

        <div x-show="open" x-transition.scale.origin.center.duration.200ms
            class="bg-white rounded-lg shadow-xl p-6 w-full max-w-sm text-center">
            <h3 class="text-lg font-semibold text-gray-800 mb-3">Yakin Simpan Pembelian Ini?</h3>

            <p class="text-sm text-gray-600 mb-5">
                Metode: <strong>{{ strtoupper($metodeBayar ?? '-') }}</strong><br>
                Total: <strong>Rp {{ number_format($totalPreview, 0, ',', '.') }}</strong>
            </p>

            <div class="flex justify-center gap-3">
                <button type="button" @click="$wire.set('showFinalConfirmModal', false)"
                    class="px-4 py-2 text-sm bg-gray-200 rounded-lg">Batal</button>
                <button type="button" wire:click="simpanFinal"
                    class="px-4 py-2 text-sm font-semibold text-white bg-primary-600 rounded-lg hover:bg-primary-700">
                    Ya, Simpan
                </button>
            </div>
        </div>
    </div>

</div>