# 📌 Context Prompt Aplikasi POS (Update Progres Terbaru)

## 🏗️ Teknologi

-   **Stack**: TALL (TailwindCSS, AlpineJS, Laravel, Livewire)
-   **UI Base**: Flowbite
-   **Color Palette**: primary, info, danger, secondary, warning, success

---

## 🎯 Kebutuhan Utama Sistem

-   Handle **pembelian barang dari banyak supplier** (barang sama bisa beda harga & status pajak).
-   **Harga jual default** = harga beli tertinggi; jika kena pajak → tambahkan sesuai presentase dari tabel `settings`.
-   **Stok** dihitung dari tabel `pergerakan_stoks` (masuk/keluar), bukan dari field statis.
-   **Kas** = satu tabel terintegrasi, gabungan dari penjualan, pembelian, input manual; bisa multi akun kas (toko, bank, e-wallet).
-   Ada **kategori kas masuk/keluar**, dipilih saat input.
-   **Audit log** aktif untuk semua perubahan (insert/update/delete).
-   **Retur & Stok Opname (SO)** → masih manual via `pergerakan_stoks` + `transaksi_kas`.
-   **Settings** digunakan untuk konfigurasi global (misal: `presentase`, `akun_penjualan`).

---

## 🗄️ Daftar Tabel Utama

1. **produks** (id, nama, slug, kode_barang, timestamps, deleted_at)
2. **suppliers** (id, nama, alamat, telepon, npwp, timestamps, deleted_at)
3. **produk_suppliers** (id, produk_id, supplier_id, harga_beli, kena_pajak, tanggal_pembelian_terakhir, timestamps)
4. **pembelians** (id, no_faktur, supplier_id, tanggal, total, kena_pajak, keterangan, timestamps)
5. **item_pembelians** (id, pembelian_id, produk_id, harga_beli, qty, kena_pajak, timestamps)
6. **penjualans** (id, no_struk, customer_id, tanggal, total, kena_pajak, timestamps)
7. **item_penjualans** (id, penjualan_id, produk_id, produk_supplier_id, harga_jual, qty, subtotal, kena_pajak, timestamps)
8. **customers** (id, nama, telepon, alamat, timestamps, deleted_at)
9. **akun_kas** (id, nama, timestamps, deleted_at)
10. **kategori_kas** (id, tipe, nama, timestamps, deleted_at)
11. **transaksi_kas** (id, akun_kas_id, tanggal, tipe, kategori_id, jumlah, keterangan, sumber_type, sumber_id, timestamps)
12. **pergerakan_stoks** (id, produk_id, produk_supplier_id, tanggal, tipe, qty, sumber_type, sumber_id, kena_pajak, keterangan, timestamps)
13. **audit_logs** (id, tabel, record_id, aksi, data_lama, data_baru, user_id, timestamps)
14. **settings** (id, label, data, timestamps)

---

## ✅ Progres Implementasi Terbaru

### 1. **Soft Delete & FK Handling**

-   Semua tabel master (`suppliers`, `customers`, `produks`, `akun_kas`, `kategori_kas`) sudah mendukung `softDeletes()`.
-   Relasi `belongsTo` yang mengarah ke tabel-tabel ini menggunakan `withTrashed()`.
-   Sekarang data transaksi tetap bisa diakses meskipun data master dihapus.
-   Tidak ada lagi error _foreign key constraint fails_.
-   Audit log tetap aktif mencatat semua perubahan.

---

### 2. **Form Pembelian (Livewire)**

-   Form pembelian sudah dilengkapi:
    -   Pilihan supplier.
    -   Toggle pajak (update bulk semua item).
    -   Input item dinamis (nama, qty, harga beli).
    -   Input `harga_beli` otomatis format Rupiah.
    -   Validasi stok dan supplier.
-   Harga beli otomatis diubah ke integer sebelum disimpan.
-   Nomor faktur (`no_faktur`) dibuat otomatis.
-   Stok dan kas otomatis diperbarui setelah transaksi berhasil.

---

### 3. **Autocomplete Produk di Form Pembelian**

-   Input **nama barang** sekarang mendukung _autocomplete_.
-   Saat user mengetik, muncul referensi produk dari tabel `produks`.
-   User tetap bisa mengetik produk baru tanpa harus memilih dari daftar.
-   Dibangun dengan kombinasi **Livewire + AlpineJS** (tanpa Select2 / JS eksternal).
-   Dropdown muncul halus tanpa terpotong container card.

---

### 4. **UI Tabel Pembelian**

-   Wrapper card/table Flowbite (`overflow-x-auto`) dihapus agar dropdown autocomplete tidak terpotong.
-   Tampilan tabel tetap rapi dengan border & shadow ringan.
-   Fungsi tambah/hapus baris barang tetap berjalan normal.

---

### 5. **Modal Konfirmasi Pembelian (Livewire + Flowbite)**

-   Sebelum transaksi disimpan, muncul modal konfirmasi.
-   Informasi vital yang ditampilkan:
    -   Supplier
    -   Status Pajak (badge hijau/abu)
    -   Total Pembelian (Rp)
    -   Tanggal Transaksi
-   Tombol aksi:
    -   ❌ **Batal**
    -   ✅ **Ya, Simpan**
-   Modal reaktif penuh menggunakan `@entangle('showConfirmModal')`.
-   Badge status pajak tampil dengan warna:
    -   Hijau untuk “Pakai Pajak”
    -   Abu-abu untuk “Tanpa Pajak”
-   Transisi halus & tampilan profesional mengikuti Flowbite modal.

---

## 💾 Struktur Livewire Pembelian (ringkasan)

### Komponen

-   **`App\Livewire\Pembelian\Form`**
    -   `mount()`: set tanggal default & inisialisasi row item.
    -   `addItem()` / `removeItem()`: manajemen input dinamis.
    -   `togglePajak()`: update pajak global untuk semua item.
    -   `konfirmasiSimpan()`: hitung total → tampilkan modal.
    -   `simpanFinal()`: commit transaksi pembelian.
    -   `simpan()`: logika insert data ke pembelian, item, stok, dan kas.
    -   `render()`: menampilkan view + data supplier.

---

## ⚙️ Fitur-Fitur Lain yang Sudah Stabil

-   **Audit Log** aktif otomatis di setiap perubahan (insert/update/delete).
-   **Saldo Kas Otomatis** dihitung dari `transaksi_kas`.
-   **Menu Kas, Stok, Supplier, Akun Kas, Kategori Kas** sudah lengkap dengan Livewire CRUD, pencarian, dan filter.
-   **PDF Laporan Pembelian & Penjualan** sudah rapi dengan template Flowbite.
-   **Laporan Penjualan & Pembelian** mendukung filter + export per faktur dan bulk.

---

## 🔮 Fitur Next Plan (Tahap Berikut)

1. **Edit Transaksi Pembelian / Penjualan (revisi aman + rollback stok & kas).**
2. **Konfirmasi serupa untuk Penjualan** (UX konsisten).
3. **Dashboard Analitik** — grafik arus kas, total penjualan, stok hampir habis.
4. **Notifikasi Realtime** — stok menipis, retur, kas negatif.
5. **Role & Permission** (pakai Spatie) untuk keamanan admin/operator.

---

## 🧾 Catatan Desain

-   Semua transaksi (pembelian, penjualan, kas, stok) bersifat **immutable** — tidak dihapus, hanya bisa direvisi.
-   **Produk, supplier, dan master lain** bisa dihapus dengan aman (soft delete).
-   Semua transaksi terkait tetap terbaca dan tidak error berkat `withTrashed()`.

---

## 📅 Update Terakhir

**Tanggal:** 11 Oktober 2025  
**Status:** ✅ Pembelian, Soft Delete, Modal Konfirmasi, dan Autocomplete Produk — _selesai dan stabil_.
