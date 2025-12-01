@extends('layouts.dashboard')

@section('title', 'Sales Overview - AdventureWorks DW')

@push('styles')
<style>
    .filter-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .filter-card:hover { transform: translateY(-2px); box-shadow: 0 12px 24px -8px rgba(0,0,0,0.15); }
    .stat-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .loading-spinner { border: 3px solid #f3f4f6; border-top: 3px solid #3b82f6; border-radius: 50%; width: 36px; height: 36px; animation: spin 1s linear infinite; }
    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>
@endpush

@section('content')
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-800 rounded-lg shadow fade-in">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ session('success') }}
            </div>
        </div>
    @endif

    <div class="mb-8 fade-in">
        <h2 class="text-4xl font-bold text-gray-800 mb-2">Sales Overview Dashboard</h2>
        <p class="text-gray-600 font-medium">Konsolidasi filter interaktif dengan data DWH</p>
    </div>

    <!-- Interactive Filters -->
    <div class="bg-white rounded-2xl shadow-lg p-6 mb-8 filter-card fade-in">
        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
            </svg>
            Interactive Filters
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Year Filter -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">📅 Tahun Order</label>
                <select id="dateRangeFilter" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                    <option value="all">Semua Tahun</option>
                    @foreach($availableYears as $year)
                        <option value="{{ $year->Year }}">{{ $year->Year }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Territory Filter -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">🌍 Territory</label>
                <select id="territoryFilter" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                    <option value="all">All Territories</option>
                    @foreach($territoryMetrics as $territory)
                        <option value="{{ $territory->TerritoryID }}">{{ $territory->TerritoryName }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Top N Products Filter -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">📊 Show Top</label>
                <select id="topNFilter" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                    <option value="10">Top 10 Products</option>
                    <option value="15" selected>Top 15 Products</option>
                    <option value="20">Top 20 Products</option>
                    <option value="30">Top 30 Products</option>
                </select>
            </div>
        </div>
        <div class="mt-4 flex flex-wrap items-center space-x-3">
            <button onclick="applyFilters()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-lg font-semibold transition shadow-lg hover:shadow-xl transform hover:scale-105">
                Apply Filters
            </button>
            <button onclick="resetFilters()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2.5 rounded-lg font-semibold transition">
                Reset
            </button>
            <div id="loadingIndicator" class="hidden items-center ml-4">
                <div class="loading-spinner"></div>
                <span class="ml-3 text-gray-600 font-medium">Loading data...</span>
            </div>
        </div>
    </div>

    <!-- Question 1: Market Basket - Bundling Products -->
    <div class="bg-white rounded-2xl shadow-xl p-8 mb-8 filter-card fade-in hover:shadow-2xl">
        <div class="flex items-start justify-between mb-6">
            <div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2 flex items-center">
                    <span class="bg-gradient-to-r from-blue-500 to-purple-600 text-white w-10 h-10 rounded-full flex items-center justify-center mr-3 text-lg">1</span>
                    Market Basket Analysis
                </h3>
                <p class="text-gray-600 leading-relaxed">Produk yang sering dibeli bersamaan - landasan bundling & cross-selling</p>
            </div>
            <div class="bg-blue-50 px-4 py-2 rounded-lg">
                <span class="text-blue-700 font-bold text-lg" id="productCount">{{ min(count($bundlingProducts), 15) }}</span>
                <span class="text-blue-600 text-sm ml-1">products</span>
            </div>
        </div>
        
        <div class="overflow-x-auto bg-gray-50 rounded-xl p-2">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white">
                        <th class="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider rounded-tl-lg">Product ID</th>
                        <th class="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider">Product Name</th>
                        <th class="px-6 py-4 text-right text-sm font-bold uppercase tracking-wider rounded-tr-lg">Co-Purchase Orders</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="bundlingTableBody">
                    @foreach(array_slice($bundlingProducts, 0, 15) as $product)
                        <tr class="hover:bg-blue-50 transition duration-200 cursor-pointer">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                    {{ $product->ProductID }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-semibold text-gray-900">{{ $product->Name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <span class="text-lg font-bold text-indigo-600">{{ number_format($product->OrdersWithOtherProducts) }}</span>
                                <span class="text-xs text-gray-500 ml-1">orders</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-8 bg-gray-50 rounded-xl p-6">
            <canvas id="bundlingChart" height="70"></canvas>
        </div>
    </div>

    <!-- Question 2: Territory Discount vs Profit -->
    <div class="bg-white rounded-2xl shadow-xl p-8 filter-card fade-in hover:shadow-2xl">
        <div class="flex items-start justify-between mb-6">
            <div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2 flex items-center">
                    <span class="bg-gradient-to-r from-green-500 to-teal-600 text-white w-10 h-10 rounded-full flex items-center justify-center mr-3 text-lg">2</span>
                    Territory Performance Analysis
                    <span class="ml-3 bg-gradient-to-r from-amber-400 to-orange-500 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-md animate-pulse">
                        ⚡ OLAP: Drill-Down
                    </span>
                </h3>
                <p class="text-gray-600 leading-relaxed">
                    Discount vs profit margin per territory - klik untuk drill-down ke halaman detail.
                </p>
            </div>
            <div class="bg-green-50 px-4 py-2 rounded-lg">
                <span class="text-green-700 font-bold text-lg" id="territoryCount">{{ count($territoryMetrics) }}</span>
                <span class="text-green-600 text-sm ml-1">territories</span>
            </div>
        </div>
        
        <div class="overflow-x-auto bg-gray-50 rounded-xl p-2">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gradient-to-r from-green-600 to-teal-600 text-white">
                        <th class="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider rounded-tl-lg">Territory</th>
                        <th class="px-6 py-4 text-right text-sm font-bold uppercase tracking-wider">Discount Rate</th>
                        <th class="px-6 py-4 text-right text-sm font-bold uppercase tracking-wider">Profit Margin</th>
                        <th class="px-6 py-4 text-right text-sm font-bold uppercase tracking-wider">Revenue</th>
                        <th class="px-6 py-4 text-center text-sm font-bold uppercase tracking-wider rounded-tr-lg">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="territoryTableBody">
                    @foreach($territoryMetrics as $territory)
                        <tr class="hover:bg-green-50 transition duration-200">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <span class="text-2xl mr-2">🌍</span>
                                    <span class="font-semibold text-gray-900">{{ $territory->TerritoryName ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold
                                    {{ $territory->AvgDiscountRate > 0.1 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                    {{ number_format($territory->AvgDiscountRate * 100, 2) }}%
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold
                                    {{ $territory->AvgProfitMargin > 0.3 ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ number_format($territory->AvgProfitMargin * 100, 2) }}%
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-lg font-bold text-gray-900">${{ number_format($territory->TotalRevenue, 0) }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($territory->TerritoryID)
                                    <a href="{{ route('dashboard.territory-drilldown', $territory->TerritoryID) }}" 
                                    class="inline-flex items-center bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white px-4 py-2 rounded-lg text-sm font-bold transition transform hover:scale-105 shadow-lg">
                                        🔍 Drill-Down
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-8 bg-gray-50 rounded-xl p-6">
            <canvas id="territoryChart" height="70"></canvas>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const bundlingDataAll = @json($bundlingProducts);
    const bundlingDataYearly = @json($bundlingProductsYearly);
    const territoryDataAll = @json($territoryMetrics);
    const territoryDataYearly = @json($territoryMetricsYearly);

    const bundlingChartCtx = document.getElementById('bundlingChart');
    const territoryChartCtx = document.getElementById('territoryChart');

    const currency = (value) => '$' + Number(value || 0).toLocaleString();

    const bundlingChart = new Chart(bundlingChartCtx, {
        type: 'bar',
        data: {
            labels: bundlingDataAll.slice(0, 15).map(p => p.Name.substring(0, 35)),
            datasets: [{
                label: 'Co-Purchase Orders',
                data: bundlingDataAll.slice(0, 15).map(p => p.OrdersWithOtherProducts),
                backgroundColor: 'rgba(99, 102, 241, 0.8)',
                borderColor: 'rgba(99, 102, 241, 1)',
                borderWidth: 2,
                borderRadius: 8,
                hoverBackgroundColor: 'rgba(139, 92, 246, 0.9)'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: true, position: 'top', labels: { font: { size: 14, weight: 'bold' }, padding: 15 } },
                tooltip: { backgroundColor: 'rgba(0, 0, 0, 0.8)', padding: 12, cornerRadius: 8 }
            },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(0, 0, 0, 0.05)' } },
                x: { grid: { display: false }, ticks: { font: { size: 11 } } }
            }
        }
    });

    let territorySourceCache = [];

    const territoryChart = new Chart(territoryChartCtx, {
        type: 'scatter',
        data: {
            datasets: [{
                label: 'Territory Performance',
                data: territoryDataAll.map(t => ({
                    x: t.AvgDiscountRate * 100,
                    y: t.AvgProfitMargin * 100,
                    r: Math.sqrt(t.TotalRevenue) / 100
                })),
                backgroundColor: territoryDataAll.map(t => 
                    t.AvgProfitMargin > 0.3 ? 'rgba(34, 197, 94, 0.7)' : 
                    t.AvgProfitMargin > 0.2 ? 'rgba(251, 191, 36, 0.7)' : 'rgba(239, 68, 68, 0.7)'
                ),
                borderColor: 'rgba(255, 255, 255, 0.8)',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            const index = context.dataIndex;
                            const source = (territorySourceCache[index]) || {};
                            return [
                                source.TerritoryName || 'Unknown',
                                'Discount: ' + context.parsed.x.toFixed(2) + '%',
                                'Profit: ' + context.parsed.y.toFixed(2) + '%',
                                'Revenue: ' + currency(source.TotalRevenue)
                            ];
                        }
                    }
                }
            },
            scales: {
                x: { title: { display: true, text: 'Average Discount Rate (%)', font: { size: 13, weight: 'bold' } }, grid: { color: 'rgba(0, 0, 0, 0.05)' } },
                y: { title: { display: true, text: 'Average Profit Margin (%)', font: { size: 13, weight: 'bold' } }, grid: { color: 'rgba(0, 0, 0, 0.05)' } }
            }
        }
    });

    const currentTerritorySource = (selectedYear, selectedTerritory) => {
        let source = selectedYear === 'all'
            ? [...territoryDataAll]
            : territoryDataYearly.filter(t => String(t.OrderYear) === String(selectedYear));

        if (selectedTerritory !== 'all') {
            source = source.filter(t => String(t.TerritoryID) === String(selectedTerritory));
        }

        return source.sort((a, b) => (b.TotalRevenue || 0) - (a.TotalRevenue || 0));
    };

    territorySourceCache = currentTerritorySource('all', 'all');

    function updateBundlingTable(data) {
        const rows = data.map(product => `
            <tr class="hover:bg-blue-50 transition duration-200 cursor-pointer">
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                        ${product.ProductID}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm font-semibold text-gray-900">${product.Name}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                    <span class="text-lg font-bold text-indigo-600">${(product.OrdersWithOtherProducts || 0).toLocaleString()}</span>
                    <span class="text-xs text-gray-500 ml-1">orders</span>
                </td>
            </tr>
        `).join('');
        document.getElementById('bundlingTableBody').innerHTML = rows || `<tr><td class="px-6 py-4 text-sm text-gray-600" colspan="3">No data for selected filter.</td></tr>`;
        document.getElementById('productCount').textContent = data.length;
    }

    function updateTerritoryTable(data) {
        const rows = data.map(territory => `
            <tr class="hover:bg-green-50 transition duration-200">
                <td class="px-6 py-4">
                    <div class="flex items-center">
                        <span class="text-2xl mr-2">🌍</span>
                        <span class="font-semibold text-gray-900">${territory.TerritoryName || 'N/A'}</span>
                    </div>
                </td>
                <td class="px-6 py-4 text-right">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold ${territory.AvgDiscountRate > 0.1 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'}">
                        ${(territory.AvgDiscountRate * 100).toFixed(2)}%
                    </span>
                </td>
                <td class="px-6 py-4 text-right">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold ${territory.AvgProfitMargin > 0.3 ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}">
                        ${(territory.AvgProfitMargin * 100).toFixed(2)}%
                    </span>
                </td>
                <td class="px-6 py-4 text-right">
                    <span class="text-lg font-bold text-gray-900">${currency(territory.TotalRevenue)}</span>
                </td>
                <td class="px-6 py-4 text-center">
                    ${territory.TerritoryID ? `<a href="/dashboard/territory/${territory.TerritoryID}" class="inline-flex items-center bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white px-4 py-2 rounded-lg text-sm font-bold transition transform hover:scale-105 shadow-lg">🔍 Drill-Down</a>` : ''}
                </td>
            </tr>
        `).join('');
        document.getElementById('territoryTableBody').innerHTML = rows || `<tr><td class="px-6 py-4 text-sm text-gray-600" colspan="5">No data for selected filter.</td></tr>`;
        document.getElementById('territoryCount').textContent = data.length;
    }

    function applyFilters() {
        const selectedYear = document.getElementById('dateRangeFilter').value;
        const selectedTerritory = document.getElementById('territoryFilter').value;
        const topN = parseInt(document.getElementById('topNFilter').value, 10);

        document.getElementById('loadingIndicator').classList.remove('hidden');
        document.getElementById('loadingIndicator').classList.add('flex');

        setTimeout(() => {
            // Bundling data
            let bundlingSource = selectedYear === 'all'
                ? [...bundlingDataAll]
                : bundlingDataYearly.filter(p => String(p.OrderYear) === String(selectedYear));
            bundlingSource = bundlingSource
                .sort((a, b) => (b.OrdersWithOtherProducts || 0) - (a.OrdersWithOtherProducts || 0))
                .slice(0, topN);

            bundlingChart.data.labels = bundlingSource.map(p => p.Name.substring(0, 35));
            bundlingChart.data.datasets[0].data = bundlingSource.map(p => p.OrdersWithOtherProducts);
            bundlingChart.update('active');
            updateBundlingTable(bundlingSource);

            // Territory data
            const territorySource = currentTerritorySource(selectedYear, selectedTerritory);
            territorySourceCache = territorySource;
            territoryChart.data.datasets[0].data = territorySource.map(t => ({
                x: t.AvgDiscountRate * 100,
                y: t.AvgProfitMargin * 100,
                r: Math.sqrt(t.TotalRevenue) / 100
            }));
            territoryChart.data.datasets[0].backgroundColor = territorySource.map(t => 
                t.AvgProfitMargin > 0.3 ? 'rgba(34, 197, 94, 0.7)' : 
                t.AvgProfitMargin > 0.2 ? 'rgba(251, 191, 36, 0.7)' : 'rgba(239, 68, 68, 0.7)'
            );
            territoryChart.update('active');
            updateTerritoryTable(territorySource);

            document.getElementById('loadingIndicator').classList.add('hidden');
            document.getElementById('loadingIndicator').classList.remove('flex');
        }, 400);
    }

    function resetFilters() {
        document.getElementById('dateRangeFilter').value = 'all';
        document.getElementById('territoryFilter').value = 'all';
        document.getElementById('topNFilter').value = '15';
        applyFilters();
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.fade-in').forEach((el, index) => {
            el.style.animationDelay = `${index * 0.05}s`;
        });
        applyFilters();
    });
</script>
@endpush
