@extends('layouts.layoutMaster')

@section('title', 'Landing Page')

@section('content')
    <!-- Hero Section -->
    <section class="relative bg-cover bg-center h-screen flex items-center justify-center px-6 md:px-20 pt-24"
        style="background-image: url('{{ asset('image.png') }}'); font-family: 'Poppins', sans-serif;">
        
        {{-- <div class="absolute inset-0 bg-[#1e1e1e] bg-opacity-70"></div> --}}

        <div class="relative z-10 max-w-6xl mx-auto flex flex-col lg:flex-row items-center justify-between w-full gap-10 text-white">
            <!-- Teks -->
            <div class="max-w-xl text-center lg:text-left space-y-4">
                <p class="text-sm tracking-widest text-[#b7f2cd] uppercase font-semibold">
                    Boost Your Study Sustain Your Health
                </p>
                <h1 class="text-4xl md:text-5xl font-extrabold leading-tight">
                    Start your day with<br><span class="text-[#f8f8f8]">Handai Coffee</span>
                </h1>
                <p class="text-base text-gray-200 leading-relaxed">
                    Minuman rendah glikemik untuk mendukung hari-harimu dengan gaya dan energi.
                </p>
                <a href="https://handaicoffee.shop" target="_blank" rel="noopener noreferrer" class="inline-block mt-4">
                    <button
                        class="px-6 py-3 bg-[#dddd03] text-white font-semibold rounded-full shadow-lg hover:bg-[#4e944f] transition">
                        Learn About Us
                    </button>
                </a>
            </div>

            <!-- Gambar Kopi -->
            <div class="w-full max-w-sm hidden lg:block">
                <img src="{{ asset('splash-sukur.png') }}" alt="Coffee Cup"
                    class="w-full drop-shadow-2xl animate-bounce-slow">
            </div>
        </div>
    </section>
@endsection
