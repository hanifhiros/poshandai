@extends('handai-manager.layouts.master')

@section('title', 'Add Customer')

@section('content')
<div class="container mx-auto px-4 max-w-lg">
    <h1 class="text-xl font-bold mb-4">Tambah Customer Baru</h1>

    <form action="{{ route('manager.marketing.customers.store') }}" method="POST">
        @csrf

        <div class="mb-4">
            <label class="block font-semibold">Nama</label>
            <input type="text" name="name" class="input input-primary w-full" required>
        </div>

        <div class="mb-4">
            <label class="block font-semibold">Nomor Telepon</label>
            <input type="text" name="contact_number" class="input input-primary w-full" required>
        </div>

        <div class="mb-4">
            <label class="block font-semibold">Email</label>
            <input type="email" name="email" class="input input-primary w-full">
        </div>

        <div class="mb-4">
            <label class="block font-semibold">Alamat</label>
            <input type="text" name="address" class="input input-primary w-full">
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</div>
@endsection
