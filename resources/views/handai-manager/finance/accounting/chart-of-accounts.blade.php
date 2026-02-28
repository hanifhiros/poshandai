@extends('handai-manager.layouts.master')

@section('title', 'Chart of Accounts — Handai Finance')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Chart of Accounts</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $store->name ?? 'Semua Store' }} — Daftar Akun</p>
        </div>
        <a href="{{ route('manager.finance.accounting.dashboard') }}" class="mt-3 sm:mt-0 text-sm text-green-600 hover:underline">&larr; Kembali ke Dashboard</a>
    </div>

    @php
        $typeLabels = [
            'asset'     => ['Aset',       'bg-blue-50 border-blue-200',   'text-blue-700',   'ti-wallet'],
            'liability' => ['Kewajiban',  'bg-red-50 border-red-200',     'text-red-700',    'ti-receipt-tax'],
            'equity'    => ['Ekuitas',    'bg-purple-50 border-purple-200','text-purple-700', 'ti-building-bank'],
            'revenue'   => ['Pendapatan', 'bg-green-50 border-green-200', 'text-green-700',  'ti-chart-bar'],
            'cogs'      => ['HPP',        'bg-amber-50 border-amber-200', 'text-amber-700',  'ti-package'],
            'expense'   => ['Biaya',      'bg-rose-50 border-rose-200',   'text-rose-700',   'ti-receipt'],
        ];
    @endphp

    <div class="space-y-6">
        @foreach ($typeLabels as $type => $meta)
            @if (isset($accounts[$type]) && $accounts[$type]->count())
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    {{-- Section Header --}}
                    <div class="px-5 py-4 {{ $meta[1] }} border-b flex items-center gap-3">
                        <i class="ti {{ $meta[3] }} text-xl {{ $meta[2] }}"></i>
                        <h3 class="text-base font-semibold {{ $meta[2] }}">{{ $meta[0] }}</h3>
                        <span class="ml-auto text-xs {{ $meta[2] }} font-medium bg-white/60 px-2 py-0.5 rounded-full">
                            {{ $accounts[$type]->count() }} akun
                        </span>
                    </div>

                    {{-- Account Table --}}
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                            <tr>
                                <th class="px-5 py-2.5 text-left">Kode</th>
                                <th class="px-5 py-2.5 text-left">Nama Akun</th>
                                <th class="px-5 py-2.5 text-left">Sub Tipe</th>
                                <th class="px-5 py-2.5 text-center">Sistem</th>
                                <th class="px-5 py-2.5 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($accounts[$type] as $acc)
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                                    <td class="px-5 py-3 font-mono font-semibold text-gray-700">{{ $acc->code }}</td>
                                    <td class="px-5 py-3 text-gray-800">
                                        @if ($acc->parent_id)
                                            <span class="text-gray-300 mr-1">└</span>
                                        @endif
                                        {{ $acc->name }}
                                    </td>
                                    <td class="px-5 py-3 text-gray-500">
                                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded">{{ $acc->sub_type ?? '-' }}</span>
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        @if ($acc->is_system)
                                            <span class="inline-flex items-center gap-1 text-xs text-blue-600">
                                                <i class="ti ti-lock text-sm"></i> Ya
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        @if ($acc->is_active)
                                            <span class="inline-block w-2 h-2 rounded-full bg-green-500"></span>
                                        @else
                                            <span class="inline-block w-2 h-2 rounded-full bg-gray-300"></span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @endforeach

        @if ($accounts->isEmpty())
            <div class="bg-white rounded-xl shadow-sm border p-10 text-center">
                <i class="ti ti-folder-off text-4xl text-gray-300 mb-3"></i>
                <p class="text-gray-500">Belum ada Chart of Accounts. Jalankan seeder terlebih dahulu.</p>
                <p class="text-xs text-gray-400 mt-1 font-mono">php artisan db:seed --class=ChartOfAccountSeeder</p>
            </div>
        @endif
    </div>
</div>
@endsection
