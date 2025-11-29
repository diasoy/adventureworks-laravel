/*
  schema.sql
  Creates adventureworks_dwh and DW tables (MySQL syntax)
*/

CREATE DATABASE IF NOT EXISTS adventureworks_dwh;

USE adventureworks_dwh;

-- Dimension: Date
DROP TABLE IF EXISTS DimDate;
CREATE TABLE DimDate (
    DateKey INT NOT NULL PRIMARY KEY,
    `Date` DATE NOT NULL,
    `Year` INT NOT NULL,
    `Quarter` TINYINT NOT NULL,
    `Month` TINYINT NOT NULL,
    DayOfMonth TINYINT NOT NULL,
    DayOfWeek TINYINT NOT NULL,
    IsWeekend TINYINT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dimension: Product
DROP TABLE IF EXISTS DimProduct;
CREATE TABLE DimProduct (
    ProductKey INT AUTO_INCREMENT PRIMARY KEY,
    ProductID INT NOT NULL UNIQUE,
    Name VARCHAR(255),
    ProductNumber VARCHAR(50),
    Color VARCHAR(50),
    StandardCost DECIMAL(19,4),
    ListPrice DECIMAL(19,4),
    Size VARCHAR(50),
    Weight DECIMAL(8,2),
    ProductSubcategoryID INT NULL,
    ProductCategoryID INT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dimension: Customer
DROP TABLE IF EXISTS DimCustomer;
CREATE TABLE DimCustomer (
    CustomerKey INT AUTO_INCREMENT PRIMARY KEY,
    CustomerID INT NOT NULL UNIQUE,
    AccountNumber VARCHAR(50),
    CustomerType VARCHAR(50),
    PersonID INT NULL,
    StoreID INT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dimension: Salesperson
DROP TABLE IF EXISTS DimSalesperson;
CREATE TABLE DimSalesperson (
    SalespersonKey INT AUTO_INCREMENT PRIMARY KEY,
    SalesPersonID INT NULL UNIQUE,
    FirstName VARCHAR(100),
    LastName VARCHAR(100),
    SalesTerritoryID INT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dimension: Geography (Territory)
DROP TABLE IF EXISTS DimGeography;
CREATE TABLE DimGeography (
    GeographyKey INT AUTO_INCREMENT PRIMARY KEY,
    TerritoryID INT NOT NULL UNIQUE,
    Name VARCHAR(100),
    CountryRegionCode VARCHAR(10),
    `Group` VARCHAR(100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Fact table: sales order lines
DROP TABLE IF EXISTS FactSalesOrderLine;
CREATE TABLE FactSalesOrderLine (
    FactID BIGINT AUTO_INCREMENT PRIMARY KEY,
    SalesOrderID INT NOT NULL,
    SalesOrderDetailID INT NOT NULL,
    OrderDateKey INT NOT NULL,
    DueDateKey INT NULL,
    ShipDateKey INT NULL,
    ProductKey INT NULL,
    CustomerKey INT NULL,
    SalespersonKey INT NULL,
    GeographyKey INT NULL,
    OrderQty INT,
    UnitPrice DECIMAL(19,4),
    UnitPriceDiscount DECIMAL(19,4),
    LineTotal DECIMAL(19,4),
    StandardCost DECIMAL(19,4),
    Profit DECIMAL(19,4),
    INDEX IX_Fact_OrderDate (OrderDateKey),
    INDEX IX_Fact_Product (ProductKey),
    INDEX IX_Fact_Customer (CustomerKey)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

