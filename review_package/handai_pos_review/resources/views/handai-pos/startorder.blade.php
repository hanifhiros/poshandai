@extends('layouts.layoutBlank')

@section('title', 'Landing Page')

@section('vendor-style')
@endsection

@section('page-style')
@endsection

@section('content')
    <div class="">
        <div class="container mx-auto px-4">
            <div class="flex min-h-screen w-full items-center justify-center py-6">
                <div class="relative w-full max-w-md py-4">
                    <!-- Vector Pojok Kiri Atas -->
                    <div class="hidden sm:block absolute z-0"
                        style=" width: 250px; height: 250px; top: -100px; left: -120px; opacity: 50%; background-image: url('{{ asset('assets/svg/design2.svg') }}');background-repeat: no-repeat;background-size: contain;">
                    </div>
                    <!-- Vector Pojok Kanan Bawah -->
                    <div class="hidden sm:block absolute z-0"
                        style="width: 180px; height: 180px; bottom: -50px;right: -90px;opacity: 50%;background-image: url('{{ asset('assets/svg/design1.svg') }}');background-repeat: no-repeat;background-size: contain;">
                    </div>
                    <!-- Card -->
                    <div class="relative bg-white shadow-lg rounded-lg z-10">
                        <div class="p-8">
                            <!-- Logo -->

                            <div class="flex justify-center space-x-4 mb-8">
                                <a class="flex items-center">
                                    <img src="{{ asset('assets/svg/Partner/BTP.svg') }}" class="h-16 mr-2" alt="Logo">
                                </a>
                                <a class="flex items-center">
                                    <img src="{{ asset('assets/svg/Partner/kemenkop.svg') }}" class="h-12" alt="Logo">
                                </a>
                                <a class="flex items-center">
                                    <img src="{{ asset('assets/svg/Partner/TelU.svg') }}" class="h-12" alt="Logo">
                                </a>
                            </div>
                            <div class="flex justify-center ">
                                <img src="{{ asset('assets/svg/handai-logo-filled.svg') }}" class="h-12" alt="Logo Handai">
                            </div>
                            <div class="text-center">
                                <h3 class="text-3xl font-bold text-primary">HANDAI COFFEE</h3>
                                <h5 class="text-xl font-semibold mb-2">Telkom University</h5>
                                <p class="text-gray-500">Boost Your Studies Sustain Your Health</p>
                            </div>

                            <div class="flex justify-center space-x-4 mt-6">
                                <div class="alert alert-success alert-soft  w-full m-2 flex items-center justify-center">
                                    <i class="ti ti-building-store text-lg text-primary"></i>
                                    <span class="font-semibold text text-primary m-0 ">Store
                                        {{ $selected_store ? $selected_store->store_name : 'No Store Selected' }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex justify-center space-x-4 mt-3 m-2">
                                <a href="{{ route('pos.dashboard') }}" class="btn btn-primary w-full">Start New Order</a>
                            </div>
                            <div class="flex justify-center space-x-4 mt-3 m-2 text-sm">
                                <span>Bingung menggunakannya? <a href="#" class="link link-hover link-primary"> klik
                                        disini</a></span>
                            </div>
                        </div>
                        <!-- /Card -->
                    </div>
                </div>
            </div>
        </div>
@endsection

    @section('vendor-script')
    @endsection

    @section('page-script')
    @endsection