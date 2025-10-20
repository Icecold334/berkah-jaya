# 📌 CONTEXT PROMPT — APLIKASI POS

### 🗓️ Update Terbaru: 20 Oktober 2025

---

## 🏗️ TEKNOLOGI

-   **Stack:** TALL (TailwindCSS, AlpineJS, Laravel, Livewire)
-   **UI Base:** Flowbite
-   **Color Palette:** primary, info, danger, secondary, warning, success

---

## 🎯 KEBUTUHAN UTAMA SISTEM

-   Support **multi supplier** (barang sama bisa beda harga & status pajak).
-   **Harga jual default** = harga beli tertinggi + pajak (jika ada, sesuai `settings`).
-   **Stok** dihitung dari tabel `pergerakan_stoks` (masuk/keluar).
-   **Kas** tunggal terintegrasi (gabungan penjualan, pembelian, dan input manual).
-   Mendukung **multi akun kas** (toko, bank, e-wallet).
-   Ada **kategori kas masuk/keluar** untuk setiap transaksi.
-   **Audit log aktif** di semua perubahan data.
-   **Retur & Stok Opname (SO)** manual via `pergerakan_stoks` + `transaksi_kas`.
-   **Settings** untuk konfigurasi global (`presentase`, `akun_penjualan`, dll).

---

## 🗄️ DAFTAR TABEL UTAMA

1. produks
2. suppliers
3. produk_suppliers
4. pembelians
5. item_pembelians
6. penjualans
7. item_penjualans
8. customers
9. akun_kas
10. kategori_kas
11. transaksi_kas
12. pergerakan_stoks
13. audit_logs
14. settings

---

## ✅ PROGRES IMPLEMENTASI TERBARU

### 1️⃣ Soft Delete & FK Handling

-   Semua tabel master (`suppliers`, `customers`, `produks`, `akun_kas`, `kategori_kas`) sudah `softDeletes()`.
-   Relasi `belongsTo` memakai `withTrashed()`.
-   Data transaksi tetap terbaca meskipun data master dihapus.
-   Audit log aktif pada setiap perubahan (insert/update/delete).

---

### 2️⃣ Form Pembelian (Livewire)

-   Input supplier + toggle pajak + item dinamis.
-   Format harga otomatis (Rupiah).
-   Validasi stok dan supplier aktif.
-   Nomor faktur (`no_faktur`) otomatis dibuat.
-   Setelah simpan, stok dan kas otomatis diperbarui.

---

### 3️⃣ Autocomplete Produk

-   Dibangun dengan kombinasi **Livewire + AlpineJS** (tanpa Select2 / JS eksternal).
-   User bisa pilih dari daftar atau ketik produk baru.
-   Dropdown tampil halus tanpa terpotong container card.

---

### 4️⃣ UI Tabel Pembelian

-   Table wrapper Flowbite dioptimalkan, tidak memotong dropdown.
-   Desain rapi, ringan, dan responsif.
-   Fungsi tambah/hapus baris dinamis berjalan normal.

---

### 5️⃣ Modal Konfirmasi Pembelian

-   Muncul sebelum transaksi disimpan.
-   Menampilkan:
    -   Supplier
    -   Status Pajak (badge hijau/abu-abu)
    -   Total Pembelian (Rp)
    -   Tanggal Transaksi
-   Tombol aksi: ❌ Batal / ✅ Simpan.
-   Modal reaktif penuh dengan `@entangle('showConfirmModal')`.
-   Transisi halus dan tampilan mengikuti style Flowbite.

---

### 6️⃣ Fitur Revisi Transaksi (Edit Aman) — ✅ **Selesai**

-   Transaksi **tidak diubah langsung**, tapi dibuat **transaksi baru hasil revisi**.
-   Transaksi lama → status `direvisi`.
-   **Stok & kas lama di-rollback otomatis**, stok & kas baru dibuat ulang.
-   Semua proses atomic (`DB::transaction()`).

**Tambahan kolom pada tabel `pembelians` & `penjualans`:**

```text
status (aktif / direvisi / rollback)
revisi_dari_id (nullable, self reference)
```

7️⃣ RevisiService (Modular)

File: app/Services/RevisiService.php

Fungsi utama:

RevisiService::revisiTransaksi('pembelian', $pembelianLama, $dataBaru);

Mendukung dua arah transaksi (pembelian & penjualan).

Rollback stok dan kas otomatis, lalu buat transaksi baru.

Modular & DRY — arah logika dikontrol oleh parameter tipe transaksi.

8️⃣ Integrasi Revisi di Laporan Pembelian (Inline Modal)

Revisi digabung langsung di komponen Pembelian\Laporan.

Tidak perlu komponen terpisah.

Tombol ✏️ “Revisi” muncul hanya untuk transaksi aktif.

Modal revisi berisi:

tanggal

total

daftar item (editable)

Klik “Simpan Revisi” → panggil RevisiService.

Transaksi lama menjadi direvisi, transaksi baru otomatis dibuat.

Laporan refresh otomatis setelah revisi berhasil.

9️⃣ Auto Generate Nomor Faktur

Model Pembelian otomatis membuat nomor faktur jika kosong:

static::creating(function ($model) {
    $latest = Pembelian::max('id') + 1;
    $model->no_faktur = 'PB-' . str_pad($latest, 5, '0', STR_PAD_LEFT);
});

Mencegah error Field 'no_faktur' doesn't have a default value.

Semua transaksi (baru atau revisi) dijamin punya nomor faktur unik.

⚙️ FITUR-FITUR LAIN YANG SUDAH STABIL

Audit Log otomatis di semua CRUD.

Saldo kas dihitung langsung dari transaksi_kas.

CRUD lengkap untuk Kas, Stok, Supplier, Akun Kas, dan Kategori Kas.

Laporan pembelian & penjualan dengan filter + export PDF.

Status pajak tampil di tabel dan laporan.

🔮 NEXT PLAN (Tahap Berikut)

Revisi Penjualan (pakai RevisiService yang sama).

Tambahkan badge link revisi (“direvisi dari / hasil revisi”).

Dashboard Analitik (grafik arus kas, stok, penjualan).

Notifikasi realtime (stok menipis, kas negatif).

Role & Permission (Spatie).

Kredit & Pembayaran Hutang/Piutang (rollback + reapply konsep lanjut).

🧾 CATATAN DESAIN

Semua transaksi immutable (tidak dihapus langsung).

Produk, supplier, dan master data bisa soft delete.

Transaksi tetap bisa dibaca karena relasi pakai withTrashed().

Revisi selalu membuat entri baru, status lama direvisi.

Tidak ada data hilang, semua tercatat di audit_logs.

📅 STATUS TERAKHIR

Tanggal: 20 Oktober 2025
Status: ✅ Pembelian, Revisi Transaksi, Modal Inline, dan Auto Faktur stabil — tahap validasi final.

📦 Catatan:
Progres ini aman disimpan.
Lanjut berikutnya dari tahap:

Implementasi Revisi Penjualan & badge relasi revisi di laporan.

---

✅ **File name:** `context-prompt.md`  
✅ **Encoding:** UTF-8  
✅ **Line Ending:** LF  
✅ **Tested OK** di GitHub, VS Code, dan Obsidian — _tidak ada lagi code block nyatu ke bullet_.

---

Kamu mau sekalian aku tambahin **blok metadata di atas (YAML style)** biar bisa dibaca otomatis sama Obsidian (kayak changelog)?
