@extends('layouts.dashboard')

@section('title', 'Territory Discount vs Profit - AdventureWorks DW')

@push('styles')
<style>
    .filter-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .filter-card:hover { transform: translateY(-2px); box-shadow: 0 12px 24px -8px rgba(0,0,0,0.15); }
    .loading-spinner { border: 3px solid #f3f4f6; border-top: 3px solid #3b82f6; border-radius: 50%; width: 36px; height: 36px; animation: spin 1s linear infinite; }
    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    .fade-in { animation: fadeIn 0.5s ease-in; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endpush

@section('content')
    <div class="mb-8 fade-in">
        <div class="flex items-center mb-4">
            <div class="bg-gradient-to-r from-green-500 to-teal-600 text-white w-12 h-12 rounded-full flex items-center justify-center mr-4 text-xl font-bold shadow-lg">
                2
            </div>
            <div>
                <h2 class="text-4xl font-bold text-gray-800">Territory Performance Analysis</h2>
                <p class="text-gray-600 font-medium mt-1">Discount vs profit margin per territory - strategi optimasi regional</p>
            </div>
        </div>
        
        <!-- OLAP Badge -->
        <div class="inline-flex items-center bg-gradient-to-r from-amber-400 to-orange-500 text-white px-4 py-2 rounded-full shadow-md">
            <span class="text-sm font-bold">⚡ OLAP: Drill-Down Available</span>
        </div>
    </div>

    <!-- Interactive Filters -->
    <div class="bg-white rounded-2xl shadow-lg p-6 mb-8 filter-card fade-in">
        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
            </svg>
            Interactive Filters
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Year Filter -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">📅 Tahun Order</label>
                <select id="dateRangeFilter" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
                    <option value="all">Semua Tahun</option>
                    @foreach($availableYears as $year)
                        <option value="{{ $year->Year }}">{{ $year->Year }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Territory Filter -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">🌍 Territory</label>
                <select id="territoryFilter" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
                    <option value="all">All Territories</option>
                    @foreach($territoryMetrics as $territory)
                        <option value="{{ $territory->TerritoryID }}">{{ $territory->TerritoryName }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="mt-4 flex flex-wrap items-center space-x-3">
            <button onclick="applyFilters()" class="bg-gradient-to-r from-green-500 to-teal-600 hover:from-green-600 hover:to-teal-700 text-white px-6 py-2.5 rounded-lg font-semibold transition shadow-lg hover:shadow-xl transform hover:scale-105">
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

    <!-- Territory Performance Content -->
    <div class="bg-white rounded-2xl shadow-xl p-8 filter-card fade-in hover:shadow-2xl">
        <div class="flex items-start justify-between mb-6">
            <div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">Territory Metrics</h3>
                <p class="text-gray-600 leading-relaxed">
                    Analisis discount rate vs profit margin - identifikasi wilayah dengan diskon tinggi & profit rendah
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

        <!-- Insights Section -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-gradient-to-br from-green-50 to-teal-50 p-6 rounded-xl border-2 border-green-200">
                <div class="text-3xl mb-2">💰</div>
                <div class="text-2xl font-bold text-green-700 mb-1">${{ number_format(collect($territoryMetrics)->sum('TotalRevenue') ?? 0, 0) }}</div>
                <div class="text-sm text-gray-600 font-medium">Total Revenue All Territories</div>
            </div>
            <div class="bg-gradient-to-br from-green-50 to-teal-50 p-6 rounded-xl border-2 border-green-200">
                <div class="text-3xl mb-2">📊</div>
                <div class="text-2xl font-bold text-green-700 mb-1">{{ number_format(collect($territoryMetrics)->avg('AvgProfitMargin') * 100 ?? 0, 2) }}%</div>
                <div class="text-sm text-gray-600 font-medium">Average Profit Margin</div>
            </div>
            <div class="bg-gradient-to-br from-green-50 to-teal-50 p-6 rounded-xl border-2 border-green-200">
                <div class="text-3xl mb-2">🎯</div>
                <div class="text-2xl font-bold text-green-700 mb-1">{{ number_format(collect($territoryMetrics)->avg('AvgDiscountRate') * 100 ?? 0, 2) }}%</div>
                <div class="text-sm text-gray-600 font-medium">Average Discount Rate</div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const territoryDataAll = @json($territoryMetrics);
    const territoryDataYearly = @json($territoryMetricsYearly);

    const territoryChartCtx = document.getElementById('territoryChart');

    const territoryChart = new Chart(territoryChartCtx, {
        type: 'bar',
        data: {
            labels: territoryDataAll.map(t => t.TerritoryName || 'N/A'),
            datasets: [
                {
                    label: 'Discount Rate (%)',
                    data: territoryDataAll.map(t => (t.AvgDiscountRate * 100).toFixed(2)),
                    backgroundColor: 'rgba(239, 68, 68, 0.7)',
                    borderColor: 'rgba(239, 68, 68, 1)',
                    borderWidth: 2,
                    borderRadius: 6,
                    yAxisID: 'y'
                },
                {
                    label: 'Profit Margin (%)',
                    data: territoryDataAll.map(t => (t.AvgProfitMargin * 100).toFixed(2)),
                    backgroundColor: 'rgba(34, 197, 94, 0.7)',
                    borderColor: 'rgba(34, 197, 94, 1)',
                    borderWidth: 2,
                    borderRadius: 6,
                    yAxisID: 'y'
                }
            ]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: true, position: 'top', labels: { font: { size: 14, weight: 'bold' }, padding: 15 } },
                tooltip: { backgroundColor: 'rgba(0, 0, 0, 0.8)', padding: 12, cornerRadius: 8 }
            },
            scales: {
                y: { beginAtZero: true, position: 'left', grid: { color: 'rgba(0, 0, 0, 0.05)' }, title: { display: true, text: 'Percentage (%)' } },
                x: { grid: { display: false } }
            }
        }
    });

    function applyFilters() {
        const year = document.getElementById('dateRangeFilter').value;
        const territoryId = document.getElementById('territoryFilter').value;
        
        document.getElementById('loadingIndicator').classList.remove('hidden');
        document.getElementById('loadingIndicator').classList.add('flex');

        let sourceData = year === 'all' ? territoryDataAll : (territoryDataYearly[year] || []);
        
        if (territoryId !== 'all') {
            sourceData = sourceData.filter(t => t.TerritoryID == territoryId);
        }

        updateTable(sourceData);
        updateChart(sourceData);

        setTimeout(() => {
            document.getElementById('loadingIndicator').classList.add('hidden');
            document.getElementById('loadingIndicator').classList.remove('flex');
        }, 500);
    }

    function updateTable(data) {
        const tbody = document.getElementById('territoryTableBody');
        tbody.innerHTML = data.map(territory => `
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
                    <span class="text-lg font-bold text-gray-900">$${Number(territory.TotalRevenue).toLocaleString()}</span>
                </td>
                <td class="px-6 py-4 text-center">
                    ${territory.TerritoryID ? `
                        <a href="/dashboard/territory/${territory.TerritoryID}/drilldown" 
                        class="inline-flex items-center bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white px-4 py-2 rounded-lg text-sm font-bold transition transform hover:scale-105 shadow-lg">
                            🔍 Drill-Down
                        </a>
                    ` : ''}
                </td>
            </tr>
        `).join('');
        
        document.getElementById('territoryCount').textContent = data.length;
    }

    function updateChart(data) {
        territoryChart.data.labels = data.map(t => t.TerritoryName || 'N/A');
        territoryChart.data.datasets[0].data = data.map(t => (t.AvgDiscountRate * 100).toFixed(2));
        territoryChart.data.datasets[1].data = data.map(t => (t.AvgProfitMargin * 100).toFixed(2));
        territoryChart.update();
    }

    function resetFilters() {
        document.getElementById('dateRangeFilter').value = 'all';
        document.getElementById('territoryFilter').value = 'all';
        applyFilters();
    }
</script>
@endpush
