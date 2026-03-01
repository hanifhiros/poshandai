@extends('handai-manager.layouts.layoutBlank')


@section('title', 'Landing Page')

@section('vendor-style')
@endsection

@section('page-style')
<style>
    [x-cloak] { display: none !important; }
</style>
@endsection

@section('content')
<div class="min-h-screen   items-center  content-center" x-data="{ isMobile: window.innerWidth < 768 }"
    x-init="window.addEventListener('resize', () => isMobile = window.innerWidth < 768)" :class="isMobile ? ' ' : 'bs-container'">

    <div class="relative z-0">
        <!-- Vector kiri atas -->
        <div class="hidden lg:block absolute"
            style="z-index: 0; width: 250px; height: 250px; top: -120px; left: -120px; opacity: 50%; background-image: url('{{ asset('assets/svg/design2.svg') }}'); background-repeat: no-repeat; background-size: contain;">
        </div>

        <!-- Vector kanan bawah -->
        <div class="hidden lg:block absolute"
            style="z-index: 0; width: 180px; height: 180px; bottom: -100px; right: -100px; opacity: 50%; background-image: url('{{ asset('assets/svg/design1.svg') }}'); background-repeat: no-repeat; background-size: contain;">
        </div>

        <div id="checkout-wrapper" class="card shadow-2xl bg-white p-4"
            x-data="{ 
            addressOption: 'default',
    step: 1, 
     selectedCustomer: '{{ session('customer_id') }}',
  customerName: '{{ session('customer_name') }}',
    showOfferAlert: true, 
    paymentType: '', 
    cartItems: @js($cart),

    grandTotalText: '{{ 'Rp' . number_format($grandTotal - (session('promo_discount') ?? 0), 0, ',', '.') }}',
    paymentMethod: '',
    deliveryDate: '',
    deliveryTime: '',proceedToStep2() {
    if (
        (this.selectedCustomer && this.selectedCustomer !== '') || 
        (this.isNewCustomer && this.customerName.trim() !== '')
    ) {
        if (this.paymentMethod && this.deliveryDate && this.deliveryTime) {
            this.step = 2;
        } else {
            alert('Lengkapi metode pembayaran dan waktu pengiriman.');
        }
    } else {
        alert('Pilih customer atau tambahkan customer baru.');
    }
},

    showOnlineForm: false,
    orderId: '', 
    grossAmount: 0,
    isNewCustomer: false, 

                closeAlert() {
                    this.showOfferAlert = false;
                },

                resetPaymentMethod() {
                    this.paymentType = '';
                    this.showOnlineForm = false;
                    this.step = 2;
                }
            }" @payment-success.window="
    orderId = $event.detail.orderId;
    grossAmount = $event.detail.grossAmount;
    step = 3;
  ">

            <div class="grid grid-cols-3 items-center text-center gap-4 w-full relative">
                <!-- STEP 1: Cart -->
                <div class="relative flex flex-col items-center z-50 " :class="{ 'text-primary': step >= 1 }">
                    <button @click="step = 1" class="flex flex-col items-center focus:outline-none  cursor-pointer">
                        <span class="w-20 h-20 flex items-center justify-center  mb-2   text-current transition-colors duration-300 bg-white ">
                            <img src="{{ asset('assets/svg/cart.svg') }}" class="w-36 h-auto" alt="Cart">
                        </span>
                        <span class="text-sm font-medium">Cart</span>
                    </button>
                </div>

                <!-- STEP 2: Payment -->
                <div class="relative flex flex-col items-center z-50" :class="{ 'text-primary': step >= 2 }">
                    <button @click="step = 2" onclick="resetPaymentMethod()" class="flex flex-col items-center focus:outline-none  cursor-pointer">
                        <span class="w-20 h-20 flex items-center justify-center  mb-2   text-current transition-colors duration-300 bg-white">
                            <img src="{{ asset('assets/svg/payment.svg') }}" class="w-36 h-auto" alt="Payment">
                        </span>
                        <span class="text-sm font-medium">Payment</span>
                    </button>
                </div>

                <!-- STEP 3: Confirmation -->
                <div class="relative flex flex-col items-center z-50" :class="{ 'text-primary': step >= 3 }">
                    <button @click="step = 3" class="flex flex-col items-center focus:outline-none  cursor-pointer">
                        <span class="w-20 h-20 flex items-center justify-center  mb-2  text-current transition-colors duration-300 bg-white">
                            <img src="{{ asset('assets/svg/confirm.svg') }}" class="w-36 h-auto" alt="Confirmation">
                        </span>
                        <span class="text-sm font-medium">Confirmation</span>
                    </button>
                </div>

                <!-- Garis 1 (antara step 1 dan 2) left-[20%] w-[26%] -->
                <div class="absolute top-12.5 left-[28%]  w-[10%] lg:w-[26%] lg:left-[20%] h-1 bg-gray-200 rounded-full">
                    <div class="h-full bg-primary transition-all duration-300 rounded-full"
                        :style="{ width: step > 1 ? '100%' : '0%' }">
                    </div>
                </div>

                <!-- Garis 2 (antara step 2 dan 3) left-[54%] w-[26%] -->
                <div class="absolute top-12.5 left-[62%] w-[10%] lg:w-[26%] lg:left-[54%] h-1 bg-gray-200 rounded-full">
                    <div class="h-full bg-primary transition-all duration-300 rounded-full"
                        :style="{ width: step > 2 ? '100%' : '0%' }">
                    </div>
                </div>
            </div>

            <!-- Wizard Content -->
            <div class="border-t border-gray-200 pt-6">
                <form id="wizard-checkout-form" enctype="multipart/form-data" method="POST" action="{{ route('customerOrder.cart.checkout') }}">

                    @csrf                
                    <!-- CART Step -->
                    <div x-show="step === 1" x-cloak>
                        <div class="flex flex-col xl:flex-row gap-4">
                            <!-- Left Section -->
                            <div class="flex-1 space-y-4">
                                <!-- Shopping Bag Title -->
                                <div id="cart-title-wrapper">
                                    <h5 class="text-lg font-medium mb-4">
                                        My Shopping Bag (<span id="cart-total-items">{{ $cartTotalItems }}</span> Items)
                                    </h5>
                                </div>
                                <!-- Table Container -->
                                <div class="w-full overflow-x-auto border border-gray-200 rounded" x-data>
                                    <table class="table-auto w-full text-xs sm:text-sm md:text-base text-left">
                                        <thead class="bg-gray-100 text-gray-700 font-semibold">
                                            <tr class="text-[0.75rem] sm:text-sm">
                                                <th class="p-2 sm:p-4 whitespace-nowrap">Product</th>
                                                <th class="p-2 sm:p-4 whitespace-nowrap">Price</th>
                                                <th class="p-2 sm:p-4 whitespace-nowrap">Quantity</th>
                                                <th class="p-2 sm:p-4 whitespace-nowrap">Total</th>
                                                <th class="p-2 sm:p-4 hidden sm:table-cell"></th>
                                                <!-- HILANGKAN tombol X di mobile -->
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">

                                            @forelse($cart as $item)
                                            <tr class="hover:bg-gray-50">
                                                <!-- Product -->
                                                <td class="p-2 sm:p-4">
                                                    <div class="flex items-center gap-2 sm:gap-3">
                                                        <!-- Gambar produk (sesuaikan path Anda) -->
                                                        <img
                                                            src="{{ asset('assets/svg/produk/' . ($item['product_name'] ?? 1) . '.svg') }}"
                                                            alt="{{ $item['product_name'] ?? 'No name' }}"
                                                            class="w-5 mx-5 hidden lg:block h-auto  ">

                                                        <div class="truncate max-w-[130px] sm:max-w-[180px] md:max-w-xs lg:max-w-sm xl:max-w-md">
                                                            <p class="font-medium text-gray-700 truncate text-xs sm:text-sm"
                                                                title="{{ $item['product_name'] ?? 'No name' }}">
                                                                {{ $item['product_name'] ?? 'No name' }}
                                                            </p>
                                                            @if(isset($item['variant_summary']))
                                                                <p class="text-gray-500 text-xs">{{ $item['variant_summary'] }}</p>
                                                            @endif
                                                            <span class="inline-block bg-green-100 text-green-700 text-[10px] sm:text-xs px-2 py-0.5 rounded mt-1">
                                                                In Stock
                                                            </span>
                                                        </div>
                                                    </div>
                                                </td>

                                                <!-- Price -->
                                                <td class="p-2 sm:p-4">
                                                    <div class="text-primary font-semibold text-xs sm:text-base">
                                                        Rp{{ number_format($item['price'], 0, ',', '.') }}
                                                    </div>
                                                    @if($item['normal_price'] ?? false)
                                                    <s class="text-[10px] sm:text-xs text-gray-400">
                                                        Rp{{ number_format($item['normal_price'], 0, ',', '.') }}
                                                    </s>
                                                    @endif
                                                </td>

                                                <!-- Quantity -->
                                                <td class="p-2 sm:p-4">
                                                    <div class="flex items-center  rounded w-fit overflow-hidden text-xs sm:text-sm">
                                                        <button
                                                            type="button"
                                                            class="px-2 py-1 btn btn-soft btn-primary btn-sm"
                                                            onclick="updateQuantity('{{ $item['product_id'] }}', '{{ $item['variant_id'] }}', -1)"
>
                                                            -
                                                        </button>
                                                        <input
                                                            type="text"
                                                            id="qty-{{ $item['product_id'] }}-{{$item['variant_id'] }}"
                                                            class="w-5 lg:w-10 text-center border-0 focus:ring-0 focus:outline-none"
                                                            value="{{ $item['quantity'] }}"
                                                            readonly>
                                                        <button
                                                            type="button"
                                                            class="px-2 py-1 btn btn-soft btn-primary btn-sm"
                                                            onclick="updateQuantity('{{ $item['product_id'] }}', '{{ $item['variant_id'] }}', 1)">
                                                            +
                                                        </button>
                                                    </div>
                                                </td>

                                                <!-- Total -->
                                                <td class="p-2 sm:p-4 font-semibold text-xs sm:text-base"
                                                    id="item-total-{{ $item['product_id'] }}-{{ $item['variant_id'] }}">
                                                    Rp{{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}
                                                </td>

                                                <input type="hidden" name="store_id" value="{{ session('selected_store') }}">

                                               
                                                                        <!-- Action -->
                            <td class="p-2 sm:p-4 text-right hidden sm:table-cell">
                                <button type="button" class="text-gray-400 hover:text-gray-600"
                                    onclick="removeItem('{{ $item['product_id'] }}', '{{ $item['variant_id'] }}')">
                                    <i class="ti ti-x text-xs sm:text-base"></i>
                                </button>
                            </td>

                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="5" class="p-4 text-center">
                                                    No items in cart.
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Add More Product -->
                                <div>
                                    <a href="{{ route('customerOrder.form') }}" class="btn btn-soft btn-primary w-full">
                                        Add more products
                                        <i class="ti ti-chevron-right float-right"></i>
                                    </a>
                                </div>
                            </div>
                            <!-- /Left Section -->

                            <!-- Right Section -->
                            <div class="w-full xl:w-1/3 space-y-4">
                                <!-- === Customer Section === -->
                                <div class="border border-gray-200 rounded p-4">
                                    <h6 class="text-lg font-medium mb-2">Customer</h6>
                                    <p><strong>Nama:</strong> {{ session('customer_name') }}</p>
                                    <input type="hidden" name="customer_id" value="{{ session('customer_id') }}">
                                </div>

                                <div class="border border-gray-200 rounded p-4">
                                    <h6 class="text-lg font-medium mb-2">Alamat Pengiriman</h6>
                                
                                    <div class="mb-2">
                                        <label class="flex items-center space-x-2">
                                            <input type="radio" name="address_option" value="default" x-model="addressOption" checked>
                                            <span>Gunakan alamat default</span>
                                        </label>
                                        <label class="flex items-center space-x-2">
                                            <input type="radio" name="address_option" value="custom" x-model="addressOption">
                                            <span>Tulis alamat baru</span>
                                        </label>
                                    </div>
                                
                                    <!-- Alamat Default -->
                                    <div x-show="addressOption === 'default'" class="p-2 border border-green-100 rounded bg-green-50 text-sm">
                                        {{ session('customer_address') ?? '-' }}
                                        <input type="hidden" name="shipping_address" :value="'{{ session('customer_address') }}'">
                                    </div>
                                
                                    <!-- Alamat Custom -->
                                    <div x-show="addressOption === 'custom'" class="mt-2">
                                        <textarea 
                                            name="shipping_address" 
                                            class="input input-primary w-full" 
                                            placeholder="Masukkan alamat pengiriman lainnya..." 
                                            rows="3"
                                        ></textarea>
                                    </div>
                                </div>
                                
                                
                                <div class="border border-gray-200 rounded p-4 space-y-4">
                                   <!-- Payment Type -->
<div>
    <h6 class="text-lg font-medium mb-2">Metode Pembayaran</h6>
    <select id="payment-method" x-model="paymentMethod" name="payment_method" class="input input-primary w-full">
        <option value="">-- Pilih Metode Pembayaran --</option>
        <option value="cash">Tunai</option>
        <option value="transfer">Transfer Bank</option>
        <option value="qris">QRIS</option>
    </select>
</div>

<!-- Delivery Date & Time -->
<div class="mt-4">
    <h6 class="text-lg font-medium mb-2">Waktu Pengiriman</h6>
    <input type="date" id="delivery-date" x-model="deliveryDate" name="delivery_date" class="input input-primary w-full mb-2">
    <input type="time" id="delivery-time" x-model="deliveryTime" name="delivery_time" class="input input-primary w-full">
</div>
                                    <!-- Offer -->
                                    <div>
                                        <h6 class="text-lg font-medium mb-2">Offer</h6>
                                        <div class="flex flex-col md:flex-row gap-2">
                                            <input
                                                type="text"
                                                id="promo-code"
                                                placeholder="Enter Promo Code"
                                                class="input input-primary">
                                            <button
                                                type="button"
                                                class="btn btn-outline btn-primary"
                                                onclick="applyPromo()">
                                                Apply
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Gift wrap -->
                                    <!-- <div class="bg-gray-50 border border-gray-100 p-3 rounded text-sm">
                                        <p class="font-medium mb-1">Buying gift for a loved one?</p>
                                        <p class="mb-1">Gift wrap & personalized card for only $2.</p>
                                        <a href="#" class="text-blue-600 hover:underline font-medium">Add a gift wrap</a>
                                    </div> -->

                                    <hr class="border-gray-200">

                                    <h6 class="text-lg font-medium mb-2">Price Details</h6>
                                    <dl class="text-sm space-y-1">
                                        <div class="flex justify-between">
                                            <dt>Sub Total</dt>
                                            <dd id="price-subtotal">Rp{{ number_format($subTotal, 0, ',', '.') }}</dd>
                                        </div>

                                        <!-- Promo -->
                                        <!-- Promo Code -->
<div class="flex justify-between {{ session('promo_code') ? '' : 'hidden' }}" id="promo-code-wrapper">
    <dt>Promo Code</dt>
    <dd class="ml-2 cursor-pointer text-primary hover:underline" 
        onclick="removePromo()" 
        id="promo-code-text">
        {{ session('promo_code') }}
    </dd>
</div>

<!-- Promo Discount -->
<div class="flex justify-between {{ session('promo_discount') ? '' : 'hidden' }}" id="promo-discount-section">
    <dt>Coupon Discount</dt>
    <dd id="promo-discount">- Rp{{ number_format(session('promo_discount') ?? 0, 0, ',', '.') }}</dd>
</div>


                                        {{-- <div class="flex justify-between">
                                            <dt>PPN (11%)</dt>
                                            <dd id="price-ppn">Rp{{ number_format($ppn, 0, ',', '.') }}</dd>
                                        </div> --}}
                                        {{-- <div class="flex justify-between items-center">
                                            <dt>Packaging</dt>
                                            <dd>
                                                <s class="text-gray-400">Rp5.000</s>
                                                <span class="inline-block bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded ml-1">Free</span>
                                            </dd>
                                        </div> --}}
                                    </dl>
                                    <hr class="border-gray-200">
                                    <div class="mt-4">
                                        <label for="note" class="text-sm font-medium text-gray-700">Catatan (Opsional)</label>
                                        <textarea name="note" id="note" rows="3" class="input input-primary w-full mt-1" placeholder="Contoh: Tanpa es, kirim siang hari, dsb."></textarea>
                                    </div>
                                    
                                    <div class="flex justify-between text-base font-semibold">
                                        <span>Grand Total</span>
                                        <span id="price-grandtotal">
                                            Rp{{ number_format(max($grandTotal - (session('promo_discount') ?? 0), 0), 0, ',', '.') }}

                                        </span>
                                    </div>
                                    </dl>
                                </div>
                              
                                <button type="button"
    class="btn btn-primary mt-4"
    @click="proceedToStep2()">
    Place Order
</button>                                
                            </div>
                            
                            <!-- /Right Section -->
                        </div>
                    </div>
                    <div x-show="step === 2" x-cloak>
                        <h2 class="text-xl font-bold mb-4">Konfirmasi Pembayaran</h2>
                    
                        <p><strong>Nama Customer:</strong> <span x-text="customerName || '-'"></span></p>
                        <p><strong>Metode Pembayaran:</strong> <span x-text="paymentMethod || '-'"></span></p>
                        <p><strong>Tanggal Pengiriman:</strong> <span x-text="deliveryDate || '-'"></span></p>
                        <p><strong>Waktu Pengiriman:</strong> <span x-text="deliveryTime || '-'"></span></p>
                    
                        <div class="mt-4">
                            <h6 class="text-lg font-semibold mb-2">Daftar Produk:</h6>
                            <template x-for="item in cartItems" :key="item.variant_id">
                                <div class="flex flex-col text-sm border-b py-2">
                                    <div class="flex justify-between">
                                        <span>
                                            <span class="font-medium" x-text="item.product_name"></span>
                                            <span class="text-gray-500 text-xs block" x-text="item.variant_summary ?? 'Tanpa Varian'"></span>
                                        </span>
                                        <span>x <span x-text="item.quantity"></span></span>
                                        <span class="font-semibold" x-text="'Rp' + Number(item.price).toLocaleString('id-ID')"></span>
                                    </div>
                                </div>
                            </template>
                            
                        </div>
                    
                        <p class="mt-4"><strong>Total Bayar:</strong> <span x-text="grandTotalText"></span>
                        </span></p>

<!-- Bukti Transfer -->
<div x-show="paymentMethod === 'transfer'" class="mt-6">
    <h6 class="text-lg font-semibold mb-2">Bukti Transfer</h6>
    <p class="text-sm">Silakan transfer ke:</p>
    <div class="bg-gray-100 rounded p-3 text-sm mb-2">
        <strong>Bank Handai</strong><br>
        No. Rekening: <strong>1234567890</strong><br>
        a.n. PT Handai Coffee
    </div>
    <label class="block mb-1 text-sm">Upload Bukti Transfer:</label>
    <input type="file" name="payment_proof" class="input input-primary w-full" accept="image/*">
</div>

<!-- QRIS -->
<div x-show="paymentMethod === 'qris'" class="mt-6">
    <h6 class="text-lg font-semibold mb-2">Pembayaran QRIS</h6>
    <div x-data="{ showQRIS: false }" class="relative z-50">
        <!-- Thumbnail -->
        <img 
            @click="showQRIS = true" 
            src="{{ asset('assets/WhatsApp Image 2025-05-11 at 20.21.24_98dba2dc.jpg') }}" 
            alt="QRIS"
            class="w-48 mb-3 cursor-pointer rounded border border-gray-300 hover:shadow-md transition duration-200 ease-in-out"
        >
    
        <!-- Modal QRIS (Terpisah dari wizard x-data utama) -->
        <template x-teleport="body">
            <div 
                x-show="showQRIS" 
                x-transition.opacity 
                class="fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center z-[9999] p-4"
                @click.away="showQRIS = false"
                x-cloak
            >
                <!-- Close Button -->
                <button @click="showQRIS = false"
                    class="absolute top-4 right-4 text-white bg-red-600 hover:bg-red-700 p-2 rounded-full shadow-lg focus:outline-none transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
    
                <!-- Gambar QRIS -->
                <img 
                    src="{{ asset('assets/WhatsApp Image 2025-05-11 at 20.21.24_98dba2dc.jpg') }}" 
                    alt="QRIS Zoom"
                    class="max-w-full max-h-[80vh] w-auto h-auto rounded-lg shadow-lg transform transition duration-300 ease-in-out scale-100 hover:scale-105"
                    @click.stop
                >
            </div>
        </template>
    </div>
    
    
    <label class="block mb-1 text-sm">Upload Bukti Pembayaran QRIS:</label>
    <input type="file" name="payment_proof" class="input input-primary w-full" accept="image/*">
</div>


                    
                        <button @click="submitOrder($event)" class="btn btn-primary mt-4">Konfirmasi dan Bayar</button>
                    </div>
                    </div>
                    <div x-show="step === 3" x-cloak>
                        <h2 class="text-xl font-bold mb-4">Pesanan Berhasil!</h2>
                        
                        <p><strong>Order ID:</strong> <span x-text="orderId"></span></p>
                        <p><strong>Total Bayar:</strong> <span x-text="grossAmount"></span></p>
                        <p><strong>Status:</strong> Menunggu Pengiriman</p>
                        <div class="mt-6 flex flex-wrap items-center gap-3">
                            <a 
                                :href="`/customerOrder/invoice/print/${orderId}`" 
                                target="_blank" 
                                class="btn btn-outline btn-primary"
                            >
                                Cetak Invoice
                            </a>
                        
                            <a 
                                href="{{ route('customerOrder.form') }}"
                                class="btn border border-[#4CAF50] text-[#4CAF50] hover:bg-[#E8F5E9] hover:shadow font-semibold"
                            >
                                Buat Order Baru
                            </a>
                        </div>
                        
                        
                    </div>
                             
                </form>
                        

                
            </div>
             
            <!-- /Wizard Content -->
        </div>
    </div>
</div>

@endsection

@section('vendor-script')
@endsection

@section('page-script')
<script src="https://app.sandbox.midtrans.com/snap/snap.js"
    data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>
<script>
   function submitOrder(event) {
    if (event) event.preventDefault();
    const form = document.getElementById('wizard-checkout-form');
    const formData = new FormData(form);

    fetch("{{ route('customerOrder.cart.checkout') }}", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Accept": "application/json" ,
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
    if (data.success) {
        // simpan cart items di Alpine
        window.cartItems = data.cart_items;

        const checkout = document.querySelector('#checkout-wrapper');
        checkout.dispatchEvent(new CustomEvent('payment-success', {
            detail: {
                orderId: data.order_id,
                grossAmount: data.gross_amount
            },
            bubbles: true
        }));
    }
}).catch(error => {
        console.error('Checkout error:', error);
        alert(error);
    });
}

    function updateQuantity(productId, variantId, change) {
        const qtyInput = document.getElementById('qty-' + productId + '-' + variantId);
        let currentQty = parseInt(qtyInput.value) || 1;
        let newQty = currentQty + change;
        if (newQty < 1) newQty = 1;

        fetch('{{ route("customerOrder.cart.update") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    product_id: productId,
                    variant_id: variantId,
                    quantity: newQty
                })

            }).then(response => response.json()).then(data => {
                if (data.success) {
                    qtyInput.value = data.quantity;

                    const itemTotalElem = document.getElementById('item-total-' + productId + '-' + variantId);
                    if (itemTotalElem) {
                        itemTotalElem.textContent = 'Rp' + data.itemTotal;
                    }

                    document.getElementById('cart-total-items').textContent = data.cartTotalItems;
                    // Update total price in heading (only if you have #cart-total-price in Blade)
                    // document.getElementById('cart-total-price').textContent = 'Rp' + data.cartTotalPrice;

                    document.getElementById('price-subtotal').textContent = 'Rp' + data.subTotal;
                    // document.getElementById('price-discount').textContent = 'Rp' + data.discountTotal;
                    // document.getElementById('price-ppn').textContent = 'Rp' + data.ppn;
                    document.getElementById('price-grandtotal').textContent = 'Rp' + data.grandTotal;
                    // document.querySelector('#checkout-wrapper').__x.$data.grandTotalText = 'Rp' + data.grandTotal;
                    // Handle promo (kalau ada)
                    if (parseInt(data.promo_discount.replace(/\./g, '')) > 0) {
                        document.getElementById('promo-discount-section').classList.remove('hidden');
                        document.getElementById('promo-discount').textContent = 'Rp' + data.promo_discount;
                        document.getElementById('promo-code-wrapper').classList.remove('hidden');
                        document.getElementById('promo-code-text').textContent = data.promo_code;
                    } else {
                        document.getElementById('promo-discount-section').classList.add('hidden');
                        document.getElementById('promo-discount').textContent = 'Rp0';
                        document.getElementById('promo-code-wrapper').classList.add('hidden');
                        document.getElementById('promo-code-text').textContent = '-';
                    }
                }  else {
                    alert(data.error_detail || 'Gagal memperbarui quantity'); // ✅ pakai `data.error`
                }

            })
            .catch(error => alert(error.message));
            
    }
    
    
    

    function applyPromo() {
        const promoCode = document.getElementById('promo-code').value.trim();
       
        if (!promoCode) return alert('Masukkan promo code terlebih dahulu.');
    
        fetch('{{ route("customerOrder.cart.applyPromo") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    promo_code: promoCode
                })
            })
            .then(response =>  response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('price-subtotal').textContent = 'Rp' + data.subTotal;
                    // document.getElementById('price-discount').textContent = 'Rp' + data.discountTotal;
                    // document.getElementById('price-ppn').textContent = 'Rp' + ;
                    document.getElementById('price-grandtotal').textContent = 'Rp' + data.grandTotalAfterPromo;
                    // document.querySelector('#checkout-wrapper').__x.$data.grandTotalText = 'Rp' + data.grandTotal;

                    document.getElementById('promo-code-wrapper').classList.remove('hidden');
                    document.getElementById('promo-code-text').textContent = data.promo_code;
                    document.getElementById('promo-discount-section').classList.remove('hidden');
                    document.getElementById('promo-discount').textContent = '- Rp' + data.promo_discount;
                } else {
                    alert(data.error || 'Promo tidak valid');
                }
            })
            .catch(error => alert(error));


            
    }

    function removePromo() {
        fetch('{{ route("customerOrder.cart.removePromo") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.promoRemoved) {
                    document.getElementById('promo-code-wrapper').classList.add('hidden');
                    document.getElementById('promo-discount-section').classList.add('hidden');

                    document.getElementById('promo-discount').textContent = 'Rp0';
                    document.getElementById('promo-code-text').textContent = '-';
                    document.getElementById('price-grandtotal').textContent = 'Rp' + data.grandTotal;
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }
    function removeItem(productId, variantId) {
    if (!confirm("Yakin ingin menghapus item ini dari keranjang?")) return;

    fetch('{{ route("customerOrder.cart.removeItem") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            product_id: productId,
            variant_id: variantId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload halaman agar tampilan keranjang diperbarui
            location.reload();
        } else {
            alert(data.error || 'Gagal menghapus item.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menghapus item.');
    });
}


</script>
@endsection