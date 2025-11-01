<div class="space-y-6">

    {{-- ===================== --}}
    {{-- 🔹 Header Form Penjualan --}}
    {{-- ===================== --}}
    <div class="grid grid-cols-12 gap-6">
        {{-- === LEFT: Customer + Produk Search === --}}
        <div class="col-span-8 p-6 bg-white border border-gray-200 rounded-xl shadow-sm">

            {{-- Customer Input --}}
            <div class="relative mb-5" x-data="{ open: false }">
                <label for="customerInput" class="block text-sm font-semibold text-gray-700 mb-2">
                    Customer
                </label>
                <input type="text" id="customerInput"
                    class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring focus:ring-primary-200 text-gray-800"
                    placeholder="Ketik nama customer..." wire:model.live.debounce.500ms="customerInput"
                    @focus="open = true" @blur="setTimeout(() => open = false, 150)">

                {{-- Dropdown suggestion --}}
                @if (!empty($customerList))
                <ul x-show="open"
                    class="absolute z-10 w-full bg-white border border-gray-200 rounded-lg shadow-md max-h-56 overflow-y-auto mt-1 divide-y divide-gray-100">
                    @foreach ($customerList as $cust)
                    <li wire:click="pilihCustomer({{ $cust['id'] }})"
                        class="px-4 py-2 hover:bg-primary-50 cursor-pointer text-gray-700">
                        <i class="fa-regular fa-user mr-2 text-primary-500"></i>{{ $cust['nama'] }}
                    </li>
                    @endforeach
                </ul>
                @endif

                {{-- Badge status --}}
                @if ($customerInput)
                <div class="mt-2">
                    @if ($customer_id)
                    <span
                        class="inline-flex items-center px-2.5 py-1 text-xs font-medium bg-success-100 text-success-800 rounded-full">
                        <i class="fa-solid fa-circle-check mr-1"></i> Customer Lama
                    </span>
                    @else
                    <span
                        class="inline-flex items-center px-2.5 py-1 text-xs font-medium bg-warning-100 text-warning-800 rounded-full">
                        <i class="fa-solid fa-user-plus mr-1"></i> Customer Baru
                    </span>
                    @endif
                </div>
                @endif
            </div>

            {{-- Produk Search --}}
            <div class="relative" x-data="{ open: false }">
                <label for="searchInput" class="block text-sm font-semibold text-gray-700 mb-2">
                    Produk
                </label>
                <input type="text" id="searchInput" x-ref="searchInput"
                    class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring focus:ring-primary-200 text-gray-800"
                    placeholder="Cari barang (kode / nama)" wire:model.live="search" wire:focus="focusSearch"
                    @focus="open = true" @blur="setTimeout(() => open = false, 150)">

                @if (!empty($produkList))
                <ul x-show="open"
                    class="absolute z-10 w-full bg-white border border-gray-200 rounded-lg shadow-md max-h-60 overflow-y-auto mt-1 divide-y divide-gray-100">
                    @foreach ($produkList as $produk)
                    <li wire:click="pilihProduk({{ $produk['id'] }})"
                        class="px-4 py-2 hover:bg-primary-50 cursor-pointer text-gray-700">
                        <i class="fa-solid fa-box mr-2 text-primary-500"></i>
                        {{ $produk['kode_barang'] }} — <span class="font-medium">{{ $produk['nama'] }}</span>
                    </li>
                    @endforeach
                </ul>
                @endif
            </div>
        </div>

        {{-- === RIGHT: Total Box === --}}
        <div
            class="col-span-4 flex flex-col items-center justify-center p-6 bg-gradient-to-br from-primary-50 to-primary-100 border border-primary-200 rounded-xl shadow-sm">
            <div class="text-sm uppercase text-gray-500 font-medium mb-2 tracking-wide">
                Total Penjualan
            </div>
            <div class="text-4xl font-extrabold text-gray-800">
                Rp {{ number_format($this->total, 0, ',', '.') }}
                <span class="text-sm font-medium align-super">,00</span>
            </div>
        </div>
    </div>

    {{-- ===================== --}}
    {{-- 🧾 Tabel Item Penjualan --}}
    {{-- ===================== --}}
    <div class="p-6 bg-white border border-gray-200 rounded-xl shadow-sm overflow-x-auto">
        <table class="w-full text-sm text-gray-800 border-collapse">
            <thead class="sticky top-0 bg-primary-50 text-xs uppercase text-gray-700 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 w-1/12 text-center">#</th>
                    <th class="px-4 py-3 text-left">Kode</th>
                    <th class="px-4 py-3 text-left">Nama Barang</th>
                    <th class="px-4 py-3 w-24 text-center">Jumlah</th>
                    <th class="px-4 py-3 text-right w-32">Harga Satuan</th>
                    <th class="px-4 py-3 text-right w-32">Subtotal</th>
                    <th class="px-4 py-3 w-10 text-center"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($cart as $i => $item)
                <tr class="odd:bg-gray-50 even:bg-white border-b border-gray-100 hover:bg-primary-50/50">
                    <td class="px-4 py-3 text-center">{{ $loop->iteration }}</td>
                    <td class="px-4 py-3">{{ $item['kode_barang'] }}</td>
                    <td class="px-4 py-3">{{ $item['nama'] }}</td>
                    <td class="px-4 py-3 text-center">
                        <input type="number"
                            class="w-20 text-center rounded-md border-gray-300 focus:border-primary-500 focus:ring focus:ring-primary-200"
                            min="1" max="{{ $item['stok'] }}" wire:model.live="cart.{{ $i }}.qty" x-data x-on:input="
                                    let val = parseInt($el.value);
                                    let max = {{ $item['stok'] }};
                                    if (isNaN(val)) val = null;
                                    if (val !== null && val > max) val = max;
                                    $el.value = val ?? '';
                                    $wire.set('cart.{{ $i }}.qty', val);
                                ">
                        <div class="text-xs text-gray-500 mt-1">Sisa: {{ $item['stok'] }}</div>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div x-data="{
                                formatted: '',
                                raw: '{{ $item['harga'] }}',
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
                                    $wire.set('cart.{{ $i }}.harga', this.raw);
                                }
                            }" x-init="formatted = formatRupiah(raw)">
                            <input type="text" x-model="formatted" x-on:input="updateValue($event)" inputmode="numeric"
                                class="w-full bg-gray-50 border border-gray-300 text-right rounded-md p-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                    </td>
                    <td class="px-4 py-3 text-right">
                        Rp {{ number_format($item['qty'] * $item['harga'], 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <button type="button" wire:click="removeItem({{ $i }})"
                            class="text-danger-600 hover:text-danger-800">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-6 text-center text-gray-500">
                        Belum ada item ditambahkan
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Tombol Simpan --}}
        @if (count($cart) > 0 && collect($cart)->every(fn($c) => $c['qty'] > 0))
        <div class="flex justify-end mt-6">
            <button type="button" wire:click="konfirmasiSimpan"
                class="inline-flex items-center bg-success-600 hover:bg-success-700 text-white font-semibold px-6 py-2 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-success-500">
                <i class="fa-solid fa-floppy-disk mr-2"></i> Simpan Penjualan
            </button>
        </div>
        @endif
    </div>

    {{-- ===================== --}}
    {{-- 💬 Modal Konfirmasi --}}
    {{-- ===================== --}}
    {{-- ===================== --}}
    {{-- MODAL KONFIRMASI PENJUALAN (Sekaligus Pilih Metode) --}}
    {{-- ===================== --}}
    <div x-data="{ open: @entangle('showConfirmModal') }" x-cloak x-show="open"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">

        <div x-show="open" x-transition.scale.origin.center.duration.200ms
            class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6 relative border border-gray-200">

            <h2 class="text-lg font-semibold text-gray-800 mb-4">
                Konfirmasi Penjualan
            </h2>

            <div class="space-y-2 text-sm text-gray-700">
                <div class="flex justify-between">
                    <span>Tanggal</span>
                    <span>{{ $tanggal ?? now()->format('Y-m-d') }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Total</span>
                    <span class="font-semibold text-gray-900">
                        Rp {{ number_format($totalPreview, 0, ',', '.') }}
                    </span>
                </div>
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
            <h3 class="text-lg font-semibold text-gray-800 mb-3">Yakin Simpan Penjualan Ini?</h3>

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