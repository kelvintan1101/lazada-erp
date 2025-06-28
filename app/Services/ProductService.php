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
            // Phase 1: Mark active products for verification (ERP-centric approach)
            Log::info("Starting product sync - marking active products for Lazada verification");
            $activeProductsCount = Product::where('status', Product::STATUS_ACTIVE)->count();

            // Mark active products as pending verification (we'll restore them if found in Lazada)
            Product::where('status', Product::STATUS_ACTIVE)
                ->update(['status' => Product::STATUS_DELETED_FROM_LAZADA]);

            Log::info("Marked $activeProductsCount active products for Lazada verification");

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

            // Phase 3: Count products that were deleted from Lazada
            $totalDeletedFromLazada = Product::where('status', Product::STATUS_DELETED_FROM_LAZADA)->count();

            Log::info("Product sync completed", [
                'total_synced' => $totalSynced,
                'total_deleted_from_lazada' => $totalDeletedFromLazada
            ]);

            $message = "Successfully synced $totalSynced products";

            return [
                'success' => true,
                'message' => $message,
                'total_synced' => $totalSynced,
                'total_deleted_from_lazada' => $totalDeletedFromLazada,
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
                    'status' => Product::STATUS_ACTIVE, // Mark as active since it exists in Lazada
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

        // Use the new sellable stock adjustment API
        $response = $this->lazadaApiService->adjustSellableStock(
            $product->lazada_product_id,
            $product->sku,
            $newQuantity
        );

        if (!$response || isset($response['code']) && $response['code'] !== '0') {
            Log::error('Failed to adjust sellable stock on Lazada', [
                'product_id' => $productId,
                'lazada_product_id' => $product->lazada_product_id,
                'seller_sku' => $product->sku,
                'new_quantity' => $newQuantity,
                'response' => $response
            ]);

            return [
                'success' => false,
                'message' => 'Failed to adjust stock on Lazada: ' . ($response['message'] ?? 'Unknown error'),
            ];
        }

        // Update local database
        $product->update([
            'stock_quantity' => $newQuantity,
            'synced_at' => now(),
        ]);

        Log::info('Stock adjusted successfully', [
            'product_id' => $productId,
            'lazada_product_id' => $product->lazada_product_id,
            'seller_sku' => $product->sku,
            'old_quantity' => $product->stock_quantity,
            'new_quantity' => $newQuantity
        ]);

        return [
            'success' => true,
            'message' => 'Stock adjusted successfully on Lazada and updated locally',
            'product' => $product,
        ];
    }

    public function getProductsWithLowStock($limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        $threshold = \App\Models\Setting::getSetting('low_stock_threshold', 10);

        return Product::active() // Only active products (exist on Lazada)
            ->where('stock_quantity', '<=', $threshold)
            ->orderBy('stock_quantity')
            ->limit($limit)
            ->get();
    }

    /**
     * Get products deleted from Lazada (for admin review)
     */
    public function getProductsDeletedFromLazada($limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return Product::deletedFromLazada()
            ->orderBy('synced_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get simplified status statistics for dashboard
     */
    public function getStatusStatistics(): array
    {
        return [
            'active' => Product::active()->count(),
            'deleted_from_lazada' => Product::deletedFromLazada()->count(),
            'total' => Product::count()
        ];
    }
}