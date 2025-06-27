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
     * Display bulk update page
     */
    public function index()
    {
        return view('bulk-update.index');
    }

    public function authCheck()
{
    try {
        // Check directly if there's a valid token in the database
        $token = \App\Models\LazadaToken::latest()->first();
        
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Lazada authorization does not exist, please authorize first in the settings page'
            ], 403);
        }

        // Check if token has expired
        if ($token->expires_at && now()->gt($token->expires_at)) {
            return response()->json([
                'success' => false,
                'message' => 'Lazada authorization has expired, please re-authorize'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lazada authorization is valid'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lazada authorization check failed: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * Test Lazada API connection and product update functionality
     */
    public function testLazadaConnection()
    {
        try {
            // Get few products to test connection
            $result = app(LazadaApiService::class)->getProducts(0, 5);
            
            return response()->json([
                'success' => true,
                'message' => 'Lazada API connection is normal',
                'sample_data' => $result,
                'timestamp' => now()->toDateTimeString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lazada API connection test failed: ' . $e->getMessage(),
                'timestamp' => now()->toDateTimeString()
            ], 500);
        }
    }

    public function upload(Request $request)
    {
        try {
            // Basic debug information
            \Log::info('=== Starting file upload request processing ===');
            \Log::info('Request basic information', [
                'method' => $request->method(),
                'has_file' => $request->hasFile('excel_file'),
                'files_count' => count($request->allFiles())
            ]);
            
            // Custom file validation, support more CSV MIME types
            $file = $request->file('excel_file');
            
            if (!$file) {
                \Log::warning('File upload failed: no file');
                return response()->json([
                    'success' => false,
                    'message' => 'Please select a file to upload'
                ], 422);
            }

            \Log::info('File basic information', [
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'extension' => $file->getClientOriginalExtension()
            ]);

            // Check file size (10MB)
            if ($file->getSize() > 10 * 1024 * 1024) {
                \Log::warning('File size exceeded', ['size' => $file->getSize()]);
                return response()->json([
                    'success' => false,
                    'message' => 'File size cannot exceed 10MB'
                ], 422);
            }

            // Check file extension
            $allowedExtensions = ['xlsx', 'xls', 'csv'];
            $extension = strtolower($file->getClientOriginalExtension());
            
            if (!in_array($extension, $allowedExtensions)) {
                \Log::warning('Unsupported file extension', ['extension' => $extension]);
                return response()->json([
                    'success' => false,
                    'message' => 'Only Excel files (.xlsx, .xls) and CSV files are supported'
                ], 422);
            }

            \Log::info('File validation passed, starting to save file');

            // Ensure bulk_updates directory exists
            if (!Storage::exists('bulk_updates')) {
                Storage::makeDirectory('bulk_updates');
                \Log::info('Created bulk_updates directory');
            }

            // Save uploaded file
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('bulk_updates', $fileName, 'local');

            \Log::info('File save result', [
                'file_path' => $filePath,
                'storage_exists' => Storage::exists($filePath)
            ]);

            // Verify if file was saved successfully
            if (!Storage::exists($filePath)) {
                \Log::error('File save failed', [
                    'file_path' => $filePath,
                    'storage_path' => Storage::path($filePath)
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'File save failed'
                ], 500);
            }

            // Check if file is readable
            $fullPath = Storage::path($filePath);
            if (!is_readable($fullPath)) {
                \Log::error('File is not readable', [
                    'file_path' => $filePath,
                    'full_path' => $fullPath
                ]);
                Storage::delete($filePath);
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to read file after saving'
                ], 500);
            }

            \Log::info('File saved successfully, starting to create bulk update task');

            // Check if service is properly injected
            if (!$this->bulkUpdateService) {
                \Log::error('BulkUpdateService not properly injected');
                return response()->json([
                    'success' => false,
                    'message' => 'Service initialization failed'
                ], 500);
            }

            // Create bulk update task
            $result = $this->bulkUpdateService->createBulkUpdateTask($filePath);

            if (!$result['success']) {
                // Delete uploaded file
                Storage::delete($filePath);
                
                \Log::warning('Task creation failed, deleting file', [
                    'file_path' => $filePath,
                    'error' => $result['message']
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }

            // If there are errors but still valid products, show warning
            $response = [
                'success' => true,
                'message' => 'File uploaded successfully, task created',
                'task_id' => $result['task_id'],
                'total_items' => $result['total_items'],
                'valid_products' => $result['valid_products']
            ];

            if (!empty($result['errors'])) {
                $response['warnings'] = $result['errors'];
                $response['message'] .= ', but some products have issues';
            }

            \Log::info('=== Task created successfully ===', $response);

            return response()->json($response);

        } catch (\Exception $e) {
            // Record detailed error information
            \Log::error('=== Bulk update file upload failed ===', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file_name' => $request->hasFile('excel_file') ? $request->file('excel_file')->getClientOriginalName() : 'unknown',
                'file_size' => $request->hasFile('excel_file') ? $request->file('excel_file')->getSize() : 0,
                'line' => $e->getLine(),
                'file_location' => $e->getFile()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'File processing failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Start executing bulk update task
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

            // Check task status
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
                    'message' => 'Task status is incorrect, cannot execute'
                ], 400);
            }

            // Add task to queue for async processing
            ProcessBulkUpdateJob::dispatch($taskId);

            return response()->json([
                'success' => true,
                'message' => 'Task has started executing, please check progress later',
                'task_id' => $taskId
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Task startup failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get task status and progress
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
                'message' => 'Failed to get task status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download task result report
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
                    'message' => 'Task not yet completed, cannot download report'
                ], 400);
            }

            // Generate CSV report
            $csvContent = $this->generateCsvReport($task);
            $fileName = "bulk_update_report_{$taskId}_" . date('Y-m-d_H-i-s') . '.csv';

            return response($csvContent)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', "attachment; filename=\"{$fileName}\"");

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate CSV report
     */
    private function generateCsvReport($task)
    {
        $csv = "SKU,Product Title,Status,Message,Processing Time\n";
        
        foreach ($task['results'] as $result) {
            $csv .= sprintf(
                "%s,%s,%s,%s,%s\n",
                $result['sku'],
                '"' . str_replace('"', '""', $result['title']) . '"',
                $result['status'] === 'success' ? 'Success' : 'Failed',
                '"' . str_replace('"', '""', $result['message']) . '"',
                date('Y-m-d H:i:s')
            );
        }

        return $csv;
    }
}
