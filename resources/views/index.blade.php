@extends('layouts.layoutBlank')

@section('title', 'Landing Page')

@section('content')
    <div class="relative min-h-screen overflow-hidden" style="font-family: 'Poppins', sans-serif;">
        <nav class="absolute inset-x-0 top-0 z-30 border-b border-white/15 bg-emerald-950/20 backdrop-blur-xl">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4 md:px-10">
                <a href="{{ route('home') }}" class="flex items-center gap-3 text-white">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/15 ring-1 ring-white/20 shadow-lg shadow-emerald-950/10">
                        <img src="{{ asset('assets/logo.png') }}" alt="Handai Coffee Logo" class="h-7 w-7 object-contain">
                    </div>
                    <div class="leading-tight">
                        <p class="text-base font-semibold tracking-wide">Handai Coffee</p>
                    </div>
                </a>

                <a href="{{ route('login') }}"
                   class="inline-flex items-center gap-2 rounded-full bg-white/15 px-5 py-2.5 text-sm font-semibold text-white ring-1 ring-white/20 backdrop-blur-md transition hover:bg-white/25 hover:ring-white/30 hover:-translate-y-0.5">
                    Login
                </a>
            </div>
        </nav>

    <!-- Hero Section -->
    <section class="relative flex h-screen items-center justify-center bg-cover bg-center px-6 md:px-20 pt-28"
        style="background-image: url('{{ asset('images/image.png') }}');">
        <div class="absolute inset-0 bg-emerald-950/20"></div>
        
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
                <img src="{{ asset('images/splash-sukur.png') }}" alt="Coffee Cup"
                    class="w-full drop-shadow-2xl animate-bounce-slow">
            </div>
        </div>
    </section>
    </div>
@endsection
