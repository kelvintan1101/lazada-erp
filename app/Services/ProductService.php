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

        while ($hasMore) {
            $response = $this->lazadaApiService->getProducts($offset, $limit);

            if (!$response || !isset($response['data']['products'])) {
                Log::error('Failed to fetch products from Lazada', ['response' => $response]);
                return [
                    'success' => false,
                    'message' => 'Failed to fetch products from Lazada',
                    'total_synced' => $totalSynced,
                ];
            }

            $products = $response['data']['products'];
            $totalProducts = $response['data']['total_products'] ?? 0;

            foreach ($products as $productData) {
                $this->saveProduct($productData);
                $totalSynced++;
            }

            $offset += $limit;
            $hasMore = $offset < $totalProducts;
        }

        return [
            'success' => true,
            'message' => "Successfully synced $totalSynced products",
            'total_synced' => $totalSynced,
        ];
    }

    private function saveProduct($productData)
    {
        // Extract the necessary information from Lazada product data
        // This structure may vary based on Lazada's API response format
        $lazadaProductId = $productData['item_id'];
        
        Product::updateOrCreate(
            ['lazada_product_id' => $lazadaProductId],
            [
                'name' => $productData['attributes']['name'] ?? '',
                'sku' => $productData['skus'][0]['SellerSku'] ?? '',
                'price' => $productData['skus'][0]['price'] ?? 0,
                'stock_quantity' => $productData['skus'][0]['quantity'] ?? 0,
                'description' => $productData['attributes']['description'] ?? null,
                'images' => $productData['images'] ?? null,
                'raw_data_from_lazada' => $productData,
                'synced_at' => now(),
            ]
        );
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