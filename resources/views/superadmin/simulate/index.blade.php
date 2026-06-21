@extends('layouts.layoutBlank')

@section('title', 'Simulasi & Monitoring')

@section('content')
<div class="min-h-screen bg-white py-10 px-4 sm:px-6 lg:px-8">
    
    <div class="max-w-7xl mx-auto w-full">
        
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-green-600 mb-2">Simulasi & Monitoring</h1>
            <p class="text-gray-500 font-medium">Masuk dan pantau sistem operasional di berbagai cabang toko Anda.</p>
        </div>

        <div class="flex justify-between items-center mb-8">
            <a href="{{ route('superadmin.dashboard') }}" 
               class="px-5 py-2.5 border border-green-600 text-green-600 font-semibold rounded-lg hover:bg-green-50 transition duration-200 flex items-center">
                &larr; Kembali ke Dashboard
            </a>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-200 bg-white">
                            <th class="py-4 px-6 text-sm font-extrabold text-green-700 uppercase tracking-widest text-center w-16">#</th>
                            <th class="py-4 px-6 text-sm font-extrabold text-green-700 uppercase tracking-widest">Nama Toko / Cabang</th>
                            <th class="py-4 px-6 text-sm font-extrabold text-green-700 uppercase tracking-widest text-center">Aksi Monitoring</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        
                        @forelse(\App\Models\Store::all() as $index => $store)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="py-4 px-6 text-sm text-gray-600 text-center">{{ $index + 1 }}</td>
                            
                            <td class="py-4 px-6 text-sm font-bold text-gray-800">
                                {{ $store->store_name }}
                                <span class="block text-xs font-normal text-gray-500 mt-0.5"><i class="ti ti-map-pin"></i> {{ $store->store_address ?? 'Lokasi belum diset' }}</span>
                            </td>
                            
                            <td class="py-4 px-6 text-sm font-medium text-center">
                                <div class="flex items-center justify-center space-x-3">
                                    
                                    <form action="{{ url('superadmin/simulate/login') }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="store_id" value="{{ $store->id }}">
                                        <input type="hidden" name="role" value="POS">
                                        <button type="submit" 
                                                class="inline-flex items-center px-4 py-2 bg-green-50 text-green-700 border border-green-200 rounded-lg hover:bg-green-600 hover:text-white transition duration-200 shadow-sm">
                                            <i class="ti ti-device-desktop mr-2"></i> Masuk sbg POS
                                        </button>
                                    </form>

                                    <form action="{{ url('superadmin/simulate/login') }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="store_id" value="{{ $store->id }}">
                                        <input type="hidden" name="role" value="Manager">
                                        <button type="submit" 
                                                class="inline-flex items-center px-4 py-2 bg-blue-50 text-blue-700 border border-blue-200 rounded-lg hover:bg-blue-600 hover:text-white transition duration-200 shadow-sm">
                                            <i class="ti ti-briefcase mr-2"></i> Masuk sbg Manager
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="py-10 text-center text-gray-400 font-medium">Belum ada toko yang didaftarkan untuk disimulasikan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
    </div>
</div>
@endsection