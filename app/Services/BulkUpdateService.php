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

    /**
     * 创建批量更新任务
     * 
     * @param string $filePath 上传的文件路径
     * @return array 创建结果
     */
    public function createBulkUpdateTask($filePath)
    {
        try {
            // 验证文件
            $validation = $this->excelService->validateExcelFile($filePath);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => $validation['message']
                ];
            }

            // 解析文件
            $parseResult = $this->excelService->parseProductUpdateFile($filePath);
            if (!$parseResult['success']) {
                return [
                    'success' => false,
                    'message' => $parseResult['message']
                ];
            }

            // 验证产品是否存在于本地数据库
            $validatedProducts = $this->validateProducts($parseResult['products']);

            // 创建任务记录
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
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => '创建任务失败：' . $e->getMessage()
            ];
        }
    }

    /**
     * 执行批量更新任务
     * 
     * @param int $taskId 任务ID
     * @return array 执行结果
     */
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

            foreach ($products as $index => $product) {
                try {
                    // 添加延迟以避免API限制
                    if ($index > 0) {
                        sleep(1); // 每秒最多1个请求，保守处理
                    }

                    Log::info('正在更新产品', [
                        'task_id' => $taskId,
                        'sku' => $product['sku'],
                        'new_title' => $product['title']
                    ]);

                    // 调用Lazada API更新产品
                    $apiResult = $this->lazadaApiService->updateProduct($product['sku'], [
                        'name' => $product['title']
                    ]);

                    if ($apiResult && (!isset($apiResult['code']) || $apiResult['code'] === '0')) {
                        // 更新成功，同时更新本地数据库
                        $this->updateLocalProduct($product['sku'], $product['title']);
                        
                        $results[] = [
                            'sku' => $product['sku'],
                            'title' => $product['title'],
                            'status' => 'success',
                            'message' => '更新成功'
                        ];
                        $successCount++;
                    } else {
                        $results[] = [
                            'sku' => $product['sku'],
                            'title' => $product['title'],
                            'status' => 'failed',
                            'message' => $apiResult['message'] ?? '未知错误',
                            'api_response' => $apiResult
                        ];
                        $failCount++;
                    }

                } catch (\Exception $e) {
                    Log::error('更新产品失败', [
                        'task_id' => $taskId,
                        'sku' => $product['sku'],
                        'error' => $e->getMessage()
                    ]);

                    $results[] = [
                        'sku' => $product['sku'],
                        'title' => $product['title'],
                        'status' => 'failed',
                        'message' => $e->getMessage()
                    ];
                    $failCount++;
                }

                // 更新任务进度
                $task->update([
                    'processed_items' => $index + 1,
                    'successful_items' => $successCount,
                    'failed_items' => $failCount,
                    'results' => $results
                ]);
            }

            // 完成任务
            $task->update([
                'status' => $failCount === 0 ? 'completed' : 'completed',
                'completed_at' => now()
            ]);

            Log::info('批量更新任务完成', [
                'task_id' => $taskId,
                'total' => count($products),
                'success' => $successCount,
                'failed' => $failCount
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
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => '任务执行失败：' . $e->getMessage()
            ];
        }
    }

    /**
     * 验证产品是否存在
     * 
     * @param array $products 产品列表
     * @return array 验证结果
     */
    private function validateProducts($products)
    {
        $validProducts = [];
        $errors = [];

        foreach ($products as $product) {
            // 检查产品是否存在于本地数据库
            $localProduct = Product::where('sku', $product['sku'])->first();
            
            if (!$localProduct) {
                $errors[] = "SKU {$product['sku']} 在本地数据库中不存在";
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
