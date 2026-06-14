@extends('layouts.master')

@section('title', 'Pengeluaran â€” Handai Finance')

@section('page-style')
<style>
    .fc { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
</style>
@endsection

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6">

    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Pengeluaran</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $store->name }} â€” Kelola semua pengeluaran bisnis</p>
        </div>
        <a href="{{ route('manager.finance.expenses.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition">
            <i class="ti ti-plus text-base"></i> Tambah Pengeluaran
        </a>
    </div>

    {{-- Monthly Summary Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-6">
        <div class="fc p-4">
            <p class="text-[10px] uppercase tracking-wide text-gray-400 font-semibold mb-1">Total Bulan Ini</p>
            <p class="text-lg font-bold text-red-600">Rp{{ number_format($totalThisMonth, 0, ',', '.') }}</p>
        </div>
        @foreach ($monthlySummary->take(4) as $ms)
            <div class="fc p-4">
                <p class="text-[10px] uppercase tracking-wide text-gray-400 font-semibold mb-1">{{ $ms->category->name ?? '-' }}</p>
                <p class="text-lg font-bold text-gray-800">Rp{{ number_format($ms->total, 0, ',', '.') }}</p>
            </div>
        @endforeach
    </div>

    {{-- Filter --}}
    <div class="fc p-4 mb-6">
        <form method="GET" action="{{ route('manager.finance.expenses.index') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="text-xs text-gray-500 font-medium">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="mt-1 block w-full text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
            </div>
            <div>
                <label class="text-xs text-gray-500 font-medium">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="mt-1 block w-full text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
            </div>
            <div>
                <label class="text-xs text-gray-500 font-medium">Kategori</label>
                <select name="category" class="mt-1 block w-full text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                    <option value="">Semua</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs text-gray-500 font-medium">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Deskripsi..."
                       class="mt-1 block w-full text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-lg hover:bg-gray-900 transition">Filter</button>
            <a href="{{ route('manager.finance.expenses.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Reset</a>
        </form>
    </div>

    {{-- Table --}}
    <div class="fc overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500">
                    <tr>
                        <th class="text-left px-5 py-3 font-medium">Tanggal</th>
                        <th class="text-left px-5 py-3 font-medium">Kategori</th>
                        <th class="text-left px-5 py-3 font-medium">Deskripsi</th>
                        <th class="text-left px-5 py-3 font-medium">Metode</th>
                        <th class="text-right px-5 py-3 font-medium">Jumlah</th>
                        <th class="text-center px-5 py-3 font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($expenses as $expense)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-5 py-3 text-gray-600">{{ $expense->expense_date->format('d/m/Y') }}</td>
                        <td class="px-5 py-3">
                            <span class="inline-flex px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-700 rounded-full">
                                {{ $expense->category->name ?? '-' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-gray-700">{{ Str::limit($expense->description, 50) }}</td>
                        <td class="px-5 py-3 text-gray-500 capitalize">{{ str_replace('_', ' ', $expense->payment_method) }}</td>
                        <td class="px-5 py-3 text-right font-semibold text-red-600">Rp{{ number_format($expense->amount, 0, ',', '.') }}</td>
                        <td class="px-5 py-3 text-center">
                            <form action="{{ route('manager.finance.expenses.destroy', $expense->id) }}" method="POST"
                                  onsubmit="return confirm('Hapus pengeluaran ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-600 transition">
                                    <i class="ti ti-trash text-base"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-8 text-center text-gray-400">Belum ada data pengeluaran</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-gray-100">
            {{ $expenses->links() }}
        </div>
    </div>
</div>
@endsection

