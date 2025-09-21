# 📌 Context Prompt Aplikasi POS (Update Progress Terbaru)

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
4. **pembelians** (id, no_faktur, supplier_id, tanggal, total, kena_pajak, keterangan, timestamps)
5. **item_pembelians** (id, pembelian_id, produk_id, harga_beli, qty, kena_pajak, timestamps)
6. **penjualans** (id, no_struk, customer_id, tanggal, total, kena_pajak, timestamps)
7. **item_penjualans** (id, penjualan_id, produk_id, produk_supplier_id, harga_jual, qty, subtotal, kena_pajak, timestamps)
8. **customers** (id, nama, telepon, alamat, timestamps) – opsional
9. **akun_kas** (id, nama, tipe, saldo_awal, timestamps)
10. **kategori_kas** (id, tipe, nama, timestamps)
11. **transaksi_kas** (id, akun_kas_id, tanggal, tipe, kategori_id, jumlah, keterangan, sumber_type, sumber_id, timestamps)
12. **pergerakan_stoks** (id, produk_id, produk_supplier_id, tanggal, tipe, qty, kena_pajak, sumber_type, sumber_id, keterangan, timestamps)
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
-   Audit log aktif → semua perubahan DB tercatat.

### 4. **Pembelian (Form Livewire)**

-   Supplier harus dipilih dulu.
-   Input row barang (nama, qty, harga beli).
-   Harga realtime format rupiah.
-   Toggle pajak (bulk update tersedia).
-   Validasi sebelum simpan.
-   Saat simpan:
    -   Generate **no_faktur unik** (`INYYYYMMDDNNN`).
    -   Buat record Pembelian + Item.
    -   Update relasi Produk–Supplier.
    -   Catat stok masuk ke `pergerakan_stoks`.
    -   Buat transaksi kas keluar otomatis.
-   UI sudah Flowbite-style, tombol **Pakai Pajak/Tanpa Pajak** rapi.

### 5. **Penjualan (Form Livewire)**

-   Input produk via search (kode/nama).
-   Produk diambil dari stok FIFO (`pergerakan_stoks`).
-   Bisa edit qty & harga jual (format rupiah realtime).
-   Validasi stok tidak minus.
-   Logic simpan:
    -   Pecah invoice berdasarkan status pajak.
    -   Nomor struk auto-generate (`INVYYYYMMDDNNN`).
    -   Item penjualan simpan `produk_supplier_id` + pajak.
    -   Stok keluar dicatat.
    -   Transaksi kas masuk otomatis.

### 6. **Laporan Pembelian**

-   Route: `/laporan/beli` → `<livewire:pembelian.laporan />`.
-   Fitur:
    -   Filter tanggal awal–akhir.
    -   Filter supplier.
    -   Filter tipe pajak.
    -   Cari nomor faktur.
    -   Ringkasan total pembelian.
    -   Tabel daftar faktur dengan pagination.
    -   Kolom status pajak (Pajak/Non Pajak).
    -   Modal detail pembelian (info + item barang).
    -   Export PDF per faktur (alamat dipilih dulu).
    -   Export PDF bulk (multi faktur, multi file, bukan zip).
    -   Checkbox per row + **select all/unselect all**.
    -   Reset selection saat filter berubah.
-   UI: tabel compact, tombol download disabled kalau alamat belum dipilih.

---

## 📊 Contoh Kasus Penjualan (FIFO + Pajak)

### Stok

-   A (Supp A pajak) = 20, (Supp B non-pajak) = 50
-   B (Supp A pajak) = 100, (Supp C non-pajak) = 20
-   C (Supp C non-pajak) = 60

### Pesanan

-   A = 60, B = 50, C = 30

### Hasil

-   **Penjualan #1 (pajak)** → A=20 (Supp A), B=50 (Supp A)
-   **Penjualan #2 (non-pajak)** → A=40 (Supp B), C=30 (Supp C)

Total invoice: **2**

---
