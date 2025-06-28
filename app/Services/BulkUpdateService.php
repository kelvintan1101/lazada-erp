<?php

namespace App\Services;

use App\Models\BulkUpdateTask;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BulkUpdateService
{
    private LazadaApiService $lazadaApiService;
    private ExcelProcessingService $excelService;

    public function __construct(LazadaApiService $lazadaApiService, ExcelProcessingService $excelService)
    {
        $this->lazadaApiService = $lazadaApiService;
        $this->excelService = $excelService;
    }

    public function createBulkUpdateTask($filePath): array
    {
        try {
            \Log::info('Starting to create bulk update task', [
                'file_path' => $filePath,
                'file_exists' => Storage::exists($filePath)
            ]);

            // Validate file
            \Log::info('Starting file validation');
            $validation = $this->excelService->validateExcelFile($filePath);
            if (!$validation['valid']) {
                \Log::warning('File validation failed', ['message' => $validation['message']]);
                return [
                    'success' => false,
                    'message' => $validation['message']
                ];
            }
            \Log::info('File validation successful');

            // Parse file
            \Log::info('Starting to parse file');
            $parseResult = $this->excelService->parseProductUpdateFile($filePath);
            if (!$parseResult['success']) {
                \Log::warning('File parsing failed', ['message' => $parseResult['message']]);
                return [
                    'success' => false,
                    'message' => $parseResult['message']
                ];
            }
            \Log::info('File parsing successful', [
                'products_count' => count($parseResult['products']),
                'errors_count' => count($parseResult['errors'])
            ]);

            // Validate if products exist in local database
            \Log::info('Starting product validation');
            $validatedProducts = $this->validateProducts($parseResult['products']);
            \Log::info('Product validation completed', [
                'valid_products' => count($validatedProducts['valid_products']),
                'validation_errors' => count($validatedProducts['errors'])
            ]);

            // Create task record
            \Log::info('Starting to create task record');
            $task = BulkUpdateTask::create([
                'type' => 'product_title_update',
                'status' => 'pending',
                'file_path' => $filePath,
                'file_data' => $validatedProducts,
                'total_items' => count($validatedProducts['valid_products']),
                'processed_items' => 0,
                'successful_items' => 0,
                'failed_items' => 0,
                'results' => [],
                'errors' => array_merge($parseResult['errors'], $validatedProducts['errors'])
            ]);

            Log::info('Bulk update task created', [
                'task_id' => $task->id,
                'total_items' => $task->total_items,
                'file_path' => $filePath
            ]);

            return [
                'success' => true,
                'task_id' => $task->id,
                'total_items' => $task->total_items,
                'valid_products' => count($validatedProducts['valid_products']),
                'errors' => $task->errors
            ];

        } catch (\Exception $e) {
            Log::error('Failed to create bulk update task', [
                'file_path' => $filePath,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file_location' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Task creation failed: ' . $e->getMessage()
            ];
        }
    }

    public function executeBulkUpdateTask($taskId): array
    {
        $task = BulkUpdateTask::find($taskId);
        if (!$task) {
            return [
                'success' => false,
                'message' => 'Task does not exist'
            ];
        }

        if ($task->status !== 'pending') {
            return [
                'success' => false,
                'message' => 'Task status is incorrect, cannot execute'
            ];
        }

        try {
            // Update task status
            $task->update([
                'status' => 'processing',
                'started_at' => now()
            ]);

            $products = $task->file_data['valid_products'] ?? [];
            $results = [];
            $successCount = 0;
            $failCount = 0;

            Log::info('Starting bulk update task execution', [
                'task_id' => $taskId,
                'total_products' => count($products)
            ]);

            foreach ($products as $index => $product) {
                try {
                    // Add delay to avoid API limits (Lazada API recommends max 2 requests per second)
                    if ($index > 0) {
                        sleep(2); // 2 second delay, conservative handling
                    }

                    Log::info('Updating product', [
                        'task_id' => $taskId,
                        'index' => $index + 1,
                        'total' => count($products),
                        'sku' => $product['sku'],
                        'new_title' => $product['title']
                    ]);

                    // Call Lazada API to update product title
                    $apiResult = $this->lazadaApiService->updateProduct($product['sku'], [
                        'name' => $product['title']
                    ]);

                    // Check API response
                    $isSuccess = false;
                    $message = '';

                    if ($apiResult) {
                        // Lazada API successful response usually has code: "0"
                        if (isset($apiResult['code']) && $apiResult['code'] === '0') {
                            $isSuccess = true;
                            $message = 'Update successful';
                        } elseif (!isset($apiResult['code'])) {
                            // Some successful responses may not have code field
                            $isSuccess = true;
                            $message = 'Update successful';
                        } else {
                            $message = $apiResult['message'] ?? 'Update failed: unknown error';
                        }
                    } else {
                        $message = 'Update failed: API returned empty response';
                    }

                    if ($isSuccess) {
                        // Update successful, also update local database
                        $this->updateLocalProduct($product['sku'], $product['title']);
                        
                        $results[] = [
                            'sku' => $product['sku'],
                            'title' => $product['title'],
                            'status' => 'success',
                            'message' => $message,
                            'api_response' => $apiResult
                        ];
                        $successCount++;

                        Log::info('Product update successful', [
                            'task_id' => $taskId,
                            'sku' => $product['sku'],
                            'response' => $apiResult
                        ]);
                    } else {
                        $results[] = [
                            'sku' => $product['sku'],
                            'title' => $product['title'],
                            'status' => 'failed',
                            'message' => $message,
                            'api_response' => $apiResult
                        ];
                        $failCount++;

                        Log::warning('Product update failed', [
                            'task_id' => $taskId,
                            'sku' => $product['sku'],
                            'message' => $message,
                            'response' => $apiResult
                        ]);
                    }

                } catch (\Exception $e) {
                    Log::error('Exception occurred while updating product', [
                        'task_id' => $taskId,
                        'sku' => $product['sku'],
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);

                    $results[] = [
                        'sku' => $product['sku'],
                        'title' => $product['title'],
                        'status' => 'failed',
                        'message' => 'System error: ' . $e->getMessage()
                    ];
                    $failCount++;
                }

                // Update task progress
                $processedItems = $index + 1;
                $progressPercentage = round(($processedItems / count($products)) * 100);
                
                $task->update([
                    'processed_items' => $processedItems,
                    'successful_items' => $successCount,
                    'failed_items' => $failCount,
                    'results' => $results,
                    'progress_percentage' => $progressPercentage
                ]);

                Log::debug('Task progress updated', [
                    'task_id' => $taskId,
                    'processed' => $processedItems,
                    'total' => count($products),
                    'success' => $successCount,
                    'failed' => $failCount,
                    'progress' => $progressPercentage . '%'
                ]);
            }

            // Complete task
            $finalStatus = $failCount === 0 ? 'completed' : 'completed';
            $task->update([
                'status' => $finalStatus,
                'completed_at' => now(),
                'progress_percentage' => 100
            ]);

            Log::info('Bulk update task completed', [
                'task_id' => $taskId,
                'total' => count($products),
                'success' => $successCount,
                'failed' => $failCount,
                'final_status' => $finalStatus
            ]);

            return [
                'success' => true,
                'total' => count($products),
                'successful' => $successCount,
                'failed' => $failCount,
                'results' => $results
            ];

        } catch (\Exception $e) {
            // Task execution failed
            $task->update([
                'status' => 'failed',
                'completed_at' => now(),
                'errors' => array_merge($task->errors ?? [], [$e->getMessage()])
            ]);

            Log::error('Bulk update task execution failed', [
                'task_id' => $taskId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Task execution failed: ' . $e->getMessage()
            ];
        }
    }

    private function validateProducts($products)
{
    $validProducts = [];
    $errors = [];

    foreach ($products as $product) {
        // Basic validation, do not check local database
        if (empty($product['sku'])) {
            $errors[] = "SKU cannot be empty";
            continue;
        }
        
        if (empty($product['title'])) {
            $errors[] = "Product title cannot be empty";
            continue;
        }

        // Validate SKU format (usually numeric)
        if (!preg_match('/^[0-9]+$/', $product['sku'])) {
            $errors[] = "SKU {$product['sku']} format is incorrect, should be pure numeric";
            continue;
        }

        // Validate title length
        if (strlen($product['title']) > 255) {
            $errors[] = "SKU {$product['sku']} product title is too long (over 255 characters)";
            continue;
        }

        $validProducts[] = $product;
    }

    return [
        'valid_products' => $validProducts,
        'errors' => $errors
    ];
}

    /**
     * Update local product information
     * 
     * @param string $sku Product SKU
     * @param string $newTitle New title
     */
    private function updateLocalProduct($sku, $newTitle)
    {
        try {
            // Only update active products (soft delete implementation)
            Product::active()->where('sku', $sku)->update([
                'name' => $newTitle,
                'synced_at' => now()
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to update local product information', [
                'sku' => $sku,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get task status
     * 
     * @param int $taskId Task ID
     * @return array Task status
     */
    public function getTaskStatus($taskId): array
    {
        $task = BulkUpdateTask::find($taskId);
        if (!$task) {
            return [
                'success' => false,
                'message' => 'Task does not exist'
            ];
        }

        return [
            'success' => true,
            'task' => [
                'id' => $task->id,
                'status' => $task->status,
                'total_items' => $task->total_items,
                'processed_items' => $task->processed_items,
                'successful_items' => $task->successful_items,
                'failed_items' => $task->failed_items,
                'progress_percentage' => $task->getProgressPercentage(),
                'success_rate' => $task->getSuccessRate(),
                'started_at' => $task->started_at,
                'completed_at' => $task->completed_at,
                'errors' => $task->errors,
                'results' => $task->results
            ]
        ];
    }
}
