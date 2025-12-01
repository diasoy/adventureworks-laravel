@extends('layouts.dashboard')

@section('title', 'Salesperson Retention - AdventureWorks DW')

@push('styles')
<style>
    .filter-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .filter-card:hover { transform: translateY(-2px); box-shadow: 0 12px 24px -8px rgba(0,0,0,0.15); }
    .fade-in { animation: fadeIn 0.5s ease-in; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endpush

@section('content')
    <div class="mb-8 fade-in">
        <div class="flex items-center mb-4">
            <div class="bg-gradient-to-r from-purple-500 to-pink-600 text-white w-12 h-12 rounded-full flex items-center justify-center mr-4 text-xl font-bold shadow-lg">
                4
            </div>
            <div>
                <h2 class="text-4xl font-bold text-gray-800">Salesperson Retention Analysis</h2>
                <p class="text-gray-600 font-medium mt-1">Performance salesperson berdasarkan customer retention rate & total sales</p>
            </div>
        </div>
    </div>

    <!-- Salesperson Retention Content -->
    <div class="bg-white rounded-2xl shadow-xl p-8 filter-card fade-in hover:shadow-2xl">
        <div class="flex items-start justify-between mb-6">
            <div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">Customer Retention by Salesperson</h3>
                <p class="text-gray-600 leading-relaxed">
                    Salesperson dengan tingkat retensi pelanggan terbaik (repeat orders) & kontribusi penjualan
                </p>
            </div>
            <div class="bg-purple-50 px-4 py-2 rounded-lg">
                <span class="text-purple-700 font-bold text-lg">{{ count($salespersonRetention) }}</span>
                <span class="text-purple-600 text-sm ml-1">salespersons</span>
            </div>
        </div>
        
        <div class="overflow-x-auto bg-gray-50 rounded-xl p-2">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gradient-to-r from-purple-600 to-pink-600 text-white">
                        <th class="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider rounded-tl-lg">Salesperson</th>
                        <th class="px-6 py-4 text-right text-sm font-bold uppercase tracking-wider">Customers</th>
                        <th class="px-6 py-4 text-right text-sm font-bold uppercase tracking-wider">Repeat Orders</th>
                        <th class="px-6 py-4 text-right text-sm font-bold uppercase tracking-wider">Retention Rate</th>
                        <th class="px-6 py-4 text-right text-sm font-bold uppercase tracking-wider rounded-tr-lg">Total Sales</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($salespersonRetention as $index => $sp)
                        <tr class="hover:bg-purple-50 transition duration-200">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-500 text-white rounded-full flex items-center justify-center font-bold mr-3">
                                        {{ $index + 1 }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-gray-900">{{ $sp->SalespersonName }}</div>
                                        <div class="text-xs text-gray-500">ID: {{ $sp->SalesPersonID }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                    {{ number_format($sp->CustomersHandled) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                    {{ number_format($sp->CustomersWithRepeatOrders) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end">
                                    <div class="w-24 bg-gray-200 rounded-full h-2.5 mr-3">
                                        <div class="bg-gradient-to-r from-purple-500 to-pink-500 h-2.5 rounded-full" 
                                             style="width: {{ $sp->RetentionRate * 100 }}%"></div>
                                    </div>
                                    <span class="text-lg font-bold text-purple-600">{{ number_format($sp->RetentionRate * 100, 1) }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-lg font-bold text-gray-900">${{ number_format($sp->TotalSales, 0) }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-8 bg-gray-50 rounded-xl p-6">
            <h4 class="text-lg font-bold text-gray-800 mb-4">Performance Comparison</h4>
            <canvas id="retentionChart" height="80"></canvas>
        </div>

        <!-- Insights Section -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-gradient-to-br from-purple-50 to-pink-50 p-6 rounded-xl border-2 border-purple-200">
                <div class="text-3xl mb-2">⭐</div>
                <div class="text-2xl font-bold text-purple-700 mb-1">{{ number_format(collect($salespersonRetention)->max('RetentionRate') * 100 ?? 0, 1) }}%</div>
                <div class="text-sm text-gray-600 font-medium">Highest Retention Rate</div>
            </div>
            <div class="bg-gradient-to-br from-purple-50 to-pink-50 p-6 rounded-xl border-2 border-purple-200">
                <div class="text-3xl mb-2">📊</div>
                <div class="text-2xl font-bold text-purple-700 mb-1">{{ number_format(collect($salespersonRetention)->avg('RetentionRate') * 100 ?? 0, 1) }}%</div>
                <div class="text-sm text-gray-600 font-medium">Average Retention Rate</div>
            </div>
            <div class="bg-gradient-to-br from-purple-50 to-pink-50 p-6 rounded-xl border-2 border-purple-200">
                <div class="text-3xl mb-2">💰</div>
                <div class="text-2xl font-bold text-purple-700 mb-1">${{ number_format(collect($salespersonRetention)->sum('TotalSales') ?? 0, 0) }}</div>
                <div class="text-sm text-gray-600 font-medium">Total Sales Volume</div>
            </div>
            <div class="bg-gradient-to-br from-purple-50 to-pink-50 p-6 rounded-xl border-2 border-purple-200">
                <div class="text-3xl mb-2">👥</div>
                <div class="text-2xl font-bold text-purple-700 mb-1">{{ number_format(collect($salespersonRetention)->sum('CustomersHandled') ?? 0) }}</div>
                <div class="text-sm text-gray-600 font-medium">Total Customers Served</div>
            </div>
        </div>

        <!-- Top Performers -->
        @php
            $topPerformers = collect($salespersonRetention)->sortByDesc('RetentionRate')->take(3);
        @endphp
        
        <div class="mt-8 bg-gradient-to-r from-purple-50 to-pink-50 border-l-4 border-purple-500 p-6 rounded-lg">
            <h4 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                <span class="text-2xl mr-2">🏆</span>
                Top Retention Performers
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($topPerformers as $index => $performer)
                    <div class="bg-white p-4 rounded-lg shadow-sm border-2 {{ $index === 0 ? 'border-yellow-400' : 'border-gray-200' }}">
                        <div class="flex items-center mb-2">
                            <span class="text-2xl mr-2">{{ $index === 0 ? '🥇' : ($index === 1 ? '🥈' : '🥉') }}</span>
                            <div class="text-sm font-bold text-gray-800">{{ $performer->SalespersonName }}</div>
                        </div>
                        <div class="text-xs text-gray-600 space-y-1">
                            <div>Retention: <span class="font-bold text-purple-600">{{ number_format($performer->RetentionRate * 100, 1) }}%</span></div>
                            <div>Sales: <span class="font-bold text-green-600">${{ number_format($performer->TotalSales, 0) }}</span></div>
                            <div>Customers: <span class="font-bold text-blue-600">{{ number_format($performer->CustomersHandled) }}</span></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Recommendations -->
        <div class="mt-8 bg-gradient-to-r from-purple-50 to-pink-50 border-l-4 border-purple-500 p-6 rounded-lg">
            <h4 class="text-lg font-bold text-gray-800 mb-3 flex items-center">
                <span class="text-2xl mr-2">💡</span>
                Strategic Insights
            </h4>
            <ul class="space-y-2 text-gray-700">
                <li class="flex items-start">
                    <span class="text-purple-500 mr-2">•</span>
                    <span><strong>Best Practices Sharing:</strong> Identifikasi strategi dari top performers untuk diterapkan ke seluruh tim</span>
                </li>
                <li class="flex items-start">
                    <span class="text-purple-500 mr-2">•</span>
                    <span><strong>Training Focus:</strong> Berikan coaching kepada salesperson dengan retention rate rendah</span>
                </li>
                <li class="flex items-start">
                    <span class="text-purple-500 mr-2">•</span>
                    <span><strong>Incentive Program:</strong> Reward salesperson dengan retention & sales performance tertinggi</span>
                </li>
            </ul>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const spData = @json($salespersonRetention);
    
    new Chart(document.getElementById('retentionChart'), {
        type: 'bar',
        data: {
            labels: spData.map(sp => sp.SalespersonName.substring(0, 20)),
            datasets: [
                {
                    label: 'Retention Rate (%)',
                    data: spData.map(sp => (sp.RetentionRate * 100).toFixed(1)),
                    backgroundColor: 'rgba(168, 85, 247, 0.7)',
                    borderColor: 'rgba(168, 85, 247, 1)',
                    borderWidth: 2,
                    borderRadius: 6,
                    yAxisID: 'y'
                },
                {
                    label: 'Total Sales ($1K)',
                    data: spData.map(sp => (sp.TotalSales / 1000).toFixed(0)),
                    backgroundColor: 'rgba(236, 72, 153, 0.7)',
                    borderColor: 'rgba(236, 72, 153, 1)',
                    borderWidth: 2,
                    borderRadius: 6,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { 
                    display: true, 
                    position: 'top',
                    labels: { font: { size: 14, weight: 'bold' }, padding: 15 }
                },
                tooltip: { 
                    backgroundColor: 'rgba(0, 0, 0, 0.8)', 
                    padding: 12, 
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                if (context.datasetIndex === 0) {
                                    label += context.parsed.y + '%';
                                } else {
                                    label += '$' + (context.parsed.y * 1000).toLocaleString();
                                }
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: { display: true, text: 'Retention Rate (%)', font: { size: 14, weight: 'bold' } },
                    grid: { color: 'rgba(0, 0, 0, 0.05)' }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: { display: true, text: 'Total Sales ($1K)', font: { size: 14, weight: 'bold' } },
                    grid: { drawOnChartArea: false }
                },
                x: { 
                    grid: { display: false },
                    ticks: { font: { size: 11 } }
                }
            }
        }
    });
</script>
@endpush
