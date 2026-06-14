@extends('layouts.master')

@section('title', 'Isi Stok dari R&D')

@section('content')
<div class="p-6">
  <h1 class="text-2xl font-bold mb-4">Isi Stok dari Project R&D</h1>

  <div class="bg-white p-4 rounded shadow mb-6">
    <h2 class="text-xl font-semibold">{{ $rnd->rnd_name }}</h2>
    <p class="text-gray-600 mb-3">{{ $rnd->deskripsi }}</p>
    <p class="text-sm text-gray-500 mb-1">Tanggal: {{ \Carbon\Carbon::parse($rnd->rnd_date)->format('d/m/Y') }}</p>
  </div>

  <form action="{{ route('manager.inventory.stock.batch.storeFromRnd', $rnd->id) }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="space-y-6">
      @foreach ($rnd->stockUsages as $index => $usage)
      <div class="bg-white p-6 rounded shadow">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
          <div class="md:col-span-3">
            <label class="block text-sm font-medium mb-1">Nama Stok</label>
            @if ($usage->stock)
              <p class="font-semibold">{{ $usage->stock->name }}</p>
              <input type="hidden" name="batches[{{ $index }}][stock_id]" value="{{ $usage->stock->id }}">
            @else
              <input type="text" value="{{ $usage->manual_name }}" name="batches[{{ $index }}][manual_name]" class="w-full border rounded p-2" placeholder="Nama Bahan Baru" required>
            @endif
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Jumlah</label>
            <input type="number" name="batches[{{ $index }}][unit_qty]" value="{{ $usage->quantity_used }}" class="w-full border rounded p-2" required>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Satuan</label>
            <select name="batches[{{ $index }}][unit_id]" class="w-full border rounded p-2" required>
              @foreach ($units as $unit)
                <option value="{{ $unit->id }}" {{ isset($usage->unit_id) && $unit->id == $usage->unit_id ? 'selected' : '' }}>
                  {{ $unit->symbol }}
                </option>
              @endforeach
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Biaya</label>
            <input type="number" name="batches[{{ $index }}][cost]" value="{{ $usage->cost }}" class="w-full border rounded p-2" required>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Tanggal Beli</label>
            <input type="date" name="batches[{{ $index }}][buy_date]" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" class="w-full border rounded p-2" required>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Durasi Expired (hari)</label>
            <input type="number" name="batches[{{ $index }}][expired_duration]" value="30" class="w-full border rounded p-2" required>
          </div>

          <div class="md:col-span-3">
            <label class="block text-sm font-medium mb-1">Upload Nota (optional)</label>
            <input type="file" name="batches[{{ $index }}][nota]" accept="image/*" class="w-full border rounded p-2">
          </div>
        </div>
      </div>
      @endforeach
    </div>

    <div class="mt-6">
      <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
        Simpan Stok
      </button>
    </div>
  </form>
</div>
@endsection

{{-- @extends('layouts.master')

@section('title', 'Isi Stok dari R&D')

@section('content')
<div class="p-6">
  <h1 class="text-2xl font-bold mb-4">Isi Stok dari Project R&D</h1>

  <div class="bg-white p-4 rounded shadow mb-6">
    <h2 class="text-xl font-semibold">{{ $rnd->rnd_name }}</h2>
    <p class="text-gray-600 mb-3">{{ $rnd->deskripsi }}</p>
    <p class="text-sm text-gray-500 mb-1">Tanggal: {{ \Carbon\Carbon::parse($rnd->rnd_date)->format('d/m/Y') }}</p>
  </div>

  <form action="{{ route('manager.inventory.stock.batch.storeFromRnd', $rnd->id) }}" method="POST">
    @csrf

    <div class="space-y-4">
        @foreach ($rnd->stockUsages as $index => $usage)
        <tr>
          <td>
            @if ($usage->stock)
              {{ $usage->stock->name }}
              <input type="hidden" name="batches[{{ $index }}][stock_id]" value="{{ $usage->stock->id }}">
            @else
              <input value="{{ $usage->manual_name }}" type="text" name="batches[{{ $index }}][manual_name]" placeholder="Nama Bahan Baru" class="border p-1 rounded w-full" required>
            @endif
          </td>
          <td>
            <input type="number" name="batches[{{ $index }}][unit_qty]" class="border p-1 rounded w-full" value="{{ $usage->quantity_used }}" required>
          </td>
          <td>
            <select name="batches[{{ $index }}][unit_id]" class="border p-1 rounded w-full" required>
                @foreach($units as $unit)
                  <option value="{{ $unit->id }}" 
                    {{ isset($usage->unit_id) && $unit->id == $usage->unit_id ? 'selected' : '' }}>
                    {{ $unit->symbol }}
                  </option>
                @endforeach
              </select>
              
          </td>
          <td>
            <input value="{{ $usage->cost }}" type="number" name="batches[{{ $index }}][cost]" class="border p-1 rounded w-full" placeholder="Biaya" required>
          </td>
          <td>
            <input value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" type="date" name="batches[{{ $index }}][buy_date]" class="border p-1 rounded w-full" required>
          </td>
          <td>
            <input type="number" name="batches[{{ $index }}][expired_duration]" class="border p-1 rounded w-full" value="30" required>
          </td>
        </tr>
        @endforeach
        
    </div>

    <div class="mt-6">
      <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
        Simpan Stok
      </button>
    </div>
  </form>
</div>
@endsection --}}

