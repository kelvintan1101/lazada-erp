@extends('layouts.app')

@section('content')
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between">
        <h2 class="text-2xl font-bold text-gray-800 mb-4 md:mb-0">Orders</h2>
        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
            <a href="{{ route('orders.sync') }}" class="btn btn-primary inline-flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                </svg>
                Sync Orders
            </a>
        </div>
    </div>

    <div class="card mb-6">
        <div class="card-body">
            <form action="{{ route('orders.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="search" class="form-label">Search</label>
                    <input type="text" name="search" id="search" class="form-input" value="{{ request('search') }}" placeholder="Order # or Customer">
                </div>
                <div>
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-input">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="packed" {{ request('status') == 'packed' ? 'selected' : '' }}>Packed</option>
                        <option value="ready_to_ship" {{ request('status') == 'ready_to_ship' ? 'selected' : '' }}>Ready to Ship</option>
                        <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                        <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="canceled" {{ request('status') == 'canceled' ? 'selected' : '' }}>Canceled</option>
                    </select>
                </div>
                <div>
                    <label for="start_date" class="form-label">From Date</label>
                    <input type="date" name="start_date" id="start_date" class="form-input" value="{{ request('start_date') }}">
                </div>
                <div>
                    <label for="end_date" class="form-label">To Date</label>
                    <input type="date" name="end_date" id="end_date" class="form-input" value="{{ request('end_date') }}">
                </div>
                <div class="md:col-span-4 flex flex-col sm:flex-row sm:justify-end space-y-2 sm:space-y-0 sm:space-x-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('orders.index') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            @if($orders->isEmpty())
                <div class="text-center py-8">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="mt-2 text-gray-500">No orders found.</p>
                    <a href="{{ route('orders.sync') }}" class="btn btn-primary mt-4 inline-flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                        </svg>
                        Sync Orders from Lazada
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead class="table-header">
                            <tr>
                                <th class="table-cell-head">Order #</th>
                                <th class="table-cell-head">Customer</th>
                                <th class="table-cell-head">Date</th>
                                <th class="table-cell-head">Status</th>
                                <th class="table-cell-head">Total</th>
                                <th class="table-cell-head">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr class="table-row">
                                    <td class="table-cell">
                                        <a href="{{ route('orders.show', $order) }}" class="text-indigo-600 hover:text-indigo-900">
                                            {{ $order->lazada_order_number }}
                                        </a>
                                    </td>
                                    <td class="table-cell">{{ $order->customer_name }}</td>
                                    <td class="table-cell">{{ $order->order_date->format('M d, Y') }}</td>
                                    <td class="table-cell">
                                        <span class="badge {{ $order->status == 'pending' ? 'badge-warning' : ($order->status == 'shipped' || $order->status == 'delivered' ? 'badge-success' : ($order->status == 'canceled' ? 'badge-danger' : 'badge-info')) }}">
                                            {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                        </span>
                                    </td>
                                    <td class="table-cell">{{ number_format($order->total_amount, 2) }}</td>
                                    <td class="table-cell">
                                        <div class="flex items-center space-x-2">
                                            <a href="{{ route('orders.show', $order) }}" class="text-indigo-600 hover:text-indigo-900">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                                </svg>
                                            </a>
                                            <a href="{{ route('orders.edit-status', $order) }}" class="text-blue-600 hover:text-blue-900">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-4">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection 