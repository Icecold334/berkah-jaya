<div class="space-y-6">

  {{-- 🔎 Filter --}}
  <div class="grid grid-cols-12 gap-3">
    <div class="col-span-2">
      <input type="date" wire:model.live="tanggal_awal"
        class="w-full text-sm rounded border-gray-300 focus:border-primary-500 focus:ring focus:ring-primary-200" />
    </div>
    <div class="col-span-2">
      <input type="date" wire:model.live="tanggal_akhir"
        class="w-full text-sm rounded border-gray-300 focus:border-primary-500 focus:ring focus:ring-primary-200" />
    </div>
    <div class="col-span-3">
      <select wire:model.live="akun_kas_id"
        class="w-full text-sm rounded border-gray-300 focus:border-primary-500 focus:ring focus:ring-primary-200">
        <option value="">Semua Akun Kas</option>
        @foreach($akunKas as $ak)
        <option value="{{ $ak->id }}">{{ $ak->nama }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-span-3">
      <select wire:model.live="kategori_id"
        class="w-full text-sm rounded border-gray-300 focus:border-primary-500 focus:ring focus:ring-primary-200">
        <option value="">Semua Kategori</option>
        @foreach($kategoriKas as $kat)
        <option value="{{ $kat->id }}">{{ $kat->nama }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-span-2">
      <input type="text" wire:model.live="search_keterangan" placeholder="Cari keterangan..."
        class="w-full text-sm rounded border-gray-300 focus:border-primary-500 focus:ring focus:ring-primary-200" />
    </div>
  </div>

  {{-- 📑 Tabel --}}
  <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
    <div class="flex justify-between items-center px-4 py-2 border-b">
      <h3 class="font-semibold text-gray-700 text-sm">Arus Kas</h3>

      <button wire:click="openModal"
        class="bg-primary-100 text-primary-800 text-sm font-medium px-3 py-1 rounded shadow-sm hover:bg-primary-200">
        <i class="fa-solid fa-plus mr-1"></i> Tambah Transaksi
      </button>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-sm text-center text-gray-700">
        <thead class="text-xs text-gray-700 uppercase bg-primary-50">
          <tr>
            <th class="px-4 py-2">Tanggal</th>
            <th class="px-4 py-2">Akun Kas</th>
            <th class="px-4 py-2">Tipe</th>
            <th class="px-4 py-2">Kategori</th>
            <th class="px-4 py-2">Jumlah</th>
            <th class="px-4 py-2">Keterangan</th>
            {{-- <th class="px-4 py-2">Sumber</th> --}}
          </tr>
        </thead>
        <tbody>
          @forelse($transaksiKas as $tk)
          <tr class="odd:bg-white even:bg-gray-50 border-b">
            <td class="px-4 py-2">{{ $tk->tanggal->format('d/m/Y') }}</td>
            <td class="px-4 py-2">{{ $tk->akunKas->nama }}</td>
            <td class="px-4 py-2">
              @if($tk->tipe === 'masuk')
              <span class="bg-success-100 text-success-800 text-xs font-medium px-2.5 py-0.5 rounded">
                Masuk
              </span>
              @else
              <span class="bg-danger-100 text-danger-800 text-xs font-medium px-2.5 py-0.5 rounded">
                Keluar
              </span>
              @endif
            </td>
            <td class="px-4 py-2">{{ $tk->kategori?->nama ?? '-' }}</td>
            <td class="px-4 py-2 text-right">Rp {{ number_format($tk->jumlah, 0, ',', '.') }}</td>
            <td class="px-4 py-2 text-left">{{ $tk->keterangan }}</td>
            {{-- <td class="px-4 py-2 text-xs text-gray-500">
              {{ class_basename($tk->sumber_type) }}#{{ $tk->sumber_id }}
            </td> --}}
          </tr>
          @empty
          <tr>
            <td colspan="7" class="py-4 text-gray-500">Tidak ada data</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- 📍 Pagination + Total --}}
    <div class="flex justify-between items-center px-4 py-2 border-t">
      {{ $transaksiKas->links() }}

      <span class="text-sm text-gray-600">
        Saldo Akhir: <strong>Rp {{ number_format($saldoAkhir, 0, ',', '.') }}</strong>
      </span>
    </div>
  </div>



  {{-- 🪟 Modal Tambah Transaksi --}}
  @if($showModal)
  <div class="fixed inset-0 bg-gray-900/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-1/2">
      <div class="flex justify-between items-center border-b px-6 py-3">
        <h2 class="font-bold text-lg">Tambah Transaksi Kas</h2>
        <button wire:click="closeModal" class="text-gray-500 hover:text-gray-700">&times;</button>
      </div>

      <div class="p-6 space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Tanggal</label>
            <input type="date" wire:model.live="form.tanggal"
              class="w-full text-sm rounded border-gray-300 focus:border-primary-500 focus:ring focus:ring-primary-200">
            @error('form.tanggal') <span class="text-danger-600 text-xs">{{ $message }}</span> @enderror
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Akun Kas</label>
            <select wire:model.live="form.akun_kas_id"
              class="w-full text-sm rounded border-gray-300 focus:border-primary-500 focus:ring focus:ring-primary-200">
              <option value="">-- Pilih Akun --</option>
              @foreach($akunKas as $ak)
              <option value="{{ $ak->id }}">{{ $ak->nama }}</option>
              @endforeach
            </select>
            @error('form.akun_kas_id') <span class="text-danger-600 text-xs">{{ $message }}</span> @enderror
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Tipe</label>
            <select wire:model.live="form.tipe"
              class="w-full text-sm rounded border-gray-300 focus:border-primary-500 focus:ring focus:ring-primary-200">
              <option value="">-- Pilih --</option>
              <option value="masuk">Masuk</option>
              <option value="keluar">Keluar</option>
            </select>
            @error('form.tipe') <span class="text-danger-600 text-xs">{{ $message }}</span> @enderror
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Kategori</label>
            <select wire:model.live="form.kategori_id" @disabled(empty($form['tipe'])) class="w-full text-sm rounded border-gray-300 focus:border-primary-500 focus:ring focus:ring-primary-200
          {{ empty($form['tipe']) ? 'cursor-not-allowed bg-gray-100 text-gray-500' : '' }}">
              <option value="">-- Pilih Kategori --</option>
              @foreach($kategoriKas->where('tipe', $form['tipe']) as $kat)
              <option value="{{ $kat->id }}">{{ $kat->nama }}</option>
              @endforeach
            </select>
            @error('form.kategori_id') <span class="text-danger-600 text-xs">{{ $message }}</span> @enderror
          </div>
        </div>

        <div x-data="{
        raw: @entangle('form.jumlah').live,
        display: '',
        format() {
            this.display = this.raw ? formatRupiah(this.raw.toString()) : '';
        }
    }" x-init="format()">
          <label class="block text-sm font-medium text-gray-700">Jumlah</label>

          {{-- input visible untuk user --}}
          <input type="text" x-model="display" x-on:input="
            raw = $event.target.value.replace(/[^0-9]/g,'');
            format();
        " class="w-full text-sm rounded border-gray-300 focus:border-primary-500 focus:ring focus:ring-primary-200">

          {{-- hidden input untuk Livewire --}}
          <input type="hidden" x-model="raw" wire:model="form.jumlah">

          @error('form.jumlah')
          <span class="text-danger-600 text-xs">{{ $message }}</span>
          @enderror
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Keterangan</label>
          <textarea wire:model.live="form.keterangan"
            class="w-full text-sm rounded border-gray-300 focus:border-primary-500 focus:ring focus:ring-primary-200"></textarea>
          @error('form.keterangan') <span class="text-danger-600 text-xs">{{ $message }}</span> @enderror
        </div>
      </div>

      <div class="flex justify-end gap-3 border-t px-6 py-3">
        <button wire:click="closeModal"
          class="bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm font-medium px-4 py-2 rounded">
          Batal
        </button>
        <button wire:click="save"
          class="bg-success-100 hover:bg-success-200 text-success-800 text-sm font-medium px-4 py-2 rounded shadow-sm">
          Simpan
        </button>
      </div>
    </div>
  </div>
  @endif
</div>