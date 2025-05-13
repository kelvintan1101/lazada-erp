<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use App\Services\ProductService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $productService;
    protected $orderService;

    public function __construct(ProductService $productService, OrderService $orderService)
    {
        $this->productService = $productService;
        $this->orderService = $orderService;
    }

    public function index()
    {
        // Summary data for dashboard
        $totalProducts = Product::count();
        $lowStockProducts = $this->productService->getProductsWithLowStock(5);
        $recentOrders = $this->orderService->getRecentOrders(5);
        
        // Order statistics
        $pendingOrders = Order::byStatus('pending')->count();
        $processingOrders = Order::byStatus('ready_to_ship')->count();
        $shippedOrders = Order::byStatus('shipped')->count();
        
        // Sales data
        $totalSales = Order::sum('total_amount');
        $monthlySales = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');
        
        return view('dashboard.index', compact(
            'totalProducts',
            'lowStockProducts',
            'recentOrders',
            'pendingOrders',
            'processingOrders',
            'shippedOrders',
            'totalSales',
            'monthlySales'
        ));
    }
}