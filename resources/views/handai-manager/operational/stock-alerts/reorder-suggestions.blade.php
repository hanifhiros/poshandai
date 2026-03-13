@extends('handai-manager.layouts.master')

@section('title', 'Saran Reorder')

@section('content')
<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[1360px] mx-auto">

    @if(session('success'))
    <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-[13px] flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                <i class="ti ti-shopping-cart text-blue-500"></i> Saran Reorder
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">Bahan baku yang perlu dibeli ulang berdasarkan reorder point</p>
        </div>
        <a href="{{ route('manager.operational.stock-alerts.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-200 transition">
            <i class="ti ti-arrow-left"></i> Kembali ke Alerts
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs uppercase tracking-wider text-gray-500">
                        <th class="px-4 py-3">Bahan Baku</th>
                        <th class="px-4 py-3 text-right">Stok Saat Ini</th>
                        <th class="px-4 py-3 text-right">Reorder Point</th>
                        <th class="px-4 py-3 text-right">Saran Beli</th>
                        <th class="px-4 py-3">Supplier</th>
                        <th class="px-4 py-3 text-right">Estimasi Biaya</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($suggestions as $s)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $s->stock?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-right text-red-600 font-medium">{{ number_format($s->stock?->unit_qty ?? 0, 1) }}</td>
                        <td class="px-4 py-3 text-right text-gray-500">{{ number_format($s->stock?->reorder_point ?? 0, 1) }}</td>
                        <td class="px-4 py-3 text-right font-bold text-blue-600">{{ number_format($s->suggested_quantity, 1) }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $s->supplier?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">
                            {{ $s->estimated_cost ? 'Rp ' . number_format($s->estimated_cost, 0, ',', '.') : '-' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <form action="{{ route('manager.operational.stock-alerts.dismiss-suggestion', $s->id) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Dismiss saran ini?')">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 bg-gray-100 text-gray-600 rounded-lg text-xs font-medium hover:bg-gray-200 transition">
                                    <i class="ti ti-x"></i> Dismiss
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-gray-400">
                            <i class="ti ti-check-circle text-4xl block mb-2 text-emerald-300"></i>
                            Tidak ada saran reorder saat ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($suggestions->hasPages())
        <div class="p-4 border-t border-gray-100">
            {{ $suggestions->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
