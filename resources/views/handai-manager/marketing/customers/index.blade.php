@extends('layouts.master')

@section('title', 'Database Customer')

@push('styles')
<style>
    .cust-input { height: 36px; border-radius: 8px; border: 1px solid #e2e8f0; background: #f8fafc; padding: 0 12px; font-size: 13px; color: #334155; transition: all .15s ease; }
    .cust-input:focus { outline: none; border-color: #059669; box-shadow: 0 0 0 3px rgba(5,150,105,.1); background: #fff; }
</style>
@endpush

@section('content')
<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[1360px] mx-auto">

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-[13px] flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-[13px] flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
        <div>
            <h1 class="text-lg font-semibold text-gray-800">Database Customer</h1>
            <p class="text-[12px] text-gray-400 mt-0.5">Kelola data pelanggan</p>
        </div>
        <div class="flex items-center gap-2">
            @include('handai-manager.partials.import-export-modal', ['type' => 'customer', 'label' => 'Customer'])
            <a href="{{ route('manager.marketing.customers.create') }}" class="h-9 px-4 inline-flex items-center gap-1.5 text-[13px] font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Tambah Customer
            </a>
        </div>
    </div>

    {{-- Search --}}
    <form method="GET" action="{{ route('manager.marketing.customers.index') }}" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-5">
        <div class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1 block">Cari</label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.35-4.35"/></svg>
                    <input type="text" name="search" placeholder="Nama atau email..." value="{{ request('search') }}" class="cust-input w-full pl-9">
                </div>
            </div>
            <button type="submit" class="h-9 px-5 text-[13px] font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition shadow-sm">Cari</button>
            @if(request('search'))
                <a href="{{ route('manager.marketing.customers.index') }}" class="h-9 px-4 inline-flex items-center text-[13px] font-medium text-gray-500 bg-gray-100 hover:bg-gray-200 rounded-lg transition">Reset</a>
            @endif
        </div>
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="px-5 py-3 text-[11px] font-medium text-gray-400 uppercase tracking-wider">Nama</th>
                        <th class="px-5 py-3 text-[11px] font-medium text-gray-400 uppercase tracking-wider hidden sm:table-cell">Gender</th>
                        <th class="px-5 py-3 text-[11px] font-medium text-gray-400 uppercase tracking-wider text-right hidden md:table-cell">Qty Order</th>
                        <th class="px-5 py-3 text-[11px] font-medium text-gray-400 uppercase tracking-wider text-center">Pernah Order</th>
                        <th class="px-5 py-3 text-[11px] font-medium text-gray-400 uppercase tracking-wider hidden lg:table-cell">Kontak</th>
                        <th class="px-5 py-3 text-[11px] font-medium text-gray-400 uppercase tracking-wider hidden xl:table-cell">Email</th>
                        <th class="px-5 py-3 text-[11px] font-medium text-gray-400 uppercase tracking-wider text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                        <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition">
                            <td class="px-5 py-3">
                                <span class="text-[13px] font-medium text-gray-800">{{ $customer->name }}</span>
                                <p class="text-[11px] text-gray-400 sm:hidden">{{ $customer->gender }}</p>
                            </td>
                            <td class="px-5 py-3 text-[13px] text-gray-500 hidden sm:table-cell">{{ $customer->gender }}</td>
                            <td class="px-5 py-3 text-right font-mono text-[13px] text-gray-700 hidden md:table-cell">{{ number_format($customer->qty_ordered ?? 0) }}</td>
                            <td class="px-5 py-3 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium {{ $customer->has_ordered ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $customer->has_ordered ? 'Ya' : 'Belum' }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-[13px] text-gray-500 hidden lg:table-cell">{{ $customer->contact_number }}</td>
                            <td class="px-5 py-3 text-[13px] text-gray-500 hidden xl:table-cell">{{ $customer->email }}</td>
                            <td class="px-5 py-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('manager.marketing.customers.edit', $customer->id) }}" class="h-7 px-2.5 inline-flex items-center gap-1 text-[11px] font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-md transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                                        Edit
                                    </a>
                                    <form action="{{ route('manager.marketing.customers.destroy', $customer->id) }}" method="POST" onsubmit="return confirmDelete(this)" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="h-7 px-2.5 inline-flex items-center gap-1 text-[11px] font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-md transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-10">
                                <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                                <p class="text-[13px] text-gray-400">Tidak ada data customer.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($customers->hasPages())
            <div class="px-5 py-3 border-t border-gray-100 flex items-center justify-between">
                <span class="text-[12px] text-gray-400">Menampilkan {{ $customers->firstItem() }}â€“{{ $customers->lastItem() }} dari {{ $customers->total() }}</span>
                <div class="flex items-center gap-1">
                    @if ($customers->onFirstPage())
                        <span class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-300"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg></span>
                    @else
                        <a href="{{ $customers->appends(request()->query())->previousPageUrl() }}" class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg></a>
                    @endif
                    @foreach ($customers->appends(request()->query())->getUrlRange(1, $customers->lastPage()) as $page => $url)
                        <a href="{{ $url }}" class="w-8 h-8 flex items-center justify-center rounded-lg text-[13px] font-medium transition {{ $page == $customers->currentPage() ? 'bg-emerald-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}">{{ $page }}</a>
                    @endforeach
                    @if ($customers->hasMorePages())
                        <a href="{{ $customers->appends(request()->query())->nextPageUrl() }}" class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg></a>
                    @else
                        <span class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-300"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg></span>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    function confirmDelete(form) {
        const confirmed = confirm('Yakin ingin menghapus customer ini?');
        if (confirmed) {
            window.dispatchEvent(new Event('loading-start'));
        } else {
            setTimeout(() => {
                window.dispatchEvent(new Event('loading-end'));
            }, 500);
        }
        return confirmed;
    }
</script>
@endpush

