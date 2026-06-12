@extends('handai-manager.layouts.master')

@section('title', 'Permintaan R&D')

@section('content')
<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[1360px] mx-auto">

    {{-- Header --}}
    <div class="mb-5">
        <h1 class="text-lg font-semibold text-gray-800">Permintaan Bahan R&D</h1>
        <p class="text-[12px] text-gray-400 mt-0.5">Persetujuan permintaan bahan untuk riset & pengembangan</p>
    </div>

    {{-- Flash --}}
    @if (session('success'))
        <div class="mb-4 px-4 py-3 bg-emerald-50/60 border border-emerald-200 text-emerald-700 rounded-lg text-[13px]">
            {{ session('success') }}
        </div>
    @endif

    @if ($rndRequests->isEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 py-16 text-center">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5"/></svg>
            <p class="text-[13px] text-gray-400">Tidak ada permintaan R&D saat ini.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach ($rndRequests as $rnd)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
                    {{-- Card Header --}}
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h2 class="text-[15px] font-semibold text-gray-800 break-words">{{ $rnd->rnd_name }}</h2>
                        <p class="text-[12px] text-gray-400 mt-1 break-words">{{ $rnd->deskripsi }}</p>
                    </div>

                    {{-- Materials Table --}}
                    <div class="flex-1 overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-100">
                                    <th class="text-left px-4 py-2 text-[11px] font-medium text-gray-400 uppercase tracking-wider">Bahan</th>
                                    <th class="text-left px-4 py-2 text-[11px] font-medium text-gray-400 uppercase tracking-wider">Jumlah</th>
                                    <th class="text-right px-4 py-2 text-[11px] font-medium text-gray-400 uppercase tracking-wider">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $total = 0; @endphp
                                @foreach ($rnd->stockUsages as $usage)
                                    @php
                                        $price = $usage->cost ?? 0;
                                        $subtotal = $price * $usage->quantity_used;
                                        $total += $subtotal;
                                    @endphp
                                    <tr class="border-b border-gray-50">
                                        <td class="px-4 py-2 text-[13px] text-gray-700 break-words">{{ $usage->stock->name ?? $usage->manual_name }}</td>
                                        <td class="px-4 py-2 text-[13px] text-gray-500">{{ $usage->quantity_used }} {{ $usage->unit->symbol ?? '-' }}</td>
                                        <td class="px-4 py-2 text-right font-mono text-[12px] text-gray-600">Rp{{ \App\Helpers\NumberFormatter::short($subtotal) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Card Footer --}}
                    <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50 flex items-center justify-between">
                        <span class="text-[13px] font-semibold text-emerald-700">Total: Rp{{ number_format($total, 0, ',', '.') }}</span>
                        <div class="flex gap-2">
                            <form method="POST" action="{{ route('manager.finance.rnd-request.approveAll', $rnd->id) }}">
                                @csrf
                                <button class="h-8 px-3.5 text-[12px] font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition shadow-sm">Approve</button>
                            </form>
                            <form method="POST" action="{{ route('manager.finance.rnd-request.rejectAll', $rnd->id) }}">
                                @csrf
                                <button class="h-8 px-3.5 text-[12px] font-medium text-red-600 bg-red-50 hover:bg-red-100 border border-red-200 rounded-lg transition">Reject</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
