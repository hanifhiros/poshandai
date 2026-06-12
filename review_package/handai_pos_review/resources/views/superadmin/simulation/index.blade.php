@extends('layouts.layoutBlank')

@section('title', 'Simulasi Role per Toko')

@section('content')
<div class="flex justify-center items-center min-h-screen bg-white px-4 py-10">
    <div class="w-full max-w-6xl bg-white p-8 rounded-2xl shadow-lg border border-[#E2E8F0]">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-[#0C9044] flex items-center gap-2">
                {{-- <svg class="w-7 h-7 text-[#0C9044]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M4 4h16v4H4z" />
                    <path d="M4 8v12h16V8" />
                    <path d="M9 21V12h6v9" />
                </svg> --}}
                Managemen Toko
            </h1>
            <a href="{{ route('superadmin.dashboard') }}"
               class="px-4 py-2 bg-[#0C9044] text-white rounded-md hover:bg-[#064E3B] transition text-sm font-medium">
                ← Kembali ke Dashboard
            </a>
        </div>

        @forelse ($stores as $store)
            <div class="mb-8 border border-[#E2E8F0] rounded-xl p-6 shadow-md">
                <div class="mb-4">
                    <h2 class="text-xl font-bold text-[#0C9044]">{{ $store->store_name }}</h2>
                    <p class="text-sm text-gray-500">{{ $store->store_address ?? '-' }}</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    @foreach (['manager' => 'Manager', 'pos' => 'POS', 'kasir' => 'Kasir'] as $role => $label)
                    <div class="p-4 bg-white border border-[#E2E8F0] rounded-xl shadow-sm flex flex-col justify-between hover:shadow-md transition">
                        <div>
                            <p class="font-semibold text-[#0C9044] text-lg">{{ $label }}</p>
                            <p class="text-sm text-gray-500">Simulasi sebagai {{ $label }} di toko ini</p>
                        </div>
                        <form method="POST" action="{{ route($role . '.setstore') }}">
                            @csrf
                            <input type="hidden" name="store_id" value="{{ $store->id }}">
                            <button type="submit"
                                class="mt-3 text-sm text-[#0C9044] font-medium hover:text-white hover:bg-[#0C9044] border border-[#0C9044] px-3 py-1 rounded transition">
                                Masuk sebagai {{ $label }}
                            </button>
                        </form>
                    </div>
                    @endforeach
                </div>
            </div>
        @empty
            <p class="text-center text-gray-500">Tidak ada toko yang tersedia.</p>
        @endforelse

        <div class="mt-8 flex justify-center">
            {{ $stores->links('vendor.pagination.custom-tailwind') }}
        </div>
    </div>
</div>
@endsection
