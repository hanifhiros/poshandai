@extends('handai-manager.layouts.master')

@section('title', 'Tambah Batch Stok')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Tambah Batch untuk: {{ $stock->name }}</h1>

    <form method="POST" action="{{ route('manager.inventory.stock.batch.store', $stock->id) }}" enctype="multipart/form-data" class="bg-white p-6 rounded shadow-md">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold mb-1">Jumlah Unit</label>
                <input type="number" name="unit_qty" class="w-full border rounded p-2" required>
            </div>
            <div>
                <label class="block font-semibold mb-1">Satuan</label>
                <select name="unit_id" class="w-full border rounded p-2" required>
                    @foreach($units as $unit)
                        @if ($unit->unit_type === $stock->unit->unit_type)
                            <option value="{{ $unit->id }}" {{ $unit->id == $stock->unit_id ? 'selected' : '' }}>
                                {{ $unit->name }} ({{ $unit->symbol }})
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block font-semibold mb-1">Total Cost (Rp)</label>
                <input type="number" name="cost" step="0.01" class="w-full border rounded p-2" required>
            </div>
            <div>
                <label class="block font-semibold mb-1">Tanggal Beli</label>
                <input value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" type="date" name="buy_date" class="w-full border rounded p-2" required>
            </div>
            <div class="md:col-span-2">
                <label class="block font-semibold mb-1">Upload Nota (Optional)</label>
                <input type="file" name="nota" accept="image/*" class="w-full border rounded p-2">
            </div>
        </div>

        <div class="mt-6">
            <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">Simpan Batch</button>
        </div>
    </form>
</div>
@endsection