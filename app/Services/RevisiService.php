<?php

namespace App\Services;

use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\TransaksiKas;
use App\Models\ItemPembelian;
use App\Models\ItemPenjualan;
use App\Models\PergerakanStok;
use App\Models\ProdukSupplier;
use Illuminate\Support\Facades\DB;

class RevisiService
{
  public static function revisiTransaksi(string $tipe, $transaksiLama, array $dataBaru)
  {
    return DB::transaction(function () use ($tipe, $transaksiLama, $dataBaru) {

      $isPembelian = $tipe === 'pembelian';
      $Model = $isPembelian ? Pembelian::class : Penjualan::class;

      $stokRollbackTipe = $isPembelian ? 'keluar' : 'masuk';
      $stokBaruTipe = $isPembelian ? 'masuk' : 'keluar';
      $kasRollbackTipe = $isPembelian ? 'masuk' : 'keluar';
      $kasBaruTipe = $isPembelian ? 'keluar' : 'masuk';

      /*
       | =====================================================
       | 1. HITUNG QTY PEMBELIAN YANG SUDAH KEPAKAI JUAL
       | =====================================================
       */
      $stokTerpakai = PergerakanStok::where('sumber_type', Penjualan::class)
        ->whereIn('produk_id', $transaksiLama->items->pluck('produk_id'))
        ->where('tipe', 'keluar')
        ->get()
        ->groupBy(fn($x) => $x->produk_id . '-' . ($x->kena_pajak ? 'P' : 'TP'))
        ->map(fn($g) => $g->sum('qty'));

      /*
       | =====================================================
       | 2. ROLLBACK STOK PEMBELIAN (HANYA SISA)
       | =====================================================
       */
      foreach ($transaksiLama->items as $item) {

        $key = $item->produk_id . '-' . ($item->kena_pajak ? 'P' : 'TP');
        $qtyTerpakai = $stokTerpakai[$key] ?? 0;
        $sisa = max(0, $item->qty - $qtyTerpakai);
        if ($sisa > 0 && false) {
          PergerakanStok::create([
            'produk_id' => $item->produk_id,
            'produk_supplier_id' => $item->produk_supplier_id,
            'tanggal' => now(),
            'tipe' => $stokRollbackTipe,
            'qty' => $sisa,
            'kena_pajak' => $item->kena_pajak,
            'sumber_type' => $Model,
            'sumber_id' => $transaksiLama->id,
            'keterangan' => 'Rollback sisa stok pembelian direvisi',
          ]);
        }
      }

      /*
       | =====================================================
       | 3. ROLLBACK & PINDAHKAN PENJUALAN KE PAJAK BARU
       | =====================================================
       */
      $penjualanStok = PergerakanStok::where('sumber_type', Penjualan::class)
        ->whereIn('produk_id', $transaksiLama->items->pluck('produk_id'))
        ->where('tipe', 'keluar')
        ->get();


      foreach ($penjualanStok as $ps) {
        // dd(collect($dataBaru['items'])->where('produk_id', $ps->produk_id)->first()['harga_beli']);
        $produkSupplierOld = ProdukSupplier::updateOrCreate(
          [
            'produk_id' => $ps->produk_id,
            'supplier_id' => $dataBaru['supplier_id'],
            'kena_pajak' => $transaksiLama->kena_pajak ?? false,
          ],
          [
            'harga_beli' => collect($dataBaru['items'])->where('produk_id', $ps->produk_id)->first()['harga_beli'],
            'tanggal_pembelian_terakhir' => now(),
          ]
        );

        // rollback stok penjualan lama
        PergerakanStok::create([
          'produk_id' => $ps->produk_id,
          'produk_supplier_id' => $produkSupplierOld->id,
          'tanggal' => now(),
          'tipe' => 'masuk',
          'qty' => $ps->qty,
          'kena_pajak' => $ps->kena_pajak,
          'sumber_type' => Penjualan::class,
          'sumber_id' => $ps->sumber_id,
          'keterangan' => 'Rollback penjualan akibat revisi pembelian',
        ]);

        $produkSupplierNew = ProdukSupplier::updateOrCreate(
          [
            'produk_id' => $ps->produk_id,
            'supplier_id' => $dataBaru['supplier_id'],
            'kena_pajak' => $transaksiLama->kena_pajak ?? false,
          ],
          [
            'harga_beli' => collect($dataBaru['items'])->where('produk_id', $ps->produk_id)->first()['harga_beli'],
            'tanggal_pembelian_terakhir' => now(),
          ]
        );
        // keluarkan ulang stok pakai pajak baru
        PergerakanStok::create([
          'produk_id' => $ps->produk_id,
          'produk_supplier_id' => $produkSupplierNew->id,
          'tanggal' => now(),
          'tipe' => 'keluar',
          'qty' => $ps->qty,
          'kena_pajak' => $dataBaru['kena_pajak'] ?? false,
          'sumber_type' => Penjualan::class,
          'sumber_id' => $ps->sumber_id,
          'keterangan' => 'Penjualan dialihkan ke stok hasil revisi',
        ]);
      }

      /*
       | =====================================================
       | 4. ROLLBACK KAS
       | =====================================================
       */
      $jumlahKas = $transaksiLama->transaksiKas->sum('jumlah');
      if ($jumlahKas > 0) {
        TransaksiKas::create([
          'akun_kas_id' => $dataBaru['akun_kas_id'] ?? 1,
          'tanggal' => now(),
          'tipe' => $kasRollbackTipe,
          'kategori_id' => 4,
          'jumlah' => $jumlahKas,
          'keterangan' => 'Rollback revisi ' . $tipe,
          'sumber_type' => $Model,
          'sumber_id' => $transaksiLama->id,
        ]);
      }

      /*
       | =====================================================
       | 5. TANDAI TRANSAKSI LAMA
       | =====================================================
       */
      $transaksiLama->update(['status' => 'direvisi']);

      /*
       | =====================================================
       | 6. TRANSAKSI BARU
       | =====================================================
       */
      $kolomUtama = $isPembelian ? 'supplier_id' : 'customer_id';

      $transaksiBaru = $Model::create([
        $kolomUtama => $dataBaru[$kolomUtama],
        'tanggal' => $dataBaru['tanggal'],
        'total' => $dataBaru['total'],
        'kena_pajak' => $dataBaru['kena_pajak'] ?? false,
        'keterangan' => $dataBaru['keterangan'] ?? null,
        'status' => 'aktif',
        'revisi_dari_id' => $transaksiLama->id,
      ]);

      /*
       | =====================================================
       | 7. ITEM & STOK BARU
       | =====================================================
       */
      $ItemModel = $isPembelian ? ItemPembelian::class : ItemPenjualan::class;

      foreach ($dataBaru['items'] as $item) {

        $ItemModel::create(array_merge($item, [
          $isPembelian ? 'pembelian_id' : 'penjualan_id' => $transaksiBaru->id,
        ]));

        // PergerakanStok::create([
        //   'produk_id' => $item['produk_id'],
        //   'produk_supplier_id' => $item['produk_supplier_id'] ?? null,
        //   'tanggal' => $dataBaru['tanggal'],
        //   'tipe' => $stokBaruTipe,
        //   'qty' => $item['qty'],
        //   'kena_pajak' => $item['kena_pajak'] ?? false,
        //   'sumber_type' => $Model,
        //   'sumber_id' => $transaksiBaru->id,
        //   'keterangan' => 'Stok hasil revisi ' . $tipe,
        // ]);
      }

      /*
       | =====================================================
       | 8. KAS BARU
       | =====================================================
       */
      foreach ($transaksiLama->transaksiKas as $kas) {
        TransaksiKas::create([
          'akun_kas_id' => $dataBaru['akun_kas_id'] ?? 1,
          'tanggal' => $dataBaru['tanggal'],
          'tipe' => $kasBaruTipe,
          'jumlah' => $kas->jumlah,
          'kategori_id' => 3,
          'keterangan' => ucfirst($tipe) . ' hasil revisi',
          'sumber_type' => $Model,
          'sumber_id' => $transaksiBaru->id,
        ]);
      }

      return $transaksiBaru;
    });
  }
}
