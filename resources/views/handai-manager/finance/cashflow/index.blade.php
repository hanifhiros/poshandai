@extends('layouts.master')

@section('title', 'Cashflow â€” Handai Finance')

@section('page-style')
<style>
    .fc { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
</style>
@endsection

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Cashflow</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $store->name }} â€” Monitoring arus kas</p>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div class="fc p-4">
            <p class="text-[10px] uppercase tracking-wide text-gray-400 font-semibold mb-1">Saldo Awal</p>
            <p class="text-lg font-bold text-gray-700">Rp{{ number_format($openingBalance, 0, ',', '.') }}</p>
        </div>
        <div class="fc p-4">
            <p class="text-[10px] uppercase tracking-wide text-gray-400 font-semibold mb-1">Cash In</p>
            <p class="text-lg font-bold text-green-600">Rp{{ number_format($cashIn, 0, ',', '.') }}</p>
        </div>
        <div class="fc p-4">
            <p class="text-[10px] uppercase tracking-wide text-gray-400 font-semibold mb-1">Cash Out</p>
            <p class="text-lg font-bold text-red-600">Rp{{ number_format($cashOut, 0, ',', '.') }}</p>
        </div>
        <div class="fc p-4">
            <p class="text-[10px] uppercase tracking-wide text-gray-400 font-semibold mb-1">Net Cashflow</p>
            <p class="text-lg font-bold {{ $netCashflow >= 0 ? 'text-green-700' : 'text-red-600' }}">Rp{{ number_format($netCashflow, 0, ',', '.') }}</p>
        </div>
        <div class="fc p-4 ring-2 {{ $closingBalance >= 0 ? 'ring-blue-200' : 'ring-red-200' }}">
            <p class="text-[10px] uppercase tracking-wide text-gray-400 font-semibold mb-1">Saldo Akhir</p>
            <p class="text-lg font-bold {{ $closingBalance >= 0 ? 'text-blue-700' : 'text-red-600' }}">Rp{{ number_format($closingBalance, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Filter --}}
    <div class="fc p-4 mb-6">
        <form method="GET" action="{{ route('manager.finance.cashflow.index') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="text-xs text-gray-500 font-medium">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}"
                       class="mt-1 block w-full text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
            </div>
            <div>
                <label class="text-xs text-gray-500 font-medium">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}"
                       class="mt-1 block w-full text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-lg hover:bg-gray-900 transition">Filter</button>
            <a href="{{ route('manager.finance.cashflow.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Reset</a>
        </form>
    </div>

    {{-- Cashflow Chart --}}
    <div class="fc p-6 mb-6">
        <h3 class="text-base font-semibold text-gray-700 mb-4">Daily Cashflow</h3>
        <div style="height:300px"><canvas id="cashflowChart"></canvas></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Cashflow by Source --}}
        <div class="fc p-6">
            <h3 class="text-base font-semibold text-gray-700 mb-4">Cashflow by Source</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-gray-500">
                        <tr>
                            <th class="text-left py-2 font-medium">Source</th>
                            <th class="text-right py-2 font-medium">Cash In</th>
                            <th class="text-right py-2 font-medium">Cash Out</th>
                            <th class="text-right py-2 font-medium">Net</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($cashflowBySource as $src)
                        <tr>
                            <td class="py-2">
                                <span class="inline-flex px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-700 rounded-full">{{ $src->source }}</span>
                            </td>
                            <td class="py-2 text-right text-green-600">Rp{{ number_format($src->total_in, 0, ',', '.') }}</td>
                            <td class="py-2 text-right text-red-600">Rp{{ number_format($src->total_out, 0, ',', '.') }}</td>
                            <td class="py-2 text-right font-semibold {{ ($src->total_in - $src->total_out) >= 0 ? 'text-green-700' : 'text-red-600' }}">
                                Rp{{ number_format($src->total_in - $src->total_out, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Transaction Details --}}
        <div class="fc p-6">
            <h3 class="text-base font-semibold text-gray-700 mb-4">Transaksi Terakhir</h3>
            <div class="space-y-2 max-h-80 overflow-y-auto">
                @if ($transactionDetails instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    @forelse ($transactionDetails as $tx)
                        <div class="flex items-center justify-between py-1.5 {{ !$loop->last ? 'border-b border-gray-50' : '' }}">
                            <div>
                                <p class="text-sm text-gray-700">{{ Str::limit($tx->description, 35) }}</p>
                                <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($tx->journal_date)->format('d/m/Y') }} Â· {{ $tx->source }}</p>
                            </div>
                            @if ($tx->debit > 0)
                                <span class="text-sm font-semibold text-green-600">+Rp{{ number_format($tx->debit, 0, ',', '.') }}</span>
                            @else
                                <span class="text-sm font-semibold text-red-600">-Rp{{ number_format($tx->credit, 0, ',', '.') }}</span>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 text-center py-4">Tidak ada transaksi</p>
                    @endforelse
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const daily = @json($dailyCashflow);

    new Chart(document.getElementById('cashflowChart'), {
        type: 'bar',
        data: {
            labels: daily.map(d => d.date),
            datasets: [
                { label: 'Cash In', data: daily.map(d => d.cash_in), backgroundColor: 'rgba(34,197,94,0.7)', borderRadius: 4 },
                { label: 'Cash Out', data: daily.map(d => d.cash_out), backgroundColor: 'rgba(239,68,68,0.7)', borderRadius: 4 },
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 15, font: { size: 11 } } } },
            scales: {
                y: { beginAtZero: true, ticks: { callback: v => 'Rp' + (v/1000000).toFixed(1) + 'jt', font: { size: 10 } }, grid: { color: '#f1f5f9' } },
                x: { ticks: { font: { size: 9 }, maxRotation: 45 }, grid: { display: false } }
            }
        }
    });
});
</script>
@endsection

