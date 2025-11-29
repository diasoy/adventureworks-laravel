@extends('layouts.dashboard')

@section('title', 'Territory Drill-Down - AdventureWorks DW')

@push('styles')
<style>
    .filter-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .filter-card:hover { transform: translateY(-2px); box-shadow: 0 12px 24px -8px rgba(0,0,0,0.15); }
</style>
@endpush

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-3xl font-bold text-gray-800">Territory Drill-Down</h2>
            <p class="text-gray-600">Detail performa untuk territory: <span class="font-semibold">{{ $territory->Name ?? 'Unknown' }}</span></p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('dashboard.sales-overview') }}" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg transition">
                ← Back to Sales Overview
            </a>
        </div>
    </div>

    <!-- Territory Info Header -->
    <div class="bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-lg shadow-lg p-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <p class="text-sm text-blue-100">Territory Name</p>
                <p class="text-xl font-semibold">{{ $territory->Name ?? 'Unknown' }}</p>
            </div>
            <div>
                <p class="text-sm text-blue-100">Country/Region</p>
                <p class="text-xl font-semibold">{{ $territory->CountryRegionCode ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm text-blue-100">Group</p>
                <p class="text-xl font-semibold">{{ $territory->Group ?? 'N/A' }}</p>
            </div>
        </div>
    </div>

    <!-- Salesperson Performance -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8 filter-card">
        <h3 class="text-xl font-semibold mb-4">👥 Salesperson Performance in {{ $territory->Name }}</h3>
        <p class="text-gray-600 mb-4">Drill-down Territory → detail performance per Salesperson.</p>
        
        @if(count($salespeople) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full table-auto border-collapse">
                    <thead class="bg-blue-500 text-white">
                        <tr>
                            <th class="px-4 py-2 text-left">Salesperson ID</th>
                            <th class="px-4 py-2 text-left">Name</th>
                            <th class="px-4 py-2 text-right">Total Orders</th>
                            <th class="px-4 py-2 text-right">Total Sales</th>
                            <th class="px-4 py-2 text-right">Avg Discount</th>
                            <th class="px-4 py-2 text-right">Avg Profit Margin</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($salespeople as $sp)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-2">{{ $sp->SalesPersonID }}</td>
                                <td class="px-4 py-2 font-semibold">{{ $sp->SalespersonName }}</td>
                                <td class="px-4 py-2 text-right">{{ number_format($sp->TotalOrders) }}</td>
                                <td class="px-4 py-2 text-right">${{ number_format($sp->TotalSales, 2) }}</td>
                                <td class="px-4 py-2 text-right">{{ number_format($sp->AvgDiscountRate * 100, 2) }}%</td>
                                <td class="px-4 py-2 text-right">{{ number_format($sp->AvgProfitMargin * 100, 2) }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                <canvas id="salespersonChart" height="80"></canvas>
            </div>
        @else
            <div class="p-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700">
                Tidak ada data salesperson untuk territory ini.
            </div>
        @endif
    </div>

    <!-- Monthly Trend -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-xl font-semibold mb-4">📈 Monthly Revenue Trend (Last 12 Months)</h3>
        <p class="text-gray-600 mb-4">Drill-down temporal: Territory → Monthly performance.</p>
        
        @if(count($monthlyTrend) > 0)
            <div class="overflow-x-auto mb-6">
                <table class="min-w-full table-auto border-collapse text-sm">
                    <thead class="bg-green-500 text-white">
                        <tr>
                            <th class="px-4 py-2 text-left">Year</th>
                            <th class="px-4 py-2 text-left">Month</th>
                            <th class="px-4 py-2 text-right">Revenue</th>
                            <th class="px-4 py-2 text-right">Orders</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($monthlyTrend as $trend)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-2">{{ $trend->Year }}</td>
                                <td class="px-4 py-2">{{ date('F', mktime(0, 0, 0, $trend->Month, 1)) }}</td>
                                <td class="px-4 py-2 text-right font-semibold">${{ number_format($trend->Revenue, 2) }}</td>
                                <td class="px-4 py-2 text-right">{{ number_format($trend->Orders) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <canvas id="trendChart" height="60"></canvas>
        @else
            <div class="p-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700">
                Tidak ada data trend untuk territory ini.
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    @if(count($salespeople) > 0)
    const spData = @json($salespeople);
    new Chart(document.getElementById('salespersonChart'), {
        type: 'bar',
        data: {
            labels: spData.map(sp => sp.SalespersonName.substring(0, 15)),
            datasets: [{
                label: 'Total Sales ($)',
                data: spData.map(sp => sp.TotalSales),
                backgroundColor: 'rgba(59, 130, 246, 0.6)',
                borderColor: 'rgba(59, 130, 246, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true } }
        }
    });
    @endif

    @if(count($monthlyTrend) > 0)
    const trendData = @json($monthlyTrend);
    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels: trendData.slice().reverse().map(t => monthNames[t.Month - 1] + ' ' + t.Year),
            datasets: [{
                label: 'Revenue ($)',
                data: trendData.map(t => t.Revenue),
                borderColor: 'rgba(34, 197, 94, 1)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true } }
        }
    });
    @endif
</script>
@endpush
