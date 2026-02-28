@extends('handai-manager.layouts.master')

@section('title', 'Customer Database')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Customer Database</h1>

        {{-- Search and Add --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <form method="GET" action="{{ route('manager.marketing.customers.index') }}" class="w-full sm:w-2/3 md:w-1/2">
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Cari nama/email..." 
                    value="{{ request('search') }}" 
                    class="input input-bordered w-full"
                />
            </form>
            <a href="{{ route('manager.marketing.customers.create') }}" class="btn btn-primary whitespace-nowrap">
                + Add Customer
            </a>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto bg-white rounded-lg shadow-md">
            <table class="min-w-full table-auto text-sm text-left">
                <thead class="bg-gray-100 text-gray-700 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Gender</th>
                        <th class="px-4 py-3">Qty Ordered</th>
                        <th class="px-4 py-3">Avg Qty</th>
                        <th class="px-4 py-3">Has Ordered</th>
                        <th class="px-4 py-3">Contact</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Address</th>
                        <th class="px-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($customers as $customer)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 font-semibold text-gray-900">#{{ $customer->id }}</td>
                            <td class="px-4 py-2">{{ $customer->name }}</td>
                            <td class="px-4 py-2">{{ $customer->gender }}</td>
                            <td class="px-4 py-2">{{ number_format($customer->qty_ordered ?? 0) }}</td>
                            <td class="px-4 py-2">{{ number_format($customer->qty_ordered_avg ?? 0) }}</td>
                            <td class="px-4 py-2">
                                <span class="inline-block px-2 py-1 rounded-full text-xs font-semibold {{ $customer->has_ordered ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $customer->has_ordered ? 'Yes' : 'No' }}
                                </span>
                            </td>
                            <td class="px-4 py-2">{{ $customer->contact_number }}</td>
                            <td class="px-4 py-2">{{ $customer->email }}</td>
                            <td class="px-4 py-2">{{ $customer->address }}</td>
                     
                                <td class="px-4 py-2 text-center">
                                    <div class="flex justify-center gap-2 flex-wrap">
                                        <a href="{{ route('manager.marketing.customers.edit', $customer->id) }}"
                                           class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 text-xs font-semibold rounded hover:bg-blue-200 transition">
                                            <i class="ti ti-edit mr-1"></i>Edit
                                        </a>
                                        <form action="{{ route('manager.marketing.customers.destroy', $customer->id) }}"
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
                            <td colspan="10" class="text-center py-6 text-gray-500">
                                Tidak ada data customer.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-4 flex justify-end">
            {{ $customers->appends(request()->query())->links('vendor.pagination.custom-tailwind') }}
        </div>
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
