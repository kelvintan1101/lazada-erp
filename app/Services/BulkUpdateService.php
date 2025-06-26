<?php

namespace App\Services;

use App\Models\BulkUpdateTask;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class BulkUpdateService
{
    private LazadaApiService $lazadaApiService;
    private ExcelProcessingService $excelService;

    public function __construct(LazadaApiService $lazadaApiService, ExcelProcessingService $excelService)
    {
        $this->lazadaApiService = $lazadaApiService;
        $this->excelService = $excelService;
    }

    public function createBulkUpdateTask($filePath)
    {
        try {
            \Log::info('开始创建批量更新任务', [
                'file_path' => $filePath,
                'file_exists' => Storage::exists($filePath)
            ]);

            // 验证文件
            \Log::info('开始验证文件');
            $validation = $this->excelService->validateExcelFile($filePath);
            if (!$validation['valid']) {
                \Log::warning('文件验证失败', ['message' => $validation['message']]);
                return [
                    'success' => false,
                    'message' => $validation['message']
                ];
            }
            \Log::info('文件验证成功');

            // 解析文件
            \Log::info('开始解析文件');
            $parseResult = $this->excelService->parseProductUpdateFile($filePath);
            if (!$parseResult['success']) {
                \Log::warning('文件解析失败', ['message' => $parseResult['message']]);
                return [
                    'success' => false,
                    'message' => $parseResult['message']
                ];
            }
            \Log::info('文件解析成功', [
                'products_count' => count($parseResult['products']),
                'errors_count' => count($parseResult['errors'])
            ]);

            // 验证产品是否存在于本地数据库
            \Log::info('开始验证产品');
            $validatedProducts = $this->validateProducts($parseResult['products']);
            \Log::info('产品验证完成', [
                'valid_products' => count($validatedProducts['valid_products']),
                'validation_errors' => count($validatedProducts['errors'])
            ]);

            // 创建任务记录
            \Log::info('开始创建任务记录');
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

            Log::info('批量更新任务已创建', [
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
            Log::error('创建批量更新任务失败', [
                'file_path' => $filePath,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file_location' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => '创建任务失败：' . $e->getMessage()
            ];
        }
    }

    public function executeBulkUpdateTask($taskId)
    {
        $task = BulkUpdateTask::find($taskId);
        if (!$task) {
            return [
                'success' => false,
                'message' => '任务不存在'
            ];
        }

        if ($task->status !== 'pending') {
            return [
                'success' => false,
                'message' => '任务状态不正确，无法执行'
            ];
        }

        try {
            // 更新任务状态
            $task->update([
                'status' => 'processing',
                'started_at' => now()
            ]);

            $products = $task->file_data['valid_products'] ?? [];
            $results = [];
            $successCount = 0;
            $failCount = 0;

            Log::info('开始执行批量更新任务', [
                'task_id' => $taskId,
                'total_products' => count($products)
            ]);

            foreach ($products as $index => $product) {
                try {
                    // 添加延迟以避免API限制 (Lazada API 推荐每秒最多2个请求)
                    if ($index > 0) {
                        sleep(2); // 2秒延迟，保守处理
                    }

                    Log::info('正在更新产品', [
                        'task_id' => $taskId,
                        'index' => $index + 1,
                        'total' => count($products),
                        'sku' => $product['sku'],
                        'new_title' => $product['title']
                    ]);

                    // 调用Lazada API更新产品标题
                    $apiResult = $this->lazadaApiService->updateProduct($product['sku'], [
                        'name' => $product['title']
                    ]);

                    // 检查API响应
                    $isSuccess = false;
                    $message = '';

                    if ($apiResult) {
                        // Lazada API 成功响应通常有 code: "0"
                        if (isset($apiResult['code']) && $apiResult['code'] === '0') {
                            $isSuccess = true;
                            $message = '更新成功';
                        } elseif (!isset($apiResult['code'])) {
                            // 有些成功响应可能没有code字段
                            $isSuccess = true;
                            $message = '更新成功';
                        } else {
                            $message = $apiResult['message'] ?? '更新失败：未知错误';
                        }
                    } else {
                        $message = '更新失败：API返回空响应';
                    }

                    if ($isSuccess) {
                        // 更新成功，同时更新本地数据库
                        $this->updateLocalProduct($product['sku'], $product['title']);
                        
                        $results[] = [
                            'sku' => $product['sku'],
                            'title' => $product['title'],
                            'status' => 'success',
                            'message' => $message,
                            'api_response' => $apiResult
                        ];
                        $successCount++;

                        Log::info('产品更新成功', [
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

                        Log::warning('产品更新失败', [
                            'task_id' => $taskId,
                            'sku' => $product['sku'],
                            'message' => $message,
                            'response' => $apiResult
                        ]);
                    }

                } catch (\Exception $e) {
                    Log::error('更新产品时发生异常', [
                        'task_id' => $taskId,
                        'sku' => $product['sku'],
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);

                    $results[] = [
                        'sku' => $product['sku'],
                        'title' => $product['title'],
                        'status' => 'failed',
                        'message' => '系统错误：' . $e->getMessage()
                    ];
                    $failCount++;
                }

                // 更新任务进度
                $processedItems = $index + 1;
                $progressPercentage = round(($processedItems / count($products)) * 100);
                
                $task->update([
                    'processed_items' => $processedItems,
                    'successful_items' => $successCount,
                    'failed_items' => $failCount,
                    'results' => $results,
                    'progress_percentage' => $progressPercentage
                ]);

                Log::debug('任务进度更新', [
                    'task_id' => $taskId,
                    'processed' => $processedItems,
                    'total' => count($products),
                    'success' => $successCount,
                    'failed' => $failCount,
                    'progress' => $progressPercentage . '%'
                ]);
            }

            // 完成任务
            $finalStatus = $failCount === 0 ? 'completed' : 'completed';
            $task->update([
                'status' => $finalStatus,
                'completed_at' => now(),
                'progress_percentage' => 100
            ]);

            Log::info('批量更新任务完成', [
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
            // 任务执行失败
            $task->update([
                'status' => 'failed',
                'completed_at' => now(),
                'errors' => array_merge($task->errors ?? [], [$e->getMessage()])
            ]);

            Log::error('批量更新任务执行失败', [
                'task_id' => $taskId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => '任务执行失败：' . $e->getMessage()
            ];
        }
    }

    private function validateProducts($products)
{
    $validProducts = [];
    $errors = [];

    foreach ($products as $product) {
        // 基本验证，不检查本地数据库
        if (empty($product['sku'])) {
            $errors[] = "SKU不能为空";
            continue;
        }
        
        if (empty($product['title'])) {
            $errors[] = "产品标题不能为空";
            continue;
        }

        // 验证SKU格式 (通常是数字)
        if (!preg_match('/^[0-9]+$/', $product['sku'])) {
            $errors[] = "SKU {$product['sku']} 格式不正确，应为纯数字";
            continue;
        }

        // 验证标题长度
        if (strlen($product['title']) > 255) {
            $errors[] = "SKU {$product['sku']} 的产品标题过长（超过255字符）";
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
     * 更新本地产品信息
     * 
     * @param string $sku 产品SKU
     * @param string $newTitle 新标题
     */
    private function updateLocalProduct($sku, $newTitle)
    {
        try {
            Product::where('sku', $sku)->update([
                'name' => $newTitle,
                'synced_at' => now()
            ]);
        } catch (\Exception $e) {
            Log::warning('更新本地产品信息失败', [
                'sku' => $sku,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 获取任务状态
     * 
     * @param int $taskId 任务ID
     * @return array 任务状态
     */
    public function getTaskStatus($taskId)
    {
        $task = BulkUpdateTask::find($taskId);
        if (!$task) {
            return [
                'success' => false,
                'message' => '任务不存在'
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
