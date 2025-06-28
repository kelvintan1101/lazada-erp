<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index(Request $request)
    {
        // Start with active products only (soft delete implementation)
        $query = Product::active();

        // Handle search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Handle filters
        if ($request->has('low_stock') && $request->low_stock == 1) {
            $threshold = \App\Models\Setting::getSetting('low_stock_threshold', 10);
            $query->where('stock_quantity', '<=', $threshold);
        }

        // Handle sorting
        $sortField = $request->sort ?? 'name';
        $sortDir = $request->dir ?? 'asc';
        
        $query->orderBy($sortField, $sortDir);

        $products = $query->paginate(15);
        
        return view('products.index', compact('products'));
    }

    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    public function sync()
    {
        $result = $this->productService->syncProducts();

        // Handle AJAX requests
        if (request()->ajax()) {
            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'total_synced' => $result['total_synced'] ?? 0
            ]);
        }

        // Handle regular requests (fallback)
        if ($result['success']) {
            return redirect()->route('products.index')
                ->with('success', $result['message']);
        }

        return redirect()->route('products.index')
            ->with('error', $result['message']);
    }

    public function editStock(Product $product)
    {
        return view('products.edit-stock', compact('product'));
    }

    public function updateStock(Request $request, Product $product)
    {
        $request->validate([
            'stock_quantity' => 'required|integer|min:0',
        ]);

        $result = $this->productService->updateStock(
            $product->id,
            $request->stock_quantity
        );

        if ($result['success']) {
            return redirect()->route('products.show', $product)
                ->with('success', $result['message']);
        }

        return redirect()->route('products.show', $product)
            ->with('error', $result['message']);
    }
}