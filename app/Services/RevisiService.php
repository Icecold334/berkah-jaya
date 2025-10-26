<?php

namespace App\Services;

use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\ItemPembelian;
use App\Models\ItemPenjualan;
use App\Models\KategoriKas;
use App\Models\PergerakanStok;
use App\Models\TransaksiKas;
use Illuminate\Support\Facades\DB;

class RevisiService
{
  /**
   * Revisi transaksi modular untuk pembelian / penjualan.
   */
  public static function revisiTransaksi(string $tipe, $transaksiLama, array $dataBaru)
  {
    return DB::transaction(function () use ($tipe, $transaksiLama, $dataBaru) {

      // tentukan arah stok & kas berdasarkan tipe
      $isPembelian = $tipe === 'pembelian';
      $Model = $isPembelian ? Pembelian::class : Penjualan::class;
      // $itemRelation = $isPembelian ? 'itemPembelians' : 'itemPenjualans';

      // arah stok & kas
      $stokRollbackTipe = $isPembelian ? 'keluar' : 'masuk';
      $stokBaruTipe     = $isPembelian ? 'masuk' : 'keluar';
      $kasRollbackTipe  = $isPembelian ? 'masuk' : 'keluar';
      $kasBaruTipe      = $isPembelian ? 'keluar' : 'masuk';
      // 1️⃣ rollback stok
      foreach ($transaksiLama->items as $item) {
        PergerakanStok::create([
          'produk_id' => $item->produk_id,
          'produk_supplier_id' => $item->produk_supplier_id ?? null,
          'tanggal' => now(),
          'tipe' => $stokRollbackTipe,
          'qty' => $item->qty,
          'sumber_type' => $Model,
          'sumber_id' => $transaksiLama->id,
          'kena_pajak' => $item->kena_pajak,
          'keterangan' => "Rollback revisi {$tipe}",
        ]);
      }

      // 2️⃣ rollback kas
      if ($transaksiLama->transaksiKas->sum('jumlah')) {
        TransaksiKas::create([
          'akun_kas_id' => $dataBaru['akun_kas_id'] ?? 1,
          'tanggal' => now(),
          'tipe' => $kasRollbackTipe,
          'kategori_id' => 4, // revisi keluar
          'jumlah' => $transaksiLama->transaksiKas->sum('jumlah'),
          'keterangan' => "Rollback revisi {$tipe} #" . $transaksiLama->no_faktur ?? $transaksiLama->no_struk,
          'sumber_type' => $Model,
          'sumber_id' => $transaksiLama->id,
        ]);
      }

      // 3️⃣ tandai transaksi lama direvisi
      $transaksiLama->update(['status' => 'direvisi']);
      // 4️⃣ buat transaksi baru
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

      // 5️⃣ buat item & stok baru
      $ItemModel = $isPembelian ? ItemPembelian::class : ItemPenjualan::class;
      foreach ($dataBaru['items'] as $item) {
        $ItemModel::create(array_merge($item, [
          $isPembelian ? 'pembelian_id' : 'penjualan_id' => $transaksiBaru->id
        ]));

        PergerakanStok::create([
          'produk_id' => $item['produk_id'],
          'produk_supplier_id' => $item['produk_supplier_id'] ?? null,
          'tanggal' => $dataBaru['tanggal'],
          'tipe' => $stokBaruTipe,
          'qty' => $item['qty'],
          'sumber_type' => $Model,
          'sumber_id' => $transaksiBaru->id,
          'kena_pajak' => $item['kena_pajak'] ?? false,
          'keterangan' => "Stok {$stokBaruTipe} hasil revisi {$tipe}",
        ]);
      }

      // 6️⃣ kas baru
      foreach ($transaksiLama->transaksiKas as $transaksi) {
        TransaksiKas::create([
          'akun_kas_id' => $dataBaru['akun_kas_id'] ?? 1,
          'tanggal' => $dataBaru['tanggal'],
          'tipe' => $kasBaruTipe,
          'jumlah' => $transaksi->jumlah,
          'kategori_id' => 3, // revisi masuk
          'keterangan' => ucfirst($tipe) . ' hasil revisi #' . $transaksiLama->no_faktur ?? $transaksiLama->no_struk,
          'sumber_type' => $Model,
          'sumber_id' => $transaksiBaru->id,
        ]);
      }

      return $transaksiBaru;
    });
  }
}
