@extends('handai-manager.layouts.master')

@section('title', 'RnD Log')

@section('content')
<div class="p-6">
  <h1 class="text-3xl font-bold mb-6 text-gray-800">📋 R&D Log</h1>

  <div class="bg-white rounded shadow overflow-hidden">
    <table class="min-w-full text-sm text-gray-700">
      <thead class="bg-gray-200 text-xs uppercase text-gray-600">
        <tr>
          <th class="px-6 py-3 text-left">Tanggal</th>
          <th class="px-6 py-3 text-left">Nama Project</th>
          <th class="px-6 py-3 text-left">PIC</th>
          <th class="px-6 py-3 text-left">Deskripsi</th>
          <th class="px-6 py-3 text-left">Total Biaya</th>

        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200 bg-white">
        @forelse ($rndHistories as $project)
        <tr class="hover:bg-gray-50 transition">
          <td class="px-6 py-4">{{ \Carbon\Carbon::parse($project->rnd_date)->format('d/m/Y') }}</td>
          <td class="px-6 py-4 font-medium text-gray-900">{{ $project->rnd_name }}</td>
          <td class="px-6 py-4">{{ $project->pic->name ?? '-' }}</td>
          <td class="px-6 py-4">{{ $project->Deskripsi }}</td>
          <td class="px-6 py-4 text-green-700 font-semibold">
            Rp{{ number_format($project->stockUsages->sum('cost'), 0, ',', '.') }}
          </td>
          {{-- <td class="px-6 py-4">
            @php
              $nota = $project->stockUsages->firstWhere('nota_url', '!=', 'belum ada gambar')?->nota_url;
            @endphp
            {{-- @if($nota)
              <a href="{{ asset('storage/assets/nota/' . $nota) }}" target="_blank" class="text-blue-600 hover:underline">Lihat</a>
            @else
              <span class="text-gray-400 italic">Tidak ada</span>
            @endif --}}
          </td> 
        </tr>
        @empty
        <tr>
          <td colspan="6" class="text-center py-6 text-gray-500 italic">Tidak ada data R&D ditemukan.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-6">
    {{ $rndHistories->links('vendor.pagination.custom-tailwind') }}
  </div>
</div>
@endsection
