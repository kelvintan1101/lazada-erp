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

    public function syncProducts()
    {
        $offset = 0;
        $limit = 50;
        $totalSynced = 0;
        $hasMore = true;

        try {
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

            return [
                'success' => true,
                'message' => "Successfully synced $totalSynced products",
                'total_synced' => $totalSynced,
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

    private function saveProduct($productData)
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

    public function updateStock($productId, $newQuantity)
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

    public function getProductsWithLowStock($limit = 10)
    {
        $threshold = \App\Models\Setting::getSetting('low_stock_threshold', 10);
        
        return Product::where('stock_quantity', '<=', $threshold)
            ->orderBy('stock_quantity')
            ->limit($limit)
            ->get();
    }
}