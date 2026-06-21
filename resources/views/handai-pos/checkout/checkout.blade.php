@extends('layouts.layoutBlank')

@section('title', 'Checkout Kasir')

@section('page-style')
@vite('resources/css/handai-kasir-checkout.css')
@endsection

@section('content')
<div class="min-h-screen items-center content-center bg-slate-50" x-data="{ isMobile: window.innerWidth < 768 }"
    x-init="window.addEventListener('resize', () => isMobile = window.innerWidth < 768)" :class="isMobile ? ' ' : 'bs-container'">

    <div class="relative z-0 py-10">
        <div class="hidden lg:block absolute"
            style="z-index: 0; width: 250px; height: 250px; top: -120px; left: -120px; opacity: 50%; background-image: url('{{ asset('assets/svg/design2.svg') }}'); background-repeat: no-repeat; background-size: contain;">
        </div>

        <div class="hidden lg:block absolute"
            style="z-index: 0; width: 180px; height: 180px; bottom: -100px; right: -100px; opacity: 50%; background-image: url('{{ asset('assets/svg/design1.svg') }}'); background-repeat: no-repeat; background-size: contain;">
        </div>

        <div id="checkout-wrapper" class="card shadow-xl bg-white p-6 max-w-5xl mx-auto rounded-2xl relative z-10"
            x-data="{ 
                step: 1, 
                cartItems: @js($cartDetails ?? []),
                customerName: '',
                selectedCustomer: 'existing',
                paymentMethod: 'tunai',
                grandTotalText: '{{ 'Rp' . number_format(($grandTotal ?? 0) - (session('promo_discount') ?? 0), 0, ',', '.') }}',
                orderId: '', 
                grossAmount: 0,
                isNewCustomer: false, 

                proceedToStep2() {
                    if ((this.selectedCustomer && this.selectedCustomer !== 'existing' && this.selectedCustomer !== 'new') || (this.isNewCustomer && this.customerName.trim() !== '') || this.selectedCustomer === 'existing') {
                        if (this.paymentMethod) {
                            this.step = 2;
                        } else {
                            alert('Pilih metode pembayaran terlebih dahulu.');
                        }
                    } else {
                        alert('Pilih customer atau isi nama customer baru.');
                    }
                }
            }" 
            @payment-success.window="
                orderId = $event.detail.orderId;
                grossAmount = $event.detail.grossAmount;
                step = 3;
            ">

            <div class="flex items-center justify-center mb-8 relative">
                <div class="flex items-center w-full max-w-2xl relative z-10">
                    <div class="flex-1 text-center relative">
                        <div class="w-12 h-12 mx-auto rounded-full flex items-center justify-center border-4 transition-colors duration-300"
                             :class="step >= 1 ? 'bg-[#0C9044] border-green-200 text-white' : 'bg-white border-slate-200 text-slate-400'">
                            <i class="ti ti-shopping-cart text-xl"></i>
                        </div>
                        <p class="text-xs font-bold mt-2" :class="step >= 1 ? 'text-[#0C9044]' : 'text-slate-400'">Keranjang</p>
                    </div>
                    <div class="flex-1 h-1 mx-[-20px] rounded-full transition-colors duration-300"
                         :class="step >= 2 ? 'bg-[#0C9044]' : 'bg-slate-200'"></div>
                    <div class="flex-1 text-center relative">
                        <div class="w-12 h-12 mx-auto rounded-full flex items-center justify-center border-4 transition-colors duration-300 cursor-pointer hover:bg-slate-50"
                             @click="if(step === 3) step = 2"
                             :class="step >= 2 ? 'bg-[#0C9044] border-green-200 text-white' : 'bg-white border-slate-200 text-slate-400'">
                            <i class="ti ti-wallet text-xl"></i>
                        </div>
                        <p class="text-xs font-bold mt-2" :class="step >= 2 ? 'text-[#0C9044]' : 'text-slate-400'">Pembayaran</p>
                    </div>
                    <div class="flex-1 h-1 mx-[-20px] rounded-full transition-colors duration-300"
                         :class="step >= 3 ? 'bg-[#0C9044]' : 'bg-slate-200'"></div>
                    <div class="flex-1 text-center relative">
                        <div class="w-12 h-12 mx-auto rounded-full flex items-center justify-center border-4 transition-colors duration-300"
                             :class="step >= 3 ? 'bg-[#0C9044] border-green-200 text-white' : 'bg-white border-slate-200 text-slate-400'">
                            <i class="ti ti-check text-xl"></i>
                        </div>
                        <p class="text-xs font-bold mt-2" :class="step >= 3 ? 'text-[#0C9044]' : 'text-slate-400'">Selesai</p>
                    </div>
                </div>
            </div>

            <form id="wizard-checkout-form">
                @csrf                
                
                <div x-show="step === 1" x-cloak x-transition>
                    <div class="flex flex-col lg:flex-row gap-6">
                        <div class="flex-1 space-y-4">
                            <h5 class="text-lg font-bold text-slate-800">
                                Ringkasan Pesanan (<span id="cart-total-items">{{ $cartTotalItems ?? 0 }}</span> item)
                            </h5>
                            
                            <div class="w-full overflow-hidden border border-slate-200 rounded-xl">
                                <table class="w-full text-sm text-left">
                                    <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
                                        <tr>
                                            <th class="p-3">Produk</th>
                                            <th class="p-3">Harga</th>
                                            <th class="p-3 text-center">Jumlah</th>
                                            <th class="p-3 text-right">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @forelse($cartDetails ?? [] as $item)
                                        <tr class="hover:bg-slate-50 transition">
                                            <td class="p-3">
                                                <p class="font-bold text-slate-700">{{ $item['product_name'] ?? 'No name' }}</p>
                                                @if(isset($item['variant_summary']))
                                                    <p class="text-slate-400 text-xs">{{ str_replace('â€”', '—', $item['variant_summary']) }}</p>
                                                @endif
                                            </td>
                                            <td class="p-3 font-medium text-slate-600">Rp{{ number_format($item['price'], 0, ',', '.') }}</td>
                                            <td class="p-3">
                                                <div class="flex items-center justify-center bg-white border border-slate-200 rounded-lg w-fit mx-auto">
                                                    <button type="button" class="w-8 h-8 flex items-center justify-center text-slate-500 hover:text-green-600"
                                                        onclick="updateQuantity('{{ $item['product_id'] }}', '{{ $item['variant_id'] }}', -1)">-</button>
                                                    <input type="text" id="qty-{{ $item['product_id'] }}-{{$item['variant_id'] }}"
                                                        class="w-10 text-center text-sm font-bold border-0 focus:ring-0 p-0"
                                                        value="{{ $item['quantity'] }}" readonly>
                                                    <button type="button" class="w-8 h-8 flex items-center justify-center text-slate-500 hover:text-green-600"
                                                        onclick="updateQuantity('{{ $item['product_id'] }}', '{{ $item['variant_id'] }}', 1)">+</button>
                                                </div>
                                            </td>
                                            <td class="p-3 font-bold text-[#0C9044] text-right" id="item-total-{{ $item['product_id'] }}-{{ $item['variant_id'] }}">
                                                Rp{{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}
                                            </td>
                                            <td class="p-3">
                                                <button type="button" class="text-slate-400 hover:text-red-500" onclick="removeItem('{{ $item['product_id'] }}', '{{ $item['variant_id'] }}')">
                                                    <i class="ti ti-x"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="p-8 text-center text-slate-400 font-medium">Keranjang masih kosong.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-[#0C9044] font-medium transition">
                                <i class="ti ti-arrow-left"></i> Tambah produk lain
                            </a>
                        </div>

                        <div class="w-full lg:w-[380px] space-y-4 shrink-0">
                            <div class="border border-slate-200 rounded-xl p-5 bg-white">
                                <h6 class="text-sm font-bold text-slate-700 mb-3"><i class="ti ti-user mr-1 text-slate-400"></i> Data Pelanggan</h6>
                                <select x-model="selectedCustomer" name="customer_type" class="w-full h-11 px-3 rounded-lg border border-slate-200 text-sm focus:border-green-500 focus:ring-1 focus:ring-green-500 outline-none" 
                                @change="isNewCustomer = selectedCustomer === 'new'; if (!isNewCustomer && selectedCustomer !== 'existing') { const selectedOption = $event.target.options[$event.target.selectedIndex]; customerName = selectedOption.text; }">
                                    <option value="existing">-- Pilih Pelanggan (Opsional) --</option>
                                    @foreach($customers ?? [] as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                                    <option value="new">+ Pelanggan Baru</option>
                                </select>

                                <div x-show="isNewCustomer" x-collapse class="mt-3 space-y-3">
                                    <input type="text" x-model="customerName" name="customer_name" class="w-full h-10 px-3 rounded-lg border border-slate-200 text-sm outline-none focus:border-green-500" placeholder="Nama Pelanggan" required>
                                    <input type="text" name="new_contact" class="w-full h-10 px-3 rounded-lg border border-slate-200 text-sm outline-none focus:border-green-500" placeholder="No. WhatsApp (Opsional)">
                                </div>
                            </div>

                            <div class="border border-slate-200 rounded-xl p-5 bg-white">
                                <h6 class="text-sm font-bold text-slate-700 mb-3"><i class="ti ti-wallet mr-1 text-slate-400"></i> Metode Pembayaran</h6>
                                <div class="space-y-3">
                                    <label class="flex items-center gap-3 p-3 border rounded-xl cursor-pointer transition-all" :class="paymentMethod === 'tunai' ? 'border-[#0C9044] bg-green-50' : 'border-slate-200 hover:bg-slate-50'">
                                        <input type="radio" x-model="paymentMethod" name="payment_method" value="tunai" class="w-4 h-4 text-[#0C9044] focus:ring-[#0C9044] border-slate-300">
                                        <span class="text-sm font-bold text-slate-700">Tunai (Cash)</span>
                                    </label>
                                    <label class="flex items-center gap-3 p-3 border rounded-xl cursor-pointer transition-all" :class="paymentMethod === 'non_tunai' ? 'border-[#0C9044] bg-green-50' : 'border-slate-200 hover:bg-slate-50'">
                                        <input type="radio" x-model="paymentMethod" name="payment_method" value="non_tunai" class="w-4 h-4 text-[#0C9044] focus:ring-[#0C9044] border-slate-300">
                                        <span class="text-sm font-bold text-slate-700">QRIS / Transfer</span>
                                    </label>

                                    <div x-show="paymentMethod === 'tunai'" x-collapse class="pt-2">
                                        <label class="text-xs font-semibold text-slate-500 mb-1.5 block">Uang Diterima (Rp)</label>
                                        <input type="number" name="cash_received" id="cash-received" class="w-full h-11 px-3 rounded-lg border border-slate-200 text-base font-bold text-slate-800 outline-none focus:border-green-500" min="0" step="1000" oninput="computeChange()">
                                        
                                        <div class="mt-2 flex gap-2">
                                            <button type="button" class="flex-1 py-1.5 text-xs font-semibold border border-slate-200 rounded-lg hover:bg-slate-50 text-slate-600" onclick="setQuickAmount('exact')">Uang Pas</button>
                                            <button type="button" class="flex-1 py-1.5 text-xs font-semibold border border-slate-200 rounded-lg hover:bg-slate-50 text-slate-600" onclick="setQuickAmount(50000)">50K</button>
                                            <button type="button" class="flex-1 py-1.5 text-xs font-semibold border border-slate-200 rounded-lg hover:bg-slate-50 text-slate-600" onclick="setQuickAmount(100000)">100K</button>
                                        </div>
                                        
                                        <div class="mt-3 flex items-center justify-between p-3 bg-slate-50 rounded-lg border border-slate-100">
                                            <span class="text-sm font-semibold text-slate-500">Kembalian</span> 
                                            <span id="cash-change" class="text-lg font-black text-amber-500">Rp0</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="border border-slate-200 rounded-xl p-5 bg-slate-50">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-semibold text-slate-500">Sub Total</span>
                                    <span class="text-sm font-bold text-slate-700" id="price-subtotal">Rp{{ number_format($subTotal ?? 0, 0, ',', '.') }}</span>
                                </div>
                                <div class="border-t border-dashed border-slate-300 my-3"></div>
                                <div class="flex justify-between items-end">
                                    <span class="text-sm font-bold text-slate-800">TOTAL</span>
                                    <span class="text-2xl font-black text-[#0C9044]" id="price-grandtotal">Rp{{ number_format(($grandTotal ?? 0) - (session('promo_discount') ?? 0), 0, ',', '.') }}</span>
                                </div>
                            </div>
                          
                            <button type="button" class="w-full h-14 bg-[#0C9044] hover:bg-green-700 text-white rounded-xl font-bold text-lg shadow-lg shadow-green-600/30 transition disabled:opacity-50 disabled:cursor-not-allowed" @click="proceedToStep2()" :disabled="{{ ($cartTotalItems ?? 0) }} === 0">
                                KONFIRMASI PESANAN <i class="ti ti-arrow-right ml-1"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div x-show="step === 2" x-cloak x-transition>
                    <div class="max-w-xl mx-auto bg-white rounded-2xl shadow-lg border border-slate-100 p-8 text-center">
                        <div class="w-20 h-20 bg-green-100 text-[#0C9044] rounded-full flex items-center justify-center mx-auto mb-5">
                            <i class="ti ti-receipt text-4xl"></i>
                        </div>
                        <h2 class="text-2xl font-black text-slate-800 mb-2">Selesaikan Transaksi</h2>
                        <p class="text-slate-500 mb-6">Pastikan metode pembayaran dan tagihan sudah sesuai.</p>
                    
                        <div class="bg-slate-50 border border-slate-100 rounded-xl p-5 text-left mb-8 space-y-3 text-sm">
                            <div class="flex justify-between border-b border-slate-200 pb-3">
                                <span class="text-slate-500 font-medium">Pelanggan</span> 
                                <span class="font-bold text-slate-800" x-text="customerName || 'Pelanggan Umum'"></span>
                            </div>
                            <div class="flex justify-between border-b border-slate-200 pb-3">
                                <span class="text-slate-500 font-medium">Metode Bayar</span> 
                                <span class="font-bold text-slate-800 uppercase" x-text="paymentMethod.replace('_', ' ')"></span>
                            </div>
                            <div class="flex justify-between pt-1">
                                <span class="text-slate-500 font-medium">Total Tagihan</span> 
                                <span class="font-black text-[#0C9044] text-xl" x-text="grandTotalText"></span>
                            </div>
                        </div>
                    
                        <div class="flex gap-4">
                            <button type="button" @click="step = 1" class="flex-1 h-14 rounded-xl border-2 border-slate-200 text-slate-600 font-bold hover:bg-slate-50 transition">Kembali</button>
                            <button type="button" @click="submitOrder($event)" class="flex-1 h-14 rounded-xl bg-[#0C9044] text-white font-bold text-lg hover:bg-green-700 shadow-lg shadow-green-500/30 transition">Bayar Sekarang</button>
                        </div>
                    </div>
                </div>

                <div x-show="step === 3" x-cloak x-transition>
                    <div class="max-w-xl mx-auto bg-white rounded-2xl shadow-lg border border-slate-100 p-8 text-center">
                        <div class="w-24 h-24 bg-[#0C9044] text-white rounded-full flex items-center justify-center mx-auto mb-5 shadow-lg shadow-green-500/40">
                            <i class="ti ti-check text-5xl"></i>
                        </div>
                        <h2 class="text-3xl font-black text-slate-800 mb-2">Transaksi Berhasil!</h2>
                        <p class="text-slate-500 mb-6">Pesanan telah tercatat ke dalam sistem POS.</p>
                        
                        <div class="bg-slate-50 border border-slate-100 rounded-xl p-5 text-left mb-8 space-y-3 text-sm">
                            <div class="flex justify-between border-b border-slate-200 pb-3">
                                <span class="text-slate-500 font-medium">Order ID</span> 
                                <span class="font-mono font-bold text-slate-800" x-text="orderId"></span>
                            </div>
                            <div class="flex justify-between pt-1">
                                <span class="text-slate-500 font-medium">Nominal Masuk</span> 
                                <span class="font-black text-[#0C9044] text-lg" x-text="'Rp ' + Number(grossAmount).toLocaleString('id-ID')"></span>
                            </div>
                        </div>
                        
                        <div class="flex flex-col sm:flex-row justify-center gap-4">
                            <a :href="`/pos/invoice/print/${orderId}`" target="_blank" class="h-14 px-6 rounded-xl border-2 border-[#0C9044] text-[#0C9044] font-bold hover:bg-green-50 flex items-center justify-center gap-2 transition">
                                <i class="ti ti-printer text-xl"></i> Cetak Struk
                            </a>
                            <a href="{{ route('products.index') }}" class="h-14 px-8 rounded-xl bg-[#0C9044] text-white font-bold hover:bg-green-700 shadow-lg shadow-green-500/30 flex items-center justify-center transition">
                                Order Baru
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
    function updateQuantity(productId, variantId, change) {
        const qtyInput = document.getElementById('qty-' + productId + '-' + variantId);
        let currentQty = parseInt(qtyInput.value) || 1;
        let newQty = currentQty + change;
        if (newQty < 1) newQty = 1;

        fetch('{{ route("pos.cart.update", [], false) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ product_id: productId, variant_id: variantId, quantity: newQty })
        }).then(response => response.json()).then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.error_detail || 'Gagal memperbarui quantity');
            }
        }).catch(error => alert(error.message));
    }

    function removeItem(productId, variantId) {
        if (!confirm("Yakin ingin menghapus item ini dari keranjang?")) return;
        fetch('{{ route("pos.cart.remove", [], false) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ product_id: productId, variant_id: variantId })
        }).then(response => response.json()).then(data => {
            if (data.success) location.reload();
            else alert(data.error || 'Gagal menghapus item.');
        });
    }

    function setQuickAmount(amount) {
        const cashInput = document.getElementById('cash-received');
        if (!cashInput) return;
        if (amount === 'exact') {
            const grand = parseInt(document.getElementById('price-grandtotal').textContent.replace(/\D/g, '')) || 0;
            cashInput.value = grand;
        } else {
            cashInput.value = amount;
        }
        computeChange();
    }

    function computeChange() {
        const cashInput = document.getElementById('cash-received');
        const changeElem = document.getElementById('cash-change');
        if (!cashInput || !changeElem) return;
        const cash = parseInt(cashInput.value) || 0;
        const grand = parseInt(document.getElementById('price-grandtotal').textContent.replace(/\D/g, '')) || 0;
        const change = Math.max(cash - grand, 0);
        changeElem.textContent = 'Rp' + change.toLocaleString('id-ID');
    }

    function submitOrder(event) {
        if (event) event.preventDefault();
        const form = document.getElementById('wizard-checkout-form');
        const formData = new FormData(form);

        // Disable button to prevent double submit
        event.target.disabled = true;
        event.target.innerHTML = 'Memproses...';

        fetch('{{ route("pos.cart.checkoutCustomer", [], false) }}', {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json"
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const checkout = document.querySelector('#checkout-wrapper');
                checkout.dispatchEvent(new CustomEvent('payment-success', {
                    detail: { orderId: data.order_id, grossAmount: data.gross_amount },
                    bubbles: true
                }));
            } else {
                alert(data.message || 'Checkout gagal. Cek stok atau coba lagi.');
                event.target.disabled = false;
                event.target.innerHTML = 'Bayar Sekarang';
            }
        }).catch(error => {
            alert('Terjadi kesalahan sistem.');
            event.target.disabled = false;
            event.target.innerHTML = 'Bayar Sekarang';
        });
    }
</script>
@endsection