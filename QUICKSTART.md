# AdventureWorks DW - Quick Start

## 🚀 Cara Menjalankan (3 Langkah)

### 1. Setup Database DW
```bash
# Buat database dan tabel DW
mysql -u root -p < dwh/schema.sql

# Jalankan ETL (populate data dari adventureworks ke adventureworks_dwh)
mysql -u root -p < dwh/etl.sql
```

### 2. Konfigurasi Laravel
File `.env` sudah dikonfigurasi dengan:
```
DWH_DB_HOST=127.0.0.1
DWH_DB_PORT=3306
DWH_DB_DATABASE=adventureworks_dwh
DWH_DB_USERNAME=root
DWH_DB_PASSWORD=
```

### 3. Jalankan Laravel
```bash
php artisan serve
```

Akses dashboard:
- http://localhost:8000/dashboard/sales-overview
- http://localhost:8000/dashboard/product-analysis
- http://localhost:8000/dashboard/customer-geo

---

## 📊 5 Pertanyaan Bisnis

| No | Pertanyaan | Halaman Dashboard |
|----|-----------|-------------------|
| 1 | Produk yang sering muncul bersamaan (market basket) | Sales Overview & Product Analysis |
| 2 | Territory dengan diskon tertinggi vs profit margin | Sales Overview |
| 3 | Segmen customer high-frequency low-ticket | Customer & Geo |
| 4 | Salesperson dengan retention rate terbaik | Customer & Geo |
| 5 | Inventory turnover per kategori | Product Analysis |

---

## 📁 Struktur File Penting

```
dwh/
  ├── schema.sql              → DDL (buat database & tabel)
  ├── etl.sql                 → ETL (populate data)
  └── analytics_queries.sql   → Query untuk 5 pertanyaan

app/Http/Controllers/
  └── DwReportController.php  → Controller dashboard

resources/views/dashboard/
  ├── sales-overview.blade.php   → Page 1
  ├── product-analysis.blade.php → Page 2
  └── customer-geo.blade.php     → Page 3

routes/web.php                → Routes dashboard
config/database.php           → Konfigurasi koneksi mysql_dwh
```

---

## 🛠️ Troubleshooting

**Error: Database adventureworks not found**
- Pastikan database source `adventureworks` (MySQL) sudah ada
- Download dari: https://github.com/Microsoft/sql-server-samples

**Dashboard tidak menampilkan data**
- Cek apakah ETL sudah dijalankan: `mysql -u root -p -e "USE adventureworks_dwh; SELECT COUNT(*) FROM FactSalesOrderLine;"`
- Harusnya ada ribuan rows

**Connection refused**
- Cek MySQL service running: `mysql -u root -p`
- Sesuaikan username/password di `.env`

---

## 📖 Dokumentasi Lengkap

Lihat file `SETUP_GUIDE.md` untuk dokumentasi detail termasuk:
- Penjelasan schema DW (Star Schema)
- Detail setiap pertanyaan bisnis
- Query analitik
- Enhancement ideas

---

## ✅ Checklist Tugas

- [x] Schema DW (Star/Snowflake) - minimal 1 fact table + 4 dimension tables ✓
- [x] ETL Script untuk populate DW dari source ✓
- [x] 5 Query analitik untuk menjawab business questions ✓
- [x] Web application dashboard dengan 3 halaman ✓
- [x] Visualisasi (grafik + tabel) untuk setiap pertanyaan ✓
- [ ] **TODO ANDA**: Jalankan schema.sql dan etl.sql
- [ ] **TODO ANDA**: Test akses dashboard pages
- [ ] **TODO ANDA**: Screenshot hasil untuk laporan

Semoga berhasil! 🎉
