<div class="">
    <div class="grid grid-cols-2 mb-6">
        <div class="font-semibold text-3xl">Daftar Supplier</div>
        <div class="text-right">
            <button wire:click="openModal" class="bg-primary-600 text-white px-4 py-2 rounded-md hover:bg-primary-700">
                + Tambah Supplier
            </button>
        </div>
    </div>

    {{-- Search --}}
    <div class="mb-3">
        <input type="text" wire:model.live="search" placeholder="Cari supplier..."
            class="w-1/3 px-3 py-2 border rounded-md" />
    </div>

    {{-- Table --}}
    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
        <table class="w-full text-sm text-center text-gray-900">
            <thead class="text-xs text-gray-700 uppercase bg-primary-50">
                <tr>
                    <th class="px-6 py-3 w-1/12">#</th>
                    <th class="px-6 py-3">Nama</th>
                    <th class="px-6 py-3">Alamat</th>
                    <th class="px-6 py-3">Nomor Telepon</th>
                    <th class="px-6 py-3 w-1/12"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers as $i => $supplier)
                    <tr class="odd:bg-white even:bg-gray-50 border-b border-gray-200">
                        <td class="px-6 py-4">{{ $suppliers->firstItem() + $i }}</td>
                        <td class="px-6 py-4 text-left font-medium">{{ $supplier->nama }}</td>
                        <td class="px-6 py-4 text-left">{{ $supplier->alamat }}</td>
                        <td class="px-6 py-4 text-left">{{ $supplier->telepon }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <!-- Tombol Edit -->
                                <button wire:click="openModal({{ $supplier->id }})"
                                    class="bg-info-100 text-info-800 text-sm font-medium p-2 rounded-sm hover:bg-info-200">
                                    <i class="fa-solid fa-pen"></i>
                                </button>

                                <!-- Tombol Delete -->
                                <button wire:click="confirmDelete({{ $supplier->id }})"
                                    class="bg-danger-100 text-danger-800 text-sm font-medium p-2 rounded-sm hover:bg-danger-200">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-4">Tidak ada data supplier</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-3">
        {{ $suppliers->links() }}
    </div>

    {{-- Modal --}}
    <div x-data="{ open: false }" x-on:open-modal.window="open = true" x-on:close-modal.window="open = false">
        <div x-show="open"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/25 bg-opacity-50 backdrop-blur-md">
            <div class="bg-white rounded-md p-6 w-1/3">
                <h2 class="text-xl font-semibold mb-4">
                    {{ $supplierId ? 'Edit Supplier' : 'Tambah Supplier' }}
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
                        <label class="block text-sm">Alamat</label>
                        <input type="text" wire:model="alamat" class="w-full border rounded-md px-3 py-2">
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm">Nomor Telepon</label>
                        <input type="text" wire:model="telepon" class="w-full border rounded-md px-3 py-2">
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
</div>
