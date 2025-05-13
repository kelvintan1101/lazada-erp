@extends('layouts.app')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <!-- Total Products -->
        <div class="card">
            <div class="card-body flex items-center">
                <div class="rounded-md bg-indigo-50 p-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-gray-500 text-sm">Total Products</h3>
                    <div class="text-2xl font-semibold">{{ number_format($totalProducts) }}</div>
                </div>
            </div>
        </div>
        
        <!-- Total Sales -->
        <div class="card">
            <div class="card-body flex items-center">
                <div class="rounded-md bg-green-50 p-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-gray-500 text-sm">Total Sales</h3>
                    <div class="text-2xl font-semibold">{{ number_format($totalSales, 2) }}</div>
                </div>
            </div>
        </div>
        
        <!-- Monthly Sales -->
        <div class="card">
            <div class="card-body flex items-center">
                <div class="rounded-md bg-blue-50 p-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-gray-500 text-sm">Monthly Sales</h3>
                    <div class="text-2xl font-semibold">{{ number_format($monthlySales, 2) }}</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Order Status Summary -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-700">Order Status</h3>
            </div>
            <div class="card-body">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded-full bg-yellow-400 mr-2"></div>
                            <span class="text-sm text-gray-600">Pending</span>
                        </div>
                        <span class="text-sm font-medium">{{ $pendingOrders }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded-full bg-blue-400 mr-2"></div>
                            <span class="text-sm text-gray-600">Ready to Ship</span>
                        </div>
                        <span class="text-sm font-medium">{{ $processingOrders }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded-full bg-green-400 mr-2"></div>
                            <span class="text-sm text-gray-600">Shipped</span>
                        </div>
                        <span class="text-sm font-medium">{{ $shippedOrders }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Low Stock Products -->
        <div class="card">
            <div class="card-header flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-700">Low Stock Products</h3>
                <a href="{{ route('products.index', ['low_stock' => 1]) }}" class="text-sm text-indigo-600 hover:text-indigo-900">View All</a>
            </div>
            <div class="card-body">
                @if($lowStockProducts->isEmpty())
                    <div class="text-gray-500 text-center py-4">No low stock products</div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($lowStockProducts as $product)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <a href="{{ route('products.show', $product) }}" class="text-indigo-600 hover:text-indigo-900">
                                                {{ $product->name }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-red-600 font-medium">{{ $product->stock_quantity }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Recent Orders -->
    <div class="card mt-6">
        <div class="card-header flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-700">Recent Orders</h3>
            <a href="{{ route('orders.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">View All</a>
        </div>
        <div class="card-body">
            @if($recentOrders->isEmpty())
                <div class="text-gray-500 text-center py-4">No orders yet</div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order #</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($recentOrders as $order)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('orders.show', $order) }}" class="text-indigo-600 hover:text-indigo-900">
                                            {{ $order->lazada_order_number }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $order->customer_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $order->order_date->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="badge {{ $order->status == 'pending' ? 'badge-warning' : ($order->status == 'shipped' ? 'badge-success' : 'badge-info') }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                                        {{ number_format($order->total_amount, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection 