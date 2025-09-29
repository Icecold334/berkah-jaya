<div>
    <div class="">
        <div class="grid grid-cols-2 mb-6">
            <input type="text" wire:model.live="search" placeholder="Cari kategori kas..."
                class="w-1/3 px-3 py-2 border rounded-md" />
            <div class="text-right">
                <button wire:click="openModal"
                    class="bg-primary-600 text-white px-4 py-2 rounded-md hover:bg-primary-700">
                    + Tambah Kategori
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
                        <th class="px-6 py-3">Tipe</th>
                        <th class="px-6 py-3 w-1/12"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kategoriKas as $i => $kategori)
                    <tr class="odd:bg-white even:bg-gray-50 border-b border-gray-200">
                        <td class="px-6 py-4">{{ $kategoriKas->firstItem() + $i }}</td>
                        <td class="px-6 py-4 text-left font-medium">{{ $kategori->nama }}</td>
                        <td class="px-6 py-4 text-left capitalize">
                            @if ($kategori->tipe == 'masuk')
                            <span class="bg-success-100 text-success-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                Masuk
                            </span>
                            @else
                            <span class="bg-danger-100 text-danger-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                Keluar
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <!-- Tombol Edit -->
                                <button wire:click="openModal({{ $kategori->id }})"
                                    class="bg-info-100 text-info-800 text-sm font-medium p-2 rounded-sm hover:bg-info-200">
                                    <i class="fa-solid fa-pen"></i>
                                </button>

                                <!-- Tombol Delete -->
                                <button wire:click="confirmDelete({{ $kategori->id }})"
                                    class="bg-danger-100 text-danger-800 text-sm font-medium p-2 rounded-sm hover:bg-danger-200">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-4">Tidak ada data kategori kas</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-3">
            {{ $kategoriKas->links() }}
        </div>

        {{-- Modal --}}
        <div x-data="{ open: false }" x-on:open-modal.window="open = true" x-on:close-modal.window="open = false">
            <div x-show="open"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/25 bg-opacity-50 backdrop-blur-md">
                <div class="bg-white rounded-md p-6 w-1/3">
                    <h2 class="text-xl font-semibold mb-4">
                        {{ $kategoriId ? 'Edit Kategori' : 'Tambah Kategori' }}
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
                            <label class="block text-sm">Tipe</label>
                            <select wire:model="tipe" class="w-full border rounded-md px-3 py-2">
                                <option value="">-- Pilih Tipe --</option>
                                <option value="masuk">Masuk</option>
                                <option value="keluar">Keluar</option>
                            </select>
                            @error('tipe')
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
    </div>

</div>