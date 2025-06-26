<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessBulkUpdateJob;
use App\Services\BulkUpdateService;
use App\Services\ExcelProcessingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BulkUpdateController extends Controller
{
    private BulkUpdateService $bulkUpdateService;
    private ExcelProcessingService $excelService;

    public function __construct(BulkUpdateService $bulkUpdateService, ExcelProcessingService $excelService)
    {
        $this->bulkUpdateService = $bulkUpdateService;
        $this->excelService = $excelService;
    }

    /**
     * 显示批量更新页面
     */
    public function index()
    {
        return view('bulk-update.index');
    }

    public function authCheck()
{
    try {
        // 直接检查数据库中是否有有效的token
        $token = \App\Models\LazadaToken::latest()->first();
        
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Lazada授权不存在，请先在设置页面进行授权'
            ], 403);
        }

        // 检查token是否过期
        if ($token->expires_at && now()->gt($token->expires_at)) {
            return response()->json([
                'success' => false,
                'message' => 'Lazada授权已过期，请重新授权'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lazada授权正常'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lazada授权检查失败: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * 测试Lazada API连接和产品更新功能
     */
    public function testLazadaConnection()
    {
        try {
            // 获取少量产品来测试连接
            $result = app(LazadaApiService::class)->getProducts(0, 5);
            
            return response()->json([
                'success' => true,
                'message' => 'Lazada API连接正常',
                'sample_data' => $result,
                'timestamp' => now()->toDateTimeString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lazada API连接测试失败: ' . $e->getMessage(),
                'timestamp' => now()->toDateTimeString()
            ], 500);
        }
    }

    public function upload(Request $request)
    {
        // 自定义文件验证，支持更多CSV MIME types
        $file = $request->file('excel_file');
        
        if (!$file) {
            return response()->json([
                'success' => false,
                'message' => '请选择要上传的文件'
            ], 422);
        }

        // 检查文件大小 (10MB)
        if ($file->getSize() > 10 * 1024 * 1024) {
            return response()->json([
                'success' => false,
                'message' => '文件大小不能超过10MB'
            ], 422);
        }

        // 检查文件扩展名
        $allowedExtensions = ['xlsx', 'xls', 'csv'];
        $extension = strtolower($file->getClientOriginalExtension());
        
        if (!in_array($extension, $allowedExtensions)) {
            return response()->json([
                'success' => false,
                'message' => '只支持Excel文件（.xlsx, .xls）和CSV文件'
            ], 422);
        }

        // 对于CSV文件，记录MIME type信息用于调试
        if ($extension === 'csv') {
            $allowedCsvMimeTypes = [
                'text/csv',
                'text/plain',
                'application/csv',
                'application/vnd.ms-excel',
                'text/comma-separated-values',
                'application/octet-stream'
            ];
            
            $mimeType = $file->getMimeType();
            \Log::info('CSV文件MIME type检测', [
                'file_name' => $file->getClientOriginalName(),
                'detected_mime_type' => $mimeType,
                'file_size' => $file->getSize()
            ]);
        }

        // 记录文件信息用于调试
        \Log::info('文件上传验证通过', [
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'file_extension' => $extension,
            'mime_type' => $file->getMimeType()
        ]);

        try {
            // 确保bulk_updates目录存在
            if (!Storage::exists('bulk_updates')) {
                Storage::makeDirectory('bulk_updates');
                \Log::info('创建bulk_updates目录');
            }

            // 保存上传的文件
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('bulk_updates', $fileName, 'local');

            // 验证文件是否成功保存
            if (!Storage::exists($filePath)) {
                \Log::error('文件保存失败', [
                    'file_path' => $filePath,
                    'storage_path' => Storage::path($filePath)
                ]);
                return response()->json([
                    'success' => false,
                    'message' => '文件保存失败'
                ], 500);
            }

            \Log::info('文件保存成功', [
                'file_path' => $filePath,
                'full_path' => Storage::path($filePath),
                'file_exists' => file_exists(Storage::path($filePath))
            ]);

            // 检查文件是否可读
            $fullPath = Storage::path($filePath);
            if (!is_readable($fullPath)) {
                \Log::error('文件不可读', [
                    'file_path' => $filePath,
                    'full_path' => $fullPath
                ]);
                Storage::delete($filePath);
                return response()->json([
                    'success' => false,
                    'message' => '文件保存后无法读取'
                ], 500);
            }

            // 创建批量更新任务
            \Log::info('开始创建批量更新任务', ['file_path' => $filePath]);
            
            $result = $this->bulkUpdateService->createBulkUpdateTask($filePath);

            if (!$result['success']) {
                // 删除上传的文件
                Storage::delete($filePath);
                
                \Log::warning('创建任务失败，删除文件', [
                    'file_path' => $filePath,
                    'error' => $result['message']
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }

            // 如果有错误但仍有有效产品，显示警告
            $response = [
                'success' => true,
                'message' => '文件上传成功，任务已创建',
                'task_id' => $result['task_id'],
                'total_items' => $result['total_items'],
                'valid_products' => $result['valid_products']
            ];

            if (!empty($result['errors'])) {
                $response['warnings'] = $result['errors'];
                $response['message'] .= '，但有一些产品存在问题';
            }

            \Log::info('任务创建成功', $response);

            return response()->json($response);

        } catch (\Exception $e) {
            // 记录详细的错误信息
            \Log::error('批量更新文件上传失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'line' => $e->getLine(),
                'file_location' => $e->getFile()
            ]);

            return response()->json([
                'success' => false,
                'message' => '文件处理失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 开始执行批量更新任务
     */
    public function execute(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'task_id' => 'required|integer|exists:bulk_update_tasks,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $taskId = $request->input('task_id');

            // 检查任务状态
            $statusResult = $this->bulkUpdateService->getTaskStatus($taskId);
            if (!$statusResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $statusResult['message']
                ], 404);
            }

            $task = $statusResult['task'];
            if ($task['status'] !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => '任务状态不正确，无法执行'
                ], 400);
            }

            // 将任务加入队列异步处理
            ProcessBulkUpdateJob::dispatch($taskId);

            return response()->json([
                'success' => true,
                'message' => '任务已开始执行，请稍后查看进度',
                'task_id' => $taskId
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '启动任务失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取任务状态和进度
     */
    public function status(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'task_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $taskId = $request->input('task_id');
            $result = $this->bulkUpdateService->getTaskStatus($taskId);

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取任务状态失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 下载任务结果报告
     */
    public function downloadReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'task_id' => 'required|integer|exists:bulk_update_tasks,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $taskId = $request->input('task_id');
            $statusResult = $this->bulkUpdateService->getTaskStatus($taskId);
            
            if (!$statusResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $statusResult['message']
                ], 404);
            }

            $task = $statusResult['task'];
            
            if (!$task['completed_at']) {
                return response()->json([
                    'success' => false,
                    'message' => '任务尚未完成，无法下载报告'
                ], 400);
            }

            // 生成CSV报告
            $csvContent = $this->generateCsvReport($task);
            $fileName = "bulk_update_report_{$taskId}_" . date('Y-m-d_H-i-s') . '.csv';

            return response($csvContent)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', "attachment; filename=\"{$fileName}\"");

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '生成报告失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 生成CSV报告
     */
    private function generateCsvReport($task)
    {
        $csv = "SKU,产品标题,状态,消息,处理时间\n";
        
        foreach ($task['results'] as $result) {
            $csv .= sprintf(
                "%s,%s,%s,%s,%s\n",
                $result['sku'],
                '"' . str_replace('"', '""', $result['title']) . '"',
                $result['status'] === 'success' ? '成功' : '失败',
                '"' . str_replace('"', '""', $result['message']) . '"',
                date('Y-m-d H:i:s')
            );
        }

        return $csv;
    }
}
