<div class="">
    <div class="grid grid-cols-12 gap-6 mb-6">
        <div class="p-6 bg-primary-50 border border-primary-200 rounded-lg shadow-sm text-gray-800 col-span-8">
            <div class="relative" x-data="{ open: false }">
                <input type="text" x-ref="searchInput" id="searchInput"
                    class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring focus:ring-primary-200"
                    placeholder="Cari barang (kode / nama)" wire:model.live="search" wire:focus="focusSearch"
                    @focus="open = true" @blur="setTimeout(() => open = false, 150)">

                @if (!empty($produkList))
                    <ul x-show="open"
                        class="absolute z-10 w-full bg-white border border-gray-200 rounded-lg shadow-md max-h-60 overflow-y-auto mt-1">
                        @foreach ($produkList as $produk)
                            <li wire:click="pilihProduk({{ $produk['id'] }})"
                                class="px-4 py-2 hover:bg-primary-100 cursor-pointer">
                                {{ $produk['kode_barang'] }} - {{ $produk['nama'] }}
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
        <div class="p-6 bg-primary-50 border border-primary-200 rounded-lg shadow-sm text-gray-800 col-span-4">
            <div class="font-bold text-center text-3xl">
                Rp {{ number_format($this->total, 0, ',', '.') }}<span class="align-super text-sm">,00</span>
            </div>
        </div>
    </div>
    <div class="p-6 bg-primary-50 border border-primary-200 rounded-lg shadow-sm text-gray-800 col-span-8">
        <table class="w-full text-sm text-center text-gray-900">
            <thead class="text-xs text-gray-700 uppercase bg-primary-50">
                <tr>
                    <th class="px-6 py-3 w-1/12">#</th>
                    <th class="px-6 py-3">Kode Barang</th>
                    <th class="px-6 py-3">Nama Barang</th>
                    <th class="px-6 py-3">Jumlah</th>
                    <th class="px-6 py-3">Harga satuan</th>
                    <th class="px-6 py-3">Subtotal</th>
                    <th class="px-6 py-3 w-1/12"></th> <!-- kolom tombol hapus -->
                </tr>
            </thead>
            <tbody>
                @forelse($cart as $i => $item)
                    <tr class="odd:bg-gray-50 even:bg-primary-50 border-b border-gray-200">
                        <td class="px-6 py-4">{{ $loop->iteration }}</td>
                        <td class="px-6 py-4">{{ $item['kode_barang'] }}</td>
                        <td class="px-6 py-4">{{ $item['nama'] }}</td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col items-center">
                                <input type="number"
                                    class="w-20 text-center rounded border-gray-300 focus:border-primary-500 focus:ring focus:ring-primary-200"
                                    min="1" max="{{ $item['stok'] }}"
                                    wire:model.live="cart.{{ $i }}.qty" x-data
                                    x-on:input="
  let val = parseInt($el.value);
  let max = {{ $item['stok'] }};
  if (isNaN(val)) val = null; // kalau kosong/null
  if (val !== null && val > max) val = max;
  $el.value = val ?? '';
  $wire.set('cart.{{ $i }}.qty', val);
">
                                <span class="text-xs text-gray-500 mt-1">Sisa stok: {{ $item['stok'] }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-left">
                            <div x-data="{
                                formatted: '',
                                raw: @entangle('cart.' . $i . '.harga').live,
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
                                <input type="text" x-model="formatted" x-on:input="updateValue($event)"
                                    inputmode="numeric" placeholder="Rp 0"
                                    class="bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2 text-right font-medium tracking-wide" />
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            Rp {{ number_format($item['qty'] * $item['harga'], 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4">
                            <button type="button" wire:click="removeItem({{ $i }})"
                                class="bg-danger-100 text-danger-800 text-sm font-medium px-2.5 py-1 rounded-sm">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-4 text-gray-700">Belum ada item</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if (count($cart) > 0 && collect($cart)->every(fn($c) => $c['qty'] > 0))
            <button type="button" wire:click="konfirmasiSimpan"
                class="mt-6 bg-success-600 hover:bg-success-700 text-white font-semibold px-6 py-2 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-success-500">
                <i class="fa-solid fa-floppy-disk mr-1"></i> Simpan Penjualan
            </button>
        @endif
    </div>

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
