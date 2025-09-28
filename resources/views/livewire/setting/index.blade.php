<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    {{-- CARD untuk angka (presentase, pajak, profit) --}}
    {{-- @foreach (['presentase', 'pajak', 'profit'] as $key) --}}
    @foreach (['presentase'] as $key)
        <div class="bg-white shadow-lg rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">{{ ucfirst($key) }}</h3>
            <div class="mb-4">
                <label for="{{ $key }}" class="block text-sm font-medium text-gray-700">
                    {{ ucfirst($key) }} (%)
                </label>
                <input type="number" id="{{ $key }}" wire:model="settings.{{ $key }}" min="0"
                    max="100" step="0.01"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm 
                              focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                @error("settings.$key")
                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <button wire:click="save('{{ $key }}')"
                class="w-full py-2 px-4 bg-primary-500 hover:bg-primary-600 text-white rounded-lg shadow">
                Simpan
            </button>
        </div>
    @endforeach

    {{-- CARD untuk Akun Penjualan --}}
    <div class="bg-white shadow-lg rounded-lg p-6">
        <h3 class="text-lg font-semibold mb-4">Akun Penjualan</h3>
        <div class="mb-4">
            <label for="akun_penjualan" class="block text-sm font-medium text-gray-700">
                Pilih Akun Kas
            </label>
            <select id="akun_penjualan" wire:model="settings.akun_penjualan"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm 
                       focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                <option value="">-- Pilih Akun --</option>
                @foreach ($akunKasOptions as $akun)
                    <option value="{{ $akun->id }}">{{ $akun->nama }}</option>
                @endforeach
            </select>
            @error('settings.akun_penjualan')
                <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>
        <button wire:click="save('akun_penjualan')"
            class="w-full py-2 px-4 bg-primary-500 hover:bg-primary-600 text-white rounded-lg shadow">
            Simpan
        </button>
    </div>
</div>
