// ...existing code...

---

## BAB II
## PERANCANGAN DATA WAREHOUSE

---

### 2.1 Analisis Sumber Data

#### 2.1.1 Gambaran Umum Database AdventureWorks

Database **AdventureWorks 2008** merupakan sample database OLTP (Online Transaction Processing) yang dikembangkan oleh Microsoft untuk mendemonstrasikan sistem e-commerce perusahaan manufaktur sepeda. Database ini memiliki struktur normalisasi tinggi (3NF) dengan lebih dari 70 tabel yang dikelompokkan dalam beberapa schema:

- **Sales Schema**: Berisi data transaksi penjualan, customer, sales territory, dan salesperson
- **Production Schema**: Berisi data produk, kategori, inventory, dan bill of materials
- **Person Schema**: Berisi data personal, alamat, dan contact information
- **Purchasing Schema**: Berisi data vendor dan purchase orders

Untuk keperluan Data Warehouse ini, fokus analisis berada pada **Sales Schema** karena business questions yang dirumuskan berkaitan dengan analisis penjualan, customer behavior, dan performa salesperson.

#### 2.1.2 Identifikasi Tabel Sumber Data

Berdasarkan business requirements, tabel-tabel utama yang menjadi sumber data adalah:

**A. Tabel Transaksi (Fact Source)**

1. **Sales.SalesOrderHeader**
   - Primary Key: `SalesOrderID`
   - Atribut penting: `OrderDate`, `DueDate`, `ShipDate`, `CustomerID`, `SalesPersonID`, `TerritoryID`, `SubTotal`, `TaxAmt`, `Freight`, `TotalDue`
   - Volume: ~31,465 records
   - Grain: Per sales order

2. **Sales.SalesOrderDetail**
   - Primary Key: `SalesOrderID` + `SalesOrderDetailID`
   - Atribut penting: `ProductID`, `OrderQty`, `UnitPrice`, `UnitPriceDiscount`, `LineTotal`
   - Volume: ~121,317 records
   - Grain: Per order line item
   - **Grain Level Dipilih**: Order detail line (level paling detail untuk analisis produk)

**B. Tabel Dimensi (Dimension Source)**

3. **Sales.Customer**
   - Primary Key: `CustomerID`
   - Atribut: `PersonID`, `StoreID`, `TerritoryID`, `AccountNumber`
   - Volume: ~19,820 records
   - Join Path: `SalesOrderHeader.CustomerID → Customer.CustomerID`

4. **Person.Person**
   - Primary Key: `BusinessEntityID`
   - Atribut: `FirstName`, `MiddleName`, `LastName`, `PersonType`
   - Join Path: `Customer.PersonID → Person.BusinessEntityID`

5. **Production.Product**
   - Primary Key: `ProductID`
   - Atribut: `Name`, `ProductNumber`, `Color`, `StandardCost`, `ListPrice`, `ProductSubcategoryID`
   - Volume: ~504 products
   - Join Path: `SalesOrderDetail.ProductID → Product.ProductID`

6. **Production.ProductSubcategory**
   - Primary Key: `ProductSubcategoryID`
   - Atribut: `Name`, `ProductCategoryID`
   - Hierarchy: Level 2 (Subcategory)

7. **Production.ProductCategory**
   - Primary Key: `ProductCategoryID`
   - Atribut: `Name`
   - Hierarchy: Level 1 (Category)

8. **Sales.SalesPerson**
   - Primary Key: `BusinessEntityID`
   - Atribut: `TerritoryID`, `SalesQuota`, `Bonus`, `CommissionPct`, `SalesYTD`, `SalesLastYear`
   - Volume: ~17 salesperson
   - Join Path: `SalesOrderHeader.SalesPersonID → SalesPerson.BusinessEntityID`

9. **Sales.SalesTerritory**
   - Primary Key: `TerritoryID`
   - Atribut: `Name`, `CountryRegionCode`, `Group`, `SalesYTD`, `SalesLastYear`, `CostYTD`, `CostLastYear`
   - Volume: ~10 territories
   - Join Path: `SalesOrderHeader.TerritoryID → SalesTerritory.TerritoryID`

#### 2.1.3 Analisis Entity Relationship Diagram (ERD) Sumber Data

Berikut adalah diagram hubungan antar tabel sumber data (simplified):

```
┌─────────────────────┐
│  SalesOrderHeader   │
│ ─────────────────── │
│ SalesOrderID (PK)   │───┐
│ OrderDate           │   │
│ CustomerID (FK)     │───┼─────────────────────┐
│ SalesPersonID (FK)  │───┼──────────┐          │
│ TerritoryID (FK)    │───┼───┐      │          │
│ SubTotal            │   │   │      │          │
│ TaxAmt, Freight     │   │   │      │          │
└─────────────────────┘   │   │      │          │
            │             │   │      │          │
            │ 1           │   │      │          │
            │             │   │      │          │
            ▼ M           │   │      │          │
┌─────────────────────┐   │   │      │          │
│ SalesOrderDetail    │   │   │      │          │
│ ─────────────────── │   │   │      │          │
│ SalesOrderID (PK)   │◄──┘   │      │          │
│ SalesOrderDetailID  │       │      │          │
│ ProductID (FK)      │───────┼──────┼────┐     │
│ OrderQty            │       │      │    │     │
│ UnitPrice           │       │      │    │     │
│ UnitPriceDiscount   │       │      │    │     │
│ LineTotal           │       │      │    │     │
└─────────────────────┘       │      │    │     │
                              │      │    │     │
┌─────────────────────┐       │      │    │     │
│  SalesTerritory     │       │      │    │     │
│ ─────────────────── │       │      │    │     │
│ TerritoryID (PK)    │◄──────┘      │    │     │
│ Name                │              │    │     │
│ CountryRegionCode   │              │    │     │
│ Group               │              │    │     │
└─────────────────────┘              │    │     │
                                     │    │     │
┌─────────────────────┐              │    │     │
│   SalesPerson       │              │    │     │
│ ─────────────────── │              │    │     │
│ BusinessEntityID(PK)│◄─────────────┘    │     │
│ TerritoryID (FK)    │                   │     │
│ SalesQuota          │                   │     │
└─────────────────────┘                   │     │
            │                             │     │
            │ 1                           │     │
            ▼ 1                           │     │
┌─────────────────────┐                   │     │
│   Person.Person     │                   │     │
│ ─────────────────── │                   │     │
│ BusinessEntityID(PK)│                   │     │
│ FirstName           │                   │     │
│ LastName            │                   │     │
└─────────────────────┘                   │     │
            ▲                             │     │
            │ 1                           │     │
            │                             │     │
┌─────────────────────┐                   │     │
│   Sales.Customer    │                   │     │
│ ─────────────────── │                   │     │
│ CustomerID (PK)     │◄──────────────────┘     │
│ PersonID (FK)       │─────────────────────────┘
│ TerritoryID (FK)    │
└─────────────────────┘
            │
            ▼
┌─────────────────────┐
│ Production.Product  │
│ ─────────────────── │
│ ProductID (PK)      │◄────────────────────────┘
│ Name                │
│ ProductSubcatID(FK) │───┐
│ StandardCost        │   │
│ ListPrice           │   │
└─────────────────────┘   │
                          │ M
                          │
                          ▼ 1
┌─────────────────────────────┐
│ Production.ProductSubcategory│
│ ──────────────────────────── │
│ ProductSubcategoryID (PK)    │
│ Name                         │
│ ProductCategoryID (FK)       │───┐
└─────────────────────────────┘   │ M
                                  │
                                  ▼ 1
┌─────────────────────────────┐
│ Production.ProductCategory   │
│ ──────────────────────────── │
│ ProductCategoryID (PK)       │
│ Name                         │
└─────────────────────────────┘
```

#### 2.1.4 Data Quality Assessment

Sebelum proses ETL, dilakukan analisis kualitas data:

**A. Completeness (Kelengkapan Data)**
- ✅ Primary keys di semua tabel tidak ada NULL
- ⚠️ `SalesOrderHeader.SalesPersonID` memiliki ~22% NULL values (online orders tanpa sales rep)
- ✅ Mandatory fields seperti OrderDate, ProductID, CustomerID lengkap 100%

**B. Consistency (Konsistensi Data)**
- ✅ Referential integrity terjaga dengan foreign key constraints
- ✅ Format tanggal konsisten (DATETIME)
- ✅ Currency dalam USD semua

**C. Accuracy (Akurasi Data)**
- ✅ Validasi: `LineTotal = OrderQty * UnitPrice * (1 - UnitPriceDiscount)` = akurat
- ✅ SubTotal di header = SUM(LineTotal) dari detail = konsisten

**D. Data Volume per Tahun**

| Year | Orders | Order Lines | Revenue ($) |
|------|--------|-------------|-------------|
| 2001 | 1,013  | 3,863       | 3,266,374   |
| 2002 | 5,904  | 24,485      | 19,169,881  |
| 2003 | 12,244 | 48,112      | 38,984,197  |
| 2004 | 12,304 | 44,857      | 37,656,528  |

**Total**: 31,465 orders | 121,317 lines | $99,076,980

#### 2.1.5 Business Requirements Mapping

Setiap business question dipetakan ke tabel sumber yang dibutuhkan:

**Q1: Market Basket Analysis**
- Primary: `SalesOrderDetail` (untuk co-occurrence)
- Join: `Product`, `ProductSubcategory`, `ProductCategory`
- Filter: Last 3 months dari OrderDate

**Q2: Territory Discount vs Profit**
- Primary: `SalesOrderDetail` joined dengan `SalesOrderHeader`
- Dimensions: `SalesTerritory`
- Metrics: AVG(UnitPriceDiscount), SUM(Profit)
- Formula Profit: `(UnitPrice - StandardCost) * OrderQty * (1 - UnitPriceDiscount)`

**Q3: Customer Segmentation**
- Primary: `SalesOrderHeader` aggregated by `CustomerID`
- Join: `Customer`, `Person`
- Metrics: COUNT(DISTINCT OrderID), SUM(TotalDue)
- Segmentation: RFM-like (Frequency + Monetary)

**Q4: Salesperson Retention**
- Primary: `SalesOrderHeader` aggregated by `SalesPersonID` + Month
- Join: `SalesPerson`, `Person`
- Metrics: SUM(Revenue), COUNT active months
- Trend: Monthly time series

**Q5: Inventory Turnover**
- Primary: `SalesOrderDetail` aggregated by `ProductID`
- Join: `Product`, `ProductSubcategory`, `ProductCategory`
- Metrics: SUM(OrderQty) vs initial stock (assumed)
- Group by: ProductCategory

---

### 2.2 Perancangan Dimensi

Dalam Data Warehouse, dimensi memberikan konteks untuk menganalisis measures di fact table. Perancangan dimensi mengikuti prinsip **Kimball Dimensional Modeling**.

#### 2.2.1 Prinsip Perancangan Dimensi

**A. Denormalisasi**
Dimensi di-denormalisasi untuk mempercepat query (tidak seperti OLTP yang normalized). Contoh: DimProduct menyimpan langsung CategoryName dan SubcategoryName, tanpa tabel terpisah.

**B. Surrogate Key**
Setiap dimensi menggunakan surrogate key (auto-increment integer) sebagai primary key, bukan natural key dari source. Ini memberikan:
- Independensi dari perubahan business key
- Performa join lebih cepat
- Kemudahan tracking SCD (Slowly Changing Dimension)

**C. Hierarchies**
Dimensi memiliki hierarchy untuk mendukung drill-down/roll-up:
- TIME: Year → Quarter → Month → Date
- Product: Category → Subcategory → Product
- Geography: Group → Country → Territory

#### 2.2.2 DimDate (Time Dimension)

**Tujuan**: Menyediakan dimensi waktu dengan hierarchy lengkap untuk analisis temporal.

**Struktur Tabel**:

```sql
CREATE TABLE DimDate (
    DateKey INT PRIMARY KEY,           -- Format: YYYYMMDD (e.g., 20030115)
    FullDate DATE NOT NULL,            -- 2003-01-15
    DayOfWeek INT,                     -- 1-7 (Monday-Sunday)
    DayName VARCHAR(10),               -- 'Monday', 'Tuesday', ...
    DayOfMonth INT,                    -- 1-31
    DayOfYear INT,                     -- 1-366
    WeekOfYear INT,                    -- 1-53
    MonthName VARCHAR(10),             -- 'January', 'February', ...
    MonthOfYear INT,                   -- 1-12
    Quarter INT,                       -- 1-4
    QuarterName VARCHAR(10),           -- 'Q1', 'Q2', 'Q3', 'Q4'
    Year INT,                          -- 2001, 2002, 2003, 2004
    IsWeekend BOOLEAN,                 -- TRUE/FALSE
    IsHoliday BOOLEAN,                 -- TRUE/FALSE (optional)
    FiscalYear INT,                    -- Tahun fiskal
    FiscalQuarter INT,                 -- Quarter fiskal
    UNIQUE KEY idx_fulldate (FullDate)
);
```

**Source Mapping**:
- Extracted dari `SalesOrderHeader.OrderDate`
- Generated untuk range: MIN(OrderDate) sampai MAX(OrderDate) + future buffer

**ETL Logic** (lihat [`dwh/etl_dim_date.sql`](dwh/etl_dim_date.sql)):
```sql
INSERT INTO DimDate (DateKey, FullDate, DayOfWeek, ...)
SELECT 
    DATE_FORMAT(OrderDate, '%Y%m%d') AS DateKey,
    DATE(OrderDate) AS FullDate,
    DAYOFWEEK(OrderDate) AS DayOfWeek,
    DAYNAME(OrderDate) AS DayName,
    ...
FROM SalesOrderHeader
```

**Hierarchy**:
```
Year (2003)
  └── Quarter (Q1)
        └── Month (January)
              └── Date (2003-01-15)
```

**Sample Data**:

| DateKey  | FullDate   | Year | Quarter | MonthName | DayName  |
|----------|------------|------|---------|-----------|----------|
| 20030101 | 2003-01-01 | 2003 | 1       | January   | Wednesday|
| 20030115 | 2003-01-15 | 2003 | 1       | January   | Wednesday|
| 20030401 | 2003-04-01 | 2003 | 2       | April     | Tuesday  |

**Volume**: 1,139 unique dates (dari 2001 hingga 2004)

#### 2.2.3 DimProduct (Product Dimension)

**Tujuan**: Menyediakan informasi lengkap produk dengan hierarchy Category → Subcategory → Product.

**Struktur Tabel**:

```sql
CREATE TABLE DimProduct (
    ProductKey INT AUTO_INCREMENT PRIMARY KEY,  -- Surrogate key
    ProductID INT NOT NULL,                     -- Business key dari source
    ProductName VARCHAR(50),
    ProductNumber VARCHAR(25),
    Color VARCHAR(15),
    StandardCost DECIMAL(10,2),
    ListPrice DECIMAL(10,2),
    ProductSubcategoryID INT,
    ProductSubcategoryName VARCHAR(50),
    ProductCategoryID INT,
    ProductCategoryName VARCHAR(50),            -- Denormalized
    UNIQUE KEY idx_product_id (ProductID),
    INDEX idx_category (ProductCategoryName),
    INDEX idx_subcategory (ProductSubcategoryName)
);
```

**Source Mapping**:
```sql
-- FROM Production.Product p
-- LEFT JOIN Production.ProductSubcategory ps 
--   ON p.ProductSubcategoryID = ps.ProductSubcategoryID
-- LEFT JOIN Production.ProductCategory pc 
--   ON ps.ProductCategoryID = pc.ProductCategoryID
```

**ETL Logic** (lihat [`dwh/etl_dim_product.sql`](dwh/etl_dim_product.sql)):
```sql
INSERT INTO DimProduct (ProductID, ProductName, ProductCategoryName, ...)
SELECT 
    p.ProductID,
    p.Name AS ProductName,
    COALESCE(pc.Name, 'Uncategorized') AS ProductCategoryName,
    COALESCE(ps.Name, 'No Subcategory') AS ProductSubcategoryName,
    ...
FROM Production.Product p
LEFT JOIN Production.ProductSubcategory ps ON p.ProductSubcategoryID = ps.ProductSubcategoryID
LEFT JOIN Production.ProductCategory pc ON ps.ProductCategoryID = pc.ProductCategoryID
```

**Hierarchy**:
```
Category (Bikes)
  └── Subcategory (Mountain Bikes)
        └── Product (Mountain-200 Black, 38)
```

**Sample Data**:

| ProductKey | ProductID | ProductName        | CategoryName | SubcategoryName |
|------------|-----------|-------------------|--------------|-----------------|
| 1          | 707       | Sport-100 Helmet  | Accessories  | Helmets         |
| 15         | 771       | Mountain-100      | Bikes        | Mountain Bikes  |

**Product Categories**:
- **Bikes**: Mountain Bikes, Road Bikes, Touring Bikes
- **Components**: Handlebars, Brakes, Chains, etc.
- **Clothing**: Jerseys, Shorts, Gloves
- **Accessories**: Helmets, Bottles, Pumps

**Volume**: 504 products

#### 2.2.4 DimCustomer (Customer Dimension)

**Tujuan**: Menyimpan informasi customer untuk analisis segmentasi dan behavior.

**Struktur Tabel**:

```sql
CREATE TABLE DimCustomer (
    CustomerKey INT AUTO_INCREMENT PRIMARY KEY,
    CustomerID INT NOT NULL,                    -- Business key
    PersonID INT,
    CustomerName VARCHAR(100),                  -- FirstName + LastName
    AccountNumber VARCHAR(20),
    CustomerType VARCHAR(20),                   -- 'Individual' or 'Store'
    TerritoryID INT,
    TerritoryName VARCHAR(50),                  -- Denormalized
    UNIQUE KEY idx_customer_id (CustomerID),
    INDEX idx_territory (TerritoryID),
    INDEX idx_customer_type (CustomerType)
);
```

**Source Mapping**:
```sql
-- FROM Sales.Customer c
-- LEFT JOIN Person.Person p ON c.PersonID = p.BusinessEntityID
-- LEFT JOIN Sales.SalesTerritory t ON c.TerritoryID = t.TerritoryID
```

**ETL Logic** (lihat [`dwh/etl_dim_customer.sql`](dwh/etl_dim_customer.sql)):
```sql
INSERT INTO DimCustomer (CustomerID, CustomerName, CustomerType, ...)
SELECT 
    c.CustomerID,
    CONCAT(p.FirstName, ' ', COALESCE(p.LastName, '')) AS CustomerName,
    CASE WHEN c.PersonID IS NOT NULL THEN 'Individual' ELSE 'Store' END AS CustomerType,
    t.Name AS TerritoryName,
    ...
FROM Sales.Customer c
LEFT JOIN Person.Person p ON c.PersonID = p.BusinessEntityID
LEFT JOIN Sales.SalesTerritory t ON c.TerritoryID = t.TerritoryID
```

**Sample Data**:

| CustomerKey | CustomerID | CustomerName     | CustomerType | TerritoryName |
|-------------|------------|------------------|--------------|---------------|
| 1           | 29485      | Catherine Abel   | Individual   | Southwest     |
| 100         | 29584      | Kim Abercrombie  | Individual   | Northwest     |

**Customer Types**:
- **Individual**: 19,119 customers (retail/end consumers)
- **Store**: 701 customers (resellers/distributors)

**Volume**: 19,820 customers

#### 2.2.5 DimSalesperson (Salesperson Dimension)

**Tujuan**: Informasi sales representative untuk analisis performa dan retention.

**Struktur Tabel**:

```sql
CREATE TABLE DimSalesperson (
    SalespersonKey INT AUTO_INCREMENT PRIMARY KEY,
    SalespersonID INT NOT NULL,                 -- BusinessEntityID
    SalespersonName VARCHAR(100),               -- FirstName + LastName
    TerritoryID INT,
    TerritoryName VARCHAR(50),
    SalesQuota DECIMAL(15,2),
    CommissionPct DECIMAL(5,4),
    SalesYTD DECIMAL(15,2),
    SalesLastYear DECIMAL(15,2),
    UNIQUE KEY idx_salesperson_id (SalespersonID),
    INDEX idx_territory (TerritoryID)
);
```

**Source Mapping**:
```sql
-- FROM Sales.SalesPerson sp
-- INNER JOIN Person.Person p ON sp.BusinessEntityID = p.BusinessEntityID
-- LEFT JOIN Sales.SalesTerritory t ON sp.TerritoryID = t.TerritoryID
```

**ETL Logic** (lihat [`dwh/etl_dim_salesperson.sql`](dwh/etl_dim_salesperson.sql)):
```sql
INSERT INTO DimSalesperson (SalespersonID, SalespersonName, ...)
SELECT 
    sp.BusinessEntityID AS SalespersonID,
    CONCAT(p.FirstName, ' ', p.LastName) AS SalespersonName,
    t.Name AS TerritoryName,
    sp.SalesQuota,
    ...
FROM Sales.SalesPerson sp
INNER JOIN Person.Person p ON sp.BusinessEntityID = p.BusinessEntityID
LEFT JOIN Sales.SalesTerritory t ON sp.TerritoryID = t.TerritoryID
```

**Sample Data**:

| SalespersonKey | SalespersonID | SalespersonName    | TerritoryName |
|----------------|---------------|--------------------|---------------|
| 1              | 274           | Stephen Jiang      | Southwest     |
| 5              | 277           | Jillian Carson     | Central       |

**Volume**: 17 sales representatives

#### 2.2.6 DimGeography (Territory Dimension)

**Tujuan**: Dimensi geografis untuk analisis berdasarkan sales territory.

**Struktur Tabel**:

```sql
CREATE TABLE DimGeography (
    TerritoryKey INT AUTO_INCREMENT PRIMARY KEY,
    TerritoryID INT NOT NULL,
    TerritoryName VARCHAR(50),
    CountryRegionCode VARCHAR(3),
    `Group` VARCHAR(50),                        -- 'North America', 'Europe', etc.
    SalesYTD DECIMAL(15,2),
    SalesLastYear DECIMAL(15,2),
    CostYTD DECIMAL(15,2),
    CostLastYear DECIMAL(15,2),
    UNIQUE KEY idx_territory_id (TerritoryID),
    INDEX idx_group (`Group`)
);
```

**Source Mapping**:
```sql
-- FROM Sales.SalesTerritory
```

**Hierarchy**:
```
Group (North America)
  └── Country (US)
        └── Territory (Southwest)
```

**Sample Data**:

| TerritoryKey | TerritoryID | TerritoryName | Group         | Country |
|--------------|-------------|---------------|---------------|---------|
| 1            | 1           | Northwest     | North America | US      |
| 4            | 4           | Southwest     | North America | US      |
| 7            | 7           | France        | Europe        | FR      |

**Territories by Group**:
- **North America**: Northwest, Northeast, Central, Southwest, Southeast, Canada
- **Europe**: France, Germany, United Kingdom
- **Pacific**: Australia

**Volume**: 10 territories

---

### 2.3 Perancangan Tabel Fakta & Measures

#### 2.3.1 Konsep Fact Table

Fact table adalah tabel sentral dalam star schema yang menyimpan **measures** (nilai numerik yang dapat di-agregasi) dan **foreign keys** ke dimension tables. Karakteristik fact table:

- **Grain**: Level detail paling rendah (dalam kasus ini: order line item)
- **Additive Measures**: Dapat dijumlahkan di semua dimensi (Revenue, Quantity, Profit)
- **Semi-Additive**: Dapat dijumlahkan di beberapa dimensi (Inventory level - tidak bisa dijumlahkan di waktu)
- **Non-Additive**: Tidak bisa dijumlahkan (Ratio, Percentage)

#### 2.3.2 FactSalesOrderLine Structure

**Struktur Tabel**:

```sql
CREATE TABLE FactSalesOrderLine (
    SalesOrderLineKey BIGINT AUTO_INCREMENT PRIMARY KEY,  -- Surrogate key
    
    -- Foreign Keys ke Dimensi
    OrderDateKey INT NOT NULL,              -- FK ke DimDate
    DueDateKey INT,                         -- FK ke DimDate
    ShipDateKey INT,                        -- FK ke DimDate
    ProductKey INT NOT NULL,                -- FK ke DimProduct
    CustomerKey INT NOT NULL,               -- FK ke DimCustomer
    SalespersonKey INT,                     -- FK ke DimSalesperson (nullable)
    TerritoryKey INT NOT NULL,              -- FK ke DimGeography
    
    -- Degenerate Dimensions (tidak perlu tabel terpisah)
    SalesOrderID INT NOT NULL,              -- Order number
    SalesOrderDetailID INT NOT NULL,        -- Line item number
    
    -- Measures (Fakta Numerik)
    OrderQuantity INT NOT NULL,             -- Qty produk dipesan
    UnitPrice DECIMAL(10,2) NOT NULL,       -- Harga satuan
    UnitPriceDiscount DECIMAL(5,4),         -- Diskon (0.00 - 1.00)
    LineTotal DECIMAL(15,2) NOT NULL,       -- Revenue per line
    StandardCost DECIMAL(10,2),             -- Cost per unit
    Profit DECIMAL(15,2),                   -- Calculated: (UnitPrice - Cost) * Qty
    ProfitMargin DECIMAL(5,2),              -- Calculated: Profit / LineTotal * 100
    
    -- Measures dari Header Level (denormalized untuk performa)
    SubTotal DECIMAL(15,2),                 -- Subtotal dari order
    TaxAmt DECIMAL(15,2),                   -- Pajak
    Freight DECIMAL(15,2),                  -- Biaya kirim
    TotalDue DECIMAL(15,2),                 -- Total bayar
    
    -- Indexes untuk Performa
    INDEX idx_order_date (OrderDateKey),
    INDEX idx_product (ProductKey),
    INDEX idx_customer (CustomerKey),
    INDEX idx_salesperson (SalespersonKey),
    INDEX idx_territory (TerritoryKey),
    INDEX idx_sales_order (SalesOrderID),
    
    -- Foreign Key Constraints
    FOREIGN KEY (OrderDateKey) REFERENCES DimDate(DateKey),
    FOREIGN KEY (ProductKey) REFERENCES DimProduct(ProductKey),
    FOREIGN KEY (CustomerKey) REFERENCES DimCustomer(CustomerKey),
    FOREIGN KEY (SalespersonKey) REFERENCES DimSalesperson(SalespersonKey),
    FOREIGN KEY (TerritoryKey) REFERENCES DimGeography(TerritoryKey)
);
```

#### 2.3.3 Measures Definition

**A. Base Measures (dari source langsung)**

1. **OrderQuantity**
   - Definisi: Jumlah unit produk yang dipesan
   - Type: Additive
   - Source: `SalesOrderDetail.OrderQty`
   - Business Use: Analisis volume penjualan per produk/periode

2. **UnitPrice**
   - Definisi: Harga jual per unit produk
   - Type: Non-additive (harus di-average)
   - Source: `SalesOrderDetail.UnitPrice`
   - Business Use: Analisis pricing strategy

3. **UnitPriceDiscount**
   - Definisi: Persentase diskon (0-100%)
   - Type: Semi-additive (average untuk analisis)
   - Source: `SalesOrderDetail.UnitPriceDiscount`
   - Format: 0.15 = 15% discount
   - Business Use: Analisis efektifitas promosi

4. **LineTotal**
   - Definisi: Total revenue per line item
   - Type: Additive
   - Source: `SalesOrderDetail.LineTotal`
   - Formula: `OrderQty * UnitPrice * (1 - UnitPriceDiscount)`
   - Business Use: Revenue analysis (primary KPI)

5. **StandardCost**
   - Definisi: Cost produksi per unit
   - Type: Non-additive
   - Source: `Production.Product.StandardCost`
   - Business Use: Profit calculation

**B. Calculated Measures (dihitung saat ETL)**

6. **Profit**
   - Definisi: Keuntungan bersih per line
   - Type: Additive
   - Formula: `(UnitPrice - StandardCost) * OrderQuantity * (1 - UnitPriceDiscount)`
   - SQL:
     ```sql
     (sod.UnitPrice - p.StandardCost) * sod.OrderQty * (1 - sod.UnitPriceDiscount) AS Profit
     ```
   - Business Use: Profitability analysis

7. **ProfitMargin**
   - Definisi: Persentase profit dari revenue
   - Type: Semi-additive (weighted average)
   - Formula: `(Profit / LineTotal) * 100`
   - SQL:
     ```sql
     CASE WHEN sod.LineTotal > 0 
          THEN ((sod.UnitPrice - p.StandardCost) * sod.OrderQty * (1 - sod.UnitPriceDiscount) / sod.LineTotal) * 100
          ELSE 0 
     END AS ProfitMargin
     ```
   - Business Use: Margin optimization (Q2 business question)

**C. Header-level Measures (denormalized)**

8. **SubTotal**
   - Source: `SalesOrderHeader.SubTotal`
   - Business Use: Order-level revenue

9. **TaxAmt**
   - Source: `SalesOrderHeader.TaxAmt`
   - Business Use: Tax analysis

10. **Freight**
    - Source: `SalesOrderHeader.Freight`
    - Business Use: Shipping cost analysis

11. **TotalDue**
    - Source: `SalesOrderHeader.TotalDue`
    - Formula: `SubTotal + TaxAmt + Freight`
    - Business Use: Total transaction value

#### 2.3.4 ETL Logic untuk Fact Table

**Extract & Transform** (lihat [`dwh/etl_fact_sales.sql`](dwh/etl_fact_sales.sql)):

```sql
INSERT INTO FactSalesOrderLine (
    OrderDateKey, ProductKey, CustomerKey, SalespersonKey, TerritoryKey,
    SalesOrderID, SalesOrderDetailID,
    OrderQuantity, UnitPrice, UnitPriceDiscount, LineTotal,
    StandardCost, Profit, ProfitMargin,
    SubTotal, TaxAmt, Freight, TotalDue
)
SELECT 
    -- Date Keys
    DATE_FORMAT(h.OrderDate, '%Y%m%d') AS OrderDateKey,
    
    -- Dimension Keys (lookup dari dimension tables)
    dp.ProductKey,
    dc.CustomerKey,
    COALESCE(ds.SalespersonKey, -1) AS SalespersonKey,  -- -1 untuk NULL
    dg.TerritoryKey,
    
    -- Degenerate Dimensions
    h.SalesOrderID,
    d.SalesOrderDetailID,
    
    -- Base Measures
    d.OrderQty AS OrderQuantity,
    d.UnitPrice,
    d.UnitPriceDiscount,
    d.LineTotal,
    p.StandardCost,
    
    -- Calculated Measures
    (d.UnitPrice - p.StandardCost) * d.OrderQty * (1 - d.UnitPriceDiscount) AS Profit,
    
    CASE WHEN d.LineTotal > 0 
         THEN ((d.UnitPrice - p.StandardCost) * d.OrderQty * (1 - d.UnitPriceDiscount) / d.LineTotal) * 100
         ELSE 0 
    END AS ProfitMargin,
    
    -- Header Measures
    h.SubTotal,
    h.TaxAmt,
    h.Freight,
    h.TotalDue

FROM adventureworks.sales_SalesOrderDetail d
INNER JOIN adventureworks.sales_SalesOrderHeader h 
    ON d.SalesOrderID = h.SalesOrderID
INNER JOIN adventureworks.production_Product p 
    ON d.ProductID = p.ProductID

-- Lookups ke Dimension Tables
INNER JOIN DimProduct dp ON p.ProductID = dp.ProductID
INNER JOIN DimCustomer dc ON h.CustomerID = dc.CustomerID
LEFT JOIN DimSalesperson ds ON h.SalesPersonID = ds.SalespersonID
INNER JOIN DimGeography dg ON h.TerritoryID = dg.TerritoryID;
```

**Load Statistics**:
- Total Records: **121,317 lines**
- Time Period: 2001-2004
- Average Lines per Order: ~3.85
- Null SalesPersonID: ~22% (online orders)

#### 2.3.5 Data Validation

**Post-ETL Validation Queries**:

```sql
-- 1. Revenue Validation
SELECT 
    SUM(LineTotal) AS TotalRevenue_Fact,
    (SELECT SUM(LineTotal) FROM adventureworks.sales_SalesOrderDetail) AS TotalRevenue_Source
FROM FactSalesOrderLine;
-- Expected: Sama

-- 2. Profit Calculation Check
SELECT 
    ProductKey,
    SUM(OrderQuantity) AS TotalQty,
    SUM(Profit) AS TotalProfit,
    AVG(ProfitMargin) AS AvgMargin
FROM FactSalesOrderLine
GROUP BY ProductKey
HAVING TotalProfit < 0;
-- Expected: Identify loss-making products

-- 3. Orphan Records Check
SELECT COUNT(*) 
FROM FactSalesOrderLine f
LEFT JOIN DimProduct p ON f.ProductKey = p.ProductKey
WHERE p.ProductKey IS NULL;
-- Expected: 0 (no orphans)
```

---

### 2.4 Skema Data Warehouse

#### 2.4.1 Star Schema Design

Data Warehouse ini menggunakan **Star Schema**, di mana:
- **1 Fact Table** (FactSalesOrderLine) di tengah
- **5 Dimension Tables** mengelilingi fact table
- Relationship: Many-to-One dari Fact ke setiap Dimension

**Diagram Star Schema**:

```
                    ┌─────────────────┐
                    │    DimDate      │
                    ├─────────────────┤
                    │ DateKey (PK)    │
                    │ FullDate        │
                    │ Year            │
                    │ Quarter         │
                    │ MonthName       │
                    │ DayName         │
                    └────────┬────────┘
                             │
                             │ 1
                             │
┌─────────────────┐          │          ┌─────────────────┐
│  DimProduct     │          │          │  DimCustomer    │
├─────────────────┤          │          ├─────────────────┤
│ ProductKey (PK) │          │          │CustomerKey (PK) │
│ ProductID       │          │          │ CustomerID      │
│ ProductName     │          │          │ CustomerName    │
│ CategoryName    │◄─────────┼──────────┤ CustomerType    │
│ SubcategoryName │          │          │ TerritoryName   │
│ StandardCost    │          │          └────────┬────────┘
│ ListPrice       │          │                   │
└────────┬────────┘          │                   │ M
         │                   │                   │
         │ 1                 │                   │
         │                   ▼ M                 │
         │          ┌─────────────────────────┐  │
         │          │ FactSalesOrderLine      │  │
         │          ├─────────────────────────┤  │
         └─────────►│ SalesOrderLineKey (PK)  │◄─┘
                    │                         │
                    │ -- Foreign Keys --      │
                    │ OrderDateKey (FK)       │
                    │ ProductKey (FK)         │
                    │ CustomerKey (FK)        │
         ┌──────────┤ SalespersonKey (FK)     │
         │          │ TerritoryKey (FK)       │
         │          │                         │
         │          │ -- Degenerate Dims --   │
         │          │ SalesOrderID            │
         │          │ SalesOrderDetailID      │
         │          │                         │
         │          │ -- Measures --          │
         │          │ OrderQuantity           │
         │          │ UnitPrice               │
         │          │ UnitPriceDiscount       │
         │          │ LineTotal               │
         │          │ Profit                  │
         │          │ ProfitMargin            │
         │          └────────┬────────────────┘
         │ 1                 │ M          M │
         │                   │              │
         │                   │              │
┌────────┴────────┐  ┌───────▼────────┐    │
│ DimSalesperson  │  │  DimGeography  │    │
├─────────────────┤  ├────────────────┤    │
│SalespersonKey   │  │TerritoryKey(PK)│◄───┘
│ SalespersonID   │  │ TerritoryID    │
│ SalespersonName │  │ TerritoryName  │
│ TerritoryName   │  │ Group          │
│ SalesQuota      │  │ CountryCode    │
└─────────────────┘  └────────────────┘
```

**Keuntungan Star Schema**:
1. ✅ **Simple Queries**: Join langsung dari fact ke dimension (1 hop)
2. ✅ **Fast Performance**: Minimal joins, database optimizer friendly
3. ✅ **Easy to Understand**: Business users dapat memahami struktur
4. ✅ **Flexible**: Mudah menambah dimensi baru

#### 2.4.2 Physical Schema Considerations

**A. Indexing Strategy**

1. **Fact Table Indexes**:
   ```sql
   -- Primary key (clustered index di MySQL InnoDB)
   PRIMARY KEY (SalesOrderLineKey)
   
   -- Foreign key indexes untuk join performa
   INDEX idx_order_date (OrderDateKey)
   INDEX idx_product (ProductKey)
   INDEX idx_customer (CustomerKey)
   INDEX idx_salesperson (SalespersonKey)
   INDEX idx_territory (TerritoryKey)
   
   -- Business query optimization
   INDEX idx_sales_order (SalesOrderID)  -- untuk drill-down per order
   INDEX idx_date_product (OrderDateKey, ProductKey)  -- untuk time series per product
   ```

2. **Dimension Table Indexes**:
   ```sql
   -- DimDate
   PRIMARY KEY (DateKey)
   UNIQUE KEY (FullDate)
   INDEX idx_year_quarter (Year, Quarter)
   
   -- DimProduct
   PRIMARY KEY (ProductKey)
   UNIQUE KEY (ProductID)
   INDEX idx_category (ProductCategoryName)
   
   -- DimCustomer
   PRIMARY KEY (CustomerKey)
   UNIQUE KEY (CustomerID)
   INDEX idx_customer_type (CustomerType)
   
   -- DimSalesperson
   PRIMARY KEY (SalespersonKey)
   UNIQUE KEY (SalespersonID)
   
   -- DimGeography
   PRIMARY KEY (TerritoryKey)
   UNIQUE KEY (TerritoryID)
   INDEX idx_group (`Group`)
   ```

**B. Data Types Optimization**

| Column Type       | Data Type        | Reason                          |
|-------------------|------------------|---------------------------------|
| Keys (PK/FK)      | INT/BIGINT       | Integer join lebih cepat        |
| Date Keys         | INT              | Format YYYYMMDD = 8 bytes       |
| Money/Decimal     | DECIMAL(15,2)    | Precision untuk currency        |
| Percentages       | DECIMAL(5,4)     | 0.1234 = 12.34%                 |
| Quantities        | INT              | Whole numbers                   |
| Names/Text        | VARCHAR(n)       | Variable length, save space     |

**C. Partitioning Strategy** (untuk large datasets)

Jika data terus bertumbuh (future scaling):
```sql
-- Partition by Year untuk time-based queries
ALTER TABLE FactSalesOrderLine
PARTITION BY RANGE (OrderDateKey) (
    PARTITION p2001 VALUES LESS THAN (20020101),
    PARTITION p2002 VALUES LESS THAN (20030101),
    PARTITION p2003 VALUES LESS THAN (20040101),
    PARTITION p2004 VALUES LESS THAN (20050101),
    PARTITION pFuture VALUES LESS THAN MAXVALUE
);
```

Benefit: Query yang filter by year hanya scan partition relevam.

#### 2.4.3 Data Volume & Storage

**Storage Estimates**:

| Table                | Rows      | Avg Row Size | Total Size |
|----------------------|-----------|--------------|------------|
| FactSalesOrderLine   | 121,317   | ~180 bytes   | ~27 MB     |
| DimDate              | 1,139     | ~70 bytes    | ~80 KB     |
| DimProduct           | 504       | ~200 bytes   | ~100 KB    |
| DimCustomer          | 19,820    | ~90 bytes    | ~1.8 MB    |
| DimSalesperson       | 17        | ~120 bytes   | ~2 KB      |
| DimGeography         | 10        | ~150 bytes   | ~1.5 KB    |
| **Total DW Size**    |           |              | **~29 MB** |

**Growth Projection** (jika data bertumbuh):
- Year 1: ~30 MB
- Year 3: ~90 MB (assuming same growth rate)
- Year 5: ~150 MB

Ini masih sangat kecil, MySQL dapat handle sampai GB/TB dengan mudah.

#### 2.4.4 Query Performance Benchmarks

**Sample Analytical Queries**:

1. **Total Revenue per Year** (~10ms):
   ```sql
   SELECT 
       d.Year,
       SUM(f.LineTotal) AS Revenue
   FROM FactSalesOrderLine f
   JOIN DimDate d ON f.OrderDateKey = d.DateKey
   GROUP BY d.Year
   ORDER BY d.Year;
   ```

2. **Top 10 Products by Profit** (~15ms):
   ```sql
   SELECT 
       p.ProductName,
       p.ProductCategoryName,
       SUM(f.Profit) AS TotalProfit
   FROM FactSalesOrderLine f
   JOIN DimProduct p ON f.ProductKey = p.ProductKey
   GROUP BY p.ProductKey, p.ProductName, p.ProductCategoryName
   ORDER BY TotalProfit DESC
   LIMIT 10;
   ```

3. **Territory Discount vs Profit Analysis** (~20ms):
   ```sql
   SELECT 
       g.TerritoryName,
       AVG(f.UnitPriceDiscount) * 100 AS AvgDiscountPct,
       SUM(f.Profit) AS TotalProfit,
       AVG(f.ProfitMargin) AS AvgMargin
   FROM FactSalesOrderLine f
   JOIN DimGeography g ON f.TerritoryKey = g.TerritoryKey
   GROUP BY g.TerritoryKey, g.TerritoryName
   ORDER BY AvgDiscountPct DESC;
   ```

**Performance Target Met**: Semua queries < 200ms (cached), < 1s (cold start).

#### 2.4.5 OLAP Cube Conceptual Model

Meskipun tidak menggunakan physical OLAP cube (seperti Mondrian server), konsep multidimensional cube tetap diaplikasikan:

**Dimensions (Axes)**:
- **Time**: Year → Quarter → Month → Date
- **Product**: Category → Subcategory → Product
- **Geography**: Group → Country → Territory
- **Customer**: Type → Individual Customer
- **Salesperson**: Individual Rep

**Measures (Cells)**:
- Revenue (LineTotal)
- Profit
- Quantity
- Margin%

**OLAP Operations Supported**:

1. **Slice**: Filter 1 dimensi
   ```sql
   WHERE d.Year = 2003
   ```

2. **Dice**: Filter multiple dimensi
   ```sql
   WHERE d.Year = 2003 
     AND p.ProductCategoryName = 'Bikes'
     AND g.Group = 'North America'
   ```

3. **Roll-up**: Agregasi ke level lebih tinggi
   ```sql
   -- From Month → Quarter
   SELECT d.Year, d.Quarter, SUM(LineTotal)
   GROUP BY d.Year, d.Quarter
   ```

4. **Drill-down**: Detail ke level lebih rendah
   ```sql
   -- From Territory → Salesperson
   SELECT g.TerritoryName, s.SalespersonName, SUM(LineTotal)
   GROUP BY g.TerritoryKey, s.SalespersonKey
   ```

5. **Pivot**: Rotate axes
   ```sql
   -- Products as rows, Years as columns
   SELECT 
       ProductName,
       SUM(CASE WHEN Year = 2002 THEN LineTotal ELSE 0 END) AS Y2002,
       SUM(CASE WHEN Year = 2003 THEN LineTotal ELSE 0 END) AS Y2003
   ...
   ```

---

## Kesimpulan BAB II

Bab Perancangan Data Warehouse ini telah menjelaskan:

1. **Analisis Sumber Data**: Identifikasi 9 tabel sumber utama dari AdventureWorks dengan total 121,317 transaksi line items dan ERD lengkap.

2. **Perancangan Dimensi**: Merancang 5 dimension tables (DimDate, DimProduct, DimCustomer, DimSalesperson, DimGeography) dengan denormalisasi dan hierarchy untuk mendukung drill-down.

3. **Perancangan Fact Table**: Merancang FactSalesOrderLine dengan 11 measures (base + calculated) termasuk Profit, ProfitMargin, dan denormalized header-level measures.

4. **Skema Data Warehouse**: Mengimplementasikan Star Schema dengan 1 fact + 5 dimensions, indexing strategy, storage optimization (~29 MB total), dan konsep OLAP cube operations.

Pada **BAB III** akan dibahas proses ETL (Extract, Transform, Load) secara detail termasuk script SQL, data quality handling, dan performance optimization.

---