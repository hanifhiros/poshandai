@extends('layouts.master')

@section('title', 'Hutang (AP) â€” Handai Finance')

@section('page-style')
<style>
    .fc { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
</style>
@endsection

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6" x-data="{ payModal: false, payId: null, payMax: 0, payDesc: '' }">

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Accounts Payable (Hutang)</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $store->name }} â€” Kelola hutang ke supplier</p>
        </div>
        <a href="{{ route('manager.finance.ap.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition">
            <i class="ti ti-plus text-base"></i> Tambah Hutang
        </a>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="fc p-4">
            <p class="text-[10px] uppercase tracking-wide text-gray-400 font-semibold mb-1">Total Hutang</p>
            <p class="text-lg font-bold text-gray-800">Rp{{ number_format($summary->total_amount, 0, ',', '.') }}</p>
        </div>
        <div class="fc p-4">
            <p class="text-[10px] uppercase tracking-wide text-gray-400 font-semibold mb-1">Sudah Dibayar</p>
            <p class="text-lg font-bold text-green-600">Rp{{ number_format($summary->total_paid, 0, ',', '.') }}</p>
        </div>
        <div class="fc p-4">
            <p class="text-[10px] uppercase tracking-wide text-gray-400 font-semibold mb-1">Outstanding</p>
            <p class="text-lg font-bold text-orange-600">Rp{{ number_format($summary->total_outstanding, 0, ',', '.') }}</p>
        </div>
        <div class="fc p-4">
            <p class="text-[10px] uppercase tracking-wide text-gray-400 font-semibold mb-1">Jatuh Tempo</p>
            <p class="text-lg font-bold text-red-600">{{ $overdueCount }} hutang</p>
        </div>
    </div>

    {{-- Filter --}}
    <div class="fc p-4 mb-6">
        <form method="GET" action="{{ route('manager.finance.ap.index') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="text-xs text-gray-500 font-medium">Status</label>
                <select name="status" class="mt-1 block w-full text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                    <option value="">Semua</option>
                    <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>Belum Bayar</option>
                    <option value="partially_paid" {{ request('status') === 'partially_paid' ? 'selected' : '' }}>Sebagian</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Lunas</option>
                </select>
            </div>
            <div>
                <label class="text-xs text-gray-500 font-medium">Supplier</label>
                <select name="supplier" class="mt-1 block w-full text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                    <option value="">Semua</option>
                    @foreach ($suppliers as $sup)
                        <option value="{{ $sup->id }}" {{ request('supplier') == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-lg hover:bg-gray-900 transition">Filter</button>
            <a href="{{ route('manager.finance.ap.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Reset</a>
        </form>
    </div>

    @if (session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">{{ session('success') }}</div>
    @endif

    {{-- Table --}}
    <div class="fc overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500">
                    <tr>
                        <th class="text-left px-5 py-3 font-medium">Supplier</th>
                        <th class="text-left px-5 py-3 font-medium">Deskripsi</th>
                        <th class="text-right px-5 py-3 font-medium">Total</th>
                        <th class="text-right px-5 py-3 font-medium">Dibayar</th>
                        <th class="text-right px-5 py-3 font-medium">Sisa</th>
                        <th class="text-center px-5 py-3 font-medium">Jatuh Tempo</th>
                        <th class="text-center px-5 py-3 font-medium">Status</th>
                        <th class="text-center px-5 py-3 font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($payables as $ap)
                    @php
                        $outstanding = $ap->total_amount - $ap->paid_amount;
                        $isOverdue = $ap->status !== 'paid' && $ap->due_date && $ap->due_date->isPast();
                    @endphp
                    <tr class="hover:bg-gray-50/50 {{ $isOverdue ? 'bg-red-50/30' : '' }}">
                        <td class="px-5 py-3 text-gray-700 font-medium">{{ $ap->supplier->name ?? '-' }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ Str::limit($ap->description, 40) }}</td>
                        <td class="px-5 py-3 text-right text-gray-800">Rp{{ number_format($ap->total_amount, 0, ',', '.') }}</td>
                        <td class="px-5 py-3 text-right text-green-600">Rp{{ number_format($ap->paid_amount, 0, ',', '.') }}</td>
                        <td class="px-5 py-3 text-right font-semibold text-orange-600">Rp{{ number_format($outstanding, 0, ',', '.') }}</td>
                        <td class="px-5 py-3 text-center {{ $isOverdue ? 'text-red-600 font-semibold' : 'text-gray-500' }}">
                            {{ $ap->due_date ? $ap->due_date->format('d/m/Y') : '-' }}
                            @if ($isOverdue) <span class="text-xs">(lewat)</span> @endif
                        </td>
                        <td class="px-5 py-3 text-center">
                            @if ($ap->status === 'paid')
                                <span class="inline-flex px-2 py-0.5 text-xs font-medium bg-green-100 text-green-700 rounded-full">Lunas</span>
                            @elseif ($ap->status === 'partially_paid')
                                <span class="inline-flex px-2 py-0.5 text-xs font-medium bg-amber-100 text-amber-700 rounded-full">Sebagian</span>
                            @else
                                <span class="inline-flex px-2 py-0.5 text-xs font-medium bg-red-100 text-red-700 rounded-full">Belum</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-center">
                            @if ($ap->status !== 'paid')
                                <button @click="payModal = true; payId = {{ $ap->id }}; payMax = {{ $outstanding }}; payDesc = '{{ addslashes($ap->supplier->name ?? '') }} - {{ addslashes(Str::limit($ap->description, 30)) }}'"
                                        class="text-green-600 hover:text-green-800 text-xs font-medium">
                                    <i class="ti ti-cash text-base mr-0.5"></i> Bayar
                                </button>
                            @else
                                <span class="text-gray-400 text-xs">âœ“</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-5 py-8 text-center text-gray-400">Tidak ada data hutang</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-gray-100">{{ $payables->links() }}</div>
    </div>

    {{-- Payment Modal --}}
    <div x-show="payModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-sm" style="display:none">
        <div @click.outside="payModal = false" class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-md mx-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-1">Bayar Hutang</h3>
            <p class="text-sm text-gray-500 mb-4" x-text="payDesc"></p>

            <form method="POST" :action="'{{ route('manager.finance.ap.pay', '') }}/' + payId">
                @csrf
                <div class="space-y-3">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Jumlah (maks: <span x-text="'Rp' + payMax.toLocaleString('id-ID')"></span>)</label>
                        <input type="number" name="amount" :max="payMax" min="1" required
                               class="mt-1 block w-full text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Tanggal Bayar</label>
                        <input type="date" name="payment_date" value="{{ now()->toDateString() }}" required
                               class="mt-1 block w-full text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Metode</label>
                        <select name="payment_method" required class="mt-1 block w-full text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                            <option value="cash">Tunai</option>
                            <option value="bank_transfer">Transfer Bank</option>
                            <option value="e-wallet">E-Wallet</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Catatan</label>
                        <input type="text" name="notes" class="mt-1 block w-full text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-5">
                    <button type="button" @click="payModal = false" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition">Bayar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

