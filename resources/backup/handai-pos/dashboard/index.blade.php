@extends('handai-pos.layouts.layoutMaster')

@section('title', 'Dashboard')

@section('vendor-style')
@endsection

@section('page-style')
@endsection

@section('content')
    <div class="container mx-auto px-4 min-h-screen ">
        <h1 class="text-3xl font-bold mb-4 ">Dashboard</h1>
        <p>Selamat datang di dashboard!</p>
        <div class="">
            <div
                class="grid  grid-cols-2 xs:grid-cols-2 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-3 sm:gap-3 lg:gap-4 py-4">
                <!-- Card Produk 1 -->
                <!-- <div class="relative bg-white  rounded-lg shadow-md transform transition duration-150 hover:scale-105">
                        <div class="relative flex items-center justify-center">
                            <img class="max-h-36" src="{{ asset('assets/svg/Produk/CHOCO.svg') }}" alt="Product Image">
                            <div
                                class="absolute top-0 right-0 bg-red-500 text-white px-2 py-1 m-2 rounded-md text-sm font-medium">
                                SALE
                            </div>
                        </div>
                        <div class="p-4">
                            <h3 class="text-lg font-medium mb-2">Choco Latte</h3>
                            <p class="text-gray-600 text-sm mb-4">
                                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis vitae
                            </p>
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-lg">Rp. 30.000</span>
                                <button class="btn btn-primary">Buy Now</button>
                            </div>
                        </div>
                    </div> -->
                <!-- Ulangi card produk sesuai kebutuhan -->


                @foreach ($allProducts as $item)
                    <div class="relative bg-white rounded-lg shadow-md transform transition duration-150 hover:scale-105">
                        <div class="relative flex items-center justify-center">
                            <img class="max-h-32" src="{{ asset($item['product']->image_url) }}"
                                alt="{{ $item['product']->name }}">

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
                            <h3 class="font-bold text-lg mb-3">Pilih Size & Jumlah</h3>

                            <!-- Form Add to Cart -->
                            <form action="{{ route('cart.add') }}" method="POST">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $item['product']->id }}">
                                <input type="hidden" name="product_name" value="{{ $item['product']->name }}">

                                <!-- Pilih Size -->
                                <div class="mb-3">
                                    <label class="label">Ukuran / Size</label>
                                    <select name="size_id" class="select select-bordered w-full">
                                        @foreach ($item['product']->sizePrices as $size)
                                            <option value="{{ $size->id }}">
                                                {{ $size->size }} - Rp. {{ number_format($size->price, 0, ',', '.') }}
                                                @if ($size->is_promo === 'yes')
                                                    (Promo: Rp.
                                                    {{ number_format($size->price - $size->price_discount, 0, ',', '.') }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Quantity -->
                                <div class="mb-3">
                                    <label class="label">Quantity</label>
                                    <input type="number" name="quantity" value="1" class="input input-bordered w-full">
                                </div>

                                <!-- Submit -->
                                <div class="modal-action">
                                    <label for="modal-{{ $item['product']->id }}" class="btn btn-ghost">Batal</label>
                                    <button type="submit" class="btn btn-primary">Tambah ke Cart</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach

            </div>
        </div>
    </div>

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
                            <a href="#"
                                class="btn bg-white text-primary w-full sm:w-auto text-center px-4 py-2 text-sm font-medium">
                                View Cart
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
        console.log("Cart data:", cartData);

        var allProducts = @json($allProducts);
        console.log(allProducts);
    </script>
@endsection