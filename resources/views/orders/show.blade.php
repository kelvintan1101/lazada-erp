@extends('layouts.app')

@section('content')
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Order #{{ $order->lazada_order_number }}</h2>
            <p class="text-gray-500">{{ $order->order_date->format('F d, Y') }}</p>
        </div>
        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2 mt-4 md:mt-0">
            <a href="{{ route('orders.edit-status', $order) }}" class="btn btn-primary inline-flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                </svg>
                Update Status
            </a>
            <a href="{{ route('orders.index') }}" class="btn btn-secondary inline-flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                Back to Orders
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Main Order Info -->
        <div class="col-span-2">
            <div class="card mb-6">
                <div class="card-header flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-700">Order Details</h3>
                    <span class="badge {{ $order->status == 'pending' ? 'badge-warning' : ($order->status == 'shipped' || $order->status == 'delivered' ? 'badge-success' : ($order->status == 'canceled' ? 'badge-danger' : 'badge-info')) }}">
                        {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Customer</p>
                            <p class="mt-1">{{ $order->customer_name }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Lazada Order ID</p>
                            <p class="mt-1">{{ $order->lazada_order_id }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Payment Method</p>
                            <p class="mt-1">{{ $order->payment_method }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Last Synced</p>
                            <p class="mt-1">{{ $order->synced_at ? $order->synced_at->format('M d, Y H:i:s') : 'Never' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-700">Items</h3>
                </div>
                <div class="card-body p-0">
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead class="table-header">
                                <tr>
                                    <th class="table-cell-head">Product</th>
                                    <th class="table-cell-head">SKU</th>
                                    <th class="table-cell-head">Price</th>
                                    <th class="table-cell-head">Quantity</th>
                                    <th class="table-cell-head">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->orderItems as $item)
                                    <tr class="table-row">
                                        <td class="table-cell">
                                            @if($item->product)
                                                <a href="{{ route('products.show', $item->product) }}" class="text-indigo-600 hover:text-indigo-900">
                                                    {{ $item->product_name }}
                                                </a>
                                            @else
                                                {{ $item->product_name }}
                                            @endif
                                        </td>
                                        <td class="table-cell">{{ $item->sku }}</td>
                                        <td class="table-cell">{{ number_format($item->unit_price, 2) }}</td>
                                        <td class="table-cell">{{ $item->quantity }}</td>
                                        <td class="table-cell">{{ number_format($item->total_price, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-gray-50">
                                    <td colspan="4" class="px-6 py-3 text-right font-medium">Total:</td>
                                    <td class="px-6 py-3 font-medium">{{ number_format($order->total_amount, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div>
            <!-- Shipping Details -->
            <div class="card mb-6">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-700">Shipping Address</h3>
                </div>
                <div class="card-body">
                    @if(isset($order->shipping_address['address']))
                        <p>{{ $order->shipping_address['address'] }}</p>
                        @if(isset($order->shipping_address['city']))
                            <p>{{ $order->shipping_address['city'] }}</p>
                        @endif
                        @if(isset($order->shipping_address['zipcode']))
                            <p>{{ $order->shipping_address['zipcode'] }}</p>
                        @endif
                        @if(isset($order->shipping_address['country']))
                            <p>{{ $order->shipping_address['country'] }}</p>
                        @endif
                    @else
                        <p class="text-gray-500">No shipping address information available.</p>
                    @endif
                </div>
            </div>

            <!-- Order Status -->
            <div class="card mb-6">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-700">Status History</h3>
                </div>
                <div class="card-body">
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-2.5 h-2.5 rounded-full bg-green-500 mr-2"></div>
                            <span class="text-sm text-gray-600">Order Placed</span>
                            <span class="ml-auto text-sm text-gray-500">{{ $order->order_date->format('M d, Y') }}</span>
                        </div>
                        
                        @if($order->status != 'pending')
                            <div class="flex items-center">
                                <div class="flex-shrink-0 w-2.5 h-2.5 rounded-full bg-green-500 mr-2"></div>
                                <span class="text-sm text-gray-600">Status Updated</span>
                                <span class="ml-auto text-sm text-gray-500">{{ $order->updated_at->format('M d, Y') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-700">Quick Actions</h3>
                </div>
                <div class="card-body">
                    <nav class="space-y-2">
                        <a href="{{ route('orders.edit-status', $order) }}" class="flex items-center px-4 py-2 text-gray-700 bg-gray-100 rounded-md hover:bg-indigo-100 hover:text-indigo-900 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                            </svg>
                            Update Status
                        </a>
                        <a href="{{ route('orders.index') }}" class="flex items-center px-4 py-2 text-gray-700 bg-gray-100 rounded-md hover:bg-indigo-100 hover:text-indigo-900 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                            </svg>
                            Back to Orders
                        </a>
                    </nav>
                </div>
            </div>
        </div>
    </div>
@endsection 