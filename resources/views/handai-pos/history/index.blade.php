@extends('layouts.layoutPos')

@section('title', 'Riwayat Transaksi â€” Handai POS')

@section('page-style')
@vite('resources/css/handai-pos-history.css')
@endsection

@section('header')
<div class="h-[52px] bg-white border-b border-slate-200/80 flex items-center justify-between px-5 shrink-0">
    <div class="flex items-center gap-3">
        <a href="{{ route('pos.dashboard') }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-[#0C9044] hover:bg-green-50 transition cursor-pointer">
            <i class="ti ti-arrow-left text-lg"></i>
        </a>
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-lg bg-green-100 flex items-center justify-center">
                <i class="ti ti-history text-[#0C9044] text-sm"></i>
            </div>
            <h1 class="text-sm font-bold text-slate-800">Riwayat Transaksi</h1>
        </div>
    </div>
    <div class="hidden md:flex items-center gap-2" x-data="clock()" x-init="start()">
        <i class="ti ti-clock text-slate-400 text-base"></i>
        <span class="text-xs font-medium text-slate-500" x-text="time"></span>
    </div>
</div>
@endsection

@section('content')
<div class="flex-1 overflow-y-auto pos-scroll p-5" x-data="historyPage()">

    {{-- Summary Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
        <div class="stat-card bg-white rounded-xl border border-slate-200/80 p-4">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                    <i class="ti ti-receipt text-blue-600 text-sm"></i>
                </div>
                <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Total Order</span>
            </div>
            <p class="text-2xl font-extrabold text-slate-800">{{ $totalOrders }}</p>
        </div>
        <div class="stat-card bg-white rounded-xl border border-slate-200/80 p-4">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                    <i class="ti ti-circle-check text-emerald-600 text-sm"></i>
                </div>
                <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Selesai</span>
            </div>
            <p class="text-2xl font-extrabold text-emerald-700">{{ $completedOrders }}</p>
        </div>
        <div class="stat-card bg-white rounded-xl border border-slate-200/80 p-4">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center">
                    <i class="ti ti-circle-x text-red-500 text-sm"></i>
                </div>
                <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Dibatalkan</span>
            </div>
            <p class="text-2xl font-extrabold text-red-600">{{ $cancelledOrders }}</p>
        </div>
        <div class="stat-card bg-white rounded-xl border border-slate-200/80 p-4">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center">
                    <i class="ti ti-cash text-[#0C9044] text-sm"></i>
                </div>
                <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Pendapatan</span>
            </div>
            <p class="text-lg font-extrabold text-[#0C9044]">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('pos.history') }}" class="bg-white rounded-xl border border-slate-200/80 p-4 mb-5">
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2">
                <i class="ti ti-calendar text-slate-400"></i>
                <input type="date" name="date" value="{{ $dateFilter }}"
                       class="h-10 rounded-lg border border-slate-200 bg-slate-50/50 px-3 text-sm font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#0C9044]/20 focus:border-[#0C9044]/50" />
            </div>
            <select name="status"
                    class="h-10 rounded-lg border border-slate-200 bg-slate-50/50 px-3 text-sm font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#0C9044]/20 focus:border-[#0C9044]/50">
                <option value="">Semua Status</option>
                <option value="terkirim" {{ $statusFilter === 'terkirim' ? 'selected' : '' }}>Selesai</option>
                <option value="belum terkirim" {{ $statusFilter === 'belum terkirim' ? 'selected' : '' }}>Pending</option>
                <option value="dibatalkan" {{ $statusFilter === 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
            </select>
            <div class="flex-1 min-w-[180px] relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="ti ti-search text-slate-400 text-sm"></i>
                </div>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari ID atau nama customer..."
                       class="w-full h-10 pl-9 pr-3 rounded-lg border border-slate-200 bg-slate-50/50 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#0C9044]/20 focus:border-[#0C9044]/50" />
            </div>
            <button type="submit" class="h-10 px-5 rounded-lg bg-[#0C9044] hover:bg-green-700 text-white text-sm font-semibold transition cursor-pointer flex items-center gap-1.5">
                <i class="ti ti-filter text-sm"></i> Filter
            </button>
        </div>
    </form>

    {{-- Orders List --}}
    <div class="space-y-2.5">
        @forelse($orders as $order)
        <div class="history-card bg-white rounded-xl border border-slate-200/80 p-4 cursor-pointer"
             @click="toggleDetail({{ $order->id }})" x-data="{ expanded: false }">

            {{-- Main Row --}}
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4 min-w-0">
                    <div class="shrink-0">
                        <span class="text-xs font-bold text-slate-500">#{{ $order->id }}</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-slate-800 truncate">{{ $order->customer->name ?? 'Walk-in' }}</p>
                        <p class="text-[11px] text-slate-400">{{ \Carbon\Carbon::parse($order->created_at)->format('H:i') }} Â· {{ ucfirst($order->payment_type ?? 'Cash') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    <span class="text-sm font-bold text-slate-800">Rp {{ number_format($order->gross_amount, 0, ',', '.') }}</span>
                    @php
                        $statusClass = match($order->order_status) {
                            'terkirim' => 'status-terkirim',
                            'dibatalkan' => 'status-dibatalkan',
                            default => 'status-belum'
                        };
                        $statusLabel = match($order->order_status) {
                            'terkirim' => 'Selesai',
                            'dibatalkan' => 'Batal',
                            default => 'Pending'
                        };
                    @endphp
                    <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold {{ $statusClass }}">{{ $statusLabel }}</span>
                    <button @click.stop="expanded = !expanded" class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                        <i class="ti text-sm transition-transform duration-200" :class="expanded ? 'ti-chevron-up' : 'ti-chevron-down'"></i>
                    </button>
                </div>
            </div>

            {{-- Expanded Detail --}}
            <div x-show="expanded" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                 class="mt-3 pt-3 border-t border-slate-100">
                <div class="space-y-1.5">
                    @foreach($order->invoices as $item)
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="text-slate-600 truncate">
                                {{ $item->product->name ?? $item->product_name ?? '-' }}
                                @if($item->variant && optional($item->variant->options)->count())
                                    <span class="text-xs text-slate-400">
                                        ({{ $item->variant->options->map(fn($o) => $o->name)->join(', ') }})
                                    </span>
                                @elseif($item->variant_name)
                                    <span class="text-xs text-slate-400">({{ $item->variant_name }})</span>
                                @endif
                            </span>
                            <span class="text-slate-400 shrink-0">x{{ $item->quantity_bought }}</span>
                        </div>
                        <span class="font-medium text-slate-700 shrink-0 ml-3">
                            Rp {{ number_format(($item->price ?? 0) * $item->quantity_bought, 0, ',', '.') }}
                        </span>
                    </div>
                    @endforeach
                </div>
                <div class="flex items-center justify-between mt-3 pt-2 border-t border-dashed border-slate-100">
                    <a href="{{ route('pos.invoice.print', $order->id) }}" target="_blank"
                       class="text-xs text-[#0C9044] hover:text-green-700 font-semibold flex items-center gap-1 transition"
                       @click.stop>
                        <i class="ti ti-printer text-sm"></i> Cetak Struk
                    </a>
                    <span class="text-sm font-bold text-slate-800">Total: Rp {{ number_format($order->gross_amount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
        @empty
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mb-3">
                <i class="ti ti-receipt-off text-2xl text-slate-300"></i>
            </div>
            <p class="text-sm text-slate-400 font-medium">Tidak ada transaksi ditemukan</p>
            <p class="text-xs text-slate-300 mt-1">Coba ubah filter tanggal atau status</p>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($orders->hasPages())
    <div class="mt-5 flex justify-center">
        {{ $orders->withQueryString()->links('vendor.pagination.custom-tailwind') }}
    </div>
    @endif
</div>
@endsection

@section('page-script')
<script>
    function clock() {
        return {
            time: '',
            start() {
                this.tick();
                setInterval(() => this.tick(), 1000);
            },
            tick() {
                const now = new Date();
                this.time = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            }
        };
    }
    function historyPage() {
        return {};
    }
</script>
@endsection

