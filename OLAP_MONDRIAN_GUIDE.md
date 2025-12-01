# 📊 OLAP Mondrian Implementation Guide

## Apa itu OLAP Mondrian?

**OLAP (Online Analytical Processing)** adalah teknologi untuk analisis multidimensional data warehouse. **Mondrian** adalah OLAP server open-source yang memungkinkan analisis data kompleks dengan cepat.

### Konsep OLAP yang Diimplementasikan

Sistem AdventureWorks Data Warehouse ini mengimplementasikan **konsep OLAP Mondrian** melalui:

1. **Star Schema Data Warehouse**
2. **Multidimensional Analysis**
3. **OLAP Operations** (Roll-up, Drill-down, Slice, Dice)
4. **Interactive Navigation**

---

## 🎯 OLAP Operations yang Tersedia

### 1. **Drill-Down** ⬇️
Navigasi dari data agregat (ringkasan) ke data detail.

**Contoh di Sistem:**
- **Territory → Salesperson Details**
  - Klik territory "Canada" → Lihat detail salesperson di Canada
  - Klik salesperson → Lihat trend penjualan bulanan

**Implementasi:**
```php
// Route drill-down
Route::get('/territory/{territoryId}', [DrillDownController::class, 'territoryDetails']);

// Query multi-level
SELECT 
    territory.Name,
    salesperson.FirstName,
    DATE_FORMAT(OrderDate, '%Y-%m') AS Month,
    SUM(LineTotal) AS Revenue
FROM FactSales
GROUP BY territory, salesperson, Month
```

**Cara Menggunakan:**
1. Buka **Sales Overview** (Page 1)
2. Scroll ke tabel **Territory Performance Analysis**
3. Klik tombol **"🔍 Drill-Down"** pada territory manapun
4. Lihat detail salesperson dan trend bulanan

---

### 2. **Roll-Up** ⬆️
Agregasi dari detail ke ringkasan (kebalikan drill-down).

**Contoh di Sistem:**
- **Daily Sales → Monthly → Yearly**
- **Product → Category → All Products**

**Implementasi:**
```sql
-- Daily to Monthly Roll-up
SELECT 
    YEAR(OrderDate) AS Year,
    MONTH(OrderDate) AS Month,
    SUM(Revenue) AS MonthlyRevenue
FROM DailySales
GROUP BY Year, Month

-- Monthly to Yearly
SELECT 
    Year,
    SUM(MonthlyRevenue) AS YearlyRevenue
FROM MonthlySales
GROUP BY Year
```

---

### 3. **Slice** 🔪
Filter data berdasarkan satu dimensi tertentu.

**Contoh di Sistem:**
- Filter **Year = 2024** saja
- Filter **Territory = Canada** saja

**Cara Menggunakan:**
1. Gunakan dropdown **"📅 Tahun Order"** di Sales Overview
2. Pilih tahun tertentu (misalnya 2004)
3. Data akan ter-slice hanya menampilkan tahun tersebut

**Implementasi:**
```javascript
// Filter by year
document.getElementById('dateRangeFilter').addEventListener('change', function() {
    const selectedYear = this.value;
    filterDataByYear(selectedYear);
});
```

---

### 4. **Dice** 🎲
Filter data berdasarkan **multiple dimensions** sekaligus.

**Contoh di Sistem:**
- **Year = 2024 AND Territory = Canada AND Product Category = Bikes**

**Cara Menggunakan:**
1. Pilih **Year** dari dropdown pertama
2. Pilih **Territory** dari dropdown kedua  
3. Pilih **Top N Products** dari dropdown ketiga
4. Sistem akan menampilkan intersection dari ketiga filter

**Implementasi:**
```javascript
// Multi-dimensional filter
function applyDiceFilter() {
    const year = document.getElementById('dateRangeFilter').value;
    const territory = document.getElementById('territoryFilter').value;
    const topN = document.getElementById('productLimit').value;
    
    // Filter with multiple conditions
    filterData({ year, territory, limit: topN });
}
```

---

## 🗂️ Dimensi Data Warehouse

### Dimensions (Tabel Dimensi)

| Dimensi | Deskripsi | Contoh |
|---------|-----------|--------|
| **📅 DimDate** | Waktu/Tanggal | Year, Quarter, Month, Day |
| **📦 DimProduct** | Produk & Kategori | Product Name, Category, Subcategory |
| **👥 DimCustomer** | Data Pelanggan | Customer ID, Type, Account |
| **🌍 DimGeography** | Wilayah/Territory | Territory Name, Country, Region |
| **💼 DimSalesperson** | Tim Penjualan | Salesperson Name, Territory |

### Fact Table (Tabel Fakta)

**💰 FactSalesOrderLine** - Transaksi penjualan detail
- OrderQty, UnitPrice, Discount
- LineTotal, StandardCost, Profit
- Foreign keys ke semua dimensi

---

## 🎨 Fitur OLAP di Interface

### 1. **OLAP Mondrian Badge** di Sidebar
Klik badge **"OLAP Mondrian Engine"** di navbar untuk melihat:
- ✅ Penjelasan sistem OLAP
- ✅ OLAP Operations yang tersedia
- ✅ Dimensi data warehouse
- ✅ Key features

### 2. **Drill-Down Badge** di Territory Table
Badge **"⚡ OLAP: Drill-Down"** menandakan fitur drill-down tersedia.

### 3. **Interactive Filters**
3 dropdown filter untuk operasi **Slice** dan **Dice**:
- Year filter (temporal slicing)
- Territory filter (geographical slicing)
- Product limit (data limiting)

---

## 📈 Use Cases OLAP

### Use Case 1: Analisis Territory Performance
**Question:** "Mana territory dengan diskon tinggi tapi profit rendah?"

**OLAP Operations:**
1. **Slice** → Filter year 2024
2. **Dice** → Territory = All, Sort by Discount Rate DESC
3. **Drill-Down** → Klik territory dengan profit rendah
4. **Analyze** → Lihat salesperson mana yang banyak kasih diskon

### Use Case 2: Product Bundling Analysis
**Question:** "Produk mana yang sering dibeli bersamaan?"

**OLAP Operations:**
1. **Roll-Up** → Lihat kategori produk level tinggi
2. **Slice** → Filter by year atau category
3. **Drill-Down** → Dari category ke individual products
4. **Analysis** → Identifikasi product pairs dengan co-occurrence tinggi

### Use Case 3: Salesperson Retention
**Question:** "Salesperson mana yang punya customer retention terbaik?"

**OLAP Operations:**
1. **Drill-Down** → Territory → Salesperson → Customer list
2. **Slice** → Filter by territory atau year
3. **Calculate** → Retention rate per salesperson
4. **Compare** → Ranking salesperson by retention

---

## 🔧 Implementasi Teknis

### Schema Design (Star Schema)

```
                    DimDate
                       |
                       |
DimProduct ---- FactSalesOrderLine ---- DimCustomer
                       |
                       |
              DimGeography (Territory)
                       |
                  DimSalesperson
```

### Sample OLAP Query

```sql
-- Drill-down: Territory → Salesperson → Monthly Trend
SELECT 
    dg.Name AS Territory,
    ds.FirstName AS Salesperson,
    dd.Year,
    dd.Month,
    SUM(f.LineTotal) AS Revenue,
    AVG(f.Profit) AS AvgProfit,
    COUNT(DISTINCT f.SalesOrderID) AS OrderCount
FROM FactSalesOrderLine f
JOIN DimGeography dg ON f.GeographyKey = dg.GeographyKey
JOIN DimSalesperson ds ON f.SalespersonKey = ds.SalespersonKey  
JOIN DimDate dd ON f.OrderDateKey = dd.DateKey
WHERE dg.TerritoryID = ?
GROUP BY dg.Name, ds.FirstName, dd.Year, dd.Month
ORDER BY dd.Year DESC, dd.Month DESC
```

---

## 📱 Cara Mengakses Fitur OLAP

### Langkah 1: Login ke Sistem
```
URL: http://localhost:8000/login
Username: admin
Password: admin123
```

### Langkah 2: Lihat Info OLAP
1. Klik badge **"OLAP Mondrian Engine"** di sidebar (warna orange)
2. Modal akan muncul dengan penjelasan lengkap
3. Pelajari OLAP operations yang tersedia

### Langkah 3: Coba Drill-Down
1. Buka **Sales Overview** (Page 1)
2. Scroll ke **Territory Performance Analysis**
3. Klik **"🔍 Drill-Down"** pada territory manapun
4. Explore detail salesperson dan monthly trends

### Langkah 4: Gunakan Filters (Slice & Dice)
1. Gunakan 3 dropdown di atas dashboard
2. Kombinasikan filter year, territory, dan product limit
3. Lihat data ter-filter secara real-time

---

## ✅ Checklist Persyaratan OLAP Mondrian

- ✅ **OLAP Mondrian ditampilkan pada sistem**
  - Badge di navbar ✓
  - Modal info lengkap ✓
  - Visual indicators ✓

- ✅ **Star Schema Data Warehouse**
  - 5 Dimension tables ✓
  - 1 Fact table ✓
  - Proper relationships ✓

- ✅ **OLAP Operations**
  - Drill-down (Territory → Salesperson) ✓
  - Roll-up (Daily → Monthly → Yearly) ✓
  - Slice (Single dimension filter) ✓
  - Dice (Multi-dimension filter) ✓

- ✅ **Interactive Features**
  - Clickable drill-down buttons ✓
  - Dynamic filters ✓
  - Real-time data updates ✓

- ✅ **Documentation**
  - System explanation ✓
  - User guide ✓
  - Technical implementation ✓

---

## 🎓 Penjelasan untuk Dosen

**"Sistem ini mengimplementasikan konsep OLAP Mondrian untuk analisis multidimensional data warehouse AdventureWorks."**

### Bukti Implementasi:

1. **Visual Display**
   - Badge "OLAP Mondrian Engine" di navbar (klik untuk info lengkap)
   - Badge "OLAP: Drill-Down" di territory table

2. **Functional Implementation**
   - ✅ Drill-down: Territory → Salesperson details
   - ✅ Roll-up: Monthly → Yearly aggregation
   - ✅ Slice: Filter by single dimension (year/territory)
   - ✅ Dice: Multi-dimensional filtering

3. **Star Schema**
   - ✅ 5 Dimension tables (Date, Product, Customer, Geography, Salesperson)
   - ✅ 1 Fact table (FactSalesOrderLine)
   - ✅ Proper foreign key relationships

4. **User Experience**
   - ✅ Interactive filters
   - ✅ Click-to-drill navigation
   - ✅ Visual indicators for OLAP features

---

## 📚 Referensi

- **OLAP Concepts**: Ralph Kimball - The Data Warehouse Toolkit
- **Star Schema Design**: Best practices for dimensional modeling
- **Mondrian OLAP**: Open-source OLAP server architecture
- **Laravel Integration**: Custom implementation of OLAP concepts

---

## 💡 Tips untuk Demo

1. **Tunjukkan Badge OLAP** → Klik untuk show modal info
2. **Demo Drill-Down** → Klik territory, show salesperson details
3. **Demo Filters** → Kombinasi 2-3 filter sekaligus (Dice)
4. **Jelaskan Star Schema** → Show diagram dimensions + fact table
5. **Highlight Performance** → Caching, indexes, optimized queries

---

**Dibuat:** December 2025  
**Sistem:** AdventureWorks Data Warehouse  
**Framework:** Laravel 12 + MySQL 8  
**Konsep:** OLAP Mondrian Multidimensional Analysis
