<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockAdjustmentController extends Controller
{
    public function create(Product $product)
    {
        return view('stock-adjustments.create', compact('product'));
    }

    public function store(Request $request, Product $product)
    {
        $request->validate([
            'adjusted_quantity' => 'required|integer|not_in:0',
            'reason' => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction();
            
            // Create stock adjustment record
            StockAdjustment::create([
                'product_id' => $product->id,
                'adjusted_quantity' => $request->adjusted_quantity,
                'reason' => $request->reason,
                'adjusted_by_user_id' => auth()->id(),
            ]);
            
            // Update product stock quantity
            $newQuantity = $product->stock_quantity + $request->adjusted_quantity;
            
            if ($newQuantity < 0) {
                throw new \Exception("Cannot adjust to negative stock quantity.");
            }
            
            $product->update([
                'stock_quantity' => $newQuantity,
            ]);
            
            DB::commit();
            
            return redirect()->route('products.show', $product)
                ->with('success', 'Stock adjustment recorded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'Error recording stock adjustment: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function index(Product $product)
    {
        $adjustments = StockAdjustment::with('adjustedByUser')
            ->where('product_id', $product->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        return view('stock-adjustments.index', compact('product', 'adjustments'));
    }
}