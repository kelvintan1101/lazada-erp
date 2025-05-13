<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Request $request)
    {
        $query = Order::with('orderItems');

        // Handle search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('lazada_order_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        // Handle status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Handle date range
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('order_date', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('order_date', '<=', $request->end_date);
        }

        // Handle sorting
        $sortField = $request->sort ?? 'order_date';
        $sortDir = $request->dir ?? 'desc';
        
        $query->orderBy($sortField, $sortDir);

        $orders = $query->paginate(15);
        
        return view('orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load('orderItems.product');
        return view('orders.show', compact('order'));
    }

    public function sync(Request $request)
    {
        $status = $request->status;
        $startTime = $request->has('start_date') ? \Carbon\Carbon::parse($request->start_date) : null;
        $endTime = $request->has('end_date') ? \Carbon\Carbon::parse($request->end_date) : null;
        
        $result = $this->orderService->syncOrders($status, $startTime, $endTime);
        
        if ($result['success']) {
            return redirect()->route('orders.index')
                ->with('success', $result['message']);
        }
        
        return redirect()->route('orders.index')
            ->with('error', $result['message']);
    }

    public function editStatus(Order $order)
    {
        return view('orders.edit-status', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|string|in:pending,packed,ready_to_ship,shipped,delivered,canceled',
        ]);

        $result = $this->orderService->updateOrderStatus(
            $order->id,
            $request->status
        );

        if ($result['success']) {
            return redirect()->route('orders.show', $order)
                ->with('success', $result['message']);
        }

        return redirect()->route('orders.show', $order)
            ->with('error', $result['message']);
    }
}