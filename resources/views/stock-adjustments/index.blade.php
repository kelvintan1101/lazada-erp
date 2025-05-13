@extends('layouts.app')

@section('content')
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Stock Adjustment History</h2>
            <p class="text-gray-500">{{ $product->name }}</p>
        </div>
        <div class="mt-4 md:mt-0">
            <a href="{{ route('stock-adjustments.create', $product) }}" class="btn btn-primary inline-flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                New Adjustment
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            @if($adjustments->isEmpty())
                <div class="text-center py-8">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="mt-2 text-gray-500">No stock adjustments found.</p>
                    <a href="{{ route('stock-adjustments.create', $product) }}" class="btn btn-primary mt-4 inline-flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Create Stock Adjustment
                    </a>
                </div>
            @else
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
                            @foreach($adjustments as $adjustment)
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
                <div class="p-4">
                    {{ $adjustments->links() }}
                </div>
            @endif
        </div>
    </div>
    
    <div class="mt-6">
        <a href="{{ route('products.show', $product) }}" class="btn btn-secondary inline-flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
            </svg>
            Back to Product
        </a>
    </div>
@endsection 