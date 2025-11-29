<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DrillDownController extends Controller
{
    // Drill-down: Territory -> Salesperson details
    public function territoryDetails($territoryId)
    {
        $territory = DB::connection('mysql_dwh')->selectOne("
            SELECT TerritoryID, Name, CountryRegionCode, `Group`
            FROM DimGeography
            WHERE TerritoryID = ?
        ", [$territoryId]);

        if (!$territory) {
            return redirect()->route('dashboard.sales-overview')
                           ->with('error', 'Territory tidak ditemukan.');
        }

        $salespeople = DB::connection('mysql_dwh')->select("
            SELECT
                sp.SalesPersonID,
                CONCAT(sp.FirstName, ' ', sp.LastName) AS SalespersonName,
                COUNT(DISTINCT fs.SalesOrderID) AS TotalOrders,
                SUM(fs.LineTotal) AS TotalSales,
                AVG(fs.UnitPriceDiscount / NULLIF(fs.UnitPrice, 0)) AS AvgDiscountRate,
                AVG(fs.Profit / NULLIF(fs.LineTotal, 0)) AS AvgProfitMargin
            FROM FactSalesOrderLine fs
            JOIN DimSalesperson sp ON fs.SalespersonKey = sp.SalespersonKey
            WHERE fs.GeographyKey = (SELECT GeographyKey FROM DimGeography WHERE TerritoryID = ?)
              AND sp.SalespersonKey IS NOT NULL
            GROUP BY sp.SalespersonKey, sp.SalesPersonID, sp.FirstName, sp.LastName
            ORDER BY TotalSales DESC
        ", [$territoryId]);

        $monthlyTrend = DB::connection('mysql_dwh')->select("
            SELECT
                YEAR(STR_TO_DATE(CAST(fs.OrderDateKey AS CHAR), '%Y%m%d')) AS Year,
                MONTH(STR_TO_DATE(CAST(fs.OrderDateKey AS CHAR), '%Y%m%d')) AS Month,
                SUM(fs.LineTotal) AS Revenue,
                COUNT(DISTINCT fs.SalesOrderID) AS Orders
            FROM FactSalesOrderLine fs
            WHERE fs.GeographyKey = (SELECT GeographyKey FROM DimGeography WHERE TerritoryID = ?)
            GROUP BY Year, Month
            ORDER BY Year DESC, Month DESC
            LIMIT 12
        ", [$territoryId]);

        return view('dashboard.territory-drilldown', compact('territory', 'salespeople', 'monthlyTrend'));
    }
}
