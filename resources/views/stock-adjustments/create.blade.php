@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Stock Adjustment</h2>
        <p class="text-gray-500">Record a manual stock adjustment for {{ $product->name }}</p>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-gray-700">Current Stock: {{ $product->stock_quantity }}</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('stock-adjustments.store', $product) }}" method="POST">
                @csrf
                
                <div class="space-y-6">
                    <div>
                        <label for="adjusted_quantity" class="form-label required">Adjustment Quantity</label>
                        <input type="number" name="adjusted_quantity" id="adjusted_quantity" class="form-input" value="{{ old('adjusted_quantity') }}" required>
                        <p class="mt-1 text-sm text-gray-500">
                            Use positive values for adding stock and negative values for removing stock.
                            This is for internal record-keeping only and does not update Lazada.
                        </p>
                    </div>
                    
                    <div>
                        <label for="reason" class="form-label required">Reason for Adjustment</label>
                        <input type="text" name="reason" id="reason" class="form-input" value="{{ old('reason') }}" required maxlength="255">
                        <p class="mt-1 text-sm text-gray-500">
                            E.g., "Stock count correction", "Damaged goods", "Returned items".
                        </p>
                    </div>

                    <div class="flex items-center space-x-4">
                        <button type="submit" class="btn btn-primary">Save Adjustment</button>
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