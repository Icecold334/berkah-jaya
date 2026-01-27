<?php

namespace App\Services;

use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\ItemPembelian;
use App\Models\ItemPenjualan;
use App\Models\PergerakanStok;
use App\Models\TransaksiKas;
use Illuminate\Support\Facades\DB;

class RevisiService
{
  public static function revisiTransaksi(string $tipe, $transaksiLama, array $dataBaru)
  {
    return DB::transaction(function () use ($tipe, $transaksiLama, $dataBaru) {

      $transaksiLama->loadMissing(['items', 'transaksiKas']);

      if ($tipe === 'pembelian') {
        return self::revisiPembelianCascade($transaksiLama, $dataBaru);
      }

      return self::revisiPenjualan($transaksiLama, $dataBaru);
    });
  }

  /* ======================================================
     | PEMBELIAN + CASCADE PENJUALAN
     ====================================================== */
  private static function revisiPembelianCascade(Pembelian $pbLama, array $dataBaru)
  {
    /** 🔹 cari penjualan terdampak */
    $produkIds = $pbLama->items->pluck('produk_id');

    $penjualans = Penjualan::with(['items', 'transaksiKas'])
      ->where('status', 'aktif')
      ->where('tanggal', '>=', $pbLama->tanggal)
      ->whereHas('items', fn($q) => $q->whereIn('produk_id', $produkIds))
      ->get();

    /** 🔹 reverse penjualan */
    foreach ($penjualans as $pj) {
      self::reversePenjualan($pj);
      $pj->update(['status' => 'direvisi']);
    }

    /** 🔹 reverse pembelian */
    self::reversePembelian($pbLama);
    $pbLama->update(['status' => 'direvisi']);

    /** 🔹 repost pembelian */
    $pbBaru = self::repostPembelian($pbLama, $dataBaru);

    /** 🔹 repost penjualan */
    foreach ($penjualans as $pj) {
      self::repostPenjualan($pj, $dataBaru['kena_pajak'] ?? false);
    }

    return $pbBaru;
  }

  /* ======================================================
     | PENJUALAN SAJA
     ====================================================== */
  private static function revisiPenjualan(Penjualan $pjLama, array $dataBaru)
  {
    self::reversePenjualan($pjLama);
    $pjLama->update(['status' => 'direvisi']);

    return self::repostPenjualan($pjLama, $dataBaru['kena_pajak'] ?? false);
  }

  /* ======================================================
     | REVERSE
     ====================================================== */
  private static function reversePembelian(Pembelian $pb)
  {
    foreach ($pb->items as $item) {
      self::stok('keluar', $item->produk_id, $item->qty, Pembelian::class, $pb->id, 'Reverse pembelian');
    }

    foreach ($pb->transaksiKas as $kas) {
      self::kas('masuk', $kas, Pembelian::class, $pb->id, 'Reverse pembelian');
    }
  }

  private static function reversePenjualan(Penjualan $pj)
  {
    foreach ($pj->items as $item) {
      self::stok('masuk', $item->produk_id, $item->qty, Penjualan::class, $pj->id, 'Reverse penjualan');
    }

    foreach ($pj->transaksiKas as $kas) {
      self::kas('keluar', $kas, Penjualan::class, $pj->id, 'Reverse penjualan');
    }
  }

  /* ======================================================
     | REPOST
     ====================================================== */
  private static function repostPembelian(Pembelian $lama, array $data)
  {
    $pb = Pembelian::create([
      'supplier_id' => $data['supplier_id'],
      'tanggal' => $data['tanggal'],
      'total' => $data['total'],
      'kena_pajak' => $data['kena_pajak'],
      'status' => 'aktif',
      'revisi_dari_id' => $lama->id,
    ]);

    foreach ($data['items'] as $item) {
      ItemPembelian::create(array_merge($item, ['pembelian_id' => $pb->id]));
      self::stok('masuk', $item['produk_id'], $item['qty'], Pembelian::class, $pb->id, 'Repost pembelian');
    }

    foreach ($lama->transaksiKas as $kas) {
      self::kas('keluar', $kas, Pembelian::class, $pb->id, 'Repost pembelian');
    }

    return $pb;
  }

  private static function repostPenjualan(Penjualan $lama, bool $kenaPajak)
  {
    $pj = Penjualan::create([
      'customer_id' => $lama->customer_id,
      'tanggal' => $lama->tanggal,
      'total' => $lama->total,
      'kena_pajak' => $kenaPajak,
      'status' => 'aktif',
      'revisi_dari_id' => $lama->id,
    ]);

    foreach ($lama->items as $item) {
      ItemPenjualan::create([
        'penjualan_id' => $pj->id,
        'produk_id' => $item->produk_id,
        'qty' => $item->qty,
        'harga_jual' => $item->harga_jual,
        'kena_pajak' => $kenaPajak,
      ]);

      self::stok('keluar', $item->produk_id, $item->qty, Penjualan::class, $pj->id, 'Repost penjualan');
    }

    foreach ($lama->transaksiKas as $kas) {
      self::kas('masuk', $kas, Penjualan::class, $pj->id, 'Repost penjualan');
    }

    return $pj;
  }

  /* ======================================================
     | HELPER
     ====================================================== */
  private static function stok(string $tipe, int $produkId, int $qty, string $sumberType, int $sumberId, string $ket)
  {
    PergerakanStok::create([
      'produk_id' => $produkId,
      'tanggal' => now(),
      'tipe' => $tipe,
      'qty' => $qty,
      'sumber_type' => $sumberType,
      'sumber_id' => $sumberId,
      'keterangan' => $ket,
    ]);
  }

  private static function kas(string $tipe, $kas, string $sumberType, int $sumberId, string $ket)
  {
    TransaksiKas::create([
      'akun_kas_id' => $kas->akun_kas_id,
      'tanggal' => now(),
      'tipe' => $tipe,
      'jumlah' => $kas->jumlah,
      'kategori_id' => $kas->kategori_id,
      'sumber_type' => $sumberType,
      'sumber_id' => $sumberId,
      'keterangan' => $ket,
    ]);
  }
}
