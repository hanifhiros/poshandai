@extends('handai-manager.layouts.master')

@section('title', 'R&D Requests')

@section('content')

<div class="p-6">
  <h1 class="text-2xl font-bold mb-4">Permintaan Bahan R&D</h1>

  @if (session('success'))
    <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
      {{ session('success') }}
    </div>
  @endif

  @if ($rndRequests->isEmpty())
    <p class="text-gray-500">Tidak ada permintaan R&D saat ini.</p>
  @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach ($rndRequests as $rnd)
      
        <div class="bg-white p-6 rounded-2xl shadow-md border border-gray-200 max-w-screen-md w-full mx-auto">
          <h2 class="text-2xl font-bold text-gray-800 mb-1 break-words">{{ $rnd->rnd_name }}</h2>
          <p class="text-gray-600 mb-4 break-words">{{ $rnd->deskripsi }}</p>
        
          <div class="overflow-x-auto">
            <table class="w-full text-sm text-gray-700 border border-gray-200 table-auto">
              <thead>
                <tr class="bg-green-100 text-gray-800">
                  <th class="text-left px-2 py-1 w-1/3">Bahan</th>
                  <th class="text-left px-2 py-1 w-1/5">Jumlah</th>
                  <th class="text-left px-2 py-1 w-1/6">Harga</th>
                  <th class="text-left px-2 py-1 w-1/6">Subtotal</th>
                </tr>
              </thead>
              <tbody>
                @php $total = 0; @endphp
                @foreach ($rnd->stockUsages as $usage)
                  @php
                    $price = $usage->cost ?? 0;
                    $subtotal = $price * $usage->quantity_used;
                    $total += $subtotal;
                  @endphp
                  <tr class="border-t border-gray-200">
                    <td class="px-2 py-1 break-words">{{ $usage->stock->name ?? $usage->manual_name }}</td>
                    <td class="px-2 py-1">{{ $usage->quantity_used }} {{ $usage->unit->symbol ?? '-' }}</td>
                    <td class="px-2 py-1">Rp{{ \App\Helpers\NumberFormatter::short($price) }}</td>
                    <td class="px-2 py-1">Rp{{ \App\Helpers\NumberFormatter::short($subtotal) }}</td>                    
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        
          <div class="text-right font-semibold text-green-700 mt-3">
            Total: Rp{{ number_format($total, 0, ',', '.') }}
          </div>
        
          <div class="flex gap-2 justify-end mt-4">
            <form method="POST" action="{{ route('manager.finance.rnd-request.approveAll', $rnd->id) }}">
              @csrf
              <button class="bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded-lg shadow-sm transition">
                Approve
              </button>
            </form>
        
            <form method="POST" action="{{ route('manager.finance.rnd-request.rejectAll', $rnd->id) }}">
              @csrf
              <button class="bg-red-600 hover:bg-red-700 text-white font-semibold px-4 py-2 rounded-lg shadow-sm transition">
                Reject
              </button>
            </form>
          </div>
        </div>
        
      @endforeach
      
    </div>
  @endif
</div>
@endsection
