@extends('layouts.app')

@section('content')
    <!-- Global Loading Indicator -->
    <div id="global-loading" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.7); display: none; align-items: center; justify-content: center; z-index: 9999;">
        <div style="background-color: white; border-radius: 12px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); padding: 2rem; text-align: center; max-width: 320px; margin: 1rem;">
            <div style="width: 64px; height: 64px; border: 4px solid #e5e7eb; border-top: 4px solid #2563eb; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto;"></div>
            <p id="loading-main-text" style="margin-top: 1rem; color: #374151; font-weight: 500; font-size: 16px;">Updating stock...</p>
            <p id="loading-sub-text" style="margin-top: 0.5rem; color: #6b7280; font-size: 14px;">Please wait</p>
        </div>
    </div>

    <style>
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
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

    // Loading functions
    function showLoading(mainText, subText) {
        const loadingElement = document.getElementById('global-loading');
        const mainTextElement = document.getElementById('loading-main-text');
        const subTextElement = document.getElementById('loading-sub-text');

        if (loadingElement) {
            if (mainTextElement) mainTextElement.textContent = mainText;
            if (subTextElement) subTextElement.textContent = subText;
            loadingElement.style.display = 'flex';
        }
    }

    function hideLoading() {
        const loadingElement = document.getElementById('global-loading');
        if (loadingElement) {
            loadingElement.style.display = 'none';
        }
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Show loading animation
        showLoading('Updating stock...', 'Please wait');

        // Prepare form data
        const formData = new FormData(form);



        // Make AJAX request
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Update current stock display
                const currentStockElement = document.querySelector('.current-stock-value');
                if (currentStockElement) {
                    currentStockElement.textContent = data.new_quantity;
                }

                // Update loading message to show success
                showLoading('Stock updated successfully!', 'Redirecting...');

                // Show success notification briefly
                showNotification(data.message, 'success');

                // Redirect after showing success
                setTimeout(() => {
                    window.location.href = '{{ route("products.show", $product) }}';
                }, 1500);
            } else {
                // Hide loading and show error
                hideLoading();
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            // Hide loading and show error
            hideLoading();
            showNotification('An error occurred while updating stock. Please try again.', 'error');
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