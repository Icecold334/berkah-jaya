<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        @php
            $titles = [
                'dashboard' => 'Dashboard',
                'akun-kas.index' => 'Akun Kas',
                'kategori-kas.index' => 'Kategori Kas',
                'pembelian.index' => 'Pembelian',
                'penjualan.index' => 'Penjualan',
                'supplier.index' => 'Supplier',
                'stok.index' => 'Stok',
                'kas.index' => 'Kas',
                'laporan.index' => 'Laporan',
            ];
            $current = Route::currentRouteName();
        @endphp

        {{ $titles[$current] ?? 'Berkah Jaya' }} | Berkah Jaya
    </title>
    {{-- <script src="https://cdn.tailwindcss.com"></script> --}}
    <!-- Flowbite -->
    {{--
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" /> --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gradient-to-br from-primary-100 to-primary-200 min-h-screen">

    <!-- NAVBAR -->
    <x-navbar />

    <!-- SIDEBAR -->
    <x-sidebar />

    <!-- MAIN CONTENT -->
    <div class="p-4 sm:ml-64">
        <div class="mt-16">
            {{ $slot }}
        </div>
    </div>

    <!-- Script Flowbite -->
    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
    <script>
        // fungsi reusable format rupiah
        function formatRupiah(angka, prefix = 'Rp ') {
            // hapus semua karakter kecuali angka & koma
            let number_string = angka.replace(/[^,\d]/g, '').toString();

            // kalau kosong → balikin string kosong
            if (number_string.length === 0) {
                return '';
            }

            // buang leading zero (contoh: 00012 → 12)
            number_string = number_string.replace(/^0+/, '');

            let split = number_string.split(','),
                sisa = split[0].length % 3,
                rupiah = split[0].substr(0, sisa),
                ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
            return prefix + rupiah;
        }
    </script>
    @stack('scripts')
</body>

</html>
