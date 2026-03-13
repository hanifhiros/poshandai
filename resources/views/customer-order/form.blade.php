@extends('handai-pos.layouts.layoutMaster')

@section('title', 'Dashboard')

@section('vendor-style')

@endsection

@section('page-style')

    <style>
        .ts-control {
            border-radius: 6px;
            border-color: #d1d5db;
        }

        .ts-dropdown .active {
            background-color: #8b5cf6 !important;
            color: white;
        }
    </style>

@endsection

@section('content')
    <div class="container mx-auto px-4 min-h-screen ">
        <h1 class="text-3xl font-bold mb-4 ">Toko</h1>
        <p>Selamat datang di Toko Kami</p>
        <div class="">
            <div
                class="grid  grid-cols-2 xs:grid-cols-2 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-3 sm:gap-3 lg:gap-4 py-4">
                <!-- Card Produk  -->
                @forelse ($productsWithDetails as $item)

                    <div class="relative bg-white rounded-lg shadow-md transform transition duration-150 hover:scale-105">
                        <div class="relative flex items-center justify-center">
                            <img class="max-h-32" src="{{ $item['product']->image_url ? asset($item['product']->image_url) : asset('assets/image.png') }}"
                                alt="{{ $item['product']->name }}" onerror="this.src='{{ asset('assets/image.png') }}'">

                            @if ($item['isPromo'] === 'yes')
                                <div
                                    class="absolute top-0 right-0 bg-red-500 text-white px-2 py-1 m-2 rounded-md text-sm font-medium">
                                    PROMO
                                </div>
                            @endif
                        </div>

                        <div class="p-4 flex flex-col">
                            <h3 class="text-lg font-medium mb-2 text-ellipsis overflow-hidden whitespace-nowrap">
                                {{ $item['product']->name }}
                            </h3>

                            <p class="text-gray-600 text-sm mb-4">
                                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis vitae
                            </p>

                            <div class="flex items-baseline justify-between flex-wrap">
                                @if ($item['isPromo'] === 'yes')
                                    <div class="flex flex-col sm:flex-col items-baseline">
                                        <span class="text-lg font-semibold text-primary">
                                            Rp. {{ number_format($item['price'], 0, ',', '.') }}
                                        </span>

                                        @if (!is_null($item['normal_price']))
                                            <span class="text-gray-400 text-xs line-through sm:text-sm md:text-base mt-1">
                                                Rp. {{ number_format($item['normal_price'], 0, ',', '.') }}
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <!-- Harga Normal (Tidak Promo) -->
                                    <div class="flex items-baseline mt-2 self-center">
                                        <span class="text-lg font-semibold">
                                            Rp. {{ number_format($item['price'], 0, ',', '.') }}
                                        </span>
                                    </div>
                                @endif

                                <!-- Tombol Beli / Tambah -->
                                <div class="flex justify-center w-full sm:w-auto mt-2 sm:mt-0 self-center">
                                    <label for="modal-{{ $item['product']->id }}"
                                        class="btn btn-primary btn-sm w-full sm:w-auto items-center my-2 ">
                                        <i class="ti ti-shopping-cart-plus"></i> Tambah
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <input type="checkbox" id="modal-{{ $item['product']->id }}" class="modal-toggle" />
                    <div class="modal">
                        <div class="modal-box">
                            {{-- <h3 class="font-bold text-lg mb-3">Pilih Size & Jumlah</h3> --}}
                            <form action="{{ route('customerOrder.cart.add') }}" method="POST"
                                x-data="cartForm({{ json_encode($item['variants']) }})">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $item['product']->id }}">
                                <input type="hidden" name="product_name" value="{{ $item['product']->name }}">

                                <!-- Pilih Size -->
                                <div class="mb-3">
                                    <label class="label">Ukuran / Size</label>
                                    <select name="variant_id" class="select w-full" x-model="selectedVariantId">
                                        <template x-for="variant in variants" :key="variant . id">
                                            <option :value="variant . id"
                                                x-text="variant.variant_options.join(', ') + ' - Rp' + Number(variant.final_price).toLocaleString('id-ID')">
                                            </option>
                                        </template>
                                    </select>
                                </div>

                                <!-- Info Stok -->
                                <div class="mb-3 text-sm text-gray-600">
                                    <span class="font-medium">Tersedia:</span>
                                    <span x-text="selectedVariant?.quantity ?? '-'"></span>
                                </div>

                                <!-- Quantity -->
                                <div class="mb-3">
                                    <label class="label">Jumlah</label>
                                    <input type="number" name="quantity" value="1" min="1" class="input input-bordered w-full">
                                </div>

                                <!-- Submit -->
                                <div class="modal-action">
                                    <label for="modal-{{ $item['product']->id }}" class="btn btn-ghost">← Batal</label>
                                    <button type="submit" class="btn btn-primary">Tambah ke Cart</button>
                                </div>
                            </form>


                            <!-- Form Add to Cart -->

                        </div>


                    </div>
                @empty
                    <div class="col-span-full text-center py-12">
                        <p class="text-gray-500 text-lg">Belum ada produk tersedia.</p>
                    </div>
                @endforelse

            </div>
        </div>
    </div>
    {{-- <pre>{{ print_r(session('cart'), true) }}</pre> --}}
    <!-- Sticky Cart Summary -->
    <div x-data="{ showCart: {{ $cartTotalItems ?? 0 }} > 0 }"
        class="sticky items-end inset-x-0 bottom-0 pb-2 sm:pb-5 z-10">
        <div x-show="showCart" x-transition class=" ">
            <div class="mx-auto max-w-7xl px-2 sm:px-6 lg:px-8">
                <div class="rounded-lg bg-primary p-2 shadow-lg sm:p-3">
                    <div
                        class="flex flex-col sm:flex-row items-start sm:items-center justify-between space-y-2 sm:space-y-0">

                        <div class="flex items-center space-x-3">
                            <button class="btn btn-ghost bg-white text-primary p-2">
                                <i class="ti ti-shopping-cart-plus"></i>
                            </button>
                            <p class="text-white font-medium">
                                <span class="sm:hidden">
                                    Cart: {{ $cartTotalItems ?? 0 }} Items |
                                    Total: Rp. {{ number_format($cartTotalPrice ?? 0, 0, ',', '.') }}
                                </span>
                                <span class="hidden sm:inline">
                                    Cart: {{ $cartTotalItems ?? 0 }} Items
                                </span>
                            </p>
                        </div>

                        <div
                            class="flex flex-col sm:flex-row items-center w-full sm:w-auto sm:justify-end space-x-2 sm:space-x-4">
                            <span class="text-white font-medium hidden sm:inline">
                                Total: Rp. {{ number_format($cartTotalPrice ?? 0, 0, ',', '.') }}
                            </span>
                            <a href="{{ route('customerOrder.checkout', ['store_id' => session('selected_store')]) }}"
                                class="btn bg-white text-primary w-full sm:w-auto">
                                Lanjutkan
                             </a>
                             
                             
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>





@endsection

@section('vendor-script')
@endsection

@section('page-script')
    <script>
        var cartData = @json($cart);
        var allProducts = @json($productsWithDetails);

        function cartForm(variants) {
            return {
                variants: variants,
                selectedVariantId: variants.length > 0 ? variants[0].id : '',
                get selectedVariant() {
                    return this.variants.find(v => v.id == this.selectedVariantId) || {};
                }
            };
        }
    </script>


@endsection