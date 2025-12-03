/*
  etl.sql
  Populate DW from adventureworks source (MySQL syntax)
*/

USE adventureworks_dwh;

-- 1) Populate DimDate
TRUNCATE TABLE DimDate;

INSERT INTO DimDate (DateKey, `Date`, `Year`, `Quarter`, `Month`, DayOfMonth, DayOfWeek, IsWeekend)
SELECT DISTINCT
    DATE_FORMAT(d.OrderDate, '%Y%m%d') AS DateKey,
    DATE(d.OrderDate) AS `Date`,
    YEAR(d.OrderDate) AS `Year`,
    QUARTER(d.OrderDate) AS `Quarter`,
    MONTH(d.OrderDate) AS `Month`,
    DAY(d.OrderDate) AS DayOfMonth,
    DAYOFWEEK(d.OrderDate) AS DayOfWeek,
    IF(DAYOFWEEK(d.OrderDate) IN (1,7), 1, 0) AS IsWeekend
FROM (
    SELECT OrderDate FROM adventureworks.salesorderheader
    UNION
    SELECT DueDate FROM adventureworks.salesorderheader
    UNION
    SELECT ShipDate FROM adventureworks.salesorderheader
    UNION
    SELECT LAST_DAY(ModifiedDate) AS OrderDate FROM adventureworks.productinventory
) d
WHERE d.OrderDate IS NOT NULL;

-- 2) Populate DimProduct
TRUNCATE TABLE DimProduct;

INSERT INTO DimProduct (ProductID, Name, ProductNumber, Color, StandardCost, ListPrice, Size, Weight, ProductSubcategoryID, ProductCategoryID, ProductSubcategoryName, ProductCategoryName)
SELECT 
    p.ProductID, 
    p.Name, 
    p.ProductNumber, 
    p.Color, 
    p.StandardCost, 
    p.ListPrice, 
    p.Size, 
    p.Weight, 
    p.ProductSubcategoryID, 
    pc.ProductCategoryID,
    ps.Name AS ProductSubcategoryName,
    pc.Name AS ProductCategoryName
FROM adventureworks.product p
LEFT JOIN adventureworks.productsubcategory ps ON p.ProductSubcategoryID = ps.ProductSubcategoryID
LEFT JOIN adventureworks.productcategory pc ON ps.ProductCategoryID = pc.ProductCategoryID;

-- 3) Populate DimCustomer
TRUNCATE TABLE DimCustomer;

INSERT INTO DimCustomer (CustomerID, AccountNumber, CustomerType, PersonID, StoreID)
SELECT c.CustomerID, c.AccountNumber, c.CustomerType, NULL as PersonID, NULL as StoreID
FROM adventureworks.customer c;

-- 4) Populate DimSalesperson
TRUNCATE TABLE DimSalesperson;

INSERT INTO DimSalesperson (SalesPersonID, FirstName, LastName, SalesTerritoryID)
SELECT sp.SalesPersonID, CONCAT('Sales', sp.SalesPersonID) as FirstName, 'Person' as LastName, sp.TerritoryID
FROM adventureworks.salesperson sp;

-- 5) Populate DimGeography
TRUNCATE TABLE DimGeography;

INSERT INTO DimGeography (TerritoryID, Name, CountryRegionCode, `Group`)
SELECT TerritoryID, Name, CountryRegionCode, `Group`
FROM adventureworks.salesterritory;

-- 6) Populate FactSalesOrderLine
TRUNCATE TABLE FactSalesOrderLine;

INSERT INTO FactSalesOrderLine (
    SalesOrderID, SalesOrderDetailID, OrderDateKey, DueDateKey, ShipDateKey,
    ProductKey, CustomerKey, SalespersonKey, GeographyKey,
    OrderQty, UnitPrice, UnitPriceDiscount, LineTotal, StandardCost, Profit
)
SELECT
    soh.SalesOrderID,
    sod.SalesOrderDetailID,
    DATE_FORMAT(soh.OrderDate, '%Y%m%d') AS OrderDateKey,
    DATE_FORMAT(soh.DueDate, '%Y%m%d') AS DueDateKey,
    IF(soh.ShipDate IS NULL, NULL, DATE_FORMAT(soh.ShipDate, '%Y%m%d')) AS ShipDateKey,
    dp.ProductKey,
    dc.CustomerKey,
    ds.SalespersonKey,
    dg.GeographyKey,
    sod.OrderQty,
    sod.UnitPrice,
    sod.UnitPriceDiscount,
    sod.LineTotal,
    dp.StandardCost,
    (sod.LineTotal - (sod.OrderQty * dp.StandardCost)) AS Profit
FROM adventureworks.salesorderdetail sod
JOIN adventureworks.salesorderheader soh ON sod.SalesOrderID = soh.SalesOrderID
LEFT JOIN DimProduct dp ON dp.ProductID = sod.ProductID
LEFT JOIN DimCustomer dc ON dc.CustomerID = soh.CustomerID
LEFT JOIN DimSalesperson ds ON ds.SalesPersonID = soh.SalesPersonID
LEFT JOIN DimGeography dg ON dg.TerritoryID = soh.TerritoryID;

-- 7) Populate FactInventoryMonthly (monthly aggregated inventory from source)
TRUNCATE TABLE FactInventoryMonthly;

INSERT INTO FactInventoryMonthly (ProductKey, DateKey, EndingQuantity)
SELECT
    dp.ProductKey,
    DATE_FORMAT(LAST_DAY(pi.ModifiedDate), '%Y%m%d') AS DateKey,
    AVG(pi.Quantity) AS EndingQuantity
FROM adventureworks.productinventory pi
LEFT JOIN DimProduct dp ON dp.ProductID = pi.ProductID
WHERE dp.ProductKey IS NOT NULL
GROUP BY dp.ProductKey, DATE_FORMAT(LAST_DAY(pi.ModifiedDate), '%Y%m%d');
