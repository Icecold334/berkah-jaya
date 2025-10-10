<?php

namespace Database\Seeders;

use Carbon\Carbon;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use App\Models\Produk;
use App\Models\AkunKas;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\KategoriKas;
use App\Models\TransaksiKas;
use App\Models\ItemPembelian;
use App\Models\ItemPenjualan;
use App\Models\PergerakanStok;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        $katPenjualan = KategoriKas::create(['tipe' => 'masuk', 'nama' => 'Penjualan']);
        $katPembelian = KategoriKas::create(['tipe' => 'keluar', 'nama' => 'Pembelian']);
        $kasToko = AkunKas::create([
            'nama' => 'Akun Kas 1',
            'tipe' => 'tunai',
            'saldo_awal' => 0
        ]);

        if (env('APP_DEBUG')) {
            // === 1. Produk ===
            $produkA = Produk::create([
                'nama' => 'Beras Premium 5kg',
                'slug' => 'beras-premium-5kg',
                'kode_barang' => 'BR001'
            ]);

            $produkB = Produk::create([
                'nama' => 'Minyak Goreng 1L',
                'slug' => 'minyak-goreng-1l',
                'kode_barang' => 'MG001'
            ]);

            // === 2. Supplier ===
            $supplier1 = Supplier::create([
                'nama' => 'PT Sumber Pangan',
                'alamat' => 'Jl. Raya Pasar 123',
                'telepon' => '08123456789',
                'npwp' => '1234567890'
            ]);

            $supplier2 = Supplier::create([
                'nama' => 'CV Minyak Sejahtera',
                'alamat' => 'Jl. Industri 45',
                'telepon' => '08987654321',
                'npwp' => '0987654321'
            ]);
            // === 3. Relasi Produk-Supplier ===
            $produkA->suppliers()->attach($supplier1->id, [
                'harga_beli' => 65000,
                'kena_pajak' => true,
                'tanggal_pembelian_terakhir' => Carbon::now()->subDays(5)
            ]);

            $produkB->suppliers()->attach($supplier2->id, [
                'harga_beli' => 14000,
                'kena_pajak' => false,
                'tanggal_pembelian_terakhir' => Carbon::now()->subDays(2)
            ]);


            $bankBCA = AkunKas::create([
                'nama' => 'Akun Kas 2',
                'tipe' => 'bank',
                'saldo_awal' => 1000000
            ]);

            // === 4. Kategori Kas ===
            $katInject = KategoriKas::create(['tipe' => 'masuk', 'nama' => 'Investment']);
            $katLainnya   = KategoriKas::create(['tipe' => 'keluar', 'nama' => 'Biaya Operasional']);

            // === 5. Customer ===
            $customer1 = Customer::create([
                'nama' => 'Budi Santoso',
                'telepon' => '0811223344',
                'alamat' => 'Jl. Melati No. 10'
            ]);

            $customer2 = Customer::create([
                'nama' => 'Siti Aminah',
                'telepon' => '0822334455',
                'alamat' => 'Jl. Mawar No. 25'
            ]);

            // === 6. Contoh Pembelian ===
            $pembelian = Pembelian::create([
                'no_faktur' => Pembelian::generateNoFaktur(),
                'supplier_id' => $supplier1->id,
                'tanggal' => Carbon::now()->subDays(3),
                'total' => 0, // akan dihitung ulang
                'keterangan' => 'Pembelian stok awal beras'
            ]);

            $itemPembelian = ItemPembelian::create([
                'pembelian_id' => $pembelian->id,
                'produk_id' => $produkA->id,
                'harga_beli' => 65000,
                'qty' => 10,
            ]);

            // // update total pembelian
            $pembelian->hitungTotal();

            // // pergerakan stok (masuk)
            PergerakanStok::create([
                'produk_id' => $produkA->id,
                'tanggal' => $pembelian->tanggal,
                'tipe' => 'masuk',
                'qty' => $itemPembelian->qty,
                'sumber_type' => Pembelian::class,
                'sumber_id' => $pembelian->id,
                'keterangan' => 'Pembelian dari ' . $supplier1->nama
            ]);

            // // transaksi kas (keluar)
            TransaksiKas::create([
                'akun_kas_id' => $kasToko->id,
                'tanggal' => $pembelian->tanggal,
                'tipe' => 'keluar',
                'kategori_id' => $katPembelian->id,
                'jumlah' => $pembelian->total,
                'keterangan' => 'Pembayaran pembelian',
                'sumber_type' => Pembelian::class,
                'sumber_id' => $pembelian->id
            ]);

            // // === 7. Contoh Penjualan ===
            $penjualan = Penjualan::create([
                'customer_id' => $customer1->id,
                'no_struk' => fake()->numerify('NNN-###-###-###'),
                'tanggal' => Carbon::now()->subDays(1),
                'total' => 0 // akan dihitung ulang
            ]);

            $itemPenjualan = ItemPenjualan::create([
                'penjualan_id' => $penjualan->id,
                'produk_id' => $produkA->id,
                'harga_jual' => 67000, // contoh (harga beli 65k + 2%)
                'qty' => 2,
                'subtotal' => 134000
            ]);

            $penjualan->hitungTotal();

            // // pergerakan stok (keluar)
            PergerakanStok::create([
                'produk_id' => $produkA->id,
                'tanggal' => $penjualan->tanggal,
                'tipe' => 'keluar',
                'qty' => $itemPenjualan->qty,
                'sumber_type' => Penjualan::class,
                'sumber_id' => $penjualan->id,
                'keterangan' => 'Penjualan ke ' . $customer1->nama
            ]);

            // // transaksi kas (masuk)
            TransaksiKas::create([
                'akun_kas_id' => $kasToko->id,
                'tanggal' => $penjualan->tanggal,
                'tipe' => 'masuk',
                'kategori_id' => $katPenjualan->id,
                'jumlah' => $penjualan->total,
                'keterangan' => 'Penerimaan dari penjualan',
                'sumber_type' => Penjualan::class,
                'sumber_id' => $penjualan->id
            ]);
        }

        $this->call([
            SettingSeeder::class,
        ]);
    }
}
