<div>
    <div class="">
        <div class="grid grid-cols-2 mb-6">
            <input type="text" wire:model.live="search" placeholder="Cari customer..."
                class="w-1/3 px-3 py-2 border rounded-md" />
            <div class="text-right">
                <button wire:click="openModal"
                    class="bg-primary-600 text-white px-4 py-2 rounded-md hover:bg-primary-700">
                    + Tambah Customer
                </button>
            </div>
        </div>

        {{-- Table --}}
        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
            <table class="w-full text-sm text-center text-gray-900">
                <thead class="text-xs text-gray-700 uppercase bg-primary-50">
                    <tr>
                        <th class="px-6 py-3 w-1/12">#</th>
                        <th class="px-6 py-3">Nama</th>
                        <th class="px-6 py-3">Telepon</th>
                        <th class="px-6 py-3">Alamat</th>
                        <th class="px-6 py-3 w-1/12"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $i => $cust)
                        <tr class="odd:bg-white even:bg-gray-50 border-b border-gray-200">
                            <td class="px-6 py-4">{{ $customers->firstItem() + $i }}</td>
                            <td class="px-6 py-4 text-left font-medium">{{ $cust->nama }}</td>
                            <td class="px-6 py-4">{{ $cust->telepon }}</td>
                            <td class="px-6 py-4 text-left">{{ $cust->alamat }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <!-- Eye -->
                                    <button wire:click="showDetail({{ $cust->id }})" title="Lihat daftar pembelian"
                                        class="bg-success-100 text-success-800 text-sm font-medium p-2 rounded-sm hover:bg-success-200">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>

                                    <!-- Edit -->
                                    <button wire:click="openModal({{ $cust->id }})" title="Edit customer"
                                        class="bg-info-100 text-info-800 text-sm font-medium p-2 rounded-sm hover:bg-info-200">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>

                                    <!-- Delete -->
                                    <button wire:click="confirmDelete({{ $cust->id }})" title="Hapus customer"
                                        class="bg-danger-100 text-danger-800 text-sm font-medium p-2 rounded-sm hover:bg-danger-200">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-4">Tidak ada data customer</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-3">
            {{ $customers->links() }}
        </div>

        {{-- Modal --}}
        <div x-data="{ open: false }" x-cloak x-on:open-modal.window="open = true"
            x-on:close-modal.window="open = false">
            <div x-show="open"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/25 bg-opacity-50 backdrop-blur-md">
                <div class="bg-white rounded-md p-6 w-1/3">
                    <h2 class="text-xl font-semibold mb-4">
                        {{ $customerId ? 'Edit Customer' : 'Tambah Customer' }}
                    </h2>
                    <form wire:submit.prevent="save">
                        <div class="mb-3">
                            <label class="block text-sm">Nama</label>
                            <input type="text" wire:model="nama" class="w-full border rounded-md px-3 py-2">
                            @error('nama')
                                <span class="text-danger-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="block text-sm">Telepon</label>
                            <input type="text" wire:model="telepon" class="w-full border rounded-md px-3 py-2">
                            @error('telepon')
                                <span class="text-danger-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="block text-sm">Alamat</label>
                            <textarea wire:model="alamat" class="w-full border rounded-md px-3 py-2"></textarea>
                            @error('alamat')
                                <span class="text-danger-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="text-right">
                            <button type="button" @click="open=false"
                                class="bg-gray-300 px-4 py-2 rounded-md">Batal</button>
                            <button type="submit"
                                class="bg-primary-600 text-white px-4 py-2 rounded-md hover:bg-primary-700">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Modal Detail Produk Customer --}}
        <div x-data="{ open: false }" x-cloak x-on:open-detail-modal.window="open = true"
            x-on:close-modal.window="open = false">

            <div x-show="open"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/25 backdrop-blur-md">

                <div class="bg-white rounded-md p-6 w-3/4 max-h-[80vh] overflow-y-auto">
                    <h2 class="text-xl font-semibold mb-4">
                        Produk Pernah Dibeli - {{ $detailCustomer?->nama }}
                    </h2>

                    <table class="w-full text-sm text-left text-gray-900 border">
                        <thead class="bg-gray-100 text-xs uppercase">
                            <tr>
                                <th class="px-4 py-2">Nama Produk</th>
                                <th class="px-4 py-2 text-right">Harga Terbaru</th>
                                <th class="px-4 py-2 text-center">Total Qty Dibeli</th>
                                <th class="px-4 py-2 text-center"></th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($daftarBarang as $barang)
                                <tr class="border-b">
                                    <td class="px-4 py-2">{{ $barang['nama_produk'] }}</td>
                                    <td class="px-4 py-2 text-right">
                                        Rp {{ number_format($barang['harga_terbaru'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-2 text-center">{{ $barang['total_qty'] }}</td>
                                    <td class="px-4 py-2 text-center">
                                        <button wire:click="showItemDetail('{{ $barang['produk_id'] }}')"
                                            class="bg-primary-100 text-primary-800 text-xs font-medium px-2 py-1 rounded-sm hover:bg-primary-200">
                                            Detail Transaksi
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 italic text-gray-500">
                                        Customer belum memiliki riwayat pembelian produk
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="text-right mt-4">
                        <button type="button" @click="open=false"
                            class="bg-gray-300 px-4 py-2 rounded-md">Tutup</button>
                    </div>
                </div>
            </div>
        </div>



        {{-- Modal Detail Item --}}
        {{-- Modal Detail Item --}}
        <div x-data="{ open: false }" x-cloak x-on:open-item-modal.window="open = true"
            x-on:close-item-modal.window="open = false">

            <div x-show="open"
                class="fixed inset-0 z-[60] flex items-center justify-center bg-black/25 backdrop-blur-md">

                <div class="bg-white rounded-md p-6 w-2/3 max-h-[80vh] overflow-y-auto">
                    <h2 class="text-lg font-semibold mb-3">
                        Riwayat Transaksi - {{ $detailPenjualan['nama_produk'] ?? '' }}
                    </h2>

                    @if (!empty($detailItems))
                        <table class="w-full text-sm text-left text-gray-900 border">
                            <thead class="bg-gray-100 text-xs uppercase">
                                <tr>
                                    <th class="px-4 py-2">Tanggal</th>
                                    <th class="px-4 py-2">No Struk</th>
                                    <th class="px-4 py-2 text-center">Qty</th>
                                    <th class="px-4 py-2 text-right">Harga</th>
                                    <th class="px-4 py-2 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($detailItems as $item)
                                    <tr class="border-b">
                                        <td class="px-4 py-2">{{ $item['tanggal'] }}</td>
                                        <td class="px-4 py-2">{{ $item['no_struk'] }}</td>
                                        <td class="px-4 py-2 text-center">{{ $item['qty'] }}</td>
                                        <td class="px-4 py-2 text-right">
                                            Rp {{ number_format($item['harga'], 0, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-2 text-right">
                                            Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="font-semibold bg-gray-50">
                                    <td colspan="4" class="px-4 py-2 text-right">Total</td>
                                    <td class="px-4 py-2 text-right">
                                        Rp {{ number_format($detailPenjualan['total'] ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    @else
                        <p class="text-center text-gray-600 py-4 italic">
                            Tidak ada transaksi untuk produk ini.
                        </p>
                    @endif

                    <div class="text-right mt-4">
                        <button type="button" @click="open=false"
                            class="bg-gray-300 px-4 py-2 rounded-md">Tutup</button>
                    </div>
                </div>
            </div>
        </div>


    </div>
</div>
