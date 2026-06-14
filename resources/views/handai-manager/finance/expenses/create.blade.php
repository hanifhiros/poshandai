@extends('layouts.master')

@section('title', 'Tambah Pengeluaran â€” Handai Finance')

@section('page-style')
<style>
    .fc { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
</style>
@endsection

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6">

    <div class="mb-6">
        <a href="{{ route('manager.finance.expenses.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
            <i class="ti ti-arrow-left text-sm"></i> Kembali ke Pengeluaran
        </a>
        <h1 class="text-2xl font-bold text-gray-800 mt-2">Tambah Pengeluaran</h1>
    </div>

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl">
            <ul class="text-sm text-red-600">
                @foreach ($errors->all() as $error)
                    <li>â€¢ {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="fc p-6 max-w-2xl">
        <form method="POST" action="{{ route('manager.finance.expenses.store') }}">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="text-sm font-medium text-gray-700">Tanggal <span class="text-red-500">*</span></label>
                    <input type="date" name="expense_date" value="{{ old('expense_date', now()->toDateString()) }}" required
                           class="mt-1 block w-full text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Kategori <span class="text-red-500">*</span></label>
                    <select name="expense_category_id" required
                            class="mt-1 block w-full text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                        <option value="">Pilih Kategori</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('expense_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label class="text-sm font-medium text-gray-700">Deskripsi <span class="text-red-500">*</span></label>
                <input type="text" name="description" value="{{ old('description') }}" required placeholder="Contoh: Pembelian bahan baku bulan ini"
                       class="mt-1 block w-full text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="text-sm font-medium text-gray-700">Jumlah (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" name="amount" value="{{ old('amount') }}" required min="1" step="1" placeholder="0"
                           class="mt-1 block w-full text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Metode Pembayaran <span class="text-red-500">*</span></label>
                    <select name="payment_method" required
                            class="mt-1 block w-full text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                        <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Tunai / Kas</option>
                        <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Transfer Bank</option>
                        <option value="e-wallet" {{ old('payment_method') === 'e-wallet' ? 'selected' : '' }}>E-Wallet</option>
                    </select>
                </div>
            </div>

            <div class="mb-6">
                <label class="text-sm font-medium text-gray-700">No. Referensi</label>
                <input type="text" name="reference_number" value="{{ old('reference_number') }}" placeholder="Opsional"
                       class="mt-1 block w-full text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="px-6 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition">
                    <i class="ti ti-check text-base mr-1"></i> Simpan
                </button>
                <a href="{{ route('manager.finance.expenses.index') }}" class="px-4 py-2.5 text-sm text-gray-600 hover:text-gray-800">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection

