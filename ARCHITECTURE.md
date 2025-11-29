# Data Warehouse Architecture

## 🏗️ Arsitektur Sistem

```
┌─────────────────────────────────────────────────────────────┐
│                    SOURCE DATABASE                          │
│                   adventureworks (MySQL)                    │
├─────────────────────────────────────────────────────────────┤
│  Tables:                                                    │
│  - salesorderheader        - product                        │
│  - salesorderdetail        - productcategory                │
│  - customer                - productsubcategory             │
│  - person                  - productinventory               │
│  - salesperson             - salesterritory                 │
└────────────────┬────────────────────────────────────────────┘
                 │
                 │ ETL Process (etl.sql)
                 ▼
┌─────────────────────────────────────────────────────────────┐
│              DATA WAREHOUSE (Star Schema)                   │
│               adventureworks_dwh (MySQL)                    │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  DIMENSION TABLES:                                          │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │   DimDate    │  │  DimProduct  │  │ DimCustomer  │      │
│  ├──────────────┤  ├──────────────┤  ├──────────────┤      │
│  │ DateKey (PK) │  │ProductKey(PK)│  │CustomerKey   │      │
│  │ Date         │  │ ProductID    │  │ CustomerID   │      │
│  │ Year         │  │ Name         │  │ AccountNumber│      │
│  │ Quarter      │  │ Category     │  │ PersonID     │      │
│  │ Month        │  │ StandardCost │  └──────────────┘      │
│  └──────────────┘  │ ListPrice    │                        │
│                    └──────────────┘                        │
│  ┌──────────────┐  ┌──────────────┐                        │
│  │DimSalesperson│  │ DimGeography │                        │
│  ├──────────────┤  ├──────────────┤                        │
│  │SalespersonKey│  │GeographyKey  │                        │
│  │SalesPersonID │  │ TerritoryID  │                        │
│  │ FirstName    │  │ Name         │                        │
│  │ LastName     │  │ CountryCode  │                        │
│  └──────────────┘  └──────────────┘                        │
│                                                             │
│  FACT TABLE:                                                │
│  ┌────────────────────────────────────────────────────┐    │
│  │          FactSalesOrderLine                        │    │
│  ├────────────────────────────────────────────────────┤    │
│  │ FactID (PK)                                        │    │
│  │ SalesOrderID, SalesOrderDetailID                   │    │
│  │ OrderDateKey (FK → DimDate)                        │    │
│  │ ProductKey (FK → DimProduct)                       │    │
│  │ CustomerKey (FK → DimCustomer)                     │    │
│  │ SalespersonKey (FK → DimSalesperson)               │    │
│  │ GeographyKey (FK → DimGeography)                   │    │
│  │ OrderQty, UnitPrice, UnitPriceDiscount             │    │
│  │ LineTotal, StandardCost, Profit (calculated)       │    │
│  └────────────────────────────────────────────────────┘    │
│                                                             │
└────────────────┬────────────────────────────────────────────┘
                 │
                 │ SQL Queries (analytics_queries.sql)
                 ▼
┌─────────────────────────────────────────────────────────────┐
│                  ANALYTICS LAYER                            │
│               Laravel Application                           │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  Controller: DwReportController.php                         │
│  ├─ salesOverview()      → Q1 + Q2                          │
│  ├─ productAnalysis()    → Q1 + Q5                          │
│  └─ customerGeo()        → Q3 + Q4                          │
│                                                             │
└────────────────┬────────────────────────────────────────────┘
                 │
                 │ HTTP Response (Blade Views)
                 ▼
┌─────────────────────────────────────────────────────────────┐
│                 PRESENTATION LAYER                          │
│                  Dashboard Views                            │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  Page 1: Sales Overview                                     │
│  ├─ Market Basket Analysis (Table + Bar Chart)             │
│  └─ Territory Discount vs Profit (Table + Scatter Chart)   │
│                                                             │
│  Page 2: Product Analysis                                   │
│  ├─ Product Pairs Co-occurrence (Table)                    │
│  └─ Inventory Turnover by Category (Table + Bar Chart)    │
│                                                             │
│  Page 3: Customer & Geo                                     │
│  ├─ Customer Segments Analysis (Table + Scatter Chart)     │
│  └─ Salesperson Retention (Table + Dual-Axis Chart)       │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

## 🔄 Data Flow

1. **Source → DW (ETL)**
   - Extract: Read dari tabel source adventureworks
   - Transform: Join, calculate (profit = LineTotal - qty*cost)
   - Load: Insert ke DimTables dan FactTable

2. **DW → Analytics**
   - Complex queries (CTE, window functions, aggregations)
   - Answer business questions

3. **Analytics → Dashboard**
   - Laravel controller execute queries
   - Pass data ke Blade views
   - Render dengan Chart.js

## 📊 Schema Type: Star Schema

- **Fact Table**: 1 (FactSalesOrderLine) - grain = sales order detail line
- **Dimension Tables**: 5 (Date, Product, Customer, Salesperson, Geography)
- **Relationships**: Fact table has FK to each dimension (star pattern)

## 🎯 Business Questions Mapping

| Question | Fact Table | Dimensions | Metrics |
|----------|-----------|------------|---------|
| Q1: Market Basket | FactSalesOrderLine | DimProduct | COUNT(DISTINCT orders) per product pair |
| Q2: Discount vs Profit | FactSalesOrderLine | DimGeography | AVG(discount), AVG(profit margin) |
| Q3: Customer Frequency | FactSalesOrderLine | DimCustomer, DimDate | AVG(orders/year), AVG(order value) |
| Q4: Salesperson Retention | FactSalesOrderLine | DimSalesperson, DimCustomer | Retention rate, total sales |
| Q5: Inventory Turnover | FactSalesOrderLine | DimProduct | SUM(units sold) / AVG(inventory) |

## 🔧 Technologies

- **Database**: MySQL 8.0+
- **Backend**: Laravel 11, PHP 8.2+
- **Frontend**: Blade Templates, Tailwind CSS, Chart.js
- **ETL**: SQL Scripts (schema.sql, etl.sql)
