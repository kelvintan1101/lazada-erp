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
            <form id="stock-form" action="{{ route('products.update-stock', $product) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="space-y-6">
                    <!-- Product Information -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-3">Product Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="font-medium text-gray-700">Product Name:</span>
                                <span class="text-gray-900">{{ $product->name }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">SKU:</span>
                                <span class="text-gray-900">{{ $product->sku }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Lazada Product ID:</span>
                                <span class="text-gray-900">{{ $product->lazada_product_id }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Current Stock:</span>
                                <span class="text-gray-900 font-semibold current-stock-value">{{ $product->stock_quantity }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Stock Adjustment -->
                    <div>
                        <label for="stock_quantity" class="form-label required">New Sellable Quantity</label>
                        <input type="number" name="stock_quantity" id="stock_quantity" class="form-input" value="{{ old('stock_quantity', $product->stock_quantity) }}" min="0" required>
                        <p class="mt-1 text-sm text-gray-500">
                            <strong>Note:</strong> This will adjust the sellable stock quantity on Lazada using the
                            <code>/product/stock/sellable/adjust</code> API endpoint and update the local database.
                        </p>
                        <p class="mt-1 text-sm text-blue-600">
                            <svg class="inline w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            This uses the same API as the bulk update feature but for individual stock management.
                        </p>
                    </div>

                    <div class="flex items-center space-x-4">
                        <button id="submit-btn" type="submit" class="btn btn-primary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 1.79 4 4 4h8c0 2.21 1.79 4 4 4h8c0-2.21-1.79-4-4-4V7c0-2.21-1.79-4-4-4H8c-2.21 0-4 1.79-4 4z"></path>
                            </svg>
                            Adjust Stock on Lazada
                        </button>
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

    .btn-loading {
        position: relative;
        color: transparent;
    }

    .btn-loading::after {
        content: "";
        position: absolute;
        width: 16px;
        height: 16px;
        top: 50%;
        left: 50%;
        margin-left: -8px;
        margin-top: -8px;
        border: 2px solid #ffffff;
        border-radius: 50%;
        border-top-color: transparent;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('stock-form');
    const submitBtn = document.getElementById('submit-btn');
    const originalBtnText = submitBtn.innerHTML;

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Show loading state
        submitBtn.disabled = true;
        submitBtn.classList.add('btn-loading');
        submitBtn.innerHTML = 'Adjusting Stock...';

        // Prepare form data
        const formData = new FormData(form);

        // Make AJAX request
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            // Reset button state
            submitBtn.disabled = false;
            submitBtn.classList.remove('btn-loading');
            submitBtn.innerHTML = originalBtnText;

            if (data.success) {
                // Show success notification
                showNotification(data.message, 'success');

                // Update current stock display
                const currentStockElement = document.querySelector('.current-stock-value');
                if (currentStockElement) {
                    currentStockElement.textContent = data.new_quantity;
                }

                // Redirect after short delay
                setTimeout(() => {
                    window.location.href = '{{ route("products.show", $product) }}';
                }, 2000);
            } else {
                // Show error notification
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);

            // Reset button state
            submitBtn.disabled = false;
            submitBtn.classList.remove('btn-loading');
            submitBtn.innerHTML = originalBtnText;

            showNotification('An error occurred while adjusting stock. Please try again.', 'error');
        });
    });

    function showNotification(message, type) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `fixed bottom-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
            type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
        }`;
        notification.innerHTML = `
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    ${type === 'success'
                        ? '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>'
                        : '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>'
                    }
                </svg>
                <span>${message}</span>
            </div>
        `;

        document.body.appendChild(notification);

        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateY(10px)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 5000);
    }
});
</script>
@endpush