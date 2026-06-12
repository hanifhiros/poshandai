@extends('layouts.layoutBlank')

@section('title', 'Daftar Toko')

@section('content')
<div class="flex justify-center items-center min-h-screen bg-white px-4 py-10">
    <div class="w-full max-w-6xl bg-white p-8 rounded-2xl shadow-md border border-[#E2E8F0]">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-center mb-8 gap-4">
            <h1 class="text-3xl font-bold text-[#0C9044] flex items-center gap-2">
                {{-- <svg class="w-7 h-7 text-[#0C9044]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M3 9.75L12 4.5l9 5.25M4.5 10.5v7.875a.375.375 0 00.375.375H9V15a3 3 0 116 0v3.75h4.125a.375.375 0 00.375-.375V10.5M8.25 21h7.5"/>
                </svg> --}}
                Daftar Toko
            </h1>

            <div class="flex flex-col sm:flex-row gap-2">
                <a href="{{ route('superadmin.dashboard') }}"
                   class="px-4 py-2 border border-[#0C9044] text-[#0C9044] font-medium rounded-md hover:bg-[#0C9044] hover:text-white transition">
                    ← Kembali
                </a>
                <a href="{{ route('superadmin.stores.create') }}"
                   class="px-5 py-2 bg-[#0C9044] text-white font-semibold rounded-md hover:bg-[#0A7D3B] transition">
                    + Tambah Toko
                </a>
            </div>
        </div>

        <!-- Tabel -->
        <div class="overflow-x-auto rounded-xl border border-[#E2E8F0]">
            <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-800">
                <thead class="bg-white text-xs font-semibold uppercase text-[#0C9044] tracking-wider border-b border-gray-200">

                    <tr>
                        <th class="px-6 py-3 text-center">#</th>
                        <th class="px-6 py-3 text-left">Nama Toko</th>
                        <th class="px-6 py-3 text-left">Alamat</th>
                        <th class="px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-[#f3f3f3]">
                    @forelse ($stores as $store)
                        <tr class="hover:bg-[#f9fdfb] transition">
                            <td class="px-6 py-4 text-center">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4 font-medium">{{ $store->store_name }}</td>
                            <td class="px-6 py-4">{{ $store->store_address ?? '-' }}</td>
                            <td class="px-6 py-4 text-center">
                                <div class="inline-flex gap-3 justify-center">
                                    <a href="#" class="text-[#0C9044] hover:underline font-medium transition">Edit</a>
                                    <form action="{{ route('superadmin.stores.destroy', $store->id) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus toko ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline font-medium transition">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-6 text-center text-gray-400">
                                Belum ada data toko yang terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex justify-end mt-6">
            {{ $stores->links('vendor.pagination.custom-tailwind') }}
        </div>
    </div>
</div>
@endsection
