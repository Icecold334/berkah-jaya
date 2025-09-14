# 📌 Context Prompt Aplikasi POS (Update Progress Final)

## 🏗️ Teknologi

-   **Stack**: TALL (TailwindCSS, AlpineJS, Laravel, Livewire)
-   **UI Base**: Flowbite
-   **Color Palette**: primary, info, danger, secondary, warning, success

---

## 🎯 Kebutuhan Utama Sistem

-   Bisa handle **pembelian barang dari banyak supplier** (barang sama bisa beda harga & status pajak).
-   **Harga jual default** = harga beli tertinggi; kalau harga itu kena pajak → tambah 2%. Harga jual hanya auto-fill, editable saat penjualan.
-   **Stok** dihitung dari tabel `pergerakan_stoks` (masuk/keluar), bukan disimpan di field statis.
-   **Kas** = satu tabel terintegrasi, gabungan penjualan, pembelian, input manual, dan bisa multi akun kas (kas toko, bank, e-wallet).
-   Ada **kategori kas masuk/keluar**, bisa dipilih saat input.
-   **Audit log**: semua perubahan database (insert/update/delete) otomatis tercatat.
-   **Retur & Stok Opname** → tidak pakai tabel baru, cukup entri manual di `pergerakan_stoks` + `transaksi_kas`.

---

## 🗄️ Daftar Tabel POS Saat Ini

1. **produks** (id, nama, slug, kode_barang, timestamps)
2. **suppliers** (id, nama, alamat, telepon, npwp, timestamps)
3. **produk_suppliers** (id, produk_id, supplier_id, harga_beli, kena_pajak, tanggal_pembelian_terakhir, timestamps)
4. **pembelians** (id, supplier_id, tanggal, total, keterangan, timestamps)
5. **item_pembelians** (id, pembelian_id, produk_id, harga_beli, qty, kena_pajak, timestamps)
6. **penjualans** (id, customer_id, tanggal, total, timestamps)
7. **item_penjualans** (id, penjualan_id, produk_id, harga_jual, qty, subtotal, timestamps)
8. **customers** (id, nama, telepon, alamat, timestamps) – opsional
9. **akun_kas** (id, nama, tipe, saldo_awal, timestamps)
10. **kategori_kas** (id, tipe, nama, timestamps)
11. **transaksi_kas** (id, akun_kas_id, tanggal, tipe, kategori_id, jumlah, keterangan, sumber_type, sumber_id, timestamps)
12. **pergerakan_stoks** (id, produk_id, tanggal, tipe, qty, sumber_type, sumber_id, keterangan, timestamps)
13. **audit_logs** (id, tabel, record_id, aksi, data_lama, data_baru, user_id, timestamps)

---

## ✅ Progres Implementasi

### 1. **Model & Relasi**

-   Semua model & relasi sudah dibuat: Produk, Supplier, Pembelian+Item, Penjualan+Item, Customer, AkunKas, KategoriKas, TransaksiKas, PergerakanStok, AuditLog.

### 2. **Seeder**

-   Produk contoh (Beras, Minyak).
-   Supplier contoh (PT Sumber Pangan, CV Minyak Sejahtera).
-   Customer contoh (Budi, Siti).
-   Akun Kas contoh (Kas Toko, Bank BCA).
-   Kategori Kas contoh (Penjualan, Pembelian, Biaya Operasional).
-   Contoh Pembelian & Penjualan → otomatis memengaruhi stok & kas.

### 3. **Saldo & Audit Log**

-   Saldo kas bisa dihitung otomatis.
-   Audit log sudah aktif → semua perubahan DB tercatat.

### 4. **Pembelian (Livewire Form)**

-   **Supplier sebagai gatekeeper** → sebelum pilih supplier, semua input row & tombol disable (`cursor-not-allowed`, `opacity-50`).
-   **Row Barang**:
    -   Input manual `nama`, `qty`, `harga_beli`.
    -   Nama barang otomatis `firstOrCreate` di DB dengan slug.
    -   Harga input **diformat rupiah** realtime dengan JS helper `formatRupiah()` (tidak ada leading zero, kosong kalau belum diisi).
-   **Toggle Pajak**:
    -   Tombol toggle outline/filled.
    -   Label berubah **Tanpa Pajak ↔ Pakai Pajak**.
    -   Mengupdate semua row `kena_pajak` sesuai mode.
-   **Row Management**:
    -   Tombol `+` hanya muncul di row terakhir **dan** kalau semua field valid.
    -   Tombol `hapus` muncul di semua row kecuali kalau hanya 1 row.
-   **Tombol Simpan**:
    -   Muncul hanya kalau ada ≥ 1 row **dan** semua field valid.
    -   Setelah simpan → form reset (supplier kosong, 1 row baru).
    -   SweetAlert Toast muncul di kanan atas ("Pembelian berhasil disimpan!").

### 5. **Backend Logic (Form.php)**

-   Normalisasi `harga_beli` sebelum validasi (hapus Rp, titik, koma).
-   Validasi field supplier, tanggal, items.
-   Transaksi pembelian:
    -   Buat record `Pembelian`.
    -   Cek/buat produk (`Produk::firstOrCreate`).
    -   Buat `ItemPembelian`.
    -   Catat `PergerakanStok`.
    -   Buat `TransaksiKas`.
-   Setelah simpan → reset form + dispatch event `toast`.
