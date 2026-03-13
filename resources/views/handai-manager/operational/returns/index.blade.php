@extends('handai-manager.layouts.master')

@section('content')
<div class="container-xl">
    {{-- Header --}}
    <div class="page-header d-print-none mb-3">
        <div class="row align-items-center">
            <div class="col-auto">
                <h2 class="page-title">Manajemen Retur</h2>
                <p class="text-muted mt-1">Kelola pengembalian barang & refund</p>
            </div>
            <div class="col-auto ms-auto">
                <a href="{{ route('manager.operational.returns.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i> Retur Baru
                </a>
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="row row-deck row-cards mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader">Total Retur</div>
                    </div>
                    <div class="h1 mb-0 mt-2">{{ $stats['total_returns'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader">Menunggu Persetujuan</div>
                    </div>
                    <div class="h1 mb-0 mt-2 text-warning">{{ $stats['pending_count'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader">Selesai</div>
                    </div>
                    <div class="h1 mb-0 mt-2 text-success">{{ $stats['completed_count'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader">Total Refund</div>
                    </div>
                    <div class="h1 mb-0 mt-2 text-danger">Rp {{ number_format($stats['total_refunded'], 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Cari</label>
                    <input type="text" name="search" class="form-control" placeholder="No. Retur / Pelanggan..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua</option>
                        @foreach(\App\Models\ReturnOrder::STATUSES as $s)
                            <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tipe</label>
                    <select name="return_type" class="form-select">
                        <option value="">Semua</option>
                        @foreach(\App\Models\ReturnOrder::TYPES as $t)
                            <option value="{{ $t }}" {{ request('return_type') == $t ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $t)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-outline-primary"><i class="ti ti-search me-1"></i> Filter</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>No. Retur</th>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th>Tipe</th>
                        <th>Refund</th>
                        <th>Status</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returns as $ret)
                    <tr>
                        <td>
                            <a href="{{ route('manager.operational.returns.show', $ret->id) }}">{{ $ret->return_number }}</a>
                        </td>
                        <td>{{ $ret->return_date->format('d/m/Y') }}</td>
                        <td>{{ $ret->customer->name ?? '-' }}</td>
                        <td>
                            @php
                                $typeBadge = ['refund' => 'bg-red', 'exchange' => 'bg-blue', 'store_credit' => 'bg-purple'];
                            @endphp
                            <span class="badge {{ $typeBadge[$ret->return_type] ?? 'bg-secondary' }}">{{ ucfirst(str_replace('_', ' ', $ret->return_type)) }}</span>
                        </td>
                        <td>Rp {{ number_format($ret->total_refund_amount, 0, ',', '.') }}</td>
                        <td>
                            @php
                                $statusBadge = ['pending' => 'bg-warning', 'approved' => 'bg-info', 'processed' => 'bg-primary', 'rejected' => 'bg-danger', 'completed' => 'bg-success'];
                            @endphp
                            <span class="badge {{ $statusBadge[$ret->status] ?? 'bg-secondary' }}">{{ ucfirst($ret->status) }}</span>
                        </td>
                        <td>
                            <a href="{{ route('manager.operational.returns.show', $ret->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="ti ti-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Belum ada data retur.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex align-items-center">
            {{ $returns->links() }}
        </div>
    </div>
</div>
@endsection
