@extends('handai-manager.layouts.master')

@section('title', 'Tambah Supplier')

@section('content')
<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-3xl mx-auto">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('manager.operational.suppliers.index') }}"
           class="p-2 rounded-lg hover:bg-gray-100 transition text-gray-500">
            <i class="ti ti-arrow-left text-xl"></i>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">Tambah Supplier</h1>
            <p class="text-sm text-gray-500">Masukkan informasi supplier baru</p>
        </div>
    </div>

    <form action="{{ route('manager.operational.suppliers.store') }}" method="POST"
          class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 space-y-5">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Nama --}}
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Supplier <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full h-10 px-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Contact Person --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Contact Person</label>
                <input type="text" name="contact_person" value="{{ old('contact_person') }}"
                       class="w-full h-10 px-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>

            {{-- Phone --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">No. Telepon</label>
                <input type="text" name="phone" value="{{ old('phone') }}"
                       class="w-full h-10 px-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>

            {{-- Email --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       class="w-full h-10 px-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>

            {{-- City --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kota</label>
                <input type="text" name="city" value="{{ old('city') }}"
                       class="w-full h-10 px-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>

            {{-- Address --}}
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                <textarea name="address" rows="2"
                          class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">{{ old('address') }}</textarea>
            </div>

            {{-- Payment Terms --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Terms Pembayaran</label>
                <select name="payment_terms"
                        class="w-full h-10 px-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="COD">COD (Cash on Delivery)</option>
                    <option value="NET7">NET 7 (7 hari)</option>
                    <option value="NET14">NET 14 (14 hari)</option>
                    <option value="NET30">NET 30 (30 hari)</option>
                </select>
            </div>

            {{-- Bank --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Bank</label>
                <input type="text" name="bank_name" value="{{ old('bank_name') }}" placeholder="Nama bank"
                       class="w-full h-10 px-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>

            {{-- Bank Account --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">No. Rekening</label>
                <input type="text" name="bank_account" value="{{ old('bank_account') }}"
                       class="w-full h-10 px-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>

            {{-- Notes --}}
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                <textarea name="notes" rows="2"
                          class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
            <a href="{{ route('manager.operational.suppliers.index') }}"
               class="px-4 py-2.5 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-100 transition">
                ← Batal
            </a>
            <button type="submit"
                    class="px-6 py-2.5 bg-green-600 text-white rounded-xl text-sm font-medium hover:bg-green-700 transition shadow-sm">
                <i class="ti ti-check"></i> Simpan
            </button>
        </div>
    </form>
</div>
@endsection
