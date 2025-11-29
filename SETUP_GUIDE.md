# AdventureWorks Data Warehouse - Panduan Lengkap

## Overview
Project ini mengimplementasikan Data Warehouse untuk AdventureWorks dengan:
- **Database DW**: `adventureworks_dwh` (MySQL)
- **Source Database**: `adventureworks` (MySQL)
- **5 Business Questions** dengan visualisasi di Laravel web app
- **3 Dashboard Pages**: Sales Overview, Product Analysis, Customer & Geo

---

## Langkah-langkah Setup

### 1. Persiapan Database

#### a. Pastikan database adventureworks sudah ada
Jika belum punya database `adventureworks` untuk MySQL, download dari:
- https://github.com/Microsoft/sql-server-samples/releases/tag/adventureworks

Atau convert dari AdventureWorks SQL Server ke MySQL.

#### b. Jalankan script schema DW
```bash
mysql -u root -p < dwh/schema.sql
```

Script ini akan:
- Membuat database `adventureworks_dwh`
- Membuat tabel dimensi: `DimDate`, `DimProduct`, `DimCustomer`, `DimSalesperson`, `DimGeography`
- Membuat tabel fakta: `FactSalesOrderLine`

#### c. Jalankan ETL untuk populate data
```bash
mysql -u root -p < dwh/etl.sql
```

Script ini akan:
- Populate semua tabel dimensi dari source `adventureworks`
- Populate tabel fakta dengan join dari berbagai tabel source

### 2. Konfigurasi Laravel

#### a. Setup environment variables
Edit file `.env` dan tambahkan konfigurasi untuk koneksi DW:

```env
# Data Warehouse Connection
DWH_DB_HOST=127.0.0.1
DWH_DB_PORT=3306
DWH_DB_DATABASE=adventureworks_dwh
DWH_DB_USERNAME=root
DWH_DB_PASSWORD=
```

#### b. Install dependencies (jika belum)
```bash
composer install
npm install
```

#### c. Generate application key (jika belum)
```bash
php artisan key:generate
```

### 3. Jalankan Laravel Application

#### a. Start development server
```bash
php artisan serve
```

#### b. Akses dashboard pages
- **Sales Overview**: http://localhost:8000/dashboard/sales-overview
- **Product Analysis**: http://localhost:8000/dashboard/product-analysis
- **Customer & Geo**: http://localhost:8000/dashboard/customer-geo

---

## Struktur File

```
adventureworks-laravel/
├── dwh/
│   ├── README.md                 # Dokumentasi DW
│   ├── schema.sql                # DDL untuk database & tabel DW
│   ├── etl.sql                   # Script ETL untuk populate data
│   └── analytics_queries.sql     # Query analitik (5 pertanyaan)
├── app/Http/Controllers/
│   └── DwReportController.php    # Controller untuk dashboard
├── resources/views/dashboard/
│   ├── sales-overview.blade.php  # Page 1: Q1 & Q2
│   ├── product-analysis.blade.php # Page 2: Q1 extended & Q5
│   └── customer-geo.blade.php    # Page 3: Q3 & Q4
├── routes/
│   └── web.php                   # Routes untuk dashboard
├── config/
│   └── database.php              # Konfigurasi koneksi mysql_dwh
└── SETUP_GUIDE.md                # File ini
```

---

## 5 Business Questions & Implementasi

### Question 1: Market Basket Analysis
**Pertanyaan**: Produk atau kategori apa yang paling sering muncul dalam satu keranjang transaksi bersamaan dengan produk lain?

**Implementasi**:
- **Page 1**: Top products yang sering dibeli dengan produk lain (bundling candidates)
- **Page 2**: Top product pairs (co-occurrence analysis)

**Tabel**: `FactSalesOrderLine`, `DimProduct`

### Question 2: Territory Discount vs Profit
**Pertanyaan**: Wilayah mana yang memiliki rata-rata diskon tertinggi, dan apakah wilayah tersebut menghasilkan profit margin lebih rendah?

**Implementasi**:
- **Page 1**: Tabel & scatter chart menampilkan avg discount rate vs avg profit margin per territory

**Tabel**: `FactSalesOrderLine`, `DimGeography`

### Question 3: Customer Purchase Frequency
**Pertanyaan**: Rata-rata frekuensi pembelian per pelanggan per tahun, dan segmen mana yang high-frequency but low-ticket?

**Implementasi**:
- **Page 3**: Tabel customer dengan avg orders/year dan avg order value, filtered untuk high-freq low-ticket segment

**Tabel**: `FactSalesOrderLine`, `DimCustomer`, `DimDate`

### Question 4: Salesperson Retention
**Pertanyaan**: Salesperson mana yang memiliki tingkat retensi pelanggan terbaik (repeat orders)?

**Implementasi**:
- **Page 3**: Tabel & dual-axis chart untuk retention rate dan total sales per salesperson

**Tabel**: `FactSalesOrderLine`, `DimSalesperson`, `DimCustomer`

### Question 5: Inventory Turnover
**Pertanyaan**: Rasio antara jumlah produk terjual vs tersedia di inventory per kategori?

**Implementasi**:
- **Page 2**: Tabel & horizontal bar chart untuk units sold per kategori

**Tabel**: `FactSalesOrderLine`, `DimProduct`, `adventureworks.productinventory`

---

## Troubleshooting

### Error: "Database adventureworks not found"
- Pastikan database source `adventureworks` sudah ada di MySQL
- Cek nama database dengan `SHOW DATABASES;`

### Error: "Connection refused mysql_dwh"
- Periksa konfigurasi `.env` untuk `DWH_DB_*` variables
- Test koneksi: `mysql -u root -p adventureworks_dwh`

### Error: "Table doesn't exist"
- Pastikan sudah menjalankan `schema.sql` dan `etl.sql`
- Cek tabel: `USE adventureworks_dwh; SHOW TABLES;`

### Dashboard kosong / no data
- Pastikan ETL sudah dijalankan dan berhasil
- Cek jumlah rows: `SELECT COUNT(*) FROM FactSalesOrderLine;`

---

## Testing Queries Manual

Jika ingin test query secara manual tanpa web interface:

```bash
mysql -u root -p adventureworks_dwh < dwh/analytics_queries.sql > results.txt
```

Atau jalankan query individual di MySQL client/workbench.

---

## Export Data untuk Report

Dari dashboard web, Anda bisa copy data dari tabel, atau tambahkan fitur CSV export dengan menambahkan route baru di controller.

Contoh quick export via MySQL:
```bash
mysql -u root -p adventureworks_dwh -e "SELECT * FROM FactSalesOrderLine LIMIT 100;" > fact_sample.csv
```

---

## Next Steps / Enhancements

1. **Cross-filtering**: Tambahkan interaktivitas click pada chart untuk filter data
2. **Date range filter**: Tambahkan filter tanggal di dashboard
3. **CSV Export**: Tambahkan tombol download CSV per tabel
4. **Drill-down**: Klik territory → detail per salesperson
5. **Real-time updates**: Schedule ETL job untuk refresh data berkala

---

## Credits

- **Data Source**: AdventureWorks (Microsoft sample database)
- **Framework**: Laravel 11
- **Charts**: Chart.js
- **CSS**: Tailwind CSS

Semoga sukses dengan tugasnya! 🚀
