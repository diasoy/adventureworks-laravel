/*
  performance_indexes.sql
  Add indexes to improve query performance
*/

USE adventureworks_dwh;

SET @schema := DATABASE();

-- Helper to drop/create index safely for MySQL versions tanpa IF EXISTS
SET @sql := NULL;

-- FactSalesOrderLine: idx_fact_salesorderid
SET @exists := (SELECT COUNT(1) FROM information_schema.statistics WHERE table_schema=@schema AND table_name='FactSalesOrderLine' AND index_name='idx_fact_salesorderid');
SET @sql := IF(@exists>0, 'DROP INDEX idx_fact_salesorderid ON FactSalesOrderLine', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @exists := (SELECT COUNT(1) FROM information_schema.statistics WHERE table_schema=@schema AND table_name='FactSalesOrderLine' AND index_name='idx_fact_salesorderid');
SET @sql := IF(@exists=0, 'CREATE INDEX idx_fact_salesorderid ON FactSalesOrderLine(SalesOrderID)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- FactSalesOrderLine: idx_fact_salespersonkey
SET @exists := (SELECT COUNT(1) FROM information_schema.statistics WHERE table_schema=@schema AND table_name='FactSalesOrderLine' AND index_name='idx_fact_salespersonkey');
SET @sql := IF(@exists>0, 'DROP INDEX idx_fact_salespersonkey ON FactSalesOrderLine', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @exists := (SELECT COUNT(1) FROM information_schema.statistics WHERE table_schema=@schema AND table_name='FactSalesOrderLine' AND index_name='idx_fact_salespersonkey');
SET @sql := IF(@exists=0, 'CREATE INDEX idx_fact_salespersonkey ON FactSalesOrderLine(SalespersonKey)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- FactSalesOrderLine: idx_fact_geographykey
SET @exists := (SELECT COUNT(1) FROM information_schema.statistics WHERE table_schema=@schema AND table_name='FactSalesOrderLine' AND index_name='idx_fact_geographykey');
SET @sql := IF(@exists>0, 'DROP INDEX idx_fact_geographykey ON FactSalesOrderLine', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @exists := (SELECT COUNT(1) FROM information_schema.statistics WHERE table_schema=@schema AND table_name='FactSalesOrderLine' AND index_name='idx_fact_geographykey');
SET @sql := IF(@exists=0, 'CREATE INDEX idx_fact_geographykey ON FactSalesOrderLine(GeographyKey)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- FactSalesOrderLine: idx_fact_customerkey_salesperson
SET @exists := (SELECT COUNT(1) FROM information_schema.statistics WHERE table_schema=@schema AND table_name='FactSalesOrderLine' AND index_name='idx_fact_customerkey_salesperson');
SET @sql := IF(@exists>0, 'DROP INDEX idx_fact_customerkey_salesperson ON FactSalesOrderLine', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @exists := (SELECT COUNT(1) FROM information_schema.statistics WHERE table_schema=@schema AND table_name='FactSalesOrderLine' AND index_name='idx_fact_customerkey_salesperson');
SET @sql := IF(@exists=0, 'CREATE INDEX idx_fact_customerkey_salesperson ON FactSalesOrderLine(CustomerKey, SalespersonKey)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Composite for product pair analysis: idx_fact_order_product
SET @exists := (SELECT COUNT(1) FROM information_schema.statistics WHERE table_schema=@schema AND table_name='FactSalesOrderLine' AND index_name='idx_fact_order_product');
SET @sql := IF(@exists>0, 'DROP INDEX idx_fact_order_product ON FactSalesOrderLine', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @exists := (SELECT COUNT(1) FROM information_schema.statistics WHERE table_schema=@schema AND table_name='FactSalesOrderLine' AND index_name='idx_fact_order_product');
SET @sql := IF(@exists=0, 'CREATE INDEX idx_fact_order_product ON FactSalesOrderLine(SalesOrderID, ProductKey)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Date-based queries: idx_fact_datekey_customer
SET @exists := (SELECT COUNT(1) FROM information_schema.statistics WHERE table_schema=@schema AND table_name='FactSalesOrderLine' AND index_name='idx_fact_datekey_customer');
SET @sql := IF(@exists>0, 'DROP INDEX idx_fact_datekey_customer ON FactSalesOrderLine', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @exists := (SELECT COUNT(1) FROM information_schema.statistics WHERE table_schema=@schema AND table_name='FactSalesOrderLine' AND index_name='idx_fact_datekey_customer');
SET @sql := IF(@exists=0, 'CREATE INDEX idx_fact_datekey_customer ON FactSalesOrderLine(OrderDateKey, CustomerKey)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Inventory fact: idx_inv_product_date
SET @exists := (SELECT COUNT(1) FROM information_schema.statistics WHERE table_schema=@schema AND table_name='FactInventoryMonthly' AND index_name='idx_inv_product_date');
SET @sql := IF(@exists>0, 'DROP INDEX idx_inv_product_date ON FactInventoryMonthly', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @exists := (SELECT COUNT(1) FROM information_schema.statistics WHERE table_schema=@schema AND table_name='FactInventoryMonthly' AND index_name='idx_inv_product_date');
SET @sql := IF(@exists=0, 'CREATE INDEX idx_inv_product_date ON FactInventoryMonthly(ProductKey, DateKey)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Analyze tables for better query optimization
ANALYZE TABLE FactSalesOrderLine;
ANALYZE TABLE DimProduct;
ANALYZE TABLE DimCustomer;
ANALYZE TABLE DimSalesperson;
ANALYZE TABLE DimGeography;
ANALYZE TABLE FactInventoryMonthly;

SELECT 'Indexes created successfully!' as Status;
