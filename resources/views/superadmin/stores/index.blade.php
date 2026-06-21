@extends('layouts.layoutBlank')

@section('title', 'Daftar Toko')

@section('content')
<div class="min-h-screen bg-white py-10 px-4 sm:px-6 lg:px-8">
    
    <div class="max-w-7xl mx-auto w-full">
        
        <h1 class="text-4xl font-bold text-green-600 mb-8">Daftar Toko</h1>

        <div class="flex justify-between items-center mb-8">
            <a href="{{ route('superadmin.dashboard') }}" 
               class="px-5 py-2.5 border border-green-600 text-green-600 font-semibold rounded-lg hover:bg-green-50 transition duration-200 flex items-center">
                &larr; Kembali ke Dashboard
            </a>
            
            <a href="{{ route('superadmin.stores.create') }}" 
               class="px-5 py-2.5 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 hover:shadow-md transition duration-200 flex items-center">
                + Tambah Toko
            </a>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg shadow-sm font-medium text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg shadow-sm font-medium text-sm">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-200 bg-white">
                            <th class="py-4 px-6 text-sm font-extrabold text-green-700 uppercase tracking-widest text-center w-16">#</th>
                            <th class="py-4 px-6 text-sm font-extrabold text-green-700 uppercase tracking-widest">Nama Toko</th>
                            <th class="py-4 px-6 text-sm font-extrabold text-green-700 uppercase tracking-widest">Alamat</th>
                            <th class="py-4 px-6 text-sm font-extrabold text-green-700 uppercase tracking-widest text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($stores as $index => $store)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="py-4 px-6 text-sm text-gray-600 text-center">{{ $index + 1 }}</td>
                            
                            <td class="py-4 px-6 text-sm font-bold text-gray-800">{{ $store->store_name }}</td>
                            <td class="py-4 px-6 text-sm text-gray-600">{{ $store->store_address ?? '-' }}</td>
                            
                            <td class="py-4 px-6 text-sm font-medium text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <a href="{{ url('superadmin/stores/'.$store->id.'/edit') }}" 
                                    class="inline-flex items-center px-3 py-1.5 bg-yellow-50 text-yellow-600 border border-yellow-200 rounded-lg hover:bg-yellow-600 hover:text-white transition duration-200">
                                        <i class="ti ti-edit mr-1"></i> Edit
                                    </a>
                                    
                                    <form action="{{ route('superadmin.stores.destroy', $store->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" 
                                                onclick="if(confirm('Apakah Anda yakin ingin menghapus toko {{ $store->store_name }}? Tindakan ini tidak bisa dibatalkan.')) { this.closest('form').submit(); }"
                                                class="inline-flex items-center px-3 py-1.5 bg-red-50 text-red-600 border border-red-200 rounded-lg hover:bg-red-600 hover:text-white transition duration-200">
                                            <i class="ti ti-trash mr-1"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-10 text-center text-gray-400 font-medium">Belum ada toko yang didaftarkan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
    </div>
</div>
@endsection