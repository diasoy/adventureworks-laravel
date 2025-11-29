/*
  performance_indexes.sql
  Add indexes to improve query performance
*/

USE adventureworks_dwh;

-- Add indexes to FactSalesOrderLine for faster joins and filters
CREATE INDEX idx_fact_salesorderid ON FactSalesOrderLine(SalesOrderID);
CREATE INDEX idx_fact_salespersonkey ON FactSalesOrderLine(SalespersonKey);
CREATE INDEX idx_fact_geographykey ON FactSalesOrderLine(GeographyKey);
CREATE INDEX idx_fact_customerkey_salesperson ON FactSalesOrderLine(CustomerKey, SalespersonKey);

-- Composite index for product pair analysis
CREATE INDEX idx_fact_order_product ON FactSalesOrderLine(SalesOrderID, ProductKey);

-- Index for date-based queries
CREATE INDEX idx_fact_datekey_customer ON FactSalesOrderLine(OrderDateKey, CustomerKey);

-- Analyze tables for better query optimization
ANALYZE TABLE FactSalesOrderLine;
ANALYZE TABLE DimProduct;
ANALYZE TABLE DimCustomer;
ANALYZE TABLE DimSalesperson;
ANALYZE TABLE DimGeography;

SELECT 'Indexes created successfully!' as Status;
