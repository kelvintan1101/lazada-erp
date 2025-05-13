@extends('layouts.app')

@section('content')
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">{{ $product->name }}</h2>
            <p class="text-gray-500">Product Details</p>
        </div>
        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2 mt-4 md:mt-0">
            <a href="{{ route('products.edit-stock', $product) }}" class="btn btn-primary inline-flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Update Stock
            </a>
            <a href="{{ route('stock-adjustments.create', $product) }}" class="btn btn-secondary inline-flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5 3a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V5a2 2 0 00-2-2H5zm0 2h10v10H5V5z" clip-rule="evenodd" />
                </svg>
                Stock Adjustment
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="col-span-2">
            <div class="card mb-6">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-700">Product Information</h3>
                </div>
                <div class="card-body">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm font-medium text-gray-500">SKU</p>
                            <p class="mt-1">{{ $product->sku }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Lazada Product ID</p>
                            <p class="mt-1">{{ $product->lazada_product_id }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Price</p>
                            <p class="mt-1">{{ number_format($product->price, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Stock Quantity</p>
                            <p class="mt-1 {{ $product->isLowStock() ? 'text-red-600 font-medium' : '' }}">
                                {{ $product->stock_quantity }}
                                @if($product->isLowStock())
                                    <span class="badge badge-danger ml-2">Low Stock</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Last Synced</p>
                            <p class="mt-1">{{ $product->synced_at ? $product->synced_at->format('M d, Y H:i:s') : 'Never' }}</p>
                        </div>
                    </div>

                    @if($product->description)
                        <div class="mt-6">
                            <p class="text-sm font-medium text-gray-500">Description</p>
                            <div class="mt-1 prose max-w-none">
                                {!! nl2br(e($product->description)) !!}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Stock Adjustment History -->
            <div class="card">
                <div class="card-header flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-700">Stock Adjustment History</h3>
                    <a href="{{ route('stock-adjustments.index', $product) }}" class="text-sm text-indigo-600 hover:text-indigo-900">View All</a>
                </div>
                <div class="card-body p-0">
                    @if($product->stockAdjustments && $product->stockAdjustments->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="table">
                                <thead class="table-header">
                                    <tr>
                                        <th class="table-cell-head">Date</th>
                                        <th class="table-cell-head">Adjusted By</th>
                                        <th class="table-cell-head">Quantity</th>
                                        <th class="table-cell-head">Reason</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($product->stockAdjustments->take(5) as $adjustment)
                                        <tr class="table-row">
                                            <td class="table-cell">
                                                {{ $adjustment->created_at->format('M d, Y H:i') }}
                                            </td>
                                            <td class="table-cell">
                                                {{ $adjustment->adjustedByUser->name }}
                                            </td>
                                            <td class="table-cell">
                                                <span class="{{ $adjustment->adjusted_quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ $adjustment->adjusted_quantity > 0 ? '+' : '' }}{{ $adjustment->adjusted_quantity }}
                                                </span>
                                            </td>
                                            <td class="table-cell">
                                                {{ $adjustment->reason }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-4 text-gray-500 text-center">No stock adjustments recorded.</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div>
            <!-- Images -->
            @if($product->images && count($product->images) > 0)
                <div class="card mb-6">
                    <div class="card-header">
                        <h3 class="text-lg font-semibold text-gray-700">Product Images</h3>
                    </div>
                    <div class="card-body">
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-1 lg:grid-cols-2 gap-4">
                            @foreach($product->images as $image)
                                <img src="{{ $image }}" alt="{{ $product->name }}" class="rounded-lg shadow-sm">
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-700">Quick Actions</h3>
                </div>
                <div class="card-body">
                    <nav class="space-y-2">
                        <a href="{{ route('products.edit-stock', $product) }}" class="flex items-center px-4 py-2 text-gray-700 bg-gray-100 rounded-md hover:bg-indigo-100 hover:text-indigo-900 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            Update Stock
                        </a>
                        <a href="{{ route('stock-adjustments.create', $product) }}" class="flex items-center px-4 py-2 text-gray-700 bg-gray-100 rounded-md hover:bg-indigo-100 hover:text-indigo-900 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5 3a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V5a2 2 0 00-2-2H5zm0 2h10v10H5V5z" clip-rule="evenodd" />
                            </svg>
                            Stock Adjustment
                        </a>
                        <a href="{{ route('stock-adjustments.index', $product) }}" class="flex items-center px-4 py-2 text-gray-700 bg-gray-100 rounded-md hover:bg-indigo-100 hover:text-indigo-900 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                            </svg>
                            View All Adjustments
                        </a>
                        <a href="{{ route('products.index') }}" class="flex items-center px-4 py-2 text-gray-700 bg-gray-100 rounded-md hover:bg-indigo-100 hover:text-indigo-900 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                            </svg>
                            Back to Products
                        </a>
                    </nav>
                </div>
            </div>
        </div>
    </div>
@endsection 