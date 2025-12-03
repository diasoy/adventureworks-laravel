<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::connection('mysql_dwh');

        $this->ensureDimProductAttributes($schema);
        $this->ensureFactSalesIndexes($schema);
        $this->ensureInventoryFact($schema);
    }

    public function down(): void
    {
        $schema = Schema::connection('mysql_dwh');

        if ($schema->hasTable('FactSalesOrderLine')) {
            $schema->table('FactSalesOrderLine', function (Blueprint $table) {
                if ($this->hasIndex('FactSalesOrderLine', 'IX_Fact_Salesperson')) {
                    $table->dropIndex('IX_Fact_Salesperson');
                }
                if ($this->hasIndex('FactSalesOrderLine', 'IX_Fact_Geography')) {
                    $table->dropIndex('IX_Fact_Geography');
                }
            });
        }

        if ($schema->hasTable('DimProduct')) {
            $schema->table('DimProduct', function (Blueprint $table) {
                if ($this->hasIndex('DimProduct', 'IX_DimProduct_Subcategory')) {
                    $table->dropIndex('IX_DimProduct_Subcategory');
                }
                if ($this->hasIndex('DimProduct', 'IX_DimProduct_Category')) {
                    $table->dropIndex('IX_DimProduct_Category');
                }
            });

            if ($schema->hasColumn('DimProduct', 'ProductSubcategoryName')) {
                DB::connection('mysql_dwh')->statement('ALTER TABLE DimProduct DROP COLUMN ProductSubcategoryName');
            }
            if ($schema->hasColumn('DimProduct', 'ProductCategoryName')) {
                DB::connection('mysql_dwh')->statement('ALTER TABLE DimProduct DROP COLUMN ProductCategoryName');
            }
        }

        if ($schema->hasTable('FactInventoryMonthly')) {
            $schema->dropIfExists('FactInventoryMonthly');
        }
    }

    private function ensureDimProductAttributes($schema): void
    {
        if ($schema->hasTable('DimProduct')) {
            if (!$schema->hasColumn('DimProduct', 'ProductSubcategoryName')) {
                $schema->table('DimProduct', function (Blueprint $table) {
                    $table->string('ProductSubcategoryName', 100)->nullable()->after('ProductCategoryID');
                });
            }

            if (!$schema->hasColumn('DimProduct', 'ProductCategoryName')) {
                $schema->table('DimProduct', function (Blueprint $table) {
                    $table->string('ProductCategoryName', 100)->nullable()->after('ProductSubcategoryName');
                });
            }

            $schema->table('DimProduct', function (Blueprint $table) {
                if (!$this->hasIndex('DimProduct', 'IX_DimProduct_Subcategory')) {
                    $table->index('ProductSubcategoryID', 'IX_DimProduct_Subcategory');
                }
                if (!$this->hasIndex('DimProduct', 'IX_DimProduct_Category')) {
                    $table->index('ProductCategoryID', 'IX_DimProduct_Category');
                }
            });
        }
    }

    private function ensureFactSalesIndexes($schema): void
    {
        if (!$schema->hasTable('FactSalesOrderLine')) {
            return;
        }

        $schema->table('FactSalesOrderLine', function (Blueprint $table) {
            if (!$this->hasIndex('FactSalesOrderLine', 'IX_Fact_Salesperson')) {
                $table->index('SalespersonKey', 'IX_Fact_Salesperson');
            }
            if (!$this->hasIndex('FactSalesOrderLine', 'IX_Fact_Geography')) {
                $table->index('GeographyKey', 'IX_Fact_Geography');
            }
        });
    }

    private function ensureInventoryFact($schema): void
    {
        if (!$schema->hasTable('FactInventoryMonthly')) {
            $schema->create('FactInventoryMonthly', function (Blueprint $table) {
                $table->bigIncrements('InventoryFactID');
                $table->unsignedInteger('ProductKey');
                $table->unsignedInteger('DateKey');
                $table->decimal('EndingQuantity', 18, 2)->nullable();
                $table->index('ProductKey', 'IX_Inventory_Product');
                $table->index('DateKey', 'IX_Inventory_Date');
            });
            return;
        }

        $schema->table('FactInventoryMonthly', function (Blueprint $table) {
            if (!$this->hasIndex('FactInventoryMonthly', 'IX_Inventory_Product')) {
                $table->index('ProductKey', 'IX_Inventory_Product');
            }
            if (!$this->hasIndex('FactInventoryMonthly', 'IX_Inventory_Date')) {
                $table->index('DateKey', 'IX_Inventory_Date');
            }
        });
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $result = DB::connection('mysql_dwh')->select(
            'SHOW INDEX FROM ' . $table . ' WHERE Key_name = ?',
            [$indexName]
        );

        return !empty($result);
    }
};
