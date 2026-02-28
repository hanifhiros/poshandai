@extends('handai-manager.layouts.master')

@section('title', 'Add New Stock')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Add New Stock</h1>
    @if ($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

    <form method="POST" action="{{ route('manager.stocks.store') }}" enctype="multipart/form-data" class="space-y-4 bg-white p-6 rounded shadow">
        @csrf

        <div>
            <label for="name" class="block font-medium">Stock Name</label>
            <input type="text" name="name" id="name" class="border p-2 rounded w-full" required>
        </div>

        <div>
            <label for="category_id" class="block font-medium">Category</label>
            <select name="stock_category_id" id="stock_category_id" class="border p-2 rounded w-full" required>
                <option value="">Select category</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->stock_category_name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="unit_qty" class="block font-medium">Quantity</label>
            <input type="number" name="unit_qty" id="unit_qty" class="border p-2 rounded w-full" required>
        </div>

        <div>
            <label for="unit" class="block font-medium">Unit</label>
            <input type="text" name="unit" id="unit" class="border p-2 rounded w-full" required>
        </div>

        <div>
            <label for="price" class="block font-medium">Price Per Unit</label>
            <input type="number" name="price_per_unit" id="price" class="border p-2 rounded w-full" required>
        </div>

        <div>
            <label for="image" class="block font-medium">Stock Image</label>
            <input type="file" name="image" id="image" class="border p-2 rounded w-full">
        </div>

        <div>
            <label for="image" class="block font-medium">Buy Date</label>
            <input type="date" name="buy_date" id="buy_date" class="border p-2 rounded w-full">
        </div>
        <div class="flex gap-4">
            <div class="w-2/3">
                <label for="expired_duration_value" class="block font-medium">Expired Duration</label>
                <input type="number" name="expired_duration_value" class="border p-2 rounded w-full" placeholder="Masukkan jumlah (contoh: 7)">
            </div>
            <div class="w-1/3">
                <label for="expired_duration_unit" class="block font-medium">&nbsp;</label>
                <select name="expired_duration_unit" class="border p-2 rounded w-full">
                    <option value="days">Days</option>
                    <option value="weeks">Weeks</option>
                    <option value="months">Months</option>
                    <option value="years">Years</option>
                </select>
            </div>
        </div>
        
        
        
        
        
        
        

        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Add Stock</button>
    </form>
</div>

<script>
   
</script>

    
@endsection


