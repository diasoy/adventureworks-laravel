Overview
--------
This folder contains MySQL scripts to create a small Data Warehouse (DW) schema for AdventureWorks, populate it with ETL queries, and run analytical queries that answer five business questions required by your assignment.

Files
-----
- `schema.sql` - creates database `adventureworks_dwh` and DW tables (dimensions + fact table).
- `etl.sql` - ETL scripts to populate dimension tables and the fact table from `adventureworks` source database.
- `analytics_queries.sql` - Analytical queries that answer the 5 specified questions (market-basket, discounts vs profit by territory, frequency/segments, salesperson retention, inventory turnover).

Notes
-----
- These scripts are written for MySQL. Make sure your source database `adventureworks` exists (AdventureWorks MySQL version).
- Run the scripts with a MySQL client. Example using command line:

```bash
mysql -u root -p < dwh/schema.sql
mysql -u root -p < dwh/etl.sql
mysql -u root -p adventureworks_dwh < dwh/analytics_queries.sql
```

Or run from Laravel using the web interface (see below).

Laravel Integration
-------------------
After running schema.sql and etl.sql, you can:
- Access dashboard pages at `/dashboard/sales-overview`, `/dashboard/product-analysis`, `/dashboard/customer-geo`
- These pages will display visualizations and tables answering the 5 business questions
- Export results as CSV for your report

Next steps
----------
1. Run `schema.sql` to create the DW database and tables
2. Run `etl.sql` to populate the DW from your adventureworks database
3. Access the Laravel dashboard pages to view analytics

