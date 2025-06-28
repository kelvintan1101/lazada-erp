@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Update Stock Quantity</h2>
        <p class="text-gray-500">Adjust the available stock for this product</p>
    </div>

    <div class="card">
        <div class="card-body">
            <form id="stock-form" action="{{ route('products.update-stock', $product) }}" method="POST">
                @csrf
                
                <div class="space-y-6">
                    <!-- Product Summary -->
                    <div class="bg-blue-50 border border-blue-200 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                            <div class="ml-4 flex-1">
                                <h3 class="text-lg font-medium text-blue-900">{{ $product->name }}</h3>
                                <div class="mt-1 flex items-center space-x-4 text-sm text-blue-700">
                                    <span>SKU: <strong>{{ $product->sku }}</strong></span>
                                    <span>Current Stock: <strong class="current-stock-value">{{ $product->stock_quantity }}</strong></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stock Input -->
                    <div>
                        <label for="stock_quantity" class="form-label required">New Stock Quantity</label>
                        <div class="mt-1 relative">
                            <input type="number" name="stock_quantity" id="stock_quantity" class="form-input pr-12" value="{{ old('stock_quantity', $product->stock_quantity) }}" min="0" required>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                <span class="text-gray-500 text-sm">units</span>
                            </div>
                        </div>
                        <p class="mt-2 text-sm text-gray-600">
                            This will update the available stock quantity on Lazada marketplace.
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                        <a href="{{ route('products.show', $product) }}" class="btn btn-secondary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Back to Product
                        </a>
                        <button id="submit-btn" type="submit" class="btn btn-primary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Update Stock
                        </button>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('stock-form');



    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Show loading with save type
        GlobalLoading.show('save');

        // Prepare form data
        const formData = new FormData(form);

        // Make API call
        const result = await GlobalAPI.post(form.action, formData);

        if (result.success && result.data.success) {
            // Update current stock display
            if (result.data.new_quantity) {
                const currentStockElement = document.querySelector('.current-stock-value');
                if (currentStockElement) {
                    currentStockElement.textContent = result.data.new_quantity;
                }
            }

            // Update loading message to show success
            GlobalLoading.updateText('Stock updated successfully!', 'Redirecting...');

            // Show success notification
            GlobalNotification.success('Stock Updated', result.data.message);

            // Redirect after showing success
            setTimeout(() => {
                GlobalLoading.navigateTo('{{ route("products.show", $product) }}');
            }, 1500);
        } else {
            // Hide loading and show error
            GlobalLoading.hide();
            const errorMessage = result.data?.message || result.error || 'An error occurred while updating stock';
            GlobalNotification.error('Update Failed', errorMessage);
        }
    });

    // Notification system is now handled by GlobalNotification
    // No need for custom showNotification function
});
</script>
@endpush