@extends('handai-manager.layouts.master')

@section('content')
<div class="container-xl" x-data="returnForm()">
    <div class="page-header d-print-none mb-3">
        <div class="row align-items-center">
            <div class="col-auto">
                <a href="{{ route('manager.operational.returns.index') }}" class="btn btn-outline-secondary btn-sm me-2">
                    <i class="ti ti-arrow-left"></i>
                </a>
            </div>
            <div class="col">
                <h2 class="page-title">Buat Retur Baru</h2>
            </div>
        </div>
    </div>

    {{-- Step 1: Search Order --}}
    @if(!$order)
    <div class="card">
        <div class="card-header"><h3 class="card-title">Cari Order</h3></div>
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label class="form-label">Order ID</label>
                    <input type="text" name="order_id" class="form-control" placeholder="Masukkan ID order..." required>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary"><i class="ti ti-search me-1"></i> Cari</button>
                </div>
            </form>
        </div>
    </div>
    @else
    {{-- Step 2: Return Form --}}
    <form method="POST" action="{{ route('manager.operational.returns.store') }}">
        @csrf
        <input type="hidden" name="order_id" value="{{ $order->id }}">

        {{-- Order Info --}}
        <div class="card mb-3">
            <div class="card-header"><h3 class="card-title">Informasi Order</h3></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>Order ID:</strong>
                        <p>{{ $order->order_id }}</p>
                    </div>
                    <div class="col-md-3">
                        <strong>Pelanggan:</strong>
                        <p>{{ $order->customer->name ?? '-' }}</p>
                    </div>
                    <div class="col-md-3">
                        <strong>Total:</strong>
                        <p>Rp {{ number_format($order->gross_amount, 0, ',', '.') }}</p>
                    </div>
                    <div class="col-md-3">
                        <strong>Status:</strong>
                        <p>{{ $order->order_status }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Return Details --}}
        <div class="card mb-3">
            <div class="card-header"><h3 class="card-title">Detail Retur</h3></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label required">Tipe Retur</label>
                        <select name="return_type" class="form-select" required>
                            <option value="refund">Refund</option>
                            <option value="exchange">Tukar Barang</option>
                            <option value="store_credit">Store Credit</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label required">Alasan</label>
                        <input type="text" name="reason" class="form-control" placeholder="Alasan pengembalian..." required maxlength="500">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control" rows="2" maxlength="1000"></textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Items Selection --}}
        <div class="card mb-3">
            <div class="card-header"><h3 class="card-title">Pilih Item yang Diretur</h3></div>
            <div class="table-responsive">
                <table class="table table-vcenter">
                    <thead>
                        <tr>
                            <th>Pilih</th>
                            <th>Produk</th>
                            <th>Varian</th>
                            <th>Qty Order</th>
                            <th>Harga</th>
                            <th>Qty Retur</th>
                            <th>Kondisi</th>
                            <th>Restock</th>
                            <th>Refund</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orderItems as $idx => $inv)
                        <tr x-show="true">
                            <td>
                                <input type="checkbox" class="form-check-input"
                                       x-model="selectedItems[{{ $idx }}]"
                                       @change="recalculate()">
                            </td>
                            <td>{{ $inv->product_name }}</td>
                            <td>{{ $inv->variant_name ?? '-' }}</td>
                            <td>{{ number_format($inv->quantity_bought, 0) }}</td>
                            <td>Rp {{ number_format($inv->price, 0, ',', '.') }}</td>
                            <td>
                                <template x-if="selectedItems[{{ $idx }}]">
                                    <div>
                                        <input type="hidden" name="items[{{ $idx }}][invoice_id]" value="{{ $inv->id }}">
                                        <input type="number" name="items[{{ $idx }}][quantity]"
                                               class="form-control form-control-sm" style="width: 80px"
                                               min="0.001" max="{{ $inv->quantity_bought }}" step="0.001"
                                               x-model.number="quantities[{{ $idx }}]"
                                               @input="recalculate()" required>
                                    </div>
                                </template>
                            </td>
                            <td>
                                <template x-if="selectedItems[{{ $idx }}]">
                                    <select name="items[{{ $idx }}][condition]" class="form-select form-select-sm" style="width: 120px">
                                        <option value="good">Baik</option>
                                        <option value="damaged">Rusak</option>
                                        <option value="expired">Expired</option>
                                    </select>
                                </template>
                            </td>
                            <td>
                                <template x-if="selectedItems[{{ $idx }}]">
                                    <div>
                                        <input type="checkbox" name="items[{{ $idx }}][restock]" value="1" class="form-check-input" checked>
                                    </div>
                                </template>
                            </td>
                            <td>
                                <template x-if="selectedItems[{{ $idx }}]">
                                    <span x-text="'Rp ' + formatNumber(quantities[{{ $idx }}] * {{ $inv->price }})"></span>
                                </template>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="8" class="text-end fw-bold">Estimasi Total Refund:</td>
                            <td class="fw-bold text-danger" x-text="'Rp ' + formatNumber(totalRefund)"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('manager.operational.returns.index') }}" class="btn btn-outline-secondary">Batal</a>
            <button type="submit" class="btn btn-primary" :disabled="!hasSelection">
                <i class="ti ti-check me-1"></i> Buat Retur
            </button>
        </div>
    </form>
    @endif
</div>

@if($order)
<script>
function returnForm() {
    const itemCount = {{ count($orderItems) }};
    const prices = [@foreach($orderItems as $inv){{ $inv->price }},@endforeach];

    return {
        selectedItems: Object.fromEntries(Array.from({length: itemCount}, (_, i) => [i, false])),
        quantities: Object.fromEntries(Array.from({length: itemCount}, (_, i) => [i, 1])),
        totalRefund: 0,
        hasSelection: false,

        recalculate() {
            let total = 0;
            let selected = false;
            for (let i = 0; i < itemCount; i++) {
                if (this.selectedItems[i]) {
                    total += (this.quantities[i] || 0) * prices[i];
                    selected = true;
                }
            }
            this.totalRefund = total;
            this.hasSelection = selected;
        },

        formatNumber(val) {
            return Math.round(val).toLocaleString('id-ID');
        }
    };
}
</script>
@endif
@endsection
