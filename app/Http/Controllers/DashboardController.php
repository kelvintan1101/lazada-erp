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
        // Aggregated query optimization - merge multiple individual queries into 2 efficient queries
        
        // 1. Product statistics - simplified status breakdown
        $productStats = Product::selectRaw('
            COUNT(*) as total_products,
            COUNT(CASE WHEN status = ? THEN 1 END) as active_products,
            COUNT(CASE WHEN status = ? THEN 1 END) as deleted_from_lazada_products
        ', [
            Product::STATUS_ACTIVE,
            Product::STATUS_DELETED_FROM_LAZADA
        ])->first();
        
        // 2. Order statistics - aggregated query (reduced from 6 queries to 1)
        $orderStats = Order::selectRaw('
            COUNT(*) as total_orders,
            COUNT(CASE WHEN status = "pending" THEN 1 END) as pending_orders,
            COUNT(CASE WHEN status = "ready_to_ship" THEN 1 END) as processing_orders,
            COUNT(CASE WHEN status = "shipped" THEN 1 END) as shipped_orders,
            COALESCE(SUM(total_amount), 0) as total_sales,
            COALESCE(SUM(CASE 
                WHEN MONTH(created_at) = ? AND YEAR(created_at) = ? 
                THEN total_amount 
                ELSE 0 
            END), 0) as monthly_sales
        ', [now()->month, now()->year])->first();
        
        // 3. Get low stock products (unchanged, as specific product data is needed)
        $lowStockProducts = $this->productService->getProductsWithLowStock(5);
        
        // 4. Get recent orders (unchanged, as specific order data is needed)
        $recentOrders = $this->orderService->getRecentOrders(5);
        
        // Extract data from aggregated results
        $totalProducts = $productStats->total_products;
        $pendingOrders = $orderStats->pending_orders;
        $processingOrders = $orderStats->processing_orders;
        $shippedOrders = $orderStats->shipped_orders;
        $totalSales = $orderStats->total_sales;
        $monthlySales = $orderStats->monthly_sales;
        
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