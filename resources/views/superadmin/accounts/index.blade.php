@extends('layouts.layoutMaster')

@section('title', 'Manajemen Akun')

@section('content')
<div class="min-h-screen flex items-start justify-center bg-white px-6 py-10">
    <div class="w-full max-w-6xl">
        <h1 class="text-3xl font-bold text-[#0C9044] mb-6 flex items-center gap-2">
          
            Manajemen Akun
        </h1>

        <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
            <a href="{{ route('superadmin.dashboard') }}"
               class="inline-flex items-center px-4 py-2 bg-white text-[#0C9044] font-medium border border-[#0C9044] rounded-md hover:bg-[#0C9044] hover:text-white transition">
                ← Kembali ke Dashboard
            </a>

            <a href="{{ route('superadmin.accounts.create') }}"
               class="px-5 py-2 bg-[#0C9044] text-white font-semibold rounded-md hover:bg-[#0A7D3B] transition">
                + Tambah Akun Baru
            </a>
        </div>

        @if (session('success'))
            <div class="bg-[#ecfdf5] text-[#065f46] p-4 rounded-md mb-4 border border-[#d1fae5]">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="bg-[#fef2f2] text-[#991b1b] p-4 rounded-md mb-4 border border-[#fecaca]">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white shadow-md rounded-xl overflow-x-auto border border-[#E2E8F0]">
            <table class="min-w-full table-auto text-sm text-gray-700">
                <thead class="bg-white text-xs font-semibold uppercase text-[#0C9044] tracking-wider border-b border-gray-200">


                    <tr>
                        <th class="px-6 py-3 text-center">#</th>
                        <th class="px-6 py-3 text-left">Nama</th>
                        <th class="px-6 py-3 text-left">Email</th>
                        <th class="px-6 py-3 text-left">Role</th>
                        <th class="px-6 py-3 text-center">Akses</th>
                        <th class="px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse ($users as $user)
                        <tr class="hover:bg-[#f9fdfb] transition">
                            <td class="px-6 py-4 text-center">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4">{{ $user->name }}</td>
                            <td class="px-6 py-4">{{ $user->email }}</td>
                            <td class="ml-4 px-6 py-4">
                                @php
                                    $grouped = collect($user->roles)
                                        ->groupBy(fn($r) => explode('-', $r->name)[0]); // 'Manager', 'POS', etc.
                                @endphp
                            
                                <ul class="list-disc ml-4 space-y-1">
                                    @foreach($grouped as $feature => $roles)
                                        <li>
                                            <span class="font-bold text-[#0C9044]">{{ $feature }}</span>
                                            @php
                                                $divisions = $roles->filter(fn($r) => count(explode('-', $r->name)) == 2)
                                                    ->map(fn($r) => explode('-', $r->name)[1])
                                                    ->unique();
                            
                                                $subdivisions = $roles->filter(fn($r) => count(explode('-', $r->name)) == 3)
                                                    ->groupBy(fn($r) => explode('-', $r->name)[1]);
                                            @endphp
                            
                                            @if ($divisions->count() > 0 || $subdivisions->count() > 0)
                                                <ul class="list-disc ml-5 mt-1 space-y-1 text-gray-800">
                                                    {{-- Divisi --}}
                                                    @foreach($divisions as $div)
                                                        <li>
                                                            <span class="font-medium">{{ $div }}</span>
                                                            {{-- Subdivisi --}}
                                                            @if ($subdivisions->has($div))
                                                                <ul class="list-disc ml-6 text-sm text-gray-600 space-y-0.5">
                                                                    @foreach($subdivisions[$div]->map(fn($r) => explode('-', $r->name)[2])->unique() as $sub)
                                                                        <li>→ {{ $sub }}</li>
                                                                    @endforeach
                                                                </ul>
                                                            @endif
                                                        </li>
                                                    @endforeach
                            
                                                    {{-- Jika hanya ada subdivisi tanpa divisi --}}
                                                    @foreach($subdivisions as $div => $subs)
                                                        @if (!$divisions->contains($div))
                                                            <li>
                                                                <span class="font-medium">{{ $div }}</span>
                                                                <ul class="list-disc ml-6 text-sm text-gray-600 space-y-0.5">
                                                                    @foreach($subs->map(fn($r) => explode('-', $r->name)[2])->unique() as $sub)
                                                                        <li>→ {{ $sub }}</li>
                                                                    @endforeach
                                                                </ul>
                                                            </li>
                                                        @endif
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                            <td>
                                <!-- Role Hierarki (seperti sebelumnya) -->


<!-- Tambahkan daftar akses toko -->
@if ($user->roles->first()?->pivot)
    <div class=" mt-3">
       
        <ul class="list-disc ml-4 text-sm text-gray-800 space-y-1">
            @foreach($user->roles->groupBy('pivot.store_id') as $storeId => $rolesPerStore)
                <li class="mb-5">
                    @if ($storeId)
                        {{ \App\Models\Store::find($storeId)?->store_name ?? 'Toko tidak ditemukan' }}
                    @else
                        <span class="italic text-gray-600">Semua Toko (MultiStore)</span>
                    @endif
                    
                </li>
            @endforeach
        </ul>
    </div>
@endif

                            </td>
                            <td class="px-6 py-4 text-center space-x-3">
                                <a href="{{ route('superadmin.accounts.edit', ['id' => $user->id]) }}" class="text-[#0C9044] hover:underline font-medium">Edit</a>


                                <form action="{{ route('superadmin.accounts.destroy', $user->id) }}"
                                      method="POST"
                                      class="inline"
                                      onsubmit="return confirm('Yakin ingin menghapus akun ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-red-600 hover:underline font-medium">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-6 text-center text-gray-400">
                                Belum ada akun yang dibuat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex justify-end mt-6">
            {{ $users->links('vendor.pagination.custom-tailwind') }}
        </div>
    </div>
</div>
@endsection
