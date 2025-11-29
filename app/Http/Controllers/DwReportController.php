<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DwReportController extends Controller
{
    // Page 1: Sales Overview (Questions 1 & 2)
    public function salesOverview()
    {
        $availableYears = Cache::remember('dwh_available_years', 600, function () {
            return DB::connection('mysql_dwh')->select("
                SELECT DISTINCT YEAR(STR_TO_DATE(CAST(OrderDateKey AS CHAR), '%Y%m%d')) AS Year
                FROM FactSalesOrderLine
                WHERE OrderDateKey IS NOT NULL
                ORDER BY Year DESC
                LIMIT 6
            ");
        });

        // Cache for 10 minutes
        $bundlingProducts = Cache::remember('bundling_products', 600, function () {
            return DB::connection('mysql_dwh')->select("
                SELECT 
                    p.ProductID,
                    p.Name,
                    COUNT(DISTINCT f.SalesOrderID) AS OrdersWithOtherProducts
                FROM FactSalesOrderLine f
                JOIN DimProduct p ON f.ProductKey = p.ProductKey
                WHERE f.ProductKey IS NOT NULL
                  AND f.SalesOrderID IN (
                      SELECT SalesOrderID FROM FactSalesOrderLine GROUP BY SalesOrderID HAVING COUNT(*) > 1
                  )
                GROUP BY p.ProductID, p.Name
                HAVING OrdersWithOtherProducts > 0
                ORDER BY OrdersWithOtherProducts DESC
                LIMIT 50
            ");
        });

        $bundlingProductsYearly = Cache::remember('bundling_products_yearly', 600, function () {
            return DB::connection('mysql_dwh')->select("
                SELECT 
                    p.ProductID,
                    p.Name,
                    YEAR(STR_TO_DATE(CAST(f.OrderDateKey AS CHAR), '%Y%m%d')) AS OrderYear,
                    COUNT(DISTINCT f.SalesOrderID) AS OrdersWithOtherProducts
                FROM FactSalesOrderLine f
                JOIN DimProduct p ON f.ProductKey = p.ProductKey
                WHERE f.ProductKey IS NOT NULL
                  AND f.SalesOrderID IN (
                      SELECT SalesOrderID FROM FactSalesOrderLine GROUP BY SalesOrderID HAVING COUNT(*) > 1
                  )
                  AND f.OrderDateKey IS NOT NULL
                GROUP BY p.ProductID, p.Name, OrderYear
                HAVING OrdersWithOtherProducts > 0
                ORDER BY OrderYear DESC, OrdersWithOtherProducts DESC
            ");
        });

        $territoryMetrics = Cache::remember('territory_metrics', 600, function () {
            return DB::connection('mysql_dwh')->select("
                SELECT
                    IFNULL(dg.TerritoryID, 0) AS TerritoryID,
                    IFNULL(dg.Name, 'Unknown') AS TerritoryName,
                    AVG(fs.UnitPriceDiscount / NULLIF(fs.UnitPrice, 0)) AS AvgDiscountRate,
                    AVG(fs.Profit / NULLIF(fs.LineTotal, 0)) AS AvgProfitMargin,
                    SUM(fs.LineTotal) AS TotalRevenue
                FROM FactSalesOrderLine fs
                LEFT JOIN DimGeography dg ON fs.GeographyKey = dg.GeographyKey
                WHERE fs.UnitPrice > 0 AND fs.LineTotal > 0
                GROUP BY dg.TerritoryID, dg.Name
                HAVING TotalRevenue > 0
                ORDER BY AvgDiscountRate DESC
            ");
        });

        $territoryMetricsYearly = Cache::remember('territory_metrics_yearly', 600, function () {
            return DB::connection('mysql_dwh')->select("
                SELECT
                    IFNULL(dg.TerritoryID, 0) AS TerritoryID,
                    IFNULL(dg.Name, 'Unknown') AS TerritoryName,
                    YEAR(STR_TO_DATE(CAST(fs.OrderDateKey AS CHAR), '%Y%m%d')) AS OrderYear,
                    AVG(fs.UnitPriceDiscount / NULLIF(fs.UnitPrice, 0)) AS AvgDiscountRate,
                    AVG(fs.Profit / NULLIF(fs.LineTotal, 0)) AS AvgProfitMargin,
                    SUM(fs.LineTotal) AS TotalRevenue
                FROM FactSalesOrderLine fs
                LEFT JOIN DimGeography dg ON fs.GeographyKey = dg.GeographyKey
                WHERE fs.UnitPrice > 0 AND fs.LineTotal > 0
                  AND fs.OrderDateKey IS NOT NULL
                GROUP BY dg.TerritoryID, dg.Name, OrderYear
                HAVING TotalRevenue > 0
                ORDER BY OrderYear DESC, TotalRevenue DESC
            ");
        });

        return view('dashboard.sales-overview', compact(
            'bundlingProducts',
            'bundlingProductsYearly',
            'territoryMetrics',
            'territoryMetricsYearly',
            'availableYears'
        ));
    }

    // Page 2: Product Analysis (Questions 1 & 5)
    public function productAnalysis()
    {
        // Simplified query - avoid expensive self-join
        $productPairs = Cache::remember('product_pairs', 600, function () {
            return DB::connection('mysql_dwh')->select("
                SELECT 
                    p1.ProductID AS ProductA_ID,
                    p1.Name AS ProductA_Name,
                    p1.ProductCategoryID AS ProductA_CategoryID,
                    pc1.Name AS ProductA_CategoryName,
                    p2.ProductID AS ProductB_ID,
                    p2.Name AS ProductB_Name,
                    COUNT(DISTINCT f1.SalesOrderID) AS CooccurrenceOrders
                FROM FactSalesOrderLine f1
                INNER JOIN FactSalesOrderLine f2 
                    ON f1.SalesOrderID = f2.SalesOrderID 
                    AND f1.ProductKey < f2.ProductKey
                INNER JOIN DimProduct p1 ON f1.ProductKey = p1.ProductKey
                INNER JOIN DimProduct p2 ON f2.ProductKey = p2.ProductKey
                LEFT JOIN adventureworks.productcategory pc1 ON p1.ProductCategoryID = pc1.ProductCategoryID
                GROUP BY p1.ProductID, p1.Name, p1.ProductCategoryID, pc1.Name, p2.ProductID, p2.Name
                HAVING CooccurrenceOrders >= 5
                ORDER BY CooccurrenceOrders DESC
                LIMIT 50
            ");
        });

        $inventoryTurnover = Cache::remember('inventory_turnover', 600, function () {
            return DB::connection('mysql_dwh')->select("
                SELECT
                    IFNULL(dp.ProductCategoryID, -1) AS ProductCategoryID,
                    pc.Name AS CategoryName,
                    SUM(fs.OrderQty) AS TotalUnitsSold
                FROM FactSalesOrderLine fs
                JOIN DimProduct dp ON fs.ProductKey = dp.ProductKey
                LEFT JOIN adventureworks.productcategory pc ON dp.ProductCategoryID = pc.ProductCategoryID
                GROUP BY dp.ProductCategoryID, pc.Name
                ORDER BY TotalUnitsSold DESC
            ");
        });

        return view('dashboard.product-analysis', compact('productPairs', 'inventoryTurnover'));
    }

    // Page 3: Customer & Geo Analysis (Questions 3 & 4)
    public function customerGeo()
    {
        // Simplified customer segments - remove complex window functions
        $customerSegments = Cache::remember('customer_segments', 600, function () {
            return DB::connection('mysql_dwh')->select("
                SELECT 
                    dc.CustomerID,
                    COUNT(DISTINCT f.SalesOrderID) AS TotalOrders,
                    COUNT(DISTINCT YEAR(STR_TO_DATE(CAST(f.OrderDateKey AS CHAR), '%Y%m%d'))) AS YearsActive,
                    ROUND(COUNT(DISTINCT f.SalesOrderID) / NULLIF(COUNT(DISTINCT YEAR(STR_TO_DATE(CAST(f.OrderDateKey AS CHAR), '%Y%m%d'))), 0), 2) AS AvgOrdersPerYear,
                    ROUND(AVG(f.LineTotal), 2) AS AvgOrderValueAcrossYears
                FROM FactSalesOrderLine f
                LEFT JOIN DimCustomer dc ON f.CustomerKey = dc.CustomerKey
                WHERE dc.CustomerKey IS NOT NULL
                GROUP BY dc.CustomerKey, dc.CustomerID
                HAVING AvgOrdersPerYear >= 3 AND AvgOrderValueAcrossYears < 500
                ORDER BY AvgOrdersPerYear DESC
                LIMIT 50
            ");
        });

        $salespersonRetention = Cache::remember('salesperson_retention', 600, function () {
            return DB::connection('mysql_dwh')->select("
                SELECT
                    sp.SalespersonKey,
                    sp.SalesPersonID,
                    CONCAT(sp.FirstName, ' ', sp.LastName) AS SalespersonName,
                    COUNT(DISTINCT fs.CustomerKey) AS CustomersHandled,
                    COUNT(DISTINCT CASE WHEN CustomerOrders > 1 THEN fs.CustomerKey END) AS CustomersWithRepeatOrders,
                    ROUND(COUNT(DISTINCT CASE WHEN CustomerOrders > 1 THEN fs.CustomerKey END) / NULLIF(COUNT(DISTINCT fs.CustomerKey), 0), 3) AS RetentionRate,
                    SUM(fs.LineTotal) AS TotalSales
                FROM (
                    SELECT 
                        CustomerKey, 
                        SalespersonKey, 
                        COUNT(DISTINCT SalesOrderID) AS CustomerOrders,
                        SUM(LineTotal) AS LineTotal
                    FROM FactSalesOrderLine
                    WHERE SalespersonKey IS NOT NULL
                    GROUP BY CustomerKey, SalespersonKey
                ) fs
                LEFT JOIN DimSalesperson sp ON fs.SalespersonKey = sp.SalespersonKey
                WHERE sp.SalespersonKey IS NOT NULL
                GROUP BY sp.SalespersonKey, sp.SalesPersonID, sp.FirstName, sp.LastName
                ORDER BY RetentionRate DESC, TotalSales DESC
                LIMIT 20
            ");
        });

        return view('dashboard.customer-geo', compact('customerSegments', 'salespersonRetention'));
    }
}
