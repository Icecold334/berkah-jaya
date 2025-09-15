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
6. **penjualans** (id, **no_struk**, customer_id, tanggal, total, **kena_pajak**, timestamps)
7. **item_penjualans** (id, penjualan_id, produk_id, **produk_supplier_id**, harga_jual, qty, subtotal, **kena_pajak**, timestamps)
8. **customers** (id, nama, telepon, alamat, timestamps) – opsional
9. **akun_kas** (id, nama, tipe, saldo_awal, timestamps)
10. **kategori_kas** (id, tipe, nama, timestamps)
11. **transaksi_kas** (id, akun_kas_id, tanggal, tipe, kategori_id, jumlah, keterangan, sumber_type, sumber_id, timestamps)
12. **pergerakan_stoks** (id, produk_id, **produk_supplier_id**, tanggal, tipe, qty, **kena_pajak**, sumber_type, sumber_id, keterangan, timestamps)
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

-   Supplier sebagai gatekeeper sebelum input barang.
-   Row barang dengan input manual nama, qty, harga (format rupiah realtime).
-   Toggle pajak per row, bisa bulk update.
-   Manajemen row (tambah/hapus).
-   Validasi field sebelum simpan.
-   Transaksi pembelian → otomatis membuat Pembelian, Item, PergerakanStok, TransaksiKas.
-   Toast sukses setelah simpan.

### 5. **Penjualan (Livewire Form)**

-   Input produk via search (kode/nama).
-   Produk dipilih dari stok FIFO (`pergerakan_stoks`).
-   Cart bisa edit qty & harga jual (format rupiah realtime).
-   Validasi stok tidak boleh minus.
-   **Logic Baru:**
    -   Saat simpan, stok diambil FIFO dari supplier.
    -   Hasil penjualan **dikelompokkan berdasarkan status pajak**:
        -   Jika barang kena pajak → 1 invoice (`penjualans`) dengan flag `kena_pajak=true`.
        -   Jika barang non-pajak → 1 invoice (`penjualans`) dengan flag `kena_pajak=false`.
        -   Jadi 1 transaksi customer bisa pecah menjadi **≥ 1 invoice**.
    -   Tiap invoice dapat nomor struk auto-generate (`INVYYYYMMDDNNN`).
    -   Item Penjualan (`item_penjualans`) simpan detail produk + asal supplier (`produk_supplier_id`) + flag pajak.
    -   Stok keluar dicatat di `pergerakan_stoks` dengan supplier & flag pajak.
    -   Transaksi kas masuk otomatis tercatat per invoice.

---

## 📊 Contoh Kasus Penjualan

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
