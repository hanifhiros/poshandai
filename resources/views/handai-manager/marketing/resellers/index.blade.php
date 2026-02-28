@extends('handai-manager.layouts.master')

@section('title', 'Reseller Database')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Reseller Database</h1>

        {{-- Search & Add --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <form method="GET" action="{{ route('manager.marketing.resellers.index') }}" class="w-full sm:w-2/3 md:w-1/2">
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Cari nama/kode..." 
                    value="{{ request('search') }}" 
                    class="input input-bordered w-full"
                />
            </form>
            <a href="{{ route('manager.marketing.resellers.create') }}" class="btn btn-primary whitespace-nowrap">               
                + Tambah Reseller
            </a>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto bg-white rounded-lg shadow-md">
            <table class="min-w-full table-auto text-sm text-left">
                <thead class="bg-gray-100 text-gray-700 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">Kode</th>
                        <th class="px-4 py-3">Reseller Form</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Toko Aktif</th>
                        <th class="px-4 py-3">Total Terjual</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($resellers as $reseller)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 font-semibold text-gray-900">#{{ $reseller->id }}</td>
                            <td class="px-4 py-2">{{ $reseller->name }}</td>
                            <td class="px-4 py-2">{{ $reseller->code }}</td>
                            <td class="px-4 py-2">
                                <div x-data="{ copied: false }" class="flex items-center gap-2 text-xs">
                                    <!-- Link Form -->
                                    <a href="{{ url('/customer-order/login') }}?reseller={{ $reseller->code }}&store_id={{ session('selected_store') ?? session('selected_store_') }}"
                                       target="_blank" rel="noopener noreferrer" 
                                       class="text-blue-600 hover:underline font-semibold">
                                        Form
                                    </a>
                            
                                    <!-- Tombol Copy -->
                                    <button 
                                        @click="navigator.clipboard.writeText('{{ url('/customer-order/login?reseller=' . $reseller->code) }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                        class="text-gray-500 hover:text-gray-700 px-2 py-1 border border-gray-300 rounded transition"
                                        type="button">
                                        Copy Link
                                    </button>
                            
                                    <!-- Notifikasi Copied -->
                                    <span x-show="copied" x-transition class="text-green-600 text-xs font-medium">
                                        Link berhasil disalin ✅
                                    </span>
                                </div>
                            </td>
                            
                            
                            <td class="px-4 py-2">
                                <span class="inline-block px-2 py-1 rounded-full text-xs font-semibold {{ $reseller->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ ucfirst($reseller->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-2">{{ $reseller->resellerStores->count() }}</td>
                            <td class="px-4 py-2">
                                {{ number_format($reseller->resellerStores->sum('qty_sold')) }}
                            </td>
                            <td class="px-4 py-2 text-center">
                                <div class="flex justify-center gap-2 flex-wrap">
                                    <a href="{{ route('manager.marketing.resellers.edit', $reseller->id) }}"
                                       class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 text-xs font-semibold rounded hover:bg-blue-200 transition">
                                        <i class="ti ti-edit mr-1"></i>Edit
                                    </a>
                                    <form action="{{ route('manager.marketing.resellers.destroy', $reseller->id) }}"
                                          method="POST"
                                          onsubmit="return confirmDelete(this)"
                                          class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 text-xs font-semibold rounded hover:bg-red-200 transition">
                                            <i class="ti ti-trash mr-1"></i>Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-6 text-gray-500">
                                Tidak ada data reseller.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-4 flex justify-end">
            {{ $resellers->appends(request()->query())->links('vendor.pagination.custom-tailwind') }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function confirmDelete(form) {
        const confirmed = confirm('Yakin ingin menghapus reseller ini?');
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
