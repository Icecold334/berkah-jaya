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

## 🗄️ Daftar Tabel POS

1. **produks** (id, nama, slug, kode_barang, timestamps)
2. **suppliers** (id, nama, alamat, telepon, npwp, timestamps)
3. **produk_suppliers** (id, produk_id, supplier_id, harga_beli, kena_pajak, tanggal_pembelian_terakhir, timestamps)
4. **pembelians** (id, no_faktur, supplier_id, tanggal, total, kena_pajak, keterangan, timestamps)
5. **item_pembelians** (id, pembelian_id, produk_id, harga_beli, qty, kena_pajak, timestamps)
6. **penjualans** (id, no_struk, customer_id, tanggal, total, kena_pajak, timestamps)
7. **item_penjualans** (id, penjualan_id, produk_id, produk_supplier_id, harga_jual, qty, subtotal, kena_pajak, timestamps)
8. **customers** (id, nama, telepon, alamat, timestamps) – _opsional, belum dipakai sekarang_
9. **akun_kas** (id, nama, tipe, saldo_awal, timestamps)
10. **kategori_kas** (id, tipe, nama, timestamps)
11. **transaksi_kas** (id, akun_kas_id, tanggal, tipe, kategori_id, jumlah, keterangan, sumber_type, sumber_id, timestamps)
12. **pergerakan_stoks** (id, produk_id, produk_supplier_id, tanggal, tipe, qty, kena_pajak, sumber_type, sumber_id, keterangan, timestamps)
13. **audit_logs** (id, tabel, record_id, aksi, data_lama, data_baru, user_id, timestamps)

---

## ✅ Progres Implementasi

### 1. **Model & Relasi**

-   Semua model & relasi sudah dibuat.

### 2. **Seeder**

-   Data contoh: produk, supplier, customer, akun kas, kategori kas, pembelian & penjualan → otomatis memengaruhi stok & kas.

### 3. **Saldo & Audit Log**

-   Saldo kas otomatis.
-   Audit log aktif.

### 4. **Pembelian (Form Livewire)**

-   Supplier wajib pilih dulu.
-   Input row barang (nama, qty, harga beli).
-   Toggle pajak (bulk update tersedia).
-   Validasi → simpan → generate `no_faktur` unik, simpan pembelian, update stok & kas.
-   UI Flowbite-style.

### 5. **Penjualan (Form Livewire)**

-   Input produk via search (kode/nama).
-   Stok ambil FIFO dari `pergerakan_stoks`.
-   Bisa edit qty & harga jual.
-   Validasi stok tidak minus.
-   Simpan → pecah invoice berdasarkan pajak, generate `no_struk`, simpan item, update stok, transaksi kas masuk otomatis.

### 6. **Laporan Pembelian**

-   Route: `/laporan/beli` → `<livewire:pembelian.laporan />`
-   Fitur lengkap: filter, tabel, detail, export PDF (single & bulk).
-   UI: tabel compact, tombol download disabled kalau alamat belum dipilih.

### 7. **Laporan Penjualan**

-   Route: `/laporan/jual` → `<livewire:penjualan.laporan />`
-   **Filter**: tanggal, pajak, no struk (customer skip dulu).
-   **Tabel**: daftar penjualan + checkbox select all.
-   **Modal detail**: info penjualan, status pajak, item barang.
-   **Export PDF**: per nota (`pdf/penjualan.blade.php`) dan bulk download.

### 8. **PDF Template**

-   File: `resources/views/pdf/penjualan.blade.php`
-   Struktur sama dengan pembelian, tapi judul & field menyesuaikan (`no_struk`, `harga_jual`, `subtotal`).
-   Customer sementara ditampilkan `-` atau skip.

### 9. **Menu Arus Kas**

-   Route: `/kas` → `<livewire:kas.list-data />`
-   **Filter**: tanggal, akun kas, kategori kas, keterangan.
-   **Tabel**: daftar transaksi kas (tanggal, akun, tipe masuk/keluar, kategori, jumlah, keterangan, sumber).
-   **Saldo akhir** otomatis dihitung dari query filter aktif.
-   **Tambah transaksi manual**:
    -   Tombol “Tambah Transaksi” → modal Livewire.
    -   Form: tanggal (default = hari ini), akun kas, tipe (masuk/keluar), kategori (aktif setelah tipe dipilih), jumlah (input dengan auto-format Rupiah JS), keterangan.
    -   Simpan → insert ke `transaksi_kas` dengan `sumber_type = 'manual'`, `sumber_id = 0`.
-   **Format input Rupiah**: pakai Alpine + JS `formatRupiah()`, Livewire terima angka mentah.

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
