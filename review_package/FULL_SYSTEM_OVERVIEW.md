Handai POS — Full System Overview

Tujuan dokumen
- Memberi gambaran teknis menyeluruh dari sistem ERP F&B (Handai POS) untuk keperluan review eksternal.
- Menjelaskan arsitektur, modul utama, alur bisnis, kendala yang ditemukan, dan rekomendasi perbaikan.

1. Ringkasan Singkat
Sistem ini adalah ERP/Point-of-Sale berbasis Laravel (PHP) yang menangani stock, produksi, POS, finance, dan HR dasar. Banyak modul inti sudah tersedia: stock & batch, reorder suggestion, production planning (MRP), waste/expired handling, import/export, attendance, dan beberapa tampilan manajerial.

2. Arsitektur & Teknologi
- Framework: Laravel (PHP)
- DB: SQLite (dipakai di repo), direkomendasikan ke PostgreSQL untuk produksi
- Frontend: Blade + asset pipeline (Vite/ Tailwind di repo)
- Auth: Laravel session + Passport untuk API
- Struktur: modul terpisah di `app/Services`, `app/Models`, `app/Http/Controllers`

3. Modul Utama & File Kunci
- Inventory / Stock
  - `app/Models/Stock.php`, `app/Models/StockBatch.php`
  - `app/Services/StockAlertService.php` (reorder suggestion, alerts)
  - `app/Services/InventoryService.php` (expired, waste, movements)
  - Controllers: `app/Http/Controllers/Manager/Inventory/*`
- Purchasing & Supplier
  - `app/Http/Controllers/Manager/Operational/SupplierController.php`
  - Import/Export: `app/Services/ImportExportService.php` (supplier import/export)
- Production & MRP
  - `app/Services/ProductionPlanningService.php` (MRP, material requirements)
  - Models: `ProductionPlan`, `ProductionPlanItem`, `MaterialRequirement`
  - Controllers: `app/Http/Controllers/Manager/Operational/ProductionPlanController.php`
- Sales / POS
  - `app/Http/Controllers/POS/*`, `app/Http/Controllers/Kasir/*` (checkout, invoices)
- Finance & Accounting
  - `app/Services/AccountingService.php` (journals, expired stock journals)
  - Controllers: `app/Http/Controllers/Manager/Finance/*` (invoices, expense, cashflow)
- HR / Payroll groundwork
  - `app/Http/Controllers/Manager/Finance/EmployeeController.php` (employee CRUD, salary field)
  - Attendance: `app/Http/Controllers/Manager/Operational/AttendanceController.php`

4. Alur Bisnis Kritis (end-to-end)
- Reorder & Purchase Request
  1. Sistem memonitor `reorder_point` via `StockAlertService::generateReorderSuggestions()` dan membuat `ReorderSuggestion`.
  2. Saat kebutuhan muncul, idealnya ada workflow: Purchase Request (PR) → Approve → Purchase Order (PO) → Supplier → Receiving.
  3. Saat barang diterima, harus ada proses Receiving: QC, pencatatan `StockBatch`, lokasi penyimpanan (putaway), dan update stok.

- Produksi
  1. Production Plan dibuat (`ProductionPlanController`) dan MRP dihitung (`ProductionPlanningService::calculateMRP`).
  2. Produksi mencatat material usage (`ProductionStockUsage`) dan output batch (`ProductionHistory`).
  3. Sistem mendukung BOM (table `bom`) dan semi-finished products.
  4. Idealnya produksi melakukan backflush: otomatis mengurangi stok bahan saat produksi diselesaikan.

- POS → Keuangan
  1. Transaksi POS membuat invoice/order yang mengurangi stok (`StockMovement`) dan menambah pendapatan.
  2. Finance membaca transaksi yang sama untuk jurnal, tidak melakukan input ulang.
  3. Bank reconciliation harus mencocokkan mutasi bank dengan pembayaran/invoice/expense.

- Payroll
  1. Data dasar ada (`Employee.salary`) dan attendance/overtime tercatat.
  2. Belum ada modul payroll penuh; perhitungan upah variabel (per output produksi) belum otomatis.

5. Kelemahan & Celah Fungsional yang Ditemukan
- Workflow Purchase belum dipaksa: PR→Approval→PO→Receiving→QC→Putaway sering dilewati, menyebabkan permintaan tidak sampai finance.
- Master supplier kurang kaya: tidak ada lead time resmi, PIC cadangan, histori harga, atau SLA pengiriman yang dipakai otomatis oleh MRP.
- Receiving / Warehouse
  - Putaway / bin location tidak terpaksa; barang sering disimpan sembarangan.
  - Tidak ada notifikasi yang memaksa konfirmasi receiving dan QC.
- Stok sinkronisasi
  - Ada layanan alarm/reorder, tetapi sinkronisasi real stok vs sistem masih berisiko (missing sales, non-logged consumption, barang terlupa dimasukkan ke gudang).
- Produksi
  - Backflush belum menyeluruh: terkadang jumlah produksi yang tercatat berbeda dari real output.
  - Retribusi pembayaran produksi per batch/per orang belum otomatis.
- Finance
  - Tidak ada proses reconciliation mutasi bank otomatis, sehingga kas fisik vs buku sering mismatch.
  - Harga pembelian historis dan pengaruhnya ke COGS belum selalu tercatat real-time.
- HR / Payroll
  - Payroll berbasis input manual berisiko salah; komponen seperti lembur, potongan, dan upah berbasis output belum terintegrasi.

6. Rekomendasi Perbaikan (Prioritas & Implementasi)
- Prioritas Tinggi (implementasi 1–3 bulan):
  1. Standardize Purchase Workflow: implement PR → approval → PO → receiving → QC → putaway (dengan required statuses and notifications).
  2. Enrich Supplier Master: tambah `lead_time_days`, `min_order_qty`, `preferred`, `contact_persons`, `price_history`.
  3. Force Receiving & Putaway: receiving harus membuat `StockBatch` dan mencatat `location` + `buy_date` + `paid_at`.
  4. Implement Bank Reconciliation helper: import bank CSV, fuzzy-match mutasi vs invoice/expense.
- Prioritas Menengah (3–6 bulan):
  1. Backflush produksi: saat `ProductionHistory.complete`, kurangi stok bahan berdasarkan BOM dan tambahkan stok produk jadi otomatis.
  2. Link production output → payroll: capture `production_output` per person or per team, kemudian distribusikan biaya tenaga kerja ke anggota yang berpartisipasi.
  3. Improve stock audits: scheduled stock-opname flow with adjustments and root-cause logging.
- Prioritas Jangka Panjang (6–12+ bulan):
  1. Multi-location inter-store transfers and central procurement optimization.
  2. Migrate to PostgreSQL for transactional reliability and better concurrency.
  3. Implement automated costing pipeline: average-cost / FIFO for COGS adjustments.

7. Checklist untuk Reviewer
- Konfirmasi coverage: apakah Purchase Request → Receiving → Putaway → QC tercatat semua di DB.
- Pastikan semua stock-affecting actions (sale, production, waste, expired, adjustment) create `StockMovement` and journaling.
- Review `StockBatch` model for fields: `supplier_id`, `buy_date`, `expiry_date`, `location`, `isStored`.
- Inspect Production flow: are `ProductionStockUsage` and `ProductionHistory` consistent?
- Check AccountingService for correct journal entries on expired/waste and COGS.

8. Setup & How to Get the Review Zip (Download)
Lokasi file zip yang telah dibuat di workspace:
- [review_package/handai_pos_review.zip](review_package/handai_pos_review.zip)

Jika kamu berada di mesin yang sama (Windows), opsi cepat:
- Buka File Explorer dan navigasi ke folder `d:\Handai_POS\review_package`, lalu download/copy file `handai_pos_review.zip`.
- Atau jalankan PowerShell untuk menyalin ke Desktop:

```powershell
Copy-Item -Path .\review_package\handai_pos_review.zip -Destination $env:USERPROFILE\Desktop\handai_pos_review.zip -Force
```

Untuk diserahkan ke ahli melalui HTTP (sementara) — pastikan firewall aman dan hanya aktif sementara:

```powershell
# dari folder d:\Handai_POS
python -m http.server 8000
# ahli dapat mengakses http://<your-ip>:8000/review_package/handai_pos_review.zip
```

Catatan: jika ingin upload ke Google Drive/Dropbox, unggah file `review_package/handai_pos_review.zip` dari mesin lokal.

9. Petunjuk Ringkas Menjalankan Aplikasi (dev)
- Pastikan memiliki PHP, Composer, dan ekstensi yang diperlukan.
- Copy `.env.example` → `.env` dan set `DB_CONNECTION=sqlite` dan `DB_DATABASE=../database.sqlite` (atau file sqlite yang aman di luar project). Lihat juga catatan di repo tentang antivirus yang dapat mengunci sqlite.
- Install dependencies dan generate key:

```powershell
composer install --no-dev
php artisan key:generate
```

- Jika perlu migrasi/seed (hati-hati, ini akan mengubah DB):

```powershell
php artisan migrate --force
php artisan db:seed
```

10. Next Steps Saya Bisa Bantu
- Tambahkan `REVIEW_NOTES.md` template di `review_package/` (saya bisa buat sekarang).
- Siapkan skrip export tambahan (mis. `composer show --format=json > review_package/composer_deps.json`) untuk audit dependency.
- Membantu menyiapkan instruksi upload ke Google Drive/Dropbox (butuh akses).

---
Dokumen ini dibuat untuk membantu proses review eksternal. Silakan beri tahu jika mau tambahan bagian teknis (ER diagram, contoh query, atau dump skema).