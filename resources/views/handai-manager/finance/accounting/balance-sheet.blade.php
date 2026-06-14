@extends('layouts.master')

@section('title', 'Neraca (Balance Sheet) â€” Handai Finance')

@push('styles')
<style>
    [x-cloak] { display: none !important; }
    .bs-input { height: 36px; border-radius: 8px; border: 1px solid #e2e8f0; background: #f8fafc; padding: 0 12px; font-size: 13px; color: #334155; transition: all .15s ease; }
    .bs-input:focus { outline: none; border-color: #059669; box-shadow: 0 0 0 3px rgba(5,150,105,.1); background: #fff; }
</style>
@endpush

@section('content')
<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[1360px] mx-auto">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-5">
        <div class="flex items-center gap-3">
            <a href="{{ route('manager.finance.accounting.dashboard') }}" class="w-9 h-9 rounded-lg border border-gray-200 flex items-center justify-center text-gray-400 hover:text-emerald-600 hover:border-emerald-300 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-lg font-semibold text-gray-800">Neraca (Balance Sheet)</h1>
                <p class="text-[12px] text-gray-400 mt-0.5">{{ $store->name ?? 'Semua Store' }}</p>
            </div>
        </div>
    </div>

    {{-- Date Filter --}}
    <form method="GET" action="{{ route('manager.finance.accounting.balance') }}" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-5">
        <div class="flex flex-wrap gap-3 items-end">
            <div class="flex flex-col">
                <label class="text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Per Tanggal</label>
                <input type="date" name="as_of_date" value="{{ $asOfDate }}" class="bs-input w-44">
            </div>
            <button type="submit" class="h-9 px-5 text-[13px] font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition shadow-sm">Tampilkan</button>
        </div>
        <p class="text-[11px] text-gray-400 mt-2">
            Per tanggal: {{ \Carbon\Carbon::parse($asOfDate)->format('d M Y') }}
        </p>
    </form>

    {{-- Balance Check Badge --}}
    <div class="mb-5">
        @if ($isBalanced)
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-50/60 border border-emerald-200 rounded-lg">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-[13px] font-medium text-emerald-700">Neraca Seimbang</span>
            </div>
        @else
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-red-50/60 border border-red-200 rounded-lg">
                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-[13px] font-medium text-red-700">Neraca Tidak Seimbang! Selisih: Rp{{ number_format(abs($totalAssets - ($totalLiabilities + $totalEquityWithRetained)), 0, ',', '.') }}</span>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- LEFT: ASSETS --}}
        <div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-3.5 bg-blue-50/60 border-b border-gray-100 flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 013 9m18 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 013 6v3"/></svg>
                    <h3 class="text-[12px] font-semibold text-blue-700 uppercase tracking-wider">Aset (Assets)</h3>
                </div>
                <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <tbody>
                        @forelse ($assetBreakdown as $item)
                            <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition">
                                <td class="px-5 py-2.5 text-gray-700">
                                    <span class="font-mono text-[11px] text-gray-400 mr-2">{{ $item['code'] }}</span>
                                    {{ $item['name'] }}
                                </td>
                                <td class="px-5 py-2.5 text-right font-mono text-[13px] text-gray-700 whitespace-nowrap">
                                    Rp{{ number_format($item['balance'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="px-5 py-4 text-center text-gray-400 text-[12px]">Tidak ada data</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-blue-50/60">
                            <td class="px-5 py-3 font-semibold text-blue-800 text-[13px]">Total Aset</td>
                            <td class="px-5 py-3 text-right font-mono font-bold text-blue-800 text-base">
                                Rp{{ number_format($totalAssets, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
                </div>
            </div>
        </div>

        {{-- RIGHT: LIABILITIES + EQUITY --}}
        <div class="space-y-5">
            {{-- Liabilities --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-3.5 bg-red-50/60 border-b border-gray-100 flex items-center gap-2">
                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                    <h3 class="text-[12px] font-semibold text-red-700 uppercase tracking-wider">Kewajiban (Liabilities)</h3>
                </div>
                <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <tbody>
                        @forelse ($liabilityBreakdown as $item)
                            <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition">
                                <td class="px-5 py-2.5 text-gray-700">
                                    <span class="font-mono text-[11px] text-gray-400 mr-2">{{ $item['code'] }}</span>
                                    {{ $item['name'] }}
                                </td>
                                <td class="px-5 py-2.5 text-right font-mono text-[13px] text-gray-700 whitespace-nowrap">
                                    Rp{{ number_format($item['balance'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="px-5 py-4 text-center text-gray-400 text-[12px]">Tidak ada data</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-red-50/60">
                            <td class="px-5 py-3 font-semibold text-red-700 text-[13px]">Total Kewajiban</td>
                            <td class="px-5 py-3 text-right font-mono font-bold text-red-700">
                                Rp{{ number_format($totalLiabilities, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
                </div>
            </div>

            {{-- Equity --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-3.5 bg-purple-50/60 border-b border-gray-100 flex items-center gap-2">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21"/></svg>
                    <h3 class="text-[12px] font-semibold text-purple-700 uppercase tracking-wider">Ekuitas (Equity)</h3>
                </div>
                <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <tbody>
                        @forelse ($equityBreakdown as $item)
                            <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition">
                                <td class="px-5 py-2.5 text-gray-700">
                                    <span class="font-mono text-[11px] text-gray-400 mr-2">{{ $item['code'] }}</span>
                                    {{ $item['name'] }}
                                </td>
                                <td class="px-5 py-2.5 text-right font-mono text-[13px] text-gray-700 whitespace-nowrap">
                                    Rp{{ number_format($item['balance'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="px-5 py-4 text-center text-gray-400 text-[12px]">Tidak ada data</td></tr>
                        @endforelse
                        {{-- Net Income Row --}}
                        <tr class="border-b border-gray-50 bg-emerald-50/40">
                            <td class="px-5 py-2.5 text-gray-700 italic">
                                <span class="font-mono text-[11px] text-gray-400 mr-2">&nbsp;</span>
                                Laba Periode Berjalan
                            </td>
                            <td class="px-5 py-2.5 text-right font-mono text-[13px] whitespace-nowrap {{ $netIncome >= 0 ? 'text-emerald-700' : 'text-red-600' }}">
                                Rp{{ number_format($netIncome, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="bg-purple-50/60">
                            <td class="px-5 py-3 font-semibold text-purple-700 text-[13px]">Total Ekuitas + Laba</td>
                            <td class="px-5 py-3 text-right font-mono font-bold text-purple-700">
                                Rp{{ number_format($totalEquityWithRetained, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
                </div>
            </div>

            {{-- Total Liabilities + Equity --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 px-5 py-4 flex items-center justify-between">
                <p class="font-semibold text-gray-800 text-[13px]">Total Kewajiban + Ekuitas</p>
                <p class="font-mono font-bold text-base text-gray-800">
                    Rp{{ number_format($totalLiabilities + $totalEquityWithRetained, 0, ',', '.') }}
                </p>
            </div>
        </div>
    </div>

    {{-- Balance Equation Summary --}}
    <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h4 class="text-[11px] font-semibold text-gray-500 mb-4 uppercase tracking-wider flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0012 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 01-2.031.352 5.988 5.988 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 01-2.031.352 5.989 5.989 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971z"/></svg>
            Persamaan Akuntansi
        </h4>
        <div class="flex flex-wrap items-center justify-center gap-4 text-center">
            <div class="px-4 py-2">
                <p class="text-xl font-bold text-blue-700">Rp{{ number_format($totalAssets, 0, ',', '.') }}</p>
                <p class="text-[11px] text-gray-400 mt-1 uppercase tracking-wider">Aset</p>
            </div>
            <span class="text-xl text-gray-300 font-bold">=</span>
            <div class="px-4 py-2">
                <p class="text-xl font-bold text-red-600">Rp{{ number_format($totalLiabilities, 0, ',', '.') }}</p>
                <p class="text-[11px] text-gray-400 mt-1 uppercase tracking-wider">Kewajiban</p>
            </div>
            <span class="text-xl text-gray-300 font-bold">+</span>
            <div class="px-4 py-2">
                <p class="text-xl font-bold text-purple-700">Rp{{ number_format($totalEquityWithRetained, 0, ',', '.') }}</p>
                <p class="text-[11px] text-gray-400 mt-1 uppercase tracking-wider">Ekuitas + Laba</p>
            </div>
        </div>
    </div>
</div>
@endsection

