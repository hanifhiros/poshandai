@extends('handai-manager.layouts.master')

@section('title', 'Stock Batches')

@section('content')
<div class="bg-white p-6 rounded shadow">
  
  <!-- Filter Section -->
    <form method="GET" action="{{ route('manager.finance.stock-batch-log.index') }}" class="mb-6 flex flex-col md:flex-row md:items-center gap-4">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama Stock..." 
            class="border px-4 py-2 rounded w-full md:w-1/3">

        <select name="sort_date" class="border px-4 py-2 rounded w-full md:w-1/4">
            <option value="desc" {{ request('sort_date') == 'desc' ? 'selected' : '' }}>Terbaru</option>
            <option value="asc" {{ request('sort_date') == 'asc' ? 'selected' : '' }}>Terlama</option>
        </select>

        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">
            Filter
        </button>
    </form>

  <div class="overflow-x-auto">
    <table class="min-w-full text-sm text-left border-t">
      <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
        <tr>
          <th class="py-2 px-3">Stock</th>
          <th class="py-2 px-3">Quantity</th>
          <th class="py-2 px-3">Unit</th>
          <th class="py-2 px-3">Cost</th>
          <th class="py-2 px-3">Buy Date</th>
          <th class="py-2 px-3">Nota</th>
          <th class="py-2 px-3">Stored?</th>
        </tr>
      </thead>
      <tbody class="text-gray-700">
        @foreach ($stockBatches as $batch)
        @if ($batch->store_id == session('selected_store'))
        <tr class="border-b hover:bg-gray-50 transition">
          <td class="py-2 px-3 font-medium">{{ $batch->stock->name ?? $batch->stock_name }}</td>
          <td class="py-2 px-3">{{ number_format($batch->unit_qty) }}</td>
          <td class="py-2 px-3">{{ $batch->unit->symbol ?? '-' }}</td>
          <td class="py-2 px-3 font-semibold text-green-600">Rp{{ number_format($batch->cost, 0, ',', '.') }}</td>
          <td class="py-2 px-3">{{ \Carbon\Carbon::parse($batch->buy_date)->format('d M Y') }}</td>
          <td class="py-2 px-3">
            @if($batch->nota_url && $batch->nota_url !== 'belum ada gambar')
              <a href="{{ asset('storage/assets/nota/' . $batch->nota_url) }}" target="_blank" class="text-blue-600 hover:underline">Lihat</a>
            @else
              <span class="text-gray-400 italic">Tidak tersedia</span>
            @endif
          </td>
          <td class="py-2 px-3">
            <span class="inline-block px-2 py-1 rounded-full text-xs font-semibold {{ $batch->isStored === 'Yes' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
              {{ $batch->isStored }}
            </span>
          </td>
        </tr>
        @endif
        @endforeach
      </tbody>
    </table>
  </div>

  <div class="mt-4 flex flex-col md:flex-row md:justify-between md:items-center text-sm text-gray-600">
    <div class="mb-2 md:mb-0">
      Displaying {{ $stockBatches->firstItem() }} to {{ $stockBatches->lastItem() }} of {{ $stockBatches->total() }} records
    </div>
    <div>
      {{ $stockBatches->links('vendor.pagination.custom-tailwind') }}
    </div>
  </div>
</div>
@endsection
