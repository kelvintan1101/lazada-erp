<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Log;

class ProductService
{
    private LazadaApiService $lazadaApiService;

    public function __construct(LazadaApiService $lazadaApiService)
    {
        $this->lazadaApiService = $lazadaApiService;
    }

    public function syncProducts(): array
    {
        $offset = 0;
        $limit = 50;
        $totalSynced = 0;
        $totalMarkedInactive = 0;
        $hasMore = true;

        try {
            // Phase 1: Mark all existing products as inactive before sync starts
            Log::info("Starting product sync - marking all products as inactive");
            $markedInactiveCount = Product::where('is_active', true)->update(['is_active' => false]);
            Log::info("Marked $markedInactiveCount products as inactive for verification");

            // Phase 2: Fetch products from Lazada and mark them as active
            while ($hasMore) {
                Log::info("Fetching products from Lazada", ['offset' => $offset, 'limit' => $limit]);
                
                $response = $this->lazadaApiService->getProducts($offset, $limit);
                
                // Check if the response has the expected structure
                if (!$response || !isset($response['data']) || !isset($response['data']['products'])) {
                    Log::error('Unexpected response structure from Lazada', ['response' => $response]);
                    return [
                        'success' => false,
                        'message' => 'Failed to fetch products from Lazada: Unexpected response structure',
                        'total_synced' => $totalSynced,
                        'response' => $response,
                    ];
                }
                
                $products = $response['data']['products'];
                $totalProducts = $response['data']['total_products'] ?? 0;
                
                Log::info("Received products from Lazada", [
                    'total_received' => count($products), 
                    'total_available' => $totalProducts
                ]);

                foreach ($products as $productData) {
                    $result = $this->saveProduct($productData);
                    if ($result) {
                        $totalSynced++;
                    }
                }

                $offset += $limit;
                $hasMore = $offset < $totalProducts && count($products) > 0;
            }

            // Phase 3: Count products that remain inactive (deleted from Lazada)
            $totalMarkedInactive = Product::where('is_active', false)->count();

            Log::info("Product sync completed", [
                'total_synced' => $totalSynced,
                'total_marked_inactive' => $totalMarkedInactive
            ]);

            $message = "Successfully synced $totalSynced products";
            if ($totalMarkedInactive > 0) {
                $message .= " and marked $totalMarkedInactive products as inactive (deleted from Lazada)";
            }

            return [
                'success' => true,
                'message' => $message,
                'total_synced' => $totalSynced,
                'total_marked_inactive' => $totalMarkedInactive,
            ];
        } catch (\Exception $e) {
            Log::error('Exception syncing products', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Error syncing products: ' . $e->getMessage(),
                'total_synced' => $totalSynced,
            ];
        }
    }

    private function saveProduct($productData): bool
    {
        try {
            if (!isset($productData['item_id'])) {
                Log::warning('Skipping product without item_id', ['product' => $productData]);
                return false;
            }

            // Extract the necessary information from Lazada product data
            $lazadaProductId = $productData['item_id'];
            $skuData = $productData['skus'][0] ?? null;
            
            if (!$skuData) {
                Log::warning('Skipping product without SKU data', ['item_id' => $lazadaProductId]);
                return false;
            }
            
            $attrs = $productData['attributes'] ?? [];
            
            Product::updateOrCreate(
                ['lazada_product_id' => $lazadaProductId],
                [
                    'name' => $attrs['name'] ?? 'Unknown Product',
                    'sku' => $skuData['SellerSku'] ?? '',
                    'price' => $skuData['price'] ?? 0,
                    'stock_quantity' => $skuData['quantity'] ?? 0,
                    'description' => $attrs['description'] ?? null,
                    'images' => $productData['images'] ?? null,
                    'raw_data_from_lazada' => $productData,
                    'synced_at' => now(),
                    'is_active' => true, // Mark as active since it exists in Lazada
                ]
            );
            
            return true;
        } catch (\Exception $e) {
            Log::error('Error saving product', [
                'item_id' => $productData['item_id'] ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function updateStock($productId, $newQuantity): array
    {
        $product = Product::findOrFail($productId);
        
        $response = $this->lazadaApiService->updateProductStock(
            $product->lazada_product_id,
            $product->sku,
            $newQuantity
        );

        if (!$response || isset($response['code']) && $response['code'] !== '0') {
            Log::error('Failed to update stock on Lazada', [
                'product_id' => $productId,
                'new_quantity' => $newQuantity,
                'response' => $response
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to update stock on Lazada: ' . ($response['message'] ?? 'Unknown error'),
            ];
        }

        // Update local database
        $product->update([
            'stock_quantity' => $newQuantity,
            'synced_at' => now(),
        ]);

        return [
            'success' => true,
            'message' => 'Stock updated successfully',
            'product' => $product,
        ];
    }

    public function getProductsWithLowStock($limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        $threshold = \App\Models\Setting::getSetting('low_stock_threshold', 10);

        return Product::active() // Only include active products
            ->where('stock_quantity', '<=', $threshold)
            ->orderBy('stock_quantity')
            ->limit($limit)
            ->get();
    }

    /**
     * Get inactive products (deleted from Lazada)
     * Useful for admin review and potential restoration
     */
    public function getInactiveProducts($limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return Product::inactive()
            ->orderBy('synced_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Restore an inactive product (mark as active)
     */
    public function restoreProduct($productId): array
    {
        try {
            $product = Product::findOrFail($productId);
            $product->markAsActive();

            Log::info('Product restored', [
                'product_id' => $productId,
                'sku' => $product->sku,
                'name' => $product->name
            ]);

            return [
                'success' => true,
                'message' => 'Product restored successfully',
                'product' => $product
            ];
        } catch (\Exception $e) {
            Log::error('Failed to restore product', [
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to restore product: ' . $e->getMessage()
            ];
        }
    }
}