{{-- Cart Panel (shared between desktop sidebar and mobile drawer) --}}

{{-- Cart Header --}}
<div class="px-5 py-4 border-b border-slate-100 shrink-0">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <div class="relative">
                <i class="ti ti-shopping-cart text-xl text-slate-600"></i>
                <span x-show="cartItems.length > 0"
                      class="absolute -top-1.5 -right-2 w-4 h-4 rounded-full bg-[#0C9044] text-white text-[10px] font-bold flex items-center justify-center badge-bounce"
                      x-text="cartTotalQty">
                </span>
            </div>
            <h3 class="text-sm font-bold text-slate-800" x-text="t('cart')"></h3>
        </div>
        <div class="flex items-center gap-3">
            {{-- Hold/Park button --}}
            <button @click="showHoldModal = true; mobileCartOpen = false"
                    class="text-xs text-amber-500 hover:text-amber-600 font-medium transition cursor-pointer flex items-center gap-1 px-2 py-1.5 rounded-lg hover:bg-amber-50"
                    title="Hold / Simpan Sementara (F3)">
                <i class="ti ti-bookmark text-sm"></i>
                <span class="hidden sm:inline">Hold</span>
                <span x-show="holdOrders.length > 0" class="w-4 h-4 rounded-full bg-amber-100 text-amber-600 text-[9px] font-bold flex items-center justify-center" x-text="holdOrders.length"></span>
            </button>
            <button x-show="cartItems.length > 0"
                    @click="confirmClearCart()"
                    class="text-xs text-red-400 hover:text-red-600 font-medium transition cursor-pointer flex items-center gap-1 px-2 py-1.5 rounded-lg hover:bg-red-50">
                <i class="ti ti-trash text-sm"></i> <span x-text="t('delete_all')"></span>
            </button>
        </div>
    </div>
</div>

{{-- Loading overlay --}}
<div x-show="isLoading" class="absolute inset-0 z-10 bg-white/60 flex items-center justify-center">
    <div class="pos-spinner" style="width:24px;height:24px;border-width:3px;"></div>
</div>

{{-- Customer selector --}}
<div class="px-4 py-2 border-b border-slate-50 shrink-0">
    <button @click="showCustomerPicker = true; mobileCartOpen = false"
            class="w-full flex items-center gap-2 h-8 px-2.5 rounded-lg border border-slate-200 bg-slate-50/50 text-xs text-slate-500 hover:border-[#0C9044]/30 hover:bg-green-50/30 transition cursor-pointer">
        <i class="ti ti-user-circle text-sm text-slate-400"></i>
        <span class="flex-1 text-left truncate" x-text="selectedCustomerName || 'Walk-in'"></span>
        <i class="ti ti-chevron-down text-[10px] text-slate-300"></i>
    </button>
</div>

{{-- Cart Items --}}
<div class="flex-1 overflow-y-auto pos-scroll px-4 py-4 relative" x-ref="cartList">
    {{-- Empty cart --}}
    <div x-show="cartItems.length === 0" class="flex flex-col items-center justify-center h-full text-center py-10">
        <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center mb-3">
            <i class="ti ti-shopping-cart-off text-2xl text-slate-300"></i>
        </div>
        <p class="text-sm text-slate-400 font-medium" x-text="t('empty_cart')"></p>
        <p class="text-xs text-slate-300 mt-1" x-text="t('pick_product')"></p>
    </div>

    {{-- Cart item list --}}
    <div class="space-y-3">
        <template x-for="(item, idx) in cartItems" :key="item.variant_id">
            <div class="cart-item cart-slide-in rounded-xl border border-slate-100 p-3.5 group">
                <div class="flex items-start gap-3">
                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-semibold text-slate-700 truncate" x-text="item.product_name"></h4>
                        <p class="text-[11px] text-slate-400 mt-0.5" x-text="item.variant_summary || 'Default'"></p>
                        <p x-show="item.note" class="text-[10px] text-amber-600 mt-0.5 italic flex items-center gap-1">
                            <i class="ti ti-note text-xs"></i> <span x-text="item.note"></span>
                        </p>
                        <div class="flex items-baseline gap-1.5 mt-1">
                            <span class="text-sm font-bold text-[#0C9044]"
                                  x-text="'Rp ' + Number(item.price).toLocaleString('id-ID')"></span>
                            <span x-show="item.normal_price && item.normal_price != item.price"
                                  class="text-[10px] text-slate-400 line-through"
                                  x-text="'Rp ' + Number(item.normal_price).toLocaleString('id-ID')"></span>
                        </div>
                    </div>

                    {{-- Remove --}}
                    <button @click="removeCartItem(item)"
                            class="opacity-0 group-hover:opacity-100 w-7 h-7 rounded-lg flex items-center justify-center text-slate-300 hover:text-red-500 hover:bg-red-50 transition cursor-pointer">
                        <i class="ti ti-x text-sm"></i>
                    </button>
                </div>

                {{-- Qty + Subtotal row --}}
                <div class="flex items-center justify-between mt-2.5 pt-2.5 border-t border-slate-50">
                    <div class="flex items-center gap-1.5">
                        <button @click="decreaseQty(item)" :disabled="item.quantity <= 1"
                                class="qty-btn w-8 h-8 rounded-lg border border-slate-200 flex items-center justify-center text-slate-500 cursor-pointer">
                            <i class="ti ti-minus text-xs"></i>
                        </button>
                        {{-- Click to edit qty --}}
                        <span x-show="editingQtyIdx !== idx"
                              @click="startEditQty(idx)"
                              class="w-10 text-center text-sm font-bold text-slate-700 cursor-pointer hover:text-[#0C9044] hover:bg-green-50 rounded-lg py-1 transition"
                              x-text="item.quantity"
                              title="Klik untuk input langsung"></span>
                        <input x-show="editingQtyIdx === idx"
                               type="number" min="1"
                               x-model.number="editQtyValue"
                               @keydown.enter="confirmEditQty(idx)"
                               @keydown.escape="editingQtyIdx = -1"
                               @blur="confirmEditQty(idx)"
                               class="w-12 h-8 text-center text-sm font-bold text-slate-700 border border-[#0C9044] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0C9044]/20"
                               :x-ref="'qtyInput' + idx" />
                        <button @click="increaseQty(item)"
                                class="qty-btn w-8 h-8 rounded-lg border border-slate-200 flex items-center justify-center text-slate-500 cursor-pointer">
                            <i class="ti ti-plus text-xs"></i>
                        </button>
                    </div>
                    <span class="text-sm font-bold text-slate-800"
                          x-text="'Rp ' + Number(item.price * item.quantity).toLocaleString('id-ID')"></span>
                </div>
            </div>
        </template>
    </div>
</div>

{{-- Cart Summary & Checkout --}}
<div class="border-t border-slate-200 bg-white px-5 py-5 shrink-0 space-y-3.5">
    {{-- Summary rows --}}
    <div class="space-y-2">
        <div class="flex items-center justify-between">
            <span class="text-xs text-slate-400 font-medium" x-text="t('subtotal')"></span>
            <span class="text-xs font-semibold text-slate-600" x-text="'Rp ' + Number(cartSubtotal).toLocaleString('id-ID')"></span>
        </div>
        <div class="flex items-center justify-between" x-show="cartDiscount > 0">
            <span class="text-xs text-emerald-500 font-medium" x-text="t('discount')"></span>
            <span class="text-xs font-semibold text-emerald-600" x-text="'- Rp ' + Number(cartDiscount).toLocaleString('id-ID')"></span>
        </div>
    </div>

    {{-- Divider --}}
    <div class="border-t border-dashed border-slate-200"></div>

    {{-- Total --}}
    <div class="flex items-center justify-between">
        <span class="text-sm font-bold text-slate-800" x-text="t('total')"></span>
        <span class="text-lg font-extrabold text-[#3A3A3A]" x-text="'Rp ' + Number(cartTotal).toLocaleString('id-ID')"></span>
    </div>

    {{-- Checkout Button --}}
    <a :href="cartItems.length > 0 ? '{{ route('pos.checkout') }}' : '#'"
       @click.prevent="if (cartItems.length > 0) { PosSound.checkout(); window.location.href = '{{ route('pos.checkout') }}'; } else { PosSound.error(); showToast(t('cart_empty_warning'), 'warning'); }"
       class="checkout-btn w-full h-12 rounded-xl flex items-center justify-center gap-2 text-sm font-bold text-white cursor-pointer"
       :class="cartItems.length > 0 ? 'bg-[#0C9044] hover:bg-green-700' : 'bg-slate-300 cursor-not-allowed'">
        <i class="ti ti-arrow-right text-lg"></i>
        <span x-text="t('checkout')"></span>
        <span x-show="cartItems.length > 0" class="ml-1 px-2 py-0.5 rounded-md bg-white/20 text-xs"
              x-text="'Rp ' + Number(cartTotal).toLocaleString('id-ID')"></span>
    </a>
</div>
