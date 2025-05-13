<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    private LazadaApiService $lazadaApiService;

    public function __construct(LazadaApiService $lazadaApiService)
    {
        $this->lazadaApiService = $lazadaApiService;
    }

    public function syncOrders($status = null, $startTime = null, $endTime = null)
    {
        $offset = 0;
        $limit = 50;
        $totalSynced = 0;
        $hasMore = true;

        while ($hasMore) {
            $response = $this->lazadaApiService->getOrders($status, $startTime, $endTime, $offset, $limit);
            
            if (!$response || !isset($response['data']['orders'])) {
                Log::error('Failed to fetch orders from Lazada', ['response' => $response]);
                return [
                    'success' => false,
                    'message' => 'Failed to fetch orders from Lazada',
                    'total_synced' => $totalSynced,
                ];
            }

            $orders = $response['data']['orders'];
            $totalOrders = $response['data']['count'] ?? 0;

            foreach ($orders as $orderData) {
                $this->saveOrder($orderData);
                $totalSynced++;
            }

            $offset += $limit;
            $hasMore = $offset < $totalOrders;
        }

        return [
            'success' => true,
            'message' => "Successfully synced $totalSynced orders",
            'total_synced' => $totalSynced,
        ];
    }

    private function saveOrder($orderData)
    {
        try {
            DB::beginTransaction();

            // Create or update order
            $order = Order::updateOrCreate(
                ['lazada_order_id' => $orderData['order_id']],
                [
                    'lazada_order_number' => $orderData['order_number'],
                    'customer_name' => $orderData['customer_first_name'] . ' ' . $orderData['customer_last_name'],
                    'order_date' => \Carbon\Carbon::parse($orderData['created_at']),
                    'status' => $orderData['status'],
                    'total_amount' => $orderData['price'],
                    'shipping_address' => [
                        'address' => $orderData['address_shipping'],
                        'city' => $orderData['city'],
                        'country' => $orderData['country'],
                        'zipcode' => $orderData['post_code'],
                    ],
                    'payment_method' => $orderData['payment_method'],
                    'raw_data_from_lazada' => $orderData,
                    'synced_at' => now(),
                ]
            );

            // Get order items
            $orderItemsResponse = $this->lazadaApiService->getOrderItems($orderData['order_id']);

            if (!$orderItemsResponse || !isset($orderItemsResponse['data'])) {
                Log::error('Failed to fetch order items from Lazada', [
                    'order_id' => $orderData['order_id'],
                    'response' => $orderItemsResponse
                ]);
                throw new \Exception('Failed to fetch order items from Lazada');
            }

            foreach ($orderItemsResponse['data'] as $itemData) {
                // Find the product in our database
                $product = Product::where('lazada_product_id', $itemData['product_id'])->first();

                // Create or update order item
                OrderItem::updateOrCreate(
                    ['lazada_order_item_id' => $itemData['order_item_id']],
                    [
                        'order_id' => $order->id,
                        'product_id' => $product ? $product->id : null,
                        'lazada_product_id' => $itemData['product_id'],
                        'product_name' => $itemData['name'],
                        'sku' => $itemData['sku'],
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['item_price'],
                        'total_price' => $itemData['item_price'] * $itemData['quantity'],
                        'raw_data_from_lazada' => $itemData,
                    ]
                );
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving order: ' . $e->getMessage(), [
                'order_data' => $orderData,
                'exception' => $e,
            ]);
            return false;
        }
    }

    public function updateOrderStatus($orderId, $newStatus)
    {
        $order = Order::findOrFail($orderId);
        
        $response = $this->lazadaApiService->updateOrderStatus(
            $order->lazada_order_id,
            $newStatus
        );

        if (!$response || isset($response['code']) && $response['code'] !== '0') {
            Log::error('Failed to update order status on Lazada', [
                'order_id' => $orderId,
                'new_status' => $newStatus,
                'response' => $response
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to update order status on Lazada: ' . ($response['message'] ?? 'Unknown error'),
            ];
        }

        // Update local database
        $order->update([
            'status' => $newStatus,
            'synced_at' => now(),
        ]);

        return [
            'success' => true,
            'message' => 'Order status updated successfully',
            'order' => $order,
        ];
    }

    public function getRecentOrders($limit = 10)
    {
        return Order::with('orderItems')->recentOrders($limit)->get();
    }

    public function getOrdersByStatus($status)
    {
        return Order::with('orderItems')->byStatus($status)->get();
    }
}