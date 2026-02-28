@extends('handai-manager.layouts.master')

@section('title', 'Invoice History')

@section('content')
    <div class="container mx-auto px-4 sm:px-8">
        <div class="py-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-10">Invoice History</h2>
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between mb-6">

                <form method="GET" action="{{ route('manager.finance.invoices.index') }}"
                    class="flex flex-wrap gap-2 items-end">
                    <div class="flex flex-col">
                        <label class="text-sm text-gray-600 mb-1">Tanggal Mulai</label>
                        <input type="date" name="start" value="{{ request('start') }}"
                            class="input input-bordered text-sm" />
                    </div>
                    <div class="flex flex-col">
                        <label class="text-sm text-gray-600 mb-1">Tanggal Selesai</label>
                        <input type="date" name="end" value="{{ request('end') }}" class="input input-bordered text-sm" />
                    </div>
                    <div class="flex flex-col">
                        <label class="text-sm text-gray-600 mb-1">Cari Order ID</label>
                        <input type="text" name="search" placeholder="Search Order ID..." value="{{ request('search') }}"
                            class="input input-bordered text-sm" />
                    </div>
                    <div class="pt-5">
                        <button class="btn btn-sm btn-primary">Filter</button>
                    </div>
                </form>
            </div>

            @if (session('success'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded shadow-sm text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="overflow-x-auto bg-white rounded-lg shadow-md">
                <table class="min-w-full text-sm text-left">
                    <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-3">Order ID</th>
                            <th class="px-4 py-3">Customer</th>
                            <th class="px-4 py-3">Total Paid</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Purchase Date</th>
                            <th class="px-4 py-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-3 font-semibold text-gray-900">#{{ $order->id }}</td>
                                <td class="px-4 py-3">{{ $order->customer->name ?? '-' }}</td>
                                <td class="px-4 py-3">Rp{{ number_format($order->gross_amount, 0, ',', '.') }}</td>
                                <td class="px-4 py-3">
                                    <span
                                        class="px-2 py-1 rounded-full text-xs font-semibold {{ $order->is_ra === 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $order->is_ra === 0 ? 'Lunas' : 'Belum Lunas' }}
                                    </span>

                                    @if ($order->pdf_url)
                                        <br>
                                        <a href="{{ $order->pdf_url }}" target="_blank"
                                            class="text-xs text-blue-500 hover:underline">Lihat Bukti</a>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-gray-600">
                                    {{ \Carbon\Carbon::parse($order->created_at)->translatedFormat('d M Y') }}
                                </td>
                                <td class="px-4 py-3 space-x-2">
                                    <a href="{{ route('manager.finance.invoices.show', $order->id) }}"
                                        class="text-blue-600 hover:underline text-sm">Lihat Invoice</a>
                                    <form action="{{ route('manager.finance.invoices.destroy', $order->id) }}" method="POST"
                                        class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button onclick="return confirm('Yakin ingin menghapus invoice ini?')"
                                            class="text-red-600 hover:underline text-sm">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-6 text-gray-500">Tidak ada data invoice.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex justify-end">
                {{ $orders->appends(request()->query())->links('vendor.pagination.custom-tailwind') }}
            </div>

        </div>
    </div>
@endsection