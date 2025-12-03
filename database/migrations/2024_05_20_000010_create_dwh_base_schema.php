<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->ensureDwhDatabaseExists();

        $schema = Schema::connection('mysql_dwh');

        $this->createDimDate($schema);
        $this->createDimProduct($schema);
        $this->createDimCustomer($schema);
        $this->createDimSalesperson($schema);
        $this->createDimGeography($schema);
        $this->createFactSalesOrderLine($schema);
        $this->createFactInventoryMonthly($schema);
    }

    public function down(): void
    {
        $schema = Schema::connection('mysql_dwh');

        $schema->dropIfExists('FactInventoryMonthly');
        $schema->dropIfExists('FactSalesOrderLine');
        $schema->dropIfExists('DimGeography');
        $schema->dropIfExists('DimSalesperson');
        $schema->dropIfExists('DimCustomer');
        $schema->dropIfExists('DimProduct');
        $schema->dropIfExists('DimDate');
    }

    private function ensureDwhDatabaseExists(): void
    {
        $config = config('database.connections.mysql_dwh');
        $database = $config['database'] ?? null;

        if (!$database) {
            return;
        }

        // Use default connection to create the DWH database if it does not exist.
        $charset = $config['charset'] ?? 'utf8mb4';
        $collation = $config['collation'] ?? 'utf8mb4_unicode_ci';
        DB::statement("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET {$charset} COLLATE {$collation}");
    }

    private function createDimDate($schema): void
    {
        if ($schema->hasTable('DimDate')) {
            return;
        }

        $schema->create('DimDate', function (Blueprint $table) {
            $table->integer('DateKey')->primary();
            $table->date('Date');
            $table->integer('Year');
            $table->tinyInteger('Quarter');
            $table->tinyInteger('Month');
            $table->tinyInteger('DayOfMonth');
            $table->tinyInteger('DayOfWeek');
            $table->tinyInteger('IsWeekend');
        });
    }

    private function createDimProduct($schema): void
    {
        if ($schema->hasTable('DimProduct')) {
            return;
        }

        $schema->create('DimProduct', function (Blueprint $table) {
            $table->increments('ProductKey');
            $table->integer('ProductID')->unique('UQ_DimProduct_ProductID');
            $table->string('Name', 255)->nullable();
            $table->string('ProductNumber', 50)->nullable();
            $table->string('Color', 50)->nullable();
            $table->decimal('StandardCost', 19, 4)->nullable();
            $table->decimal('ListPrice', 19, 4)->nullable();
            $table->string('Size', 50)->nullable();
            $table->decimal('Weight', 8, 2)->nullable();
            $table->integer('ProductSubcategoryID')->nullable();
            $table->integer('ProductCategoryID')->nullable();
            $table->string('ProductSubcategoryName', 100)->nullable();
            $table->string('ProductCategoryName', 100)->nullable();
            $table->index('ProductSubcategoryID', 'IX_DimProduct_Subcategory');
            $table->index('ProductCategoryID', 'IX_DimProduct_Category');
        });
    }

    private function createDimCustomer($schema): void
    {
        if ($schema->hasTable('DimCustomer')) {
            return;
        }

        $schema->create('DimCustomer', function (Blueprint $table) {
            $table->increments('CustomerKey');
            $table->integer('CustomerID')->unique('UQ_DimCustomer_CustomerID');
            $table->string('AccountNumber', 50)->nullable();
            $table->string('CustomerType', 50)->nullable();
            $table->integer('PersonID')->nullable();
            $table->integer('StoreID')->nullable();
        });
    }

    private function createDimSalesperson($schema): void
    {
        if ($schema->hasTable('DimSalesperson')) {
            return;
        }

        $schema->create('DimSalesperson', function (Blueprint $table) {
            $table->increments('SalespersonKey');
            $table->integer('SalesPersonID')->nullable()->unique('UQ_DimSalesperson_SalesPersonID');
            $table->string('FirstName', 100)->nullable();
            $table->string('LastName', 100)->nullable();
            $table->integer('SalesTerritoryID')->nullable();
        });
    }

    private function createDimGeography($schema): void
    {
        if ($schema->hasTable('DimGeography')) {
            return;
        }

        $schema->create('DimGeography', function (Blueprint $table) {
            $table->increments('GeographyKey');
            $table->integer('TerritoryID')->unique('UQ_DimGeography_TerritoryID');
            $table->string('Name', 100)->nullable();
            $table->string('CountryRegionCode', 10)->nullable();
            $table->string('Group', 100)->nullable();
        });
    }

    private function createFactSalesOrderLine($schema): void
    {
        if ($schema->hasTable('FactSalesOrderLine')) {
            return;
        }

        $schema->create('FactSalesOrderLine', function (Blueprint $table) {
            $table->bigIncrements('FactID');
            $table->integer('SalesOrderID');
            $table->integer('SalesOrderDetailID');
            $table->integer('OrderDateKey');
            $table->integer('DueDateKey')->nullable();
            $table->integer('ShipDateKey')->nullable();
            $table->integer('ProductKey')->nullable();
            $table->integer('CustomerKey')->nullable();
            $table->integer('SalespersonKey')->nullable();
            $table->integer('GeographyKey')->nullable();
            $table->integer('OrderQty')->nullable();
            $table->decimal('UnitPrice', 19, 4)->nullable();
            $table->decimal('UnitPriceDiscount', 19, 4)->nullable();
            $table->decimal('LineTotal', 19, 4)->nullable();
            $table->decimal('StandardCost', 19, 4)->nullable();
            $table->decimal('Profit', 19, 4)->nullable();

            $table->index('OrderDateKey', 'IX_Fact_OrderDate');
            $table->index('ProductKey', 'IX_Fact_Product');
            $table->index('CustomerKey', 'IX_Fact_Customer');
            $table->index('SalespersonKey', 'IX_Fact_Salesperson');
            $table->index('GeographyKey', 'IX_Fact_Geography');
            $table->index('SalesOrderID', 'idx_fact_salesorderid');
            $table->index(['CustomerKey', 'SalespersonKey'], 'idx_fact_customerkey_salesperson');
            $table->index(['SalesOrderID', 'ProductKey'], 'idx_fact_order_product');
            $table->index(['OrderDateKey', 'CustomerKey'], 'idx_fact_datekey_customer');
        });
    }

    private function createFactInventoryMonthly($schema): void
    {
        if ($schema->hasTable('FactInventoryMonthly')) {
            return;
        }

        $schema->create('FactInventoryMonthly', function (Blueprint $table) {
            $table->bigIncrements('InventoryFactID');
            $table->integer('ProductKey');
            $table->integer('DateKey');
            $table->decimal('EndingQuantity', 18, 2)->nullable();
            $table->index('ProductKey', 'IX_Inventory_Product');
            $table->index('DateKey', 'IX_Inventory_Date');
            $table->index(['ProductKey', 'DateKey'], 'idx_inv_product_date');
        });
    }
};
