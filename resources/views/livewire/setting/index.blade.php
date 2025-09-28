<div class="max-w-6xl mx-auto mt-8">
    <h2 class="text-xl font-semibold mb-6">Pengaturan Aplikasi</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($settings as $key => $value)
            <div class="bg-white shadow-lg rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">{{ ucfirst($key) }}</h3>
                <div class="mb-4">
                    <label for="{{ $key }}" class="block text-sm font-medium text-gray-700">
                        {{ ucfirst($key) }} (%)
                    </label>
                    <input type="number" id="{{ $key }}" wire:model="settings.{{ $key }}"
                        min="0" max="100" step="0.01"
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
    </div>
</div>
