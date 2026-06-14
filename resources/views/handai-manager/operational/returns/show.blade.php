@extends('layouts.master')

@section('content')
<div class="container-xl">
    <div class="page-header d-print-none mb-3">
        <div class="row align-items-center">
            <div class="col-auto">
                <a href="{{ route('manager.operational.returns.index') }}" class="btn btn-outline-secondary btn-sm me-2">
                    <i class="ti ti-arrow-left"></i>
                </a>
            </div>
            <div class="col">
                <h2 class="page-title">{{ $return->return_number }}</h2>
                <p class="text-muted mt-1">Detail Retur</p>
            </div>
            <div class="col-auto ms-auto d-flex gap-2">
                @if($return->status === 'pending')
                    <form method="POST" action="{{ route('manager.operational.returns.approve', $return->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-success"><i class="ti ti-check me-1"></i> Setujui</button>
                    </form>
                    <form method="POST" action="{{ route('manager.operational.returns.reject', $return->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-danger"><i class="ti ti-x me-1"></i> Tolak</button>
                    </form>
                @elseif($return->status === 'approved')
                    <form method="POST" action="{{ route('manager.operational.returns.process', $return->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary"><i class="ti ti-refresh me-1"></i> Proses Retur</button>
                    </form>
                @elseif($return->status === 'processed')
                    <form method="POST" action="{{ route('manager.operational.returns.complete', $return->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-success"><i class="ti ti-circle-check me-1"></i> Selesai</button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    {{-- Info Cards --}}
    <div class="row row-deck row-cards mb-3">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Informasi Retur</h3></div>
                <div class="card-body">
                    <div class="datagrid">
                        <div class="datagrid-item">
                            <div class="datagrid-title">No. Retur</div>
                            <div class="datagrid-content">{{ $return->return_number }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Tanggal</div>
                            <div class="datagrid-content">{{ $return->return_date->format('d/m/Y') }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Tipe</div>
                            <div class="datagrid-content">
                                @php
                                    $typeBadge = ['refund' => 'bg-red', 'exchange' => 'bg-blue', 'store_credit' => 'bg-purple'];
                                @endphp
                                <span class="badge {{ $typeBadge[$return->return_type] ?? 'bg-secondary' }}">{{ ucfirst(str_replace('_', ' ', $return->return_type)) }}</span>
                            </div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Status</div>
                            <div class="datagrid-content">
                                @php
                                    $statusBadge = ['pending' => 'bg-warning', 'approved' => 'bg-info', 'processed' => 'bg-primary', 'rejected' => 'bg-danger', 'completed' => 'bg-success'];
                                @endphp
                                <span class="badge {{ $statusBadge[$return->status] ?? 'bg-secondary' }}">{{ ucfirst($return->status) }}</span>
                            </div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Pelanggan</div>
                            <div class="datagrid-content">{{ $return->customer->name ?? '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Order ID</div>
                            <div class="datagrid-content">{{ $return->order->order_id ?? '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Alasan</div>
                            <div class="datagrid-content">{{ $return->reason }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Diproses oleh</div>
                            <div class="datagrid-content">{{ $return->processor->name ?? '-' }}</div>
                        </div>
                    </div>
                    @if($return->notes)
                        <div class="mt-3">
                            <strong>Catatan:</strong>
                            <p class="text-muted">{{ $return->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="subheader">Total Refund</div>
                    <div class="h1 text-danger mt-2">Rp {{ number_format($return->total_refund_amount, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Items --}}
    <div class="card">
        <div class="card-header"><h3 class="card-title">Item Retur</h3></div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Varian</th>
                        <th>Qty</th>
                        <th>Harga Satuan</th>
                        <th>Refund</th>
                        <th>Kondisi</th>
                        <th>Restock</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($return->items as $item)
                    <tr>
                        <td>{{ $item->product_name }}</td>
                        <td>{{ $item->variant_name ?? '-' }}</td>
                        <td>{{ number_format($item->quantity_returned, 0) }}</td>
                        <td>Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td class="fw-bold">Rp {{ number_format($item->refund_amount, 0, ',', '.') }}</td>
                        <td>
                            @php
                                $condBadge = ['good' => 'bg-green', 'damaged' => 'bg-yellow', 'expired' => 'bg-red'];
                                $condLabel = ['good' => 'Baik', 'damaged' => 'Rusak', 'expired' => 'Expired'];
                            @endphp
                            <span class="badge {{ $condBadge[$item->condition] ?? 'bg-secondary' }}">{{ $condLabel[$item->condition] ?? $item->condition }}</span>
                        </td>
                        <td>
                            @if($item->restock)
                                <span class="badge bg-green">Ya</span>
                            @else
                                <span class="badge bg-secondary">Tidak</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

