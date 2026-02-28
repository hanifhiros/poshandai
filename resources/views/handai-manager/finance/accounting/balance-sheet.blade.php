@extends('handai-manager.layouts.master')

@section('title', 'Neraca (Balance Sheet) — Handai Finance')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Neraca (Balance Sheet)</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $store->name ?? 'Semua Store' }}</p>
        </div>
        <a href="{{ route('manager.finance.accounting.dashboard') }}" class="mt-3 sm:mt-0 text-sm text-green-600 hover:underline">&larr; Dashboard</a>
    </div>

    {{-- Date Filter --}}
    <form method="GET" action="{{ route('manager.finance.accounting.balance') }}" class="bg-white rounded-xl shadow-sm border p-4 mb-6">
        <div class="flex flex-wrap gap-3 items-end">
            <div class="flex flex-col">
                <label class="text-xs text-gray-500 mb-1">Per Tanggal</label>
                <input type="date" name="as_of_date" value="{{ $asOfDate }}" class="input input-bordered input-sm text-sm bg-white">
            </div>
            <button type="submit" class="btn btn-sm btn-primary">Tampilkan</button>
        </div>
        <p class="text-xs text-gray-400 mt-2">
            Per tanggal: {{ \Carbon\Carbon::parse($asOfDate)->format('d M Y') }}
        </p>
    </form>

    {{-- Balance Check Badge --}}
    <div class="mb-6">
        @if ($isBalanced)
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-green-50 border border-green-200 rounded-lg">
                <i class="ti ti-circle-check text-green-600"></i>
                <span class="text-sm font-medium text-green-700">Neraca Seimbang ✓</span>
            </div>
        @else
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 border border-red-200 rounded-lg">
                <i class="ti ti-alert-triangle text-red-600"></i>
                <span class="text-sm font-medium text-red-700">Neraca Tidak Seimbang! Selisih: Rp{{ number_format(abs($totalAssets - ($totalLiabilities + $totalEquityWithRetained)), 0, ',', '.') }}</span>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- LEFT: ASSETS --}}
        <div>
            <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                <div class="px-6 py-4 bg-blue-50 border-b">
                    <h3 class="text-sm font-semibold text-blue-700 uppercase tracking-wide">
                        <i class="ti ti-wallet mr-1"></i> Aset (Assets)
                    </h3>
                </div>
                <table class="min-w-full text-sm">
                    <tbody>
                        @forelse ($assetBreakdown as $item)
                            <tr class="border-b border-gray-50 hover:bg-gray-50">
                                <td class="px-6 py-2.5 text-gray-700">
                                    <span class="font-mono text-xs text-gray-400 mr-2">{{ $item['code'] }}</span>
                                    {{ $item['name'] }}
                                </td>
                                <td class="px-6 py-2.5 text-right font-mono text-gray-700">
                                    Rp{{ number_format($item['balance'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="px-6 py-3 text-center text-gray-400 text-xs">Tidak ada data</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-blue-50">
                            <td class="px-6 py-3 font-semibold text-blue-800">Total Aset</td>
                            <td class="px-6 py-3 text-right font-mono font-bold text-blue-800 text-lg">
                                Rp{{ number_format($totalAssets, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- RIGHT: LIABILITIES + EQUITY --}}
        <div class="space-y-6">
            {{-- Liabilities --}}
            <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                <div class="px-6 py-4 bg-red-50 border-b">
                    <h3 class="text-sm font-semibold text-red-700 uppercase tracking-wide">
                        <i class="ti ti-receipt-tax mr-1"></i> Kewajiban (Liabilities)
                    </h3>
                </div>
                <table class="min-w-full text-sm">
                    <tbody>
                        @forelse ($liabilityBreakdown as $item)
                            <tr class="border-b border-gray-50 hover:bg-gray-50">
                                <td class="px-6 py-2.5 text-gray-700">
                                    <span class="font-mono text-xs text-gray-400 mr-2">{{ $item['code'] }}</span>
                                    {{ $item['name'] }}
                                </td>
                                <td class="px-6 py-2.5 text-right font-mono text-gray-700">
                                    Rp{{ number_format($item['balance'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="px-6 py-3 text-center text-gray-400 text-xs">Tidak ada data</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-red-50">
                            <td class="px-6 py-3 font-semibold text-red-700">Total Kewajiban</td>
                            <td class="px-6 py-3 text-right font-mono font-bold text-red-700">
                                Rp{{ number_format($totalLiabilities, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Equity --}}
            <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                <div class="px-6 py-4 bg-purple-50 border-b">
                    <h3 class="text-sm font-semibold text-purple-700 uppercase tracking-wide">
                        <i class="ti ti-building-bank mr-1"></i> Ekuitas (Equity)
                    </h3>
                </div>
                <table class="min-w-full text-sm">
                    <tbody>
                        @forelse ($equityBreakdown as $item)
                            <tr class="border-b border-gray-50 hover:bg-gray-50">
                                <td class="px-6 py-2.5 text-gray-700">
                                    <span class="font-mono text-xs text-gray-400 mr-2">{{ $item['code'] }}</span>
                                    {{ $item['name'] }}
                                </td>
                                <td class="px-6 py-2.5 text-right font-mono text-gray-700">
                                    Rp{{ number_format($item['balance'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="px-6 py-3 text-center text-gray-400 text-xs">Tidak ada data</td></tr>
                        @endforelse
                        {{-- Net Income Row --}}
                        <tr class="border-b border-gray-50 bg-emerald-50/50">
                            <td class="px-6 py-2.5 text-gray-700 italic">
                                <span class="font-mono text-xs text-gray-400 mr-2">&nbsp;</span>
                                Laba Periode Berjalan
                            </td>
                            <td class="px-6 py-2.5 text-right font-mono {{ $netIncome >= 0 ? 'text-green-700' : 'text-red-600' }}">
                                Rp{{ number_format($netIncome, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="bg-purple-50">
                            <td class="px-6 py-3 font-semibold text-purple-700">Total Ekuitas + Laba</td>
                            <td class="px-6 py-3 text-right font-mono font-bold text-purple-700">
                                Rp{{ number_format($totalEquityWithRetained, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Total Liabilities + Equity --}}
            <div class="bg-white rounded-xl shadow-sm border px-6 py-4 flex items-center justify-between">
                <p class="font-semibold text-gray-800">Total Kewajiban + Ekuitas</p>
                <p class="font-mono font-bold text-lg text-gray-800">
                    Rp{{ number_format($totalLiabilities + $totalEquityWithRetained, 0, ',', '.') }}
                </p>
            </div>
        </div>
    </div>

    {{-- Balance Equation Summary --}}
    <div class="mt-8 bg-white rounded-xl shadow-sm border p-6">
        <h4 class="text-sm font-semibold text-gray-700 mb-4 uppercase">Persamaan Akuntansi</h4>
        <div class="flex flex-wrap items-center justify-center gap-4 text-center">
            <div>
                <p class="text-2xl font-bold text-blue-700">Rp{{ number_format($totalAssets, 0, ',', '.') }}</p>
                <p class="text-xs text-gray-400 mt-1">ASET</p>
            </div>
            <span class="text-2xl text-gray-400 font-bold">=</span>
            <div>
                <p class="text-2xl font-bold text-red-600">Rp{{ number_format($totalLiabilities, 0, ',', '.') }}</p>
                <p class="text-xs text-gray-400 mt-1">KEWAJIBAN</p>
            </div>
            <span class="text-2xl text-gray-400 font-bold">+</span>
            <div>
                <p class="text-2xl font-bold text-purple-700">Rp{{ number_format($totalEquityWithRetained, 0, ',', '.') }}</p>
                <p class="text-xs text-gray-400 mt-1">EKUITAS + LABA</p>
            </div>
        </div>
    </div>
</div>
@endsection
