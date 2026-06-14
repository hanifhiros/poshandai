@extends('layouts.layoutMaster')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-bold text-slate-800 mb-6">CEO Command Center</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <h3 class="text-slate-500 text-sm uppercase font-bold">Total Cabang</h3>
            <p class="text-3xl font-black text-slate-800">{{\App\Models\Store::count()}}</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <h3 class="text-slate-500 text-sm uppercase font-bold">Total Karyawan</h3>
            <p class="text-3xl font-black text-slate-800">{{\App\Models\User::count()}}</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <h3 class="text-slate-500 text-sm uppercase font-bold">Simulasi Aktif</h3>
            <p class="text-3xl font-black text-slate-800">Ready</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="{{ route('superadmin.account.index') }}" class="block p-6 bg-white border-l-4 border-green-600 shadow-sm rounded-lg hover:shadow-md">
            <h2 class="font-bold text-lg">Manajemen Akun</h2>
            <p class="text-sm text-slate-500">Kelola staf, role, dan hak akses.</p>
        </a>
        <a href="{{ route('superadmin.store.index') }}" class="block p-6 bg-white border-l-4 border-blue-600 shadow-sm rounded-lg hover:shadow-md">
            <h2 class="font-bold text-lg">Manajemen Toko</h2>
            <p class="text-sm text-slate-500">Tambah/Edit data cabang.</p>
        </a>
        <a href="{{ route('superadmin.simulate.index') }}" class="block p-6 bg-white border-l-4 border-purple-600 shadow-sm rounded-lg hover:shadow-md">
            <h2 class="font-bold text-lg">Simulasi & Monitoring</h2>
            <p class="text-sm text-slate-500">Masuk ke role apa saja di toko mana saja.</p>
        </a>
    </div>
</div>
@endsection