@extends('layouts.master')

@section('title', 'Tambah Hutang â€” Handai Finance')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6">

    <div class="mb-6">
        <a href="{{ route('manager.finance.ap.index') }}" class="text-sm text-green-600 hover:text-green-800 font-medium">
            <i class="ti ti-arrow-left mr-1"></i> Kembali ke Daftar Hutang
        </a>
        <h1 class="text-2xl font-bold text-gray-800 mt-2">Tambah Hutang (AP)</h1>
    </div>

    @if ($errors->any())
        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6 max-w-xl">
        <form method="POST" action="{{ route('manager.finance.ap.store') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Supplier <span class="text-red-500">*</span></label>
                    <select name="supplier_id" required class="mt-1 block w-full text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                        <option value="">â€” Pilih Supplier â€”</option>
                        @foreach ($suppliers as $sup)
                            <option value="{{ $sup->id }}" {{ old('supplier_id') == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Deskripsi <span class="text-red-500">*</span></label>
                    <input type="text" name="description" value="{{ old('description') }}" required
                           class="mt-1 block w-full text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500"
                           placeholder="Pembelian bahan baku, dll.">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Jumlah <span class="text-red-500">*</span></label>
                        <input type="number" name="total_amount" value="{{ old('total_amount') }}" min="1" required
                               class="mt-1 block w-full text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500"
                               placeholder="0">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Jatuh Tempo</label>
                        <input type="date" name="due_date" value="{{ old('due_date') }}"
                               class="mt-1 block w-full text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-6">
                <a href="{{ route('manager.finance.ap.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Batal</a>
                <button type="submit" class="px-5 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition">
                    <i class="ti ti-device-floppy mr-1"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

