@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Update Stock</h2>
        <p class="text-gray-500">Update stock quantity for {{ $product->name }}</p>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-gray-700">Current Stock: {{ $product->stock_quantity }}</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('products.update-stock', $product) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="space-y-6">
                    <div>
                        <label for="stock_quantity" class="form-label required">New Stock Quantity</label>
                        <input type="number" name="stock_quantity" id="stock_quantity" class="form-input" value="{{ old('stock_quantity', $product->stock_quantity) }}" min="0" required>
                        <p class="mt-1 text-sm text-gray-500">
                            This will update the stock on Lazada and in the local database.
                        </p>
                    </div>

                    <div class="flex items-center space-x-4">
                        <button type="submit" class="btn btn-primary">Update Stock</button>
                        <a href="{{ route('products.show', $product) }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .required::after {
        content: " *";
        color: rgb(239 68 68);
    }
</style>
@endpush 