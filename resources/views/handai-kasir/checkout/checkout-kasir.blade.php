@extends('layouts.layoutBlank')


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
    step: 1, 
    showOfferAlert: true, 
    paymentType: '', 
    cartItems: @js($cart),
    customerName: '',
    selectedCustomer: '',
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
                <form id="wizard-checkout-form">
                    @csrf                
                    <!-- CART Step -->
                    <div x-show="step === 1" x-cloak x-transition>
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
                                    <a href="{{ route('kasir.dashboard') }}" class="btn btn-soft btn-primary w-full">
                                        Add more products
                                        <i class="ti ti-chevron-right float-right"></i>
                                    </a>
                                </div>
                            </div>
                            <!-- /Left Section -->

                            <!-- Right Section -->
                            <div class="w-full xl:w-1/3 space-y-4">
                                <!-- === Customer Section === -->
<div class="border border-gray-200 rounded p-4 space-y-4">
    <h6 class="text-lg font-medium">Customer</h6>

    <!-- Dropdown Pilih Customer -->
    <select id="existingCustomer" x-model="selectedCustomer" name="customer_id" class="input input-primary w-full" 
    @change="
    isNewCustomer = selectedCustomer === 'new';
    if (!isNewCustomer) {
        const selectedOption = $event.target.options[$event.target.selectedIndex];
        customerName = selectedOption.text;
    }
"
>
    <option value="">-- Pilih Customer --</option>
    @foreach($customers as $customer)
        <option value="{{ $customer->id }}">{{ $customer->name }} - {{ $customer->contact_number }}</option>
    @endforeach
    <option value="new">+ Tambah Customer Baru</option>
</select>


            <!-- Input Customer Baru -->
        <div x-show="isNewCustomer" x-transition>
            <input type="text" x-model="customerName" name="new_name" class="input input-primary w-full mt-3" placeholder="Nama Customer">
            <input type="text" name="new_contact" class="input input-primary w-full mt-3" placeholder="Nomor Telepon">
            <input type="text" name="new_address" class="input input-primary w-full mt-3" placeholder="Alamat">
            <select name="new_gender" class="input input-primary w-full mt-3">
                <option value="">Pilih Jenis Kelamin</option>
                <option value="Laki-laki">Laki-laki</option>
                <option value="Perempuan">Perempuan</option>
            </select>
            <input type="email" name="new_email" class="input input-primary w-full mt-3" placeholder="Email (Opsional)">
        </div>

</div>
                                <div class="border border-gray-200 rounded p-4 space-y-4">
                                   <!-- Payment Type -->
<div>
    <h6 class="text-lg font-medium mb-2">Metode Pembayaran</h6>
    <div class="space-y-2">
        <label class="flex items-center gap-2">
            <input type="radio" name="payment_method" value="tunai" class="radio radio-primary" onchange="onPaymentMethodChange(this.value)" checked>
            <span>Tunai (Cash)</span>
        </label>
        <label class="flex items-center gap-2">
            <input type="radio" name="payment_method" value="non_tunai" class="radio radio-primary" onchange="onPaymentMethodChange(this.value)">
            <span>Non Tunai (Transfer/QRIS)</span>
        </label>
        <label class="flex items-center gap-2">
            <input type="radio" name="payment_method" value="campuran" class="radio radio-primary" onchange="onPaymentMethodChange(this.value)">
            <span>Campuran (Mixed)</span>
        </label>

        <div id="non-tunai-options" class="mt-2 hidden">
            <select name="non_tunai_type" id="non-tunai-type" class="input input-primary w-full">
                <option value="transfer">Transfer Bank</option>
                <option value="qris">QRIS</option>
            </select>
        </div>

        <div id="cash-section" class="mt-2">
            <label class="text-sm">Uang Diterima (Rp)</label>
            <input type="number" name="cash_received" id="cash-received" class="input input-primary w-full" min="0" step="1000" oninput="computeChange()">
            <div class="mt-2 flex gap-2 flex-wrap">
                <button type="button" class="btn btn-sm" onclick="setQuickAmount('exact')">Uang Pas</button>
                <button type="button" class="btn btn-sm" onclick="setQuickAmount(5000)">5K</button>
                <button type="button" class="btn btn-sm" onclick="setQuickAmount(10000)">10K</button>
                <button type="button" class="btn btn-sm" onclick="setQuickAmount(20000)">20K</button>
                <button type="button" class="btn btn-sm" onclick="setQuickAmount(50000)">50K</button>
                <button type="button" class="btn btn-sm" onclick="setQuickAmount(100000)">100K</button>
            </div>
            <div class="mt-2">
                <small>Kembalian: <span id="cash-change">Rp0</span></small>
            </div>
        </div>
    </div>
</div>

<!-- Delivery Date & Time -->
<div class="mt-4">
    <h6 class="text-lg font-medium mb-2">Waktu Pengiriman</h6>
    <input type="date" id="delivery-date" x-model="deliveryDate" name="delivery_date" class="input input-primary w-full mb-2">
    <input type="time" id="delivery-time" x-model="deliveryTime" name="delivery_time" class="input input-primary w-full">
</div>

<!-- Metode Pemesanan -->
<div class="border-t border-gray-200 pt-4">
    <h6 class="text-lg font-medium mb-3">Metode Pemesanan</h6>
    <div class="space-y-3">
        <label class="flex items-start gap-3 cursor-pointer">
            <input type="radio" name="metode-pemesanan" value="bayar-langsung" class="radio radio-primary mt-1" checked>
            <div>
                <span class="text-sm font-medium">Bayar Langsung</span>
                <p class="text-xs text-gray-500">Pembeli langsung melakukan pembayaran sekarang</p>
            </div>
        </label>

        <label class="flex items-start gap-3 cursor-pointer">
            <input type="radio" name="metode-pemesanan" value="piutang" class="radio radio-primary mt-1">
            <div>
                <span class="text-sm font-medium">Piutang</span>
                <p class="text-xs text-gray-500">Pembeli berhutang kepada penjual, dibayar kemudian</p>
            </div>
        </label>

        <label class="flex items-start gap-3 cursor-pointer">
            <input type="radio" name="metode-pemesanan" value="buat-pesanan" class="radio radio-primary mt-1">
            <div>
                <span class="text-sm font-medium">Buat Pesanan</span>
                <p class="text-xs text-gray-500">Pembayaran dilakukan setelah proses pemesanan selesai</p>
            </div>
        </label>

        <label class="flex items-start gap-3 cursor-pointer">
            <input type="radio" name="metode-pemesanan" value="gabung-pesanan" class="radio radio-primary mt-1">
            <div>
                <span class="text-sm font-medium">Gabungkan Pesanan</span>
                <p class="text-xs text-gray-500">Menggabungkan dengan pesanan yang sudah ada sebelumnya</p>
            </div>
        </label>
    </div>
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

                                    <!-- Additional Charges Section -->
                                    <div class="border-t border-gray-200 pt-4">
                                        <h6 class="text-lg font-medium mb-3">Biaya Tambahan</h6>
                                        
                                        <!-- Pajak (Tax) -->
                                        <div class="mb-3">
                                            <label class="flex items-center gap-2 cursor-pointer mb-2">
                                                <input type="checkbox" id="pajak-checkbox" @change="updateAdditionalCharges()" class="checkbox checkbox-primary">
                                                <span class="text-sm">Pajak</span>
                                            </label>
                                            <input type="number" id="pajak-input" @change="updateAdditionalCharges()" placeholder="Masukkan nominal pajak (Rp)" class="input input-primary w-full text-xs" min="0" :disabled="!document.getElementById('pajak-checkbox')?.checked">
                                        </div>

                                        <!-- Ongkos Kirim (Shipping) -->
                                        <div class="mb-3">
                                            <label class="flex items-center gap-2 cursor-pointer mb-2">
                                                <input type="checkbox" id="ongkos-checkbox" @change="updateAdditionalCharges()" class="checkbox checkbox-primary">
                                                <span class="text-sm">Ongkos Kirim</span>
                                            </label>
                                            <input type="number" id="ongkos-input" @change="updateAdditionalCharges()" placeholder="Masukkan nominal ongkos kirim (Rp)" class="input input-primary w-full text-xs" min="0" :disabled="!document.getElementById('ongkos-checkbox')?.checked">
                                        </div>

                                        <!-- Kemasan (Packaging) -->
                                        <div class="mb-3">
                                            <label class="flex items-center gap-2 cursor-pointer mb-2">
                                                <input type="checkbox" id="kemasan-checkbox" @change="updateAdditionalCharges()" class="checkbox checkbox-primary">
                                                <span class="text-sm">Kemasan</span>
                                            </label>
                                            <input type="number" id="kemasan-input" @change="updateAdditionalCharges()" placeholder="Masukkan nominal kemasan (Rp)" class="input input-primary w-full text-xs" min="0" :disabled="!document.getElementById('kemasan-checkbox')?.checked">
                                        </div>
                                    </div>

                                    <!-- Discount Section -->
                                    <div class="border-t border-gray-200 pt-4">
                                        <h6 class="text-lg font-medium mb-3">Diskon</h6>
                                        
                                        <!-- Discount Type Selection -->
                                        <div class="space-y-3">
                                            <!-- No Discount -->
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="radio" name="discount-type" value="none" class="radio radio-primary" @change="updateDiscount()" checked>
                                                <span class="text-sm">Tanpa Diskon</span>
                                            </label>

                                            <!-- Manual Rp Discount -->
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="radio" name="discount-type" value="manual" class="radio radio-primary" @change="updateDiscount()">
                                                <span class="text-sm">Diskon Manual (Rp)</span>
                                            </label>
                                            <input type="number" id="manual-discount-input" placeholder="Masukkan nominal diskon (Rp)" class="input input-primary w-full text-xs" min="0" @change="updateDiscount()" :disabled="document.querySelector('input[name=\"discount-type\"]:checked')?.value !== 'manual'" hidden>

                                            <!-- Percentage Discount -->
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="radio" name="discount-type" value="percentage" class="radio radio-primary" @change="updateDiscount()">
                                                <span class="text-sm">Diskon Persentase (%)</span>
                                            </label>
                                            <input type="number" id="percentage-discount-input" placeholder="Masukkan persentase diskon (0-100)" class="input input-primary w-full text-xs" min="0" max="100" @change="updateDiscount()" :disabled="document.querySelector('input[name=\"discount-type\"]:checked')?.value !== 'percentage'" hidden>

                                            <!-- Promo Code Discount -->
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="radio" name="discount-type" value="promo" class="radio radio-primary" @change="updateDiscount()">
                                                <span class="text-sm">Diskon Promo</span>
                                            </label>
                                            <select id="promo-select" name="promo_id" class="input input-primary w-full text-xs" @change="updateDiscount()" :disabled="document.querySelector('input[name=\"discount-type\"]:checked')?.value !== 'promo'" hidden>
                                                <option value="">-- Pilih Promo --</option>
                                                {{-- Will be populated by JavaScript --}}
                                            </select>
                                        </div>
                                    </div>

                                    <hr class="border-gray-200">

                                    <h6 class="text-lg font-medium mb-2">Price Details</h6>
                                    <dl class="text-sm space-y-1">
                                        <div class="flex justify-between">
                                            <dt>Sub Total</dt>
                                            <dd id="price-subtotal">Rp{{ number_format($subTotal, 0, ',', '.') }}</dd>
                                        </div>

                                        <!-- Discount Display (will show based on discount type) -->
                                        <div class="flex justify-between hidden" id="discount-display">
                                            <dt id="discount-label">Diskon</dt>
                                            <dd id="discount-value">- Rp0</dd>
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

                                        <!-- Additional Charges Display -->
                                        <div class="flex justify-between hidden" id="pajak-display">
                                            <dt>Pajak</dt>
                                            <dd id="pajak-display-value">Rp0</dd>
                                        </div>

                                        <div class="flex justify-between hidden" id="ongkos-display">
                                            <dt>Ongkos Kirim</dt>
                                            <dd id="ongkos-display-value">Rp0</dd>
                                        </div>

                                        <div class="flex justify-between hidden" id="kemasan-display">
                                            <dt>Kemasan</dt>
                                            <dd id="kemasan-display-value">Rp0</dd>
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
                    <div x-show="step === 2" x-cloak x-transition>
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
                    
                        <button @click="submitOrder($event)" class="btn btn-primary mt-4">Konfirmasi dan Bayar</button>
                    </div>
                    </div>
                    <div x-show="step === 3" x-cloak x-transition>
                        <h2 class="text-xl font-bold mb-4">Pesanan Berhasil!</h2>
                        
                        <p><strong>Order ID:</strong> <span x-text="orderId"></span></p>
                        <p><strong>Total Bayar:</strong> <span x-text="grossAmount"></span></p>
                        <p><strong>Status:</strong> Menunggu Pengiriman</p>
                        <div class="mt-6 flex flex-wrap items-center gap-3">
                            <a 
                                :href="`/kasir/invoice/print/${orderId}`" 
                                target="_blank" 
                                class="btn btn-outline btn-primary"
                            >
                                Cetak Invoice
                            </a>
                        
                            <a 
                                href="{{ route('kasir.dashboard') }}"
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

    let promoList = [];

    // Load promos on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadPromos();
    });

    function loadPromos() {
        fetch('{{ route("kasir.cart.getPromos", [], false) }}', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.promos) {
                promoList = data.promos;
                const promoSelect = document.getElementById('promo-select');
                promoSelect.innerHTML = '<option value="">-- Pilih Promo --</option>';
                data.promos.forEach(promo => {
                    const option = document.createElement('option');
                    option.value = promo.id;
                    option.textContent = `${promo.Promo_Code} - ${promo.discount_rate}% (Max: Rp${(promo.max_discount_price).toLocaleString('id-ID')})`;
                    promoSelect.appendChild(option);
                });
            }
        })
        .catch(error => console.error('Error loading promos:', error));
    }

    function updateDiscount() {
        const discountType = document.querySelector('input[name="discount-type"]:checked')?.value || 'none';
        const discountDisplay = document.getElementById('discount-display');
        const discountLabel = document.getElementById('discount-label');
        const discountValue = document.getElementById('discount-value');
        
        let discountAmount = 0;

        if (discountType === 'manual') {
            const input = document.getElementById('manual-discount-input');
            input.hidden = false;
            document.getElementById('percentage-discount-input').hidden = true;
            document.getElementById('promo-select').hidden = true;
            discountAmount = parseInt(input.value) || 0;
            discountLabel.textContent = 'Diskon Manual';
        } else if (discountType === 'percentage') {
            const input = document.getElementById('percentage-discount-input');
            input.hidden = false;
            document.getElementById('manual-discount-input').hidden = true;
            document.getElementById('promo-select').hidden = true;
            const percentage = parseInt(input.value) || 0;
            const subTotal = parseInt(document.getElementById('price-subtotal').textContent.replace(/\D/g, '')) || 0;
            discountAmount = Math.floor(subTotal * percentage / 100);
            discountLabel.textContent = `Diskon ${percentage}%`;
        } else if (discountType === 'promo') {
            const select = document.getElementById('promo-select');
            select.hidden = false;
            document.getElementById('manual-discount-input').hidden = true;
            document.getElementById('percentage-discount-input').hidden = true;
            const promoId = select.value;
            if (promoId) {
                const promo = promoList.find(p => p.id == promoId);
                if (promo) {
                    const subTotal = parseInt(document.getElementById('price-subtotal').textContent.replace(/\D/g, '')) || 0;
                    const calculated = Math.floor(subTotal * promo.discount_rate / 100);
                    discountAmount = Math.min(calculated, promo.max_discount_price);
                    discountLabel.textContent = `Diskon Promo (${promo.Promo_Code})`;
                }
            }
        } else {
            document.getElementById('manual-discount-input').hidden = true;
            document.getElementById('percentage-discount-input').hidden = true;
            document.getElementById('promo-select').hidden = true;
        }

        // Update discount display
        if (discountAmount > 0) {
            discountDisplay.classList.remove('hidden');
            discountValue.textContent = '- Rp' + discountAmount.toLocaleString('id-ID');
        } else {
            discountDisplay.classList.add('hidden');
        }

        // Update grand total
        updateGrandTotal();
    }

    function updateGrandTotal() {
        const subTotal = parseInt(document.getElementById('price-subtotal').textContent.replace(/\D/g, '')) || 0;
        
        // Get discount
        const discountType = document.querySelector('input[name="discount-type"]:checked')?.value || 'none';
        let discountAmount = 0;
        if (discountType === 'manual') {
            discountAmount = parseInt(document.getElementById('manual-discount-input')?.value) || 0;
        } else if (discountType === 'percentage') {
            const percentage = parseInt(document.getElementById('percentage-discount-input')?.value) || 0;
            discountAmount = Math.floor(subTotal * percentage / 100);
        } else if (discountType === 'promo') {
            const discountValue = document.getElementById('discount-value')?.textContent || 'Rp0';
            discountAmount = parseInt(discountValue.replace(/\D/g, '')) || 0;
        }

        // Get additional charges
        const pajakValue = (document.getElementById('pajak-checkbox')?.checked ? parseInt(document.getElementById('pajak-input')?.value) || 0 : 0);
        const ongkosValue = (document.getElementById('ongkos-checkbox')?.checked ? parseInt(document.getElementById('ongkos-input')?.value) || 0 : 0);
        const kemasinValue = (document.getElementById('kemasan-checkbox')?.checked ? parseInt(document.getElementById('kemasan-input')?.value) || 0 : 0);
        const totalAdditionalCharges = pajakValue + ongkosValue + kemasinValue;

        const grandTotal = subTotal - discountAmount + totalAdditionalCharges;
        document.getElementById('price-grandtotal').textContent = 'Rp' + Math.max(grandTotal, 0).toLocaleString('id-ID');
    }

    function updateAdditionalCharges() {
        // Get checkbox states and input values
        const pajakChecked = document.getElementById('pajak-checkbox')?.checked || false;
        const pajakValue = pajakChecked ? (parseInt(document.getElementById('pajak-input')?.value) || 0) : 0;
        
        const ongkosChecked = document.getElementById('ongkos-checkbox')?.checked || false;
        const ongkosValue = ongkosChecked ? (parseInt(document.getElementById('ongkos-input')?.value) || 0) : 0;
        
        const kemasinChecked = document.getElementById('kemasan-checkbox')?.checked || false;
        const kemasinValue = kemasinChecked ? (parseInt(document.getElementById('kemasan-input')?.value) || 0) : 0;

        // Update display
        if (pajakValue > 0) {
            document.getElementById('pajak-display').classList.remove('hidden');
            document.getElementById('pajak-display-value').textContent = 'Rp' + pajakValue.toLocaleString('id-ID');
        } else {
            document.getElementById('pajak-display').classList.add('hidden');
        }

        if (ongkosValue > 0) {
            document.getElementById('ongkos-display').classList.remove('hidden');
            document.getElementById('ongkos-display-value').textContent = 'Rp' + ongkosValue.toLocaleString('id-ID');
        } else {
            document.getElementById('ongkos-display').classList.add('hidden');
        }

        if (kemasinValue > 0) {
            document.getElementById('kemasan-display').classList.remove('hidden');
            document.getElementById('kemasan-display-value').textContent = 'Rp' + kemasinValue.toLocaleString('id-ID');
        } else {
            document.getElementById('kemasan-display').classList.add('hidden');
        }

        updateGrandTotal();
    }

   function submitOrder(event) {
    if (event) event.preventDefault();
    const form = document.getElementById('wizard-checkout-form');
    const formData = new FormData(form);

    // Add additional charges to form data
    const pajakChecked = document.getElementById('pajak-checkbox')?.checked || false;
    const pajakValue = pajakChecked ? (parseInt(document.getElementById('pajak-input')?.value) || 0) : 0;
    
    const ongkosChecked = document.getElementById('ongkos-checkbox')?.checked || false;
    const ongkosValue = ongkosChecked ? (parseInt(document.getElementById('ongkos-input')?.value) || 0) : 0;
    
    const kemasinChecked = document.getElementById('kemasan-checkbox')?.checked || false;
    const kemasinValue = kemasinChecked ? (parseInt(document.getElementById('kemasan-input')?.value) || 0) : 0;

    formData.append('additional_charges[pajak]', pajakValue);
    formData.append('additional_charges[ongkos_kirim]', ongkosValue);
    formData.append('additional_charges[kemasan]', kemasinValue);

    fetch('{{ route("kasir.cart.checkout", [], false) }}', {
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

        fetch('{{ route("kasir.cart.update", [], false) }}', {
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
    
        fetch('{{ route("kasir.cart.applyPromo", [], false) }}', {
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
        fetch('{{ route("kasir.cart.removePromo", [], false) }}', {
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

    fetch('{{ route("kasir.cart.removeItem", [], false) }}', {
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
        <script>
            function onPaymentMethodChange(value) {
                // Kasir
                const nonTunai = document.getElementById('non-tunai-options');
                const cashSection = document.getElementById('cash-section');
                if (nonTunai) {
                    if (value === 'non_tunai') {
                        nonTunai.classList.remove('hidden');
                        cashSection.classList.add('hidden');
                    } else if (value === 'tunai') {
                        nonTunai.classList.add('hidden');
                        cashSection.classList.remove('hidden');
                    } else if (value === 'campuran') {
                        nonTunai.classList.remove('hidden');
                        cashSection.classList.remove('hidden');
                    }
                }

                // POS (if present on page)
                const nonTunaiPos = document.getElementById('non-tunai-options-pos');
                const cashSectionPos = document.getElementById('cash-section-pos');
                if (nonTunaiPos) {
                    if (value === 'non_tunai') {
                        nonTunaiPos.classList.remove('hidden');
                        cashSectionPos.classList.add('hidden');
                    } else if (value === 'tunai') {
                        nonTunaiPos.classList.add('hidden');
                        cashSectionPos.classList.remove('hidden');
                    } else if (value === 'campuran') {
                        nonTunaiPos.classList.remove('hidden');
                        cashSectionPos.classList.remove('hidden');
                    }
                }

                computeChange();
                computeChangePos();
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

            // POS variants
            function setQuickAmountPos(amount) {
                const cashInput = document.getElementById('cash-received-pos');
                if (!cashInput) return;
                if (amount === 'exact') {
                    const grand = parseInt(document.getElementById('price-grandtotal').textContent.replace(/\D/g, '')) || 0;
                    cashInput.value = grand;
                } else {
                    cashInput.value = amount;
                }
                computeChangePos();
            }

            function computeChangePos() {
                const cashInput = document.getElementById('cash-received-pos');
                const changeElem = document.getElementById('cash-change-pos');
                if (!cashInput || !changeElem) return;
                const cash = parseInt(cashInput.value) || 0;
                const grand = parseInt(document.getElementById('price-grandtotal').textContent.replace(/\D/g, '')) || 0;
                const change = Math.max(cash - grand, 0);
                changeElem.textContent = 'Rp' + change.toLocaleString('id-ID');
            }
        </script>
        alert('Terjadi kesalahan saat menghapus item.');
    });
}


</script>
@endsection