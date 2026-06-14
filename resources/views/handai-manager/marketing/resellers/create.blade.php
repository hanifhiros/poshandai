@extends('layouts.master')

@section('title', 'Tambah Reseller')

@section('content')
<div class="max-w-xl mx-auto p-6 bg-white shadow rounded-lg" x-data="{ mode: 'lama' }">
    <h2 class="text-lg font-bold mb-4">Tambah Reseller ke Toko</h2>
    {{-- <pre>{{ var_dump($errors->all()) }}</pre> --}}

    @if ($errors->any())
    <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-700 rounded">
        <strong class="block mb-2">Terjadi kesalahan:</strong>
        <ul class="list-disc list-inside text-sm">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


    <form method="POST" action="{{ route('manager.marketing.resellers.store') }}">
        @csrf

        {{-- Toggle Mode --}}
        <div class="mb-4">
            <label class="font-medium block mb-2">Pilih Jenis Input</label>
          



            <label class="inline-flex items-center mr-4">
                <input type="radio" name="mode" value="lama" x-model="mode" class="mr-2" {{ old('mode', 'lama') === 'lama' ? 'checked' : '' }}> Pilih Reseller Lama
            </label>
            <label class="inline-flex items-center">
                <input type="radio" name="mode" value="baru" x-model="mode" class="mr-2" {{ old('mode') === 'baru' ? 'checked' : '' }}> Buat Reseller Baru
            </label>
        </div>

        {{-- Reseller Lama --}}
        <div class="mb-4" x-show="mode === 'lama'">
            <label class="block font-medium mb-1">Reseller Lama</label>
            <select name="reseller_id" class="select select-bordered w-full">
                <option value="">-- Pilih reseller --</option>
                @foreach ($allResellers as $reseller)
                    <option value="{{ $reseller->id }}">{{ $reseller->name }} ({{ $reseller->code }})</option>
                @endforeach
            </select>
        </div>

        {{-- Reseller Baru --}}
        <div class="space-y-4" x-show="mode === 'baru'">
            <div>
                <label class="block font-medium mb-1">Nama</label>
                <input name="new_name" type="text" class="input input-bordered w-full" placeholder="Nama Reseller Baru">
            </div>
            <div>
                <label class="block font-medium mb-1">Email</label>
                <input name="new_email" type="email" class="input input-bordered w-full" placeholder="email@contoh.com">
            </div>
            <div>
                <label class="block font-medium mb-1">Nomor HP</label>
                <input name="new_contact_number" type="text" class="input input-bordered w-full" placeholder="08xxxxxxxx">
            </div>
            <div>
                <label class="block font-medium mb-1">Password</label>
                <input name="new_password" type="password" class="input input-bordered w-full" placeholder="Minimal 6 karakter">
            </div>
        </div>

        {{-- Pilih Toko --}}
        <div class="mt-6 mb-4">
            <label class="block font-medium mb-1">Toko</label>
            <select name="store_id" class="select select-bordered w-full" required>
                @foreach ($stores as $store)
                    <option value="{{ $store->id }}">{{ $store->store_name }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</div>
@endsection

