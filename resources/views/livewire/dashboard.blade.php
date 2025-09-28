<div class="p-4 space-y-6">

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded shadow text-center">
            <p class="text-gray-500">Penjualan Hari Ini</p>
            <h2 class="text-2xl font-bold text-green-600">
                Rp {{ number_format($summary['penjualan_hari_ini'], 0, ',', '.') }}
            </h2>
        </div>
        <div class="bg-white p-4 rounded shadow text-center">
            <p class="text-gray-500">Pembelian Hari Ini</p>
            <h2 class="text-2xl font-bold text-red-600">
                Rp {{ number_format($summary['pembelian_hari_ini'], 0, ',', '.') }}
            </h2>
        </div>
        <div class="bg-white p-4 rounded shadow text-center">
            <p class="text-gray-500">Kas Masuk</p>
            <h2 class="text-2xl font-bold text-blue-600">
                Rp {{ number_format($summary['kas_masuk_hari_ini'], 0, ',', '.') }}
            </h2>
        </div>
        <div class="bg-white p-4 rounded shadow text-center">
            <p class="text-gray-500">Kas Keluar</p>
            <h2 class="text-2xl font-bold text-orange-600">
                Rp {{ number_format($summary['kas_keluar_hari_ini'], 0, ',', '.') }}
            </h2>
        </div>
    </div>

    <!-- Charts in 1 Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white p-4 rounded shadow max-h-80">
            <h2 class="font-semibold mb-2">Penjualan & Pembelian (7 Hari Terakhir)</h2>
            <div class="h-72">
                <canvas id="chartSales"></canvas>
            </div>
        </div>

        <div class="bg-white p-4 rounded shadow max-h-80">
            <h2 class="font-semibold mb-2">Kas Masuk & Keluar (7 Hari Terakhir)</h2>
            <div class="h-72">
                <canvas id="chartCash"></canvas>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const chartData = @json($chartData);

        new Chart(document.getElementById('chartSales'), {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [
                    { 
                        label: 'Penjualan', 
                        data: chartData.penjualan, 
                        borderWidth: 2, 
                        borderColor: 'green',
                        backgroundColor: 'rgba(0,128,0,0.1)',
                        fill: true,
                        tension: 0.3 // <<--- lengkungan tipis
                    },
                    { 
                        label: 'Pembelian', 
                        data: chartData.pembelian, 
                        borderWidth: 2, 
                        borderColor: 'red',
                        backgroundColor: 'rgba(255,0,0,0.1)',
                        fill: true,
                        tension: 0.3 // <<--- lengkungan tipis
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Grafik Kas Masuk & Keluar (Bar Chart dengan sudut rounded)
        new Chart(document.getElementById('chartCash'), {
            type: 'bar',
            data: {
                labels: chartData.labels,
                datasets: [
                    { 
                        label: 'Kas Masuk', 
                        data: chartData.kas_masuk, 
                        backgroundColor: 'blue',
                        borderRadius: 6 // <<--- kasih rounded bar
                    },
                    { 
                        label: 'Kas Keluar', 
                        data: chartData.kas_keluar, 
                        backgroundColor: 'orange',
                        borderRadius: 6 // <<--- kasih rounded bar
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
</script>
@endpush