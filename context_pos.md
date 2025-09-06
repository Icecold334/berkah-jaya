# Context Prompt Aplikasi POS (Update Progress)

Aku sedang mengerjakan project aplikasi **Point of Sale (POS)** dengan pendekatan **iteratif**: kebutuhan fitur bisa bertambah seiring waktu.  
Awalnya project ini dibuat dengan bahasa lain, tapi sekarang direbuild menggunakan **TALL stack** (TailwindCSS, AlpineJS, Laravel, Livewire).

---

## 🎯 Kebutuhan Utama Sistem

-   Bisa handle pembelian barang dari banyak supplier (barang sama bisa beda harga dan status pajak).
-   Harga jual default = harga beli tertinggi; kalau harga itu kena pajak → tambah 2%. Harga jual hanya auto-fill, editable saat penjualan.
-   Stok dihitung dari pergerakan (masuk/keluar) lewat tabel `pergerakan_stoks`, bukan disimpan di field statis.
-   Kas = satu tabel terintegrasi, gabungan penjualan, pembelian, input manual, dan bisa multi akun kas (kas toko, bank, e-wallet).
-   Ada kategori kas masuk/keluar, dan bisa pilih kategori saat input.
-   Audit log: semua perubahan di database otomatis tercatat (insert/update/delete).
-   Retur tidak punya tabel khusus, tapi ditangani lewat entri manual `pergerakan_stoks` & `transaksi_kas` (misalnya stok opname, retur, koreksi).

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

## ⚙️ Progres Implementasi

✅ **Model & Relasi** sudah dibuat lengkap: Produk, Supplier, Pembelian + Item, Penjualan + Item, Customer, AkunKas, KategoriKas, TransaksiKas, PergerakanStok, AuditLog.  
✅ **Seeder** sudah dibuat:

-   Produk (Beras, Minyak)
-   Supplier (PT Sumber Pangan, CV Minyak Sejahtera)
-   Customer (Budi, Siti)
-   Akun Kas (Kas Toko, Bank BCA)
-   Kategori Kas (Penjualan, Pembelian, Biaya Operasional)
-   Contoh Pembelian & Penjualan → otomatis memengaruhi stok & kas.  
    ✅ **Saldo kas** bisa dihitung (misal setelah seed → –Rp516.000).  
    ✅ **Audit log** sudah aktif → semua create/update/delete otomatis tercatat.  
    ✅ **Retur & Stok Opname** ditangani tanpa tabel baru, cukup via entri manual di `pergerakan_stoks` + `transaksi_kas`.

---
