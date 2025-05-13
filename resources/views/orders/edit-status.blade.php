@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Update Order Status</h2>
        <p class="text-gray-500">Order #{{ $order->lazada_order_number }}</p>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-gray-700">Current Status: 
                <span class="badge {{ $order->status == 'pending' ? 'badge-warning' : ($order->status == 'shipped' || $order->status == 'delivered' ? 'badge-success' : ($order->status == 'canceled' ? 'badge-danger' : 'badge-info')) }}">
                    {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                </span>
            </h3>
        </div>
        <div class="card-body">
            <form action="{{ route('orders.update-status', $order) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="space-y-6">
                    <div>
                        <label for="status" class="form-label required">New Status</label>
                        <select name="status" id="status" class="form-input" required>
                            <option value="">Select Status</option>
                            <option value="pending" {{ old('status', $order->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="packed" {{ old('status', $order->status) == 'packed' ? 'selected' : '' }}>Packed</option>
                            <option value="ready_to_ship" {{ old('status', $order->status) == 'ready_to_ship' ? 'selected' : '' }}>Ready to Ship</option>
                            <option value="shipped" {{ old('status', $order->status) == 'shipped' ? 'selected' : '' }}>Shipped</option>
                            <option value="delivered" {{ old('status', $order->status) == 'delivered' ? 'selected' : '' }}>Delivered</option>
                            <option value="canceled" {{ old('status', $order->status) == 'canceled' ? 'selected' : '' }}>Canceled</option>
                        </select>
                        <p class="mt-1 text-sm text-gray-500">
                            This will update the status on Lazada and in the local database.
                        </p>
                        <p class="mt-1 text-sm text-red-500">
                            Note: You can only update an order to specific statuses based on its current status as per Lazada's workflow.
                        </p>
                    </div>

                    <div class="flex items-center space-x-4">
                        <button type="submit" class="btn btn-primary">Update Status</button>
                        <a href="{{ route('orders.show', $order) }}" class="btn btn-secondary">Cancel</a>
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