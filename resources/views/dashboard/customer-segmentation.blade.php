@extends('layouts.dashboard')

@section('title', 'Customer Segmentation - AdventureWorks DW')

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
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 text-white w-12 h-12 rounded-full flex items-center justify-center mr-4 text-xl font-bold shadow-lg">
                3
            </div>
            <div>
                <h2 class="text-4xl font-bold text-gray-800">Customer Segmentation Analysis</h2>
                <p class="text-gray-600 font-medium mt-1">Segmen pelanggan high-frequency, low-ticket untuk strategi upselling</p>
            </div>
        </div>
    </div>

    <!-- Customer Segments Content -->
    <div class="bg-white rounded-2xl shadow-xl p-8 filter-card fade-in hover:shadow-2xl">
        <div class="flex items-start justify-between mb-6">
            <div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">High-Frequency, Low-Ticket Customers</h3>
                <p class="text-gray-600 leading-relaxed">
                    Pelanggan dengan frekuensi pembelian tinggi namun nilai transaksi per order relatif kecil
                </p>
            </div>
            <div class="bg-blue-50 px-4 py-2 rounded-lg">
                <span class="text-blue-700 font-bold text-lg">{{ count($customerSegments) }}</span>
                <span class="text-blue-600 text-sm ml-1">customers</span>
            </div>
        </div>
        
        <div class="overflow-x-auto bg-gray-50 rounded-xl p-2">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white">
                        <th class="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider rounded-tl-lg">Customer ID</th>
                        <th class="px-6 py-4 text-right text-sm font-bold uppercase tracking-wider">Avg Orders/Year</th>
                        <th class="px-6 py-4 text-right text-sm font-bold uppercase tracking-wider rounded-tr-lg">Avg Order Value</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($customerSegments as $customer)
                        <tr class="hover:bg-blue-50 transition duration-200">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                    {{ $customer->CustomerID }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end">
                                    <span class="text-lg font-bold text-indigo-600">{{ number_format($customer->AvgOrdersPerYear, 2) }}</span>
                                    <span class="text-xs text-gray-500 ml-1">orders/yr</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-lg font-bold text-gray-900">${{ number_format($customer->AvgOrderValueAcrossYears, 2) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-500">
                                    <svg class="w-16 h-16 mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                    </svg>
                                    <p class="text-lg font-semibold">No Data Available</p>
                                    <p class="text-sm mt-1">Tidak ada data pelanggan yang memenuhi kriteria high-frequency & low-ticket</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(count($customerSegments) > 0)
            <div class="mt-8 bg-gray-50 rounded-xl p-6">
                <h4 class="text-lg font-bold text-gray-800 mb-4">Frequency vs Order Value Distribution</h4>
                <canvas id="customerChart" height="80"></canvas>
            </div>

            <!-- Insights Section -->
            <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 p-6 rounded-xl border-2 border-blue-200">
                    <div class="text-3xl mb-2">📈</div>
                    <div class="text-2xl font-bold text-blue-700 mb-1">{{ number_format(collect($customerSegments)->avg('AvgOrdersPerYear') ?? 0, 2) }}</div>
                    <div class="text-sm text-gray-600 font-medium">Average Orders Per Year</div>
                </div>
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 p-6 rounded-xl border-2 border-blue-200">
                    <div class="text-3xl mb-2">💵</div>
                    <div class="text-2xl font-bold text-blue-700 mb-1">${{ number_format(collect($customerSegments)->avg('AvgOrderValueAcrossYears') ?? 0, 2) }}</div>
                    <div class="text-sm text-gray-600 font-medium">Average Order Value</div>
                </div>
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 p-6 rounded-xl border-2 border-blue-200">
                    <div class="text-3xl mb-2">👥</div>
                    <div class="text-2xl font-bold text-blue-700 mb-1">{{ count($customerSegments) }}</div>
                    <div class="text-sm text-gray-600 font-medium">Segmented Customers</div>
                </div>
            </div>

            <!-- Recommendations -->
            <div class="mt-8 bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-blue-500 p-6 rounded-lg">
                <h4 class="text-lg font-bold text-gray-800 mb-3 flex items-center">
                    <span class="text-2xl mr-2">💡</span>
                    Strategic Recommendations
                </h4>
                <ul class="space-y-2 text-gray-700">
                    <li class="flex items-start">
                        <span class="text-blue-500 mr-2">•</span>
                        <span><strong>Upselling Strategy:</strong> Target pelanggan ini dengan produk premium atau bundle untuk meningkatkan order value</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-blue-500 mr-2">•</span>
                        <span><strong>Loyalty Program:</strong> Tawarkan rewards program untuk meningkatkan lifetime value</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-blue-500 mr-2">•</span>
                        <span><strong>Cross-sell:</strong> Leverage high purchase frequency dengan produk komplementer</span>
                    </li>
                </ul>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    const customerData = @json($customerSegments);
    
    @if(count($customerSegments) > 0)
        new Chart(document.getElementById('customerChart'), {
            type: 'scatter',
            data: {
                datasets: [{
                    label: 'Customer Segments',
                    data: customerData.map(c => ({ 
                        x: parseFloat(c.AvgOrdersPerYear), 
                        y: parseFloat(c.AvgOrderValueAcrossYears) 
                    })),
                    backgroundColor: 'rgba(99, 102, 241, 0.6)',
                    borderColor: 'rgba(99, 102, 241, 1)',
                    borderWidth: 2,
                    pointRadius: 8,
                    pointHoverRadius: 12,
                    pointHoverBackgroundColor: 'rgba(79, 70, 229, 0.8)',
                    pointHoverBorderColor: 'rgba(79, 70, 229, 1)',
                    pointHoverBorderWidth: 3
                }]
            },
            options: {
                responsive: true,
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
                                return `Orders/Year: ${context.parsed.x.toFixed(2)}, Order Value: $${context.parsed.y.toFixed(2)}`;
                            }
                        }
                    }
                },
                scales: {
                    x: { 
                        title: { display: true, text: 'Average Orders Per Year', font: { size: 14, weight: 'bold' } },
                        grid: { color: 'rgba(0, 0, 0, 0.05)' }
                    },
                    y: { 
                        title: { display: true, text: 'Average Order Value ($)', font: { size: 14, weight: 'bold' } },
                        grid: { color: 'rgba(0, 0, 0, 0.05)' },
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    @endif
</script>
@endpush
