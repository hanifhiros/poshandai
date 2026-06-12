@extends('layouts.layoutBlank')

@section('title', 'Tambah Toko')

@section('content')
<div class="flex justify-center items-center min-h-screen bg-[#F3F4F6] px-4">
    <div class="w-full max-w-xl bg-white p-8 rounded-2xl shadow-lg">
        <div class="mb-6 text-center">
            <img src="{{ asset('assets/logo.png') }}" alt="Logo Handai" class="h-12 mx-auto mb-2">
            <h1 class="text-3xl font-bold text-[#199F3E]">Tambah Toko Baru</h1>
            <p class="text-gray-500 text-sm mt-1">Isi informasi toko untuk ditambahkan ke sistem.</p>
        </div>

        <form action="{{ route('superadmin.stores.store') }}" method="POST" class="space-y-5">
            @csrf

            @if($errors->any())
                <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-lg text-sm">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Toko</label>
                <input type="text" name="name" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#199F3E]" required>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Alamat</label>
                <textarea name="address" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#199F3E]" placeholder="Contoh: Jl. Raya Handai No. 21"></textarea>
            </div>

            <div class="flex justify-between items-center pt-4">
                <a href="{{ route('superadmin.store.index') }}" class="text-sm text-gray-600 hover:underline hover:text-gray-800">
                    ← Kembali ke Daftar Toko
                </a>

                <button type="submit" class="inline-flex items-center px-6 py-2 bg-[#199F3E] text-white rounded-lg shadow hover:bg-[#147C30] transition-all">
                    <i class="ti ti-check mr-2"></i> Simpan Toko
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
