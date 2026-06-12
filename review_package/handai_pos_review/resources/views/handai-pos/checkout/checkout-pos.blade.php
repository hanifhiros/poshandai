    @extends('layouts.layoutBlank')

@section('title', 'POS - Checkout')

@section('page-style')
{{-- Sync POS dark mode preference to layoutBlank's globalData theme --}}
<script>
    (function() {
        var posDark = localStorage.getItem('pos_dark_mode') === 'true';
        if (posDark) {
            localStorage.setItem('theme', 'posdark');
        } else if (localStorage.getItem('theme') === 'posdark') {
            localStorage.setItem('theme', 'light');
        }
    })();
</script>

{{-- Dark Mode Styles --}}
@include('handai-pos.layouts.components.dark-mode-styles')
@vite('resources/css/handai-pos-checkout.css')
@endsection

@section('content')
<div x-data="posCheckout()" class="min-h-screen bg-gray-50" x-cloak>

    {{-- ============ TOP BAR ============ --}}
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ route('pos.dashboard') }}" class="flex items-center gap-2 text-gray-600 hover:text-green-700 transition font-medium">
                <i class="ti ti-arrow-left text-xl"></i>
                <span class="hidden sm:inline">Kembali ke Menu</span>
            </a>
            <a href="{{ route('pos.dashboard') }}" class="flex items-center gap-2.5">
                <img src="{{ asset('assets/logo.png') }}" alt="Handai Coffee" class="h-9 w-auto object-contain">
                <span class="text-lg font-bold text-gray-800 hidden sm:inline">Handai Coffee</span>
            </a>
            <div class="w-24"></div>
        </div>
    </div>

    {{-- ============ STEP INDICATOR ============ --}}
    <div class="max-w-lg mx-auto px-4 py-6">
        <div class="flex items-center justify-center">
            {{-- Step 1 --}}
            <div class="flex items-center gap-2">
                <div class="step-circle" :class="step >= 1 ? 'step-active' : 'step-inactive'" @click="step >= 1 && (step = 1)">1</div>
                <span class="text-sm font-medium hidden sm:inline" :class="step >= 1 ? 'text-green-700' : 'text-gray-400'">Keranjang</span>
            </div>
            <div class="step-line flex-1 mx-3" :class="step >= 2 ? 'bg-green-500' : 'bg-gray-200'"></div>
            {{-- Step 2 --}}
            <div class="flex items-center gap-2">
                <div class="step-circle" :class="step >= 2 ? 'step-active' : 'step-inactive'">2</div>
                <span class="text-sm font-medium hidden sm:inline" :class="step >= 2 ? 'text-green-700' : 'text-gray-400'">Pembayaran</span>
            </div>
            <div class="step-line flex-1 mx-3" :class="step >= 3 ? 'bg-green-500' : 'bg-gray-200'"></div>
            {{-- Step 3 --}}
            <div class="flex items-center gap-2">
                <div class="step-circle" :class="step >= 3 ? 'step-active' : 'step-inactive'">3</div>
                <span class="text-sm font-medium hidden sm:inline" :class="step >= 3 ? 'text-green-700' : 'text-gray-400'">Selesai</span>
            </div>
        </div>
    </div>

    {{-- ============ MAIN CONTENT ============ --}}
    <div class="max-w-7xl mx-auto px-4 pb-10">

        {{-- ==================== STEP 1: KERANJANG ==================== --}}
        <div x-show="step === 1" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="flex flex-col lg:flex-row gap-6">

                {{-- LEFT: Cart Items --}}
                <div class="flex-1">
                    <div class="bg-white rounded-xl shadow-sm p-5 sm:p-6">
                        <div class="flex items-center justify-between mb-5">
                            <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                                <i class="ti ti-shopping-cart text-green-600"></i>
                                Keranjang
                                <span class="text-base font-normal text-gray-400" x-text="'(' + totalItems + ' item)'"></span>
                            </h2>
                            <a href="{{ route('pos.dashboard') }}" class="btn btn-sm btn-soft btn-primary gap-1">
                                <i class="ti ti-plus"></i> Tambah
                            </a>
                        </div>

                        {{-- Cart Items List --}}
                        <div class="space-y-3" x-show="cartItems.length > 0">
                            <template x-for="(item, idx) in cartItems" :key="item.variant_id">
                                <div class="border border-gray-100 rounded-xl p-4 hover:border-green-200 hover:shadow-sm transition-all">
                                    <div class="flex items-start gap-4">
                                        {{-- Product info --}}
                                        <div class="flex-1 min-w-0">
                                            <h3 class="font-semibold text-gray-800 truncate" x-text="item.product_name"></h3>
                                            <p class="text-xs text-gray-400 mt-0.5" x-text="item.variant_summary || 'Standar'"></p>
                                            <p class="text-green-700 font-bold text-sm mt-1" x-text="'Rp' + Number(item.price).toLocaleString('id-ID')"></p>
                                        </div>

                                        {{-- Quantity controls --}}
                                        <div class="flex items-center gap-1">
                                            <button type="button"
                                                class="w-9 h-9 rounded-lg flex items-center justify-center text-lg transition"
                                                :class="item.quantity <= 1 ? 'bg-gray-100 text-gray-300 cursor-not-allowed' : 'bg-gray-100 hover:bg-red-50 hover:text-red-500 text-gray-500 cursor-pointer'"
                                                @click="decreaseQty(item)" :disabled="item.quantity <= 1">
                                                <i class="ti ti-minus"></i>
                                            </button>
                                            <span class="w-10 text-center font-bold text-lg" x-text="item.quantity"></span>
                                            <button type="button"
                                                class="w-9 h-9 rounded-lg bg-green-50 hover:bg-green-100 text-green-600 flex items-center justify-center text-lg cursor-pointer transition"
                                                @click="increaseQty(item)">
                                                <i class="ti ti-plus"></i>
                                            </button>
                                        </div>

                                        {{-- Total & Delete --}}
                                        <div class="text-right flex flex-col items-end gap-1 min-w-[90px]">
                                            <p class="font-bold text-gray-800" x-text="'Rp' + Number(item.price * item.quantity).toLocaleString('id-ID')"></p>
                                            <button type="button" class="text-red-300 hover:text-red-500 transition cursor-pointer mt-1" @click="removeItem(item)" title="Hapus item">
                                                <i class="ti ti-trash text-lg"></i>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Per-item note --}}
                                    <div class="mt-2 pt-2 border-t border-dashed border-gray-100">
                                        <button type="button"
                                            class="text-xs text-gray-400 hover:text-green-600 transition cursor-pointer flex items-center gap-1"
                                            @click="item.showNote = !item.showNote">
                                            <i class="ti ti-note text-sm"></i>
                                            <span x-text="item.note ? 'Edit catatan' : 'Tambah catatan'"></span>
                                        </button>
                                        <div x-show="item.showNote" x-transition class="mt-2">
                                            <input type="text" x-model="item.note"
                                                placeholder="Contoh: tanpa es, less sugar..."
                                                class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-400 outline-none"
                                                @change="saveItemNote(item)">
                                        </div>
                                        <p x-show="item.note && !item.showNote" class="text-xs text-gray-500 mt-1 italic" x-text="'📝 ' + item.note"></p>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Empty state --}}
                        <div x-show="cartItems.length === 0" class="text-center py-16">
                            <i class="ti ti-shopping-cart-off text-7xl text-gray-200"></i>
                            <p class="text-gray-400 mt-4 text-lg">Keranjang masih kosong</p>
                            <a href="{{ route('pos.dashboard') }}" class="btn btn-primary mt-6 gap-2">
                                <i class="ti ti-plus"></i> Mulai Belanja
                            </a>
                        </div>
                    </div>
                </div>

                {{-- RIGHT: Summary Panel --}}
                <div class="w-full lg:w-[400px]" x-show="cartItems.length > 0">
                    <div class="bg-white rounded-xl shadow-sm p-5 sm:p-6 lg:sticky lg:top-4 space-y-4">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                            <i class="ti ti-receipt text-green-600"></i> Ringkasan Pesanan
                        </h3>

                        {{-- Discount (collapsible) --}}
                        <div x-data="{ open: false }" class="border border-gray-100 rounded-lg">
                            <button @click="open = !open" type="button" class="flex items-center justify-between w-full px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-lg transition cursor-pointer">
                                <span class="flex items-center gap-2"><i class="ti ti-discount-2 text-orange-500"></i> Diskon</span>
                                <i class="ti transition-transform" :class="open ? 'ti-chevron-up' : 'ti-chevron-down'"></i>
                            </button>
                            <div x-show="open" x-transition class="px-4 pb-4 space-y-3">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" value="none" x-model="discountType" class="radio radio-sm radio-success">
                                    <span class="text-sm">Tanpa Diskon</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" value="manual" x-model="discountType" class="radio radio-sm radio-success">
                                    <span class="text-sm">Manual (Rp)</span>
                                </label>
                                <input x-show="discountType === 'manual'" x-transition type="number" x-model.number="manualDiscount"
                                    placeholder="Nominal diskon" class="w-full text-sm border rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 outline-none" min="0">

                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" value="percentage" x-model="discountType" class="radio radio-sm radio-success">
                                    <span class="text-sm">Persentase (%)</span>
                                </label>
                                <input x-show="discountType === 'percentage'" x-transition type="number" x-model.number="percentageDiscount"
                                    placeholder="Persentase (0-100)" class="w-full text-sm border rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 outline-none" min="0" max="100">

                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" value="promo" x-model="discountType" class="radio radio-sm radio-success">
                                    <span class="text-sm">Promo</span>
                                </label>
                                <select x-show="discountType === 'promo'" x-transition x-model="selectedPromoId"
                                    class="w-full text-sm border rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 outline-none">
                                    <option value="">-- Pilih Promo --</option>
                                    <template x-for="promo in promoList" :key="promo.id">
                                        <option :value="promo.id" x-text="promo.Promo_Code + ' - ' + promo.discount_rate + '% (Max Rp' + Number(promo.max_discount_price).toLocaleString('id-ID') + ')'"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        {{-- Additional Charges (collapsible) --}}
                        <div x-data="{ open: false }" class="border border-gray-100 rounded-lg">
                            <button @click="open = !open" type="button" class="flex items-center justify-between w-full px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-lg transition cursor-pointer">
                                <span class="flex items-center gap-2"><i class="ti ti-receipt-tax text-blue-500"></i> Biaya Tambahan</span>
                                <i class="ti transition-transform" :class="open ? 'ti-chevron-up' : 'ti-chevron-down'"></i>
                            </button>
                            <div x-show="open" x-transition class="px-4 pb-4 space-y-3">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" x-model="pajakEnabled" class="checkbox checkbox-sm checkbox-success">
                                    <span class="text-sm">Pajak</span>
                                </label>
                                <input x-show="pajakEnabled" x-transition type="number" x-model.number="pajakAmount"
                                    placeholder="Nominal pajak (Rp)" class="w-full text-sm border rounded-lg px-3 py-2 outline-none" min="0">

                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" x-model="ongkosEnabled" class="checkbox checkbox-sm checkbox-success">
                                    <span class="text-sm">Ongkos Kirim</span>
                                </label>
                                <input x-show="ongkosEnabled" x-transition type="number" x-model.number="ongkosAmount"
                                    placeholder="Nominal ongkir (Rp)" class="w-full text-sm border rounded-lg px-3 py-2 outline-none" min="0">

                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" x-model="kemasanEnabled" class="checkbox checkbox-sm checkbox-success">
                                    <span class="text-sm">Kemasan</span>
                                </label>
                                <input x-show="kemasanEnabled" x-transition type="number" x-model.number="kemasanAmount"
                                    placeholder="Nominal kemasan (Rp)" class="w-full text-sm border rounded-lg px-3 py-2 outline-none" min="0">
                            </div>
                        </div>

                        {{-- Overall note --}}
                        <div>
                            <label class="text-sm font-medium text-gray-600 flex items-center gap-1 mb-1">
                                <i class="ti ti-notes text-gray-400"></i> Catatan Pesanan
                            </label>
                            <textarea x-model="overallNote" rows="2"
                                class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 outline-none resize-none"
                                placeholder="Catatan untuk keseluruhan pesanan..."></textarea>
                        </div>

                        <hr class="border-gray-100">

                        {{-- Price breakdown --}}
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Subtotal</span>
                                <span class="font-medium" x-text="'Rp' + subtotal.toLocaleString('id-ID')"></span>
                            </div>
                            <div class="flex justify-between text-green-600" x-show="discountAmount > 0" x-transition>
                                <span>Diskon</span>
                                <span x-text="'- Rp' + discountAmount.toLocaleString('id-ID')"></span>
                            </div>
                            <div class="flex justify-between text-blue-600" x-show="totalCharges > 0" x-transition>
                                <span>Biaya Tambahan</span>
                                <span x-text="'+ Rp' + totalCharges.toLocaleString('id-ID')"></span>
                            </div>
                            <hr class="border-gray-100">
                            <div class="flex justify-between text-lg font-bold">
                                <span>Total</span>
                                <span class="text-green-700" x-text="'Rp' + grandTotal.toLocaleString('id-ID')"></span>
                            </div>
                        </div>

                        {{-- Proceed button --}}
                        <button type="button"
                            class="w-full py-3 rounded-xl font-bold text-white text-lg transition shadow-lg shadow-green-200 cursor-pointer"
                            :class="cartItems.length > 0 ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-300 cursor-not-allowed'"
                            :disabled="cartItems.length === 0"
                            @click="step = 2">
                            Lanjut ke Pembayaran <i class="ti ti-arrow-right ml-1"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================== STEP 2: PEMBAYARAN ==================== --}}
        <div x-show="step === 2" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="flex flex-col lg:flex-row gap-6">

                {{-- LEFT: Customer + Payment --}}
                <div class="flex-1 space-y-6">

                    {{-- Customer Section --}}
                    <div class="bg-white rounded-xl shadow-sm p-5 sm:p-6">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2 mb-4">
                            <i class="ti ti-user text-green-600"></i> Nama Pelanggan
                            <span class="text-xs font-normal text-gray-400">(opsional)</span>
                        </h3>

                        <div class="space-y-3">
                            {{-- Customer type selection --}}
                            <div class="flex flex-wrap gap-2">
                                <button type="button" @click="customerType = 'none'"
                                    class="px-4 py-2 rounded-lg border text-sm font-medium transition cursor-pointer"
                                    :class="customerType === 'none' ? 'border-green-500 bg-green-50 text-green-700' : 'border-gray-200 text-gray-500 hover:bg-gray-50'">
                                    Tanpa Nama
                                </button>
                                <button type="button" @click="customerType = 'existing'"
                                    class="px-4 py-2 rounded-lg border text-sm font-medium transition cursor-pointer"
                                    :class="customerType === 'existing' ? 'border-green-500 bg-green-50 text-green-700' : 'border-gray-200 text-gray-500 hover:bg-gray-50'">
                                    <i class="ti ti-search mr-1"></i> Cari Customer
                                </button>
                                <button type="button" @click="customerType = 'manual'"
                                    class="px-4 py-2 rounded-lg border text-sm font-medium transition cursor-pointer"
                                    :class="customerType === 'manual' ? 'border-green-500 bg-green-50 text-green-700' : 'border-gray-200 text-gray-500 hover:bg-gray-50'">
                                    <i class="ti ti-edit mr-1"></i> Ketik Manual
                                </button>
                                <button type="button" @click="customerType = 'new'"
                                    class="px-4 py-2 rounded-lg border text-sm font-medium transition cursor-pointer"
                                    :class="customerType === 'new' ? 'border-green-500 bg-green-50 text-green-700' : 'border-gray-200 text-gray-500 hover:bg-gray-50'">
                                    <i class="ti ti-user-plus mr-1"></i> Customer Baru
                                </button>
                            </div>

                            {{-- Existing customer dropdown --}}
                            <div x-show="customerType === 'existing'" x-transition>
                                <select x-model="selectedCustomerId" class="w-full border rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-green-200 outline-none">
                                    <option value="">-- Pilih Customer --</option>
                                    @foreach($customers as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }} {{ $c->contact_number ? '- '.$c->contact_number : '' }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Manual name --}}
                            <div x-show="customerType === 'manual'" x-transition>
                                <input type="text" x-model="manualCustomerName" placeholder="Ketik nama pelanggan..."
                                    class="w-full border rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-green-200 outline-none">
                            </div>

                            {{-- New customer form --}}
                            <div x-show="customerType === 'new'" x-transition class="space-y-2">
                                <input type="text" x-model="newCustomer.name" placeholder="Nama lengkap *"
                                    class="w-full border rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-green-200 outline-none">
                                <input type="text" x-model="newCustomer.nickname" placeholder="Nama panggilan"
                                    class="w-full border rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-green-200 outline-none">
                                <input type="text" x-model="newCustomer.contact" placeholder="Nomor telepon *"
                                    class="w-full border rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-green-200 outline-none">
                            </div>
                        </div>
                    </div>

                    {{-- Payment Method --}}
                    <div class="bg-white rounded-xl shadow-sm p-5 sm:p-6">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2 mb-4">
                            <i class="ti ti-wallet text-green-600"></i> Metode Pembayaran
                        </h3>

                        {{-- 3 cards --}}
                        <div class="grid grid-cols-3 gap-3 mb-6">
                            <div class="card-payment border-2 rounded-xl p-4 text-center"
                                :class="paymentMethod === 'tunai' ? 'selected border-green-500' : 'border-gray-200'"
                                @click="paymentMethod = 'tunai'">
                                <i class="ti ti-cash text-3xl" :class="paymentMethod === 'tunai' ? 'text-green-600' : 'text-gray-400'"></i>
                                <p class="font-semibold mt-2 text-sm" :class="paymentMethod === 'tunai' ? 'text-green-700' : 'text-gray-600'">Tunai</p>
                                <p class="text-xs text-gray-400 mt-1">Bayar cash</p>
                            </div>
                            <div class="card-payment border-2 rounded-xl p-4 text-center"
                                :class="paymentMethod === 'non_tunai' ? 'selected border-green-500' : 'border-gray-200'"
                                @click="paymentMethod = 'non_tunai'">
                                <i class="ti ti-credit-card text-3xl" :class="paymentMethod === 'non_tunai' ? 'text-green-600' : 'text-gray-400'"></i>
                                <p class="font-semibold mt-2 text-sm" :class="paymentMethod === 'non_tunai' ? 'text-green-700' : 'text-gray-600'">Non Tunai</p>
                                <p class="text-xs text-gray-400 mt-1">Transfer / QRIS</p>
                            </div>
                            <div class="card-payment border-2 rounded-xl p-4 text-center"
                                :class="paymentMethod === 'campuran' ? 'selected border-green-500' : 'border-gray-200'"
                                @click="paymentMethod = 'campuran'">
                                <i class="ti ti-arrows-exchange text-3xl" :class="paymentMethod === 'campuran' ? 'text-green-600' : 'text-gray-400'"></i>
                                <p class="font-semibold mt-2 text-sm" :class="paymentMethod === 'campuran' ? 'text-green-700' : 'text-gray-600'">Campuran</p>
                                <p class="text-xs text-gray-400 mt-1">Split bayar</p>
                            </div>
                        </div>

                        {{-- Non-tunai type --}}
                        <div x-show="paymentMethod === 'non_tunai' || paymentMethod === 'campuran'" x-transition class="mb-4">
                            <label class="text-sm font-medium text-gray-600 mb-1 block">Tipe Non Tunai</label>
                            <select x-model="nonTunaiType" class="w-full border rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-green-200 outline-none">
                                <option value="transfer">Transfer Bank</option>
                                <option value="qris">QRIS</option>
                            </select>
                        </div>

                        {{-- Cash section --}}
                        <div x-show="paymentMethod === 'tunai' || paymentMethod === 'campuran'" x-transition class="space-y-3">
                            <label class="text-sm font-medium text-gray-600 block">Uang Diterima</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">Rp</span>
                                <input type="number" x-model.number="cashReceived"
                                    class="w-full border rounded-lg pl-9 pr-3 py-3 text-lg font-bold focus:ring-2 focus:ring-green-200 outline-none"
                                    min="0" step="1000" placeholder="0">
                            </div>

                            {{-- Quick amount buttons --}}
                            <div class="flex flex-wrap gap-2">
                                <button type="button" class="quick-btn btn btn-sm btn-outline btn-success" @click="cashReceived = grandTotal">Uang Pas</button>
                                <button type="button" class="quick-btn btn btn-sm btn-outline" @click="cashReceived = 5000">5K</button>
                                <button type="button" class="quick-btn btn btn-sm btn-outline" @click="cashReceived = 10000">10K</button>
                                <button type="button" class="quick-btn btn btn-sm btn-outline" @click="cashReceived = 20000">20K</button>
                                <button type="button" class="quick-btn btn btn-sm btn-outline" @click="cashReceived = 50000">50K</button>
                                <button type="button" class="quick-btn btn btn-sm btn-outline" @click="cashReceived = 100000">100K</button>
                            </div>

                            {{-- Change display --}}
                            <div class="bg-green-50 rounded-lg p-3 flex items-center justify-between">
                                <span class="text-sm text-gray-600">Kembalian</span>
                                <span class="font-bold text-lg" :class="changeAmount >= 0 ? 'text-green-700' : 'text-red-500'"
                                    x-text="'Rp' + changeAmount.toLocaleString('id-ID')"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Navigation --}}
                    <div class="flex items-center justify-between">
                        <button type="button" class="btn btn-outline gap-2 cursor-pointer" @click="step = 1">
                            <i class="ti ti-arrow-left"></i> Kembali
                        </button>
                        <button type="button"
                            class="btn text-white px-8 py-3 rounded-xl font-bold text-base gap-2 shadow-lg shadow-green-200 cursor-pointer"
                            :class="canProceedPayment ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-300 cursor-not-allowed'"
                            :disabled="!canProceedPayment || isProcessing"
                            @click="openConfirmModal()">
                            <i class="ti ti-check"></i> Konfirmasi & Bayar
                        </button>
                    </div>
                </div>

                {{-- RIGHT: Order Summary --}}
                <div class="w-full lg:w-[360px]">
                    <div class="bg-white rounded-xl shadow-sm p-5 sm:p-6 lg:sticky lg:top-4">
                        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="ti ti-list-check text-green-600"></i> Ringkasan
                        </h3>

                        <div class="space-y-3 max-h-[300px] overflow-y-auto mb-4">
                            <template x-for="item in cartItems" :key="item.variant_id">
                                <div class="flex justify-between text-sm">
                                    <div class="flex-1 min-w-0">
                                        <p class="truncate text-gray-700" x-text="item.product_name"></p>
                                        <p class="text-xs text-gray-400" x-text="(item.variant_summary || 'Standar') + ' x' + item.quantity"></p>
                                    </div>
                                    <span class="font-medium ml-3 whitespace-nowrap" x-text="'Rp' + Number(item.price * item.quantity).toLocaleString('id-ID')"></span>
                                </div>
                            </template>
                        </div>

                        <hr class="border-gray-100 my-3">

                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Subtotal</span>
                                <span x-text="'Rp' + subtotal.toLocaleString('id-ID')"></span>
                            </div>
                            <div class="flex justify-between text-green-600" x-show="discountAmount > 0">
                                <span>Diskon</span>
                                <span x-text="'- Rp' + discountAmount.toLocaleString('id-ID')"></span>
                            </div>
                            <div class="flex justify-between text-blue-600" x-show="totalCharges > 0">
                                <span>Biaya Tambahan</span>
                                <span x-text="'+ Rp' + totalCharges.toLocaleString('id-ID')"></span>
                            </div>
                            <hr class="border-gray-100">
                            <div class="flex justify-between text-lg font-bold">
                                <span>Total</span>
                                <span class="text-green-700" x-text="'Rp' + grandTotal.toLocaleString('id-ID')"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================== STEP 3: SELESAI ==================== --}}
        <div x-show="step === 3" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <div class="max-w-2xl mx-auto">
                <div class="bg-white rounded-2xl shadow-lg p-8 text-center">

                    {{-- Success icon --}}
                    <div class="w-20 h-20 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-4">
                        <i class="ti ti-check text-4xl text-green-600"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-1">Pesanan Berhasil!</h2>
                    <p class="text-gray-500 mb-6">Transaksi telah diproses</p>

                    {{-- Top info grid --}}
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6 text-left">
                        <div class="bg-gray-50 rounded-lg p-4 flex items-start gap-2">
                            <i class="ti ti-file-invoice text-green-600 text-xl"></i>
                            <div>
                                <p class="text-xs text-gray-400">Invoice</p>
                                <p class="font-bold text-lg text-green-700">#<span x-text="orderId"></span></p>
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4 flex items-start gap-2">
                            <i class="ti ti-user text-green-600 text-xl"></i>
                            <div>
                                <p class="text-xs text-gray-400">Customer</p>
                                <p class="font-bold text-lg" x-text="customerDisplayName"></p>
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4 flex items-start gap-2">
                            <i class="ti ti-calendar text-green-600 text-xl"></i>
                            <div>
                                <p class="text-xs text-gray-400">Tanggal</p>
                                <p class="font-bold text-lg" x-text="formattedDate"></p>
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4 flex items-start gap-2">
                            <i class="ti ti-clock text-green-600 text-xl"></i>
                            <div>
                                <p class="text-xs text-gray-400">Waktu</p>
                                <p class="font-bold text-lg" x-text="formattedTime"></p>
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4 flex items-start gap-2">
                            <i class="ti ti-user-check text-green-600 text-xl"></i>
                            <div>
                                <p class="text-xs text-gray-400">Dilayani oleh</p>
                                <p class="font-bold text-lg" x-text="cashierName"></p>
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4 flex items-start gap-2">
                            <i class="ti ti-wallet text-green-600 text-xl"></i>
                            <div>
                                <p class="text-xs text-gray-400">Metode Bayar</p>
                                <p class="font-bold text-lg" x-text="paymentMethodLabel"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Totals breakdown --}}
                    <div class="bg-gray-50 rounded-lg p-4 mb-6 text-left max-w-md mx-auto">
                        <div class="flex justify-between">
                            <span>Total</span>
                            <span class="font-bold" x-text="'Rp' + Number(grossAmount).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Dibayar</span>
                            <span class="font-bold" x-text="'Rp' + Number(cashReceived || grandTotal).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Kembalian</span>
                            <span class="font-bold text-green-700" x-text="'Rp' + changeAmount.toLocaleString('id-ID')"></span>
                        </div>
                    </div>

                    {{-- Item summary --}}
                    <div class="text-left bg-gray-50 rounded-lg p-4 mb-6">
                        <h4 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
                            <i class="ti ti-package text-green-600"></i> Detail Pesanan
                        </h4>
                        <div class="space-y-2">
                            <template x-for="item in orderItems" :key="item.variant_id">
                                <div class="flex justify-between text-sm border-b border-gray-100 pb-2">
                                    <div>
                                        <span class="text-gray-700" x-text="item.product_name"></span>
                                        <span class="text-gray-400 ml-1" x-text="'x' + item.quantity"></span>
                                        <span class="text-xs text-gray-400 block" x-text="item.note ? '📝 ' + item.note : ''"></span>
                                    </div>
                                    <span class="font-medium" x-text="'Rp' + Number(item.price * item.quantity).toLocaleString('id-ID')"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Action buttons --}}
                    <div class="flex flex-wrap justify-center gap-3">
                        <a :href="'/pos/invoice/print/' + orderId" target="_blank"
                            class="btn btn-outline btn-success gap-2 px-6">
                            <i class="ti ti-printer"></i> Cetak Struk
                        </a>
                        <a :href="'/pos/invoice/print/' + orderId" target="_blank"
                            class="btn btn-outline gap-2 px-6">
                            <i class="ti ti-eye"></i> Lihat Detail
                        </a>
                        <a href="{{ route('pos.dashboard') }}"
                            class="btn bg-green-600 hover:bg-green-700 text-white gap-2 px-6">
                            <i class="ti ti-plus"></i> Order Baru
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ============ CONFIRMATION MODAL ============ --}}
    <template x-teleport="body">
        <div x-show="showConfirmModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="confirm-overlay" @keydown.escape.window="!isProcessing && (showConfirmModal = false)">

            <div x-show="showConfirmModal" x-transition:enter="transition ease-out duration-200 delay-75" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95 translate-y-4" class="confirm-card" @click.outside="!isProcessing && (showConfirmModal = false)">

                {{-- Header --}}
                <div class="confirm-header">
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="ti ti-cash text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold">Konfirmasi Pembayaran</h3>
                    <p class="text-sm text-green-100 mt-1">Pastikan pembayaran sudah diterima</p>
                </div>

                {{-- Body --}}
                <div class="confirm-body">
                    {{-- Grand Total --}}
                    <div class="text-center py-4 mb-4 bg-gray-50 rounded-xl">
                        <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Total Pembayaran</p>
                        <p class="text-3xl font-extrabold text-gray-800" x-text="'Rp' + grandTotal.toLocaleString('id-ID')"></p>
                    </div>

                    {{-- Payment Method --}}
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl mb-4">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center" :class="{
                            'bg-green-100 text-green-600': paymentMethod === 'tunai',
                            'bg-blue-100 text-blue-600': paymentMethod === 'non_tunai',
                            'bg-purple-100 text-purple-600': paymentMethod === 'campuran'
                        }">
                            <i class="text-xl" :class="{
                                'ti ti-cash': paymentMethod === 'tunai',
                                'ti ti-credit-card': paymentMethod === 'non_tunai',
                                'ti ti-arrows-exchange': paymentMethod === 'campuran'
                            }"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-700" x-text="paymentMethod === 'tunai' ? 'Tunai (Cash)' : paymentMethod === 'non_tunai' ? 'Non Tunai (' + (nonTunaiType === 'qris' ? 'QRIS' : 'Transfer Bank') + ')' : 'Campuran'"></p>
                            <p class="text-xs text-gray-400" x-show="paymentMethod === 'tunai' || paymentMethod === 'campuran'" x-text="'Diterima: Rp' + Number(cashReceived || 0).toLocaleString('id-ID') + '  •  Kembalian: Rp' + changeAmount.toLocaleString('id-ID')"></p>
                        </div>
                    </div>

                    {{-- Items Summary --}}
                    <div class="border border-gray-100 rounded-xl p-3 max-h-[160px] overflow-y-auto">
                        <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-2">Item (<span x-text="totalItems"></span>)</p>
                        <template x-for="item in cartItems" :key="item.variant_id">
                            <div class="flex justify-between text-sm py-1.5 border-b border-gray-50 last:border-0">
                                <span class="text-gray-600 truncate flex-1 mr-2" x-text="item.product_name + ' x' + item.quantity"></span>
                                <span class="font-medium text-gray-800 whitespace-nowrap" x-text="'Rp' + Number(item.price * item.quantity).toLocaleString('id-ID')"></span>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="confirm-footer">
                    <button type="button" class="btn btn-outline flex-1 py-3 rounded-xl font-semibold cursor-pointer hover:bg-gray-100" :disabled="isProcessing" @click="showConfirmModal = false">
                        <i class="ti ti-x mr-1"></i> ← Batal
                    </button>
                    <button type="button" class="btn flex-[2] py-3 rounded-xl font-bold text-white text-base cursor-pointer" :class="isProcessing ? 'bg-gray-400 cursor-not-allowed' : 'bg-green-600 hover:bg-green-700 shadow-lg shadow-green-200'" :disabled="isProcessing" @click="confirmAndPay()">
                        <template x-if="isProcessing">
                            <span class="flex items-center gap-2"><span class="spinner"></span> Memproses...</span>
                        </template>
                        <template x-if="!isProcessing">
                            <span class="flex items-center gap-2"><i class="ti ti-check"></i> Ya, Pembayaran Sudah Diterima</span>
                        </template>
                    </button>
                </div>
            </div>
        </div>
    </template>

</div>
@endsection

@section('vendor-script')
@endsection

@section('page-script')
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('posCheckout', () => ({
        step: 1,

        // Cart
        cartItems: @json($cartDetails).map(item => ({...item, note: item.note || '', showNote: false})),
        overallNote: '',

        // Discount
        discountType: 'none',
        manualDiscount: 0,
        percentageDiscount: 0,
        selectedPromoId: '',
        promoList: [],

        // Additional charges
        pajakEnabled: false, pajakAmount: 0,
        ongkosEnabled: false, ongkosAmount: 0,
        kemasanEnabled: false, kemasanAmount: 0,

        // Customer
        customerType: 'none',
        selectedCustomerId: '',
        manualCustomerName: '',
        newCustomer: { name: '', nickname: '', contact: '' },

        // Payment
        paymentMethod: 'tunai',
        nonTunaiType: 'qris',
        cashReceived: 0,

        // Result
        orderId: '',
        grossAmount: 0,
        orderItems: [],
        customerDisplayName: '',
        cashierName: '{{ auth()->user()->name ?? "Kasir Tidak Diketahui" }}',
        transactionDate: null, // ISO string

        // Confirmation modal
        showConfirmModal: false,
        isProcessing: false,

        // ---- Computed ----
        get totalItems() { return this.cartItems.reduce((s, i) => s + i.quantity, 0); },
        get subtotal() { return this.cartItems.reduce((s, i) => s + (i.price * i.quantity), 0); },
        get discountAmount() {
            if (this.discountType === 'manual') return Math.min(this.manualDiscount || 0, this.subtotal);
            if (this.discountType === 'percentage') return Math.floor(this.subtotal * (this.percentageDiscount || 0) / 100);
            if (this.discountType === 'promo' && this.selectedPromoId) {
                const p = this.promoList.find(x => x.id == this.selectedPromoId);
                if (p) return Math.min(Math.floor(this.subtotal * p.discount_rate / 100), p.max_discount_price);
            }
            return 0;
        },
        get totalCharges() {
            return (this.pajakEnabled ? (this.pajakAmount || 0) : 0) +
                   (this.ongkosEnabled ? (this.ongkosAmount || 0) : 0) +
                   (this.kemasanEnabled ? (this.kemasanAmount || 0) : 0);
        },
        get grandTotal() { return Math.max(this.subtotal - this.discountAmount + this.totalCharges, 0); },
        get changeAmount() { return Math.max((this.cashReceived || 0) - this.grandTotal, 0); },
        get canProceedPayment() {
            // must select payment method first
            if (!['tunai','non_tunai','campuran'].includes(this.paymentMethod)) return false;
            if (this.paymentMethod === 'tunai' || this.paymentMethod === 'campuran') {
                return this.cashReceived >= this.grandTotal;
            }
            return true; // non-tunai always ok
        },
        get formattedDate() {
            if (!this.transactionDate) return '';
            const d = new Date(this.transactionDate);
            return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
        },
        get formattedTime() {
            if (!this.transactionDate) return '';
            const d = new Date(this.transactionDate);
            return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        },
        get paymentMethodLabel() {
            const map = {
                tunai: 'Tunai',
                non_tunai: 'Non tunai',
                campuran: 'Campuran',
            };
            return map[this.paymentMethod] || this.paymentMethod;
        },

        // ---- Lifecycle ----
        init() { this.loadPromos(); },

        // ---- Methods ----
        async loadPromos() {
            try {
                const res = await fetch('{{ route("cart.getPromos", [], false) }}', { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                if (data.success) this.promoList = data.promos || [];
            } catch (e) { console.error('Load promos error:', e); }
        },

        async increaseQty(item) {
            const newQty = item.quantity + 1;
            const ok = await this._updateQty(item, newQty);
            if (ok) item.quantity = newQty;
        },

        async decreaseQty(item) {
            if (item.quantity <= 1) return;
            const newQty = item.quantity - 1;
            const ok = await this._updateQty(item, newQty);
            if (ok) item.quantity = newQty;
        },

        async _updateQty(item, qty) {
            try {
                const res = await fetch('{{ route("pos.cart.update", [], false) }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ product_id: item.product_id, variant_id: item.variant_id, quantity: qty })
                });
                const data = await res.json();
                if (!data.success) {
                    showToast(data.message || 'Gagal memperbarui jumlah', 'warning');
                    return false;
                }
                return true;
            } catch (e) {
                console.error(e);
                showToast('Gagal memperbarui jumlah', 'error');
                return false;
            }
        },

        async removeItem(item) {
            if (!confirm('Hapus ' + item.product_name + ' dari keranjang?')) return;
            try {
                const res = await fetch('{{ route("pos.cart.remove", [], false) }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ product_id: item.product_id, variant_id: item.variant_id })
                });
                const data = await res.json();
                if (data.success) {
                    this.cartItems = this.cartItems.filter(i => i.variant_id !== item.variant_id);
                }
            } catch (e) { console.error(e); }
        },

        async saveItemNote(item) {
            try {
                await fetch('{{ route("pos.cart.itemNote", [], false) }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ variant_id: item.variant_id, note: item.note })
                });
            } catch (e) { console.error(e); }
        },

        openConfirmModal() {
            if (this.cartItems.length === 0) return;
            if (!this.canProceedPayment) return;
            if (this.isProcessing) return;
            this.showConfirmModal = true;
        },

        async confirmAndPay() {
            if (this.isProcessing) return;
            this.isProcessing = true;
            try {
                await this.submitOrder();
            } finally {
                this.isProcessing = false;
            }
        },

        async submitOrder() {
            // Build customer name
            let customerName = '';
            let customerId = null;
            if (this.customerType === 'existing') customerId = this.selectedCustomerId;
            else if (this.customerType === 'manual') customerName = this.manualCustomerName;
            else if (this.customerType === 'new') customerName = this.newCustomer.name;

            const payload = {
                customer_name: customerName || 'Walk-in',
                customer_id: customerId,
                customer_type: this.customerType,
                new_contact: this.newCustomer.contact,
                new_nickname: this.newCustomer.nickname,
                payment_method: this.paymentMethod,
                non_tunai_type: this.nonTunaiType,
                cash_received: this.cashReceived,
                note: this.overallNote,
                discount_type: this.discountType,
                discount_amount: this.discountAmount,
                additional_charges: {
                    pajak: this.pajakEnabled ? this.pajakAmount : 0,
                    ongkos_kirim: this.ongkosEnabled ? this.ongkosAmount : 0,
                    kemasan: this.kemasanEnabled ? this.kemasanAmount : 0,
                },
                item_notes: this.cartItems.reduce((acc, item) => {
                    if (item.note) acc[item.variant_id] = item.note;
                    return acc;
                }, {}),
            };
            console.debug('submitOrder payload', payload);

            // All payment methods go through direct checkout (POS handles payment in person)
            try {
                const res = await fetch('{{ route("pos.cart.checkoutCustomer", [], false) }}', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload)
                });
                if (!res.ok) {
                    let msg = 'Error: ' + res.status;
                    let body = null;
                    try { body = await res.json(); if (body && body.message) msg = body.message; }
                    catch(err) { body = await res.text(); msg = body || msg; }
                    console.error('checkout response error', res.status, body);
                    // adjust cart automatically if variant info given
                    if (body && body.variant_id !== undefined) {
                        const idx = this.cartItems.findIndex(i => i.variant_id == body.variant_id);
                        if (idx >= 0) {
                            if ((body.available || 0) <= 0) {
                                this.cartItems.splice(idx, 1);
                            } else {
                                this.cartItems[idx].quantity = body.available;
                            }
                        }
                        // enrich alert message
                        if (body.requested !== undefined && body.available !== undefined) {
                            msg += ' (requested ' + body.requested + ', available ' + body.available + ')';
                        }
                    }
                    alert(res.status === 403 ? 'Anda tidak memiliki akses. Silakan login ulang.' : msg);
                    return;
                }
                const data = await res.json();
                if (data.success) {
                    this.orderId = data.order_id;
                    this.grossAmount = data.gross_amount;
                    this.orderItems = [...this.cartItems];
                    this.customerDisplayName = data.customer_name || 'Customer Umum';
                    this.transactionDate = data.created_at || new Date().toISOString();
                    // cashierName already pre-populated from blade
                    this.paymentMethod = data.payment_method || this.paymentMethod;
                    this.showConfirmModal = false;
                    this.step = 3;
                } else {
                    alert(data.message || 'Gagal memproses pesanan.');
                }
            } catch (e) {
                console.error('Checkout error:', e);
                let msg = 'Terjadi kesalahan saat memproses pesanan.';
                if (e && e.message) msg += ' (' + e.message + ')'; if(e && e.stack) { msg += '\nStack: ' + e.stack; }
                if (e && e.stack) msg += '\n\nStack:\n' + e.stack;
                alert(msg);
            }
        },

        async handleSnapPayment(payload) {
            try {
                const res = await fetch('{{ route("cart.checkout", [], false) }}', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'), 'Accept': 'application/json' },
                    body: JSON.stringify(payload)
                });
                if (!res.ok) {
                    alert(res.status === 403 ? 'Anda tidak memiliki akses. Silakan login ulang.' : ('Error: ' + res.status));
                    return;
                }
                const data = await res.json();
                if (!data.snap_token) { alert('Gagal mendapatkan snap token'); return; }

                snap.pay(data.snap_token, {
                    onSuccess: (result) => {
                        this.orderId = data.order_id;
                        this.grossAmount = data.gross_amount;
                        this.orderItems = [...this.cartItems];
                        this.customerDisplayName = data.customer_name || 'Customer Umum';
                        this.transactionDate = data.created_at || new Date().toISOString();
                        this.cashierName = data.cashier_name || this.cashierName;
                        this.step = 3;
                        fetch('{{ route("cart.clear", [], false) }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
                    },
                    onPending: () => alert('Pembayaran masih diproses. Cek kembali nanti.'),
                    onError: () => alert('Pembayaran gagal. Silakan coba lagi.'),
                });
            } catch (e) {
                console.error(e);
                alert('Gagal memproses pembayaran.');
            }
        },
    }));
});
</script>
@endsection
