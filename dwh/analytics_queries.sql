/*
  analytics_queries.sql
  Queries to answer the five business questions (MySQL syntax)
  Run these after running schema.sql and etl.sql in the adventureworks_dwh database.
*/

USE adventureworks_dwh;

-- QUESTION 1: Market-basket analysis
-- Top product pairs (order co-occurrence counts)
SELECT 
    p1.ProductID AS ProductA_ID,
    p1.Name AS ProductA_Name,
    p1.ProductCategoryName AS ProductA_CategoryName,
    p2.ProductID AS ProductB_ID,
    p2.Name AS ProductB_Name,
    COUNT(DISTINCT f1.SalesOrderID) AS CooccurrenceOrders
FROM FactSalesOrderLine f1
JOIN FactSalesOrderLine f2 ON f1.SalesOrderID = f2.SalesOrderID AND f1.SalesOrderDetailID <> f2.SalesOrderDetailID
JOIN DimProduct p1 ON f1.ProductKey = p1.ProductKey
JOIN DimProduct p2 ON f2.ProductKey = p2.ProductKey
WHERE f1.ProductKey IS NOT NULL AND f2.ProductKey IS NOT NULL
GROUP BY p1.ProductID, p1.Name, p2.ProductID, p2.Name
ORDER BY CooccurrenceOrders DESC
LIMIT 50;

-- Top single products for bundling
SELECT 
    p.ProductID,
    p.Name,
    COUNT(DISTINCT f.SalesOrderID) AS OrdersWithOtherProducts
FROM FactSalesOrderLine f
JOIN DimProduct p ON f.ProductKey = p.ProductKey
WHERE f.ProductKey IS NOT NULL
  AND EXISTS (
      SELECT 1 FROM FactSalesOrderLine f2 
      WHERE f.SalesOrderID = f2.SalesOrderID AND f2.SalesOrderDetailID <> f.SalesOrderDetailID
  )
GROUP BY p.ProductID, p.Name
ORDER BY OrdersWithOtherProducts DESC
LIMIT 50;

-- QUESTION 2: Territory discount vs profit margin
SELECT
    dg.TerritoryID,
    dg.Name AS TerritoryName,
    AVG(IF(fs.UnitPrice > 0, fs.UnitPriceDiscount / NULLIF(fs.UnitPrice,0), 0)) AS AvgDiscountRate,
    AVG(IF(fs.LineTotal <> 0, fs.Profit / NULLIF(fs.LineTotal,0), NULL)) AS AvgProfitMargin
FROM FactSalesOrderLine fs
LEFT JOIN DimGeography dg ON fs.GeographyKey = dg.GeographyKey
GROUP BY dg.TerritoryID, dg.Name
ORDER BY AvgDiscountRate DESC;

-- QUESTION 3: Customer purchase frequency and segments
WITH CustomerOrders AS (
    SELECT
        CustomerKey,
        SalesOrderID,
        YEAR(STR_TO_DATE(CAST(OrderDateKey AS CHAR), '%Y%m%d')) AS `Year`,
        SUM(LineTotal) AS OrderValue
    FROM FactSalesOrderLine
    GROUP BY CustomerKey, SalesOrderID, OrderDateKey
),
CustYear AS (
    SELECT 
        CustomerKey, 
        `Year`, 
        COUNT(DISTINCT SalesOrderID) AS OrdersPerYear, 
        AVG(OrderValue) AS AvgOrderValue
    FROM CustomerOrders
    GROUP BY CustomerKey, `Year`
),
CustSummary AS (
    SELECT 
        CustomerKey, 
        AVG(OrdersPerYear) AS AvgOrdersPerYear, 
        AVG(AvgOrderValue) AS AvgOrderValueAcrossYears
    FROM CustYear
    GROUP BY CustomerKey
)
SELECT 
    cs.CustomerKey, 
    dc.CustomerID, 
    cs.AvgOrdersPerYear, 
    cs.AvgOrderValueAcrossYears
FROM CustSummary cs
LEFT JOIN DimCustomer dc ON cs.CustomerKey = dc.CustomerKey
ORDER BY cs.AvgOrdersPerYear DESC
LIMIT 200;

-- High-frequency, low-ticket customers
WITH CustomerOrders AS (
    SELECT
        CustomerKey,
        SalesOrderID,
        YEAR(STR_TO_DATE(CAST(OrderDateKey AS CHAR), '%Y%m%d')) AS `Year`,
        SUM(LineTotal) AS OrderValue
    FROM FactSalesOrderLine
    GROUP BY CustomerKey, SalesOrderID, OrderDateKey
),
CustYear AS (
    SELECT 
        CustomerKey, 
        `Year`, 
        COUNT(DISTINCT SalesOrderID) AS OrdersPerYear, 
        AVG(OrderValue) AS AvgOrderValue
    FROM CustomerOrders
    GROUP BY CustomerKey, `Year`
),
CustSummary AS (
    SELECT 
        CustomerKey, 
        AVG(OrdersPerYear) AS AvgOrdersPerYear, 
        AVG(AvgOrderValue) AS AvgOrderValueAcrossYears
    FROM CustYear
    GROUP BY CustomerKey
),
Ranked AS (
    SELECT 
        cs.CustomerKey, 
        cs.AvgOrdersPerYear, 
        cs.AvgOrderValueAcrossYears,
        NTILE(5) OVER (ORDER BY cs.AvgOrdersPerYear DESC) AS FreqBucket,
        NTILE(10) OVER (ORDER BY cs.AvgOrderValueAcrossYears ASC) AS ValueBucket
    FROM CustSummary cs
)
SELECT 
    r.CustomerKey, 
    dc.CustomerID, 
    r.AvgOrdersPerYear, 
    r.AvgOrderValueAcrossYears
FROM Ranked r
LEFT JOIN DimCustomer dc ON r.CustomerKey = dc.CustomerKey
WHERE r.FreqBucket = 1 AND r.ValueBucket <= 3
ORDER BY r.AvgOrdersPerYear DESC;

-- QUESTION 4: Salesperson retention and total sales
WITH SalespersonCustomerOrders AS (
    SELECT
        ds.SalespersonKey,
        fs.CustomerKey,
        COUNT(DISTINCT fs.SalesOrderID) AS OrdersByCustomer,
        SUM(fs.LineTotal) AS TotalSalesByCustomer
    FROM FactSalesOrderLine fs
    LEFT JOIN DimSalesperson ds ON fs.SalespersonKey = ds.SalespersonKey
    GROUP BY ds.SalespersonKey, fs.CustomerKey
)
SELECT
    sp.SalespersonKey,
    sp.SalesPersonID,
    CONCAT(sp.FirstName, ' ', sp.LastName) AS SalespersonName,
    COUNT(sco.CustomerKey) AS CustomersHandled,
    SUM(IF(sco.OrdersByCustomer > 1, 1, 0)) AS CustomersWithRepeatOrders,
    SUM(IF(sco.OrdersByCustomer > 1, 1, 0)) / NULLIF(COUNT(sco.CustomerKey),0) AS RetentionRate,
    SUM(sco.TotalSalesByCustomer) AS TotalSales
FROM SalespersonCustomerOrders sco
LEFT JOIN DimSalesperson sp ON sco.SalespersonKey = sp.SalespersonKey
GROUP BY sp.SalespersonKey, sp.SalesPersonID, sp.FirstName, sp.LastName
ORDER BY RetentionRate DESC, TotalSales DESC;

-- QUESTION 5: Inventory turnover per category (leveraging FactInventoryMonthly)
WITH SalesByCategory AS (
    SELECT 
        dp.ProductCategoryID,
        dp.ProductCategoryName,
        SUM(fs.OrderQty) AS TotalUnitsSold
    FROM FactSalesOrderLine fs
    JOIN DimProduct dp ON fs.ProductKey = dp.ProductKey
    GROUP BY dp.ProductCategoryID, dp.ProductCategoryName
),
InventoryByCategory AS (
    SELECT
        dp.ProductCategoryID,
        dp.ProductCategoryName,
        AVG(fim.EndingQuantity) AS AvgInventoryQty
    FROM FactInventoryMonthly fim
    JOIN DimProduct dp ON fim.ProductKey = dp.ProductKey
    GROUP BY dp.ProductCategoryID, dp.ProductCategoryName
)
SELECT
    sbc.ProductCategoryID,
    COALESCE(sbc.ProductCategoryName, 'Uncategorized') AS CategoryName,
    sbc.TotalUnitsSold,
    ibc.AvgInventoryQty,
    IF(ibc.AvgInventoryQty > 0, sbc.TotalUnitsSold / ibc.AvgInventoryQty, NULL) AS InventoryTurnover
FROM SalesByCategory sbc
LEFT JOIN InventoryByCategory ibc ON sbc.ProductCategoryID = ibc.ProductCategoryID
ORDER BY InventoryTurnover DESC;
