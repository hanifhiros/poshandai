@extends('layouts.layoutBlank')

@section('title', 'Edit Data Toko')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center py-10 px-4">
    <div class="bg-white w-full max-w-xl shadow-lg rounded-2xl p-8 border border-gray-100">
        
        <div class="flex items-center justify-center mb-4">
            <img src="{{ asset('assets/logo.png') }}" alt="Logo" class="h-12 drop-shadow-sm">
        </div>
        <h1 class="text-3xl font-extrabold text-center text-green-700 mb-1">Edit Data Toko</h1>
        <p class="text-gray-500 text-center mb-8 text-sm">Perbarui informasi cabang Handai Coffee</p>

        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg mb-6 shadow-sm">
                <ul class="list-disc pl-4 text-sm text-red-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ url('superadmin/stores/'.$store->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-5 mb-8">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Nama Toko / Cabang</label>
                    <input type="text" name="store_name" value="{{ old('store_name', $store->store_name) }}" 
                           class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition-all" required>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Alamat Lengkap</label>
                    <textarea name="store_address" rows="3" 
                              class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition-all resize-none" required>{{ old('store_address', $store->store_address) }}</textarea>
                </div>
            </div>

            <div class="flex items-center justify-between border-t border-gray-100 pt-6">
                <a href="{{ route('superadmin.store.index') }}" 
                   class="px-6 py-3 text-red-600 font-semibold border border-red-200 rounded-full bg-red-50 hover:bg-red-600 hover:text-white transition duration-300 flex items-center">
                    <i class="ti ti-arrow-left mr-2"></i> Batal / Kembali
                </a>
                
                <button type="submit" 
                        class="px-6 py-3 bg-green-600 text-white font-bold rounded-full hover:bg-green-700 hover:shadow-lg transition duration-300 flex items-center">
                    <i class="ti ti-device-floppy mr-2"></i> Perbarui Toko
                </button>
            </div>
        </form>
    </div>
</div>
@endsection