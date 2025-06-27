<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LazadaAuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\BulkUpdateController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

// Authentication routes
Route::redirect('/', '/login');

// Laravel 11 Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    
    // Password update
    Route::get('password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');
});

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth'])->name('dashboard');

// Products routes
Route::middleware(['auth'])->group(function () {
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    
    // Routes that need Lazada token
    Route::middleware(['lazada.token'])->group(function () {
        Route::get('/products/sync', [ProductController::class, 'sync'])->name('products.sync');
        Route::get('/products/{product}/edit-stock', [ProductController::class, 'editStock'])->name('products.edit-stock');
        Route::put('/products/{product}/update-stock', [ProductController::class, 'updateStock'])->name('products.update-stock');
        
        // Stock Adjustments
        Route::get('/products/{product}/adjustments', [StockAdjustmentController::class, 'index'])->name('stock-adjustments.index');
        Route::get('/products/{product}/adjustments/create', [StockAdjustmentController::class, 'create'])->name('stock-adjustments.create');
        Route::post('/products/{product}/adjustments', [StockAdjustmentController::class, 'store'])->name('stock-adjustments.store');
    });
    
    // This route must be last to avoid conflicts with /products/sync
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
});

// Orders routes
Route::middleware(['auth'])->group(function () {
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    
    // Routes that need Lazada token
    Route::middleware(['lazada.token'])->group(function () {
        Route::get('/orders/sync', [OrderController::class, 'sync'])->name('orders.sync');
        Route::get('/orders/{order}/edit-status', [OrderController::class, 'editStatus'])->name('orders.edit-status');
        Route::put('/orders/{order}/update-status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    });
    
    // This route must be last to avoid conflicts with /orders/sync
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
});

// Settings routes
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'store'])->name('settings.store');
});

// Bulk Update routes
Route::middleware(['auth'])->group(function () {
    Route::prefix('bulk-update')->group(function () {
        Route::get('/', [BulkUpdateController::class, 'index'])->name('bulk-update.index');
        Route::get('/auth-check', [BulkUpdateController::class, 'authCheck'])->name('bulk-update.auth-check');
        // COMMENTED OUT - Test connection route (remove in production)
    // Route::get('/test-connection', [BulkUpdateController::class, 'testLazadaConnection'])->name('bulk-update.test-connection');
        Route::post('/upload', [BulkUpdateController::class, 'upload'])->name('bulk-update.upload');
        Route::post('/execute', [BulkUpdateController::class, 'execute'])->name('bulk-update.execute');
        Route::get('/status', [BulkUpdateController::class, 'status'])->name('bulk-update.status');
        Route::get('/download-report', [BulkUpdateController::class, 'downloadReport'])->name('bulk-update.download-report');
    });
});

// Lazada Auth routes
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/lazada/auth', [LazadaAuthController::class, 'redirect'])->name('lazada.auth');
    Route::get('/lazada/callback', [LazadaAuthController::class, 'callback'])->name('lazada.callback');
    // COMMENTED OUT - Debug callback route (remove in production)
    // Route::get('/lazada/debug-callback', [LazadaAuthController::class, 'debugCallback'])->name('lazada.debug.callback');
    Route::post('/lazada/generate-token', [LazadaAuthController::class, 'generateToken'])->name('lazada.generate.token');
});

// COMMENTED OUT - Debug route (remove in production)
/*
Route::get('/lazada/test-db', function() {
    try {
        // Test database connection
        $result = \Illuminate\Support\Facades\DB::select('SELECT 1');
        return response()->json([
            'success' => true,
            'message' => 'Database connection successful',
            'result' => $result,
            'connection' => config('database.default')
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Database connection failed: ' . $e->getMessage(),
            'connection' => config('database.default')
        ], 500);
    }
});
*/

// COMMENTED OUT - Direct token test route (remove in production)
/*
Route::get('/lazada/test-token/{code}', function($code) {
    try {
        \Illuminate\Support\Facades\Log::info('Direct token test route called', ['code' => $code]);
        
        $apiService = app(\App\Services\LazadaApiService::class);
        $result = $apiService->getAccessTokenForDebug($code);
        
        return response()->json([
            'success' => true,
            'message' => 'Token test completed',
            'result' => $result
        ]);
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Exception in direct token test: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Token test failed: ' . $e->getMessage()
        ], 500);
    }
});
*/

// COMMENTED OUT - Direct token manual test route (remove in production)
/*
Route::get('/lazada/direct-token/{code}', function($code) {
    try {
        // Get configuration from environment or provide fallbacks
        $appKey = env('LAZADA_APP_KEY', '');
        $appSecret = env('LAZADA_APP_SECRET', '');
        
        if (empty($appKey) || empty($appSecret)) {
            return response()->json([
                'success' => false,
                'message' => 'Lazada API credentials not configured in .env file',
                'app_key_exists' => !empty($appKey),
                'app_secret_exists' => !empty($appSecret)
            ]);
        }
        
        // Create a new Guzzle client with debugging options
        $client = new \GuzzleHttp\Client([
            'timeout' => 30,
            'debug' => true,
            'verify' => false, // Skip SSL verification - only for testing!
            'http_errors' => false // Don't throw exceptions for HTTP errors
        ]);
        
        // Prepare request parameters
        $params = [
            'client_id' => $appKey,
            'client_secret' => $appSecret,
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => 'https://techsolution11.online/lazada/debug-callback',
        ];
        
        // Log what we're about to do
        \Illuminate\Support\Facades\Log::info('Preparing Lazada token request', [
            'endpoint' => 'https://auth.lazada.com/rest/auth/token/create',
            'params' => array_merge($params, ['client_secret' => '[REDACTED]'])
        ]);
        
        // Make the token request
        $response = $client->post('https://auth.lazada.com/rest/auth/token/create', [
            'form_params' => $params
        ]);
        
        // Get the response details
        $statusCode = $response->getStatusCode();
        $rawResponse = $response->getBody()->getContents();
        
        // Try to decode the JSON response
        $data = json_decode($rawResponse, true);
        
        // Log the raw response
        \Illuminate\Support\Facades\Log::info('Lazada token response received', [
            'status_code' => $statusCode,
            'raw_response' => $rawResponse,
            'is_json' => json_last_error() === JSON_ERROR_NONE
        ]);
        
        // Return comprehensive response
        return response()->json([
            'success' => $statusCode >= 200 && $statusCode < 300,
            'http_status' => $statusCode,
            'data' => $data,
            'raw_response' => $rawResponse,
            'request_params' => array_merge($params, ['client_secret' => '[REDACTED]']),
            'timestamp' => now()->toDateTimeString()
        ]);
        
    } catch (\GuzzleHttp\Exception\RequestException $e) {
        // Handle Guzzle request exceptions specifically
        $errorMessage = $e->getMessage();
        $response = $e->getResponse();
        $responseBody = null;
        
        if ($response) {
            $responseBody = $response->getBody()->getContents();
        }
        
        \Illuminate\Support\Facades\Log::error('Lazada token request exception', [
            'error_message' => $errorMessage,
            'response_body' => $responseBody,
            'request' => $e->getRequest() ? (string)$e->getRequest()->getUri() : null
        ]);
        
        return response()->json([
            'success' => false,
            'error_type' => 'request_exception',
            'error' => $errorMessage,
            'response_body' => $responseBody
        ], 500);
        
    } catch (\Exception $e) {
        // Generic exception handler
        \Illuminate\Support\Facades\Log::error('Exception during token exchange', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
        
        return response()->json([
            'success' => false,
            'error_type' => get_class($e),
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'code' => $code
        ], 500);
    }
});
*/

// COMMENTED OUT - Minimal test route for basic debugging (remove in production)
/*
Route::get('/lazada/basic-test/{code}', function($code) {
    try {
        return response()->json([
            'success' => true,
            'message' => 'Route is working',
            'code' => $code,
            'lazada_app_key' => env('LAZADA_APP_KEY'),
            'php_version' => PHP_VERSION,
            'has_guzzle' => class_exists('\GuzzleHttp\Client')
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
});
*/

// COMMENTED OUT - Completely isolated test route (remove in production)
/*
Route::get('/test', function() {
    return [
        'success' => true,
        'message' => 'Basic route is working',
        'time' => date('Y-m-d H:i:s')
    ];
});
*/

// COMMENTED OUT - This route is never executed because the route at line 92 matches first
// The route: Route::get('/lazada/callback', [LazadaAuthController::class, 'callback']) 
// takes precedence due to Laravel's route matching order (first defined, first matched)
/*
// Lazada callback route - stores token similar to the example code
Route::get('/lazada/callback', function() {
    try {
        $code = request('code');
        
        if (!$code) {
            throw new \Exception('Lazada authorization code is missing');
        }
        
        $lazadaService = app(\App\Services\LazadaApiService::class);
        $response = $lazadaService->getAccessToken($code);
        
        if ($response['code'] === '0') {
            // Store token in database
            $lazadaService->saveToken($response);
            
            return response()->view('lazada.callback', [
                'success' => true,
                'message' => 'Authorization successful'
            ]);
        } else {
            throw new \Exception($response['message'] ?? 'Failed to get access token');
        }
    } catch (\Exception $error) {
        \Illuminate\Support\Facades\Log::error('Lazada authorization error', [
            'error' => $error->getMessage(),
            'trace' => $error->getTraceAsString()
        ]);
        
        return response()->view('lazada.callback', [
            'success' => false,
            'error' => $error->getMessage()
        ]);
    }
})->name('lazada.callback.new');
*/

// No-logging version of token exchange to avoid permission issues
Route::get('/lazada/simple-token/{code}', function($code) {
    try {
        // Get configuration from environment or provide fallbacks
        $appKey = env('LAZADA_APP_KEY', '');
        $appSecret = env('LAZADA_APP_SECRET', '');
        
        if (empty($appKey) || empty($appSecret)) {
            return response()->json([
                'success' => false,
                'message' => 'Lazada API credentials not configured in .env file'
            ]);
        }
        
        // API endpoint path
        $apiPath = '/auth/token/create';
        
        // Base parameters (without signature)
        $params = [
            'app_key' => $appKey,
            'code' => $code,
            'grant_type' => 'authorization_code',
            'timestamp' => round(microtime(true) * 1000), // Current timestamp in milliseconds
            'sign_method' => 'sha256',
        ];
        
        // Generate signature using the example code pattern
        $sign = generateSignature($apiPath, $params, $appSecret);
        $params['sign'] = $sign;
        
        // Create a new Guzzle client with debugging options
        $client = new \GuzzleHttp\Client([
            'timeout' => 30,
            'verify' => false, // Skip SSL verification - only for testing!
            'http_errors' => false // Don't throw exceptions for HTTP errors
        ]);
        
        // Make the token request matching axios behavior
        $response = $client->post('https://auth.lazada.com/rest/auth/token/create', [
            'query' => $params,
            'headers' => [
                'Content-Type' => 'application/json'
            ]
        ]);
        
        // Get the response details
        $statusCode = $response->getStatusCode();
        $rawResponse = $response->getBody()->getContents();
        $data = json_decode($rawResponse, true);
        
        // Error handling exactly matching example code
        if (!$data || !isset($data['code']) || $data['code'] !== '0') {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get Lazada access token',
                'error_details' => $data['message'] ?? 'Unknown error',
                'code' => $data['code'] ?? 'unknown_error'
            ]);
        }
        
        // Return the data directly to match the example code's behavior
        return response()->json($data);
        
    } catch (\Exception $e) {
        // Generic exception handler
        return response()->json([
            'success' => false,
            'message' => 'Failed to get Lazada access token',
            'error_details' => $e->getMessage()
        ]);
    }
});

// Helper function to generate Lazada API signature following their exact requirements
function generateSignature($apiPath, $params, $appSecret) {
    // Sort parameters by key alphabetically
    $sortedParams = [];
    ksort($params);
    foreach ($params as $key => $value) {
        $sortedParams[$key] = $value;
    }
    
    // Create the signature string starting with API path
    $signString = $apiPath;
    
    // Concatenate key and value for each parameter
    foreach ($sortedParams as $key => $value) {
        // Handle arrays or objects by JSON encoding them
        if (is_array($value) || is_object($value)) {
            $value = json_encode($value);
        }
        $signString .= $key . $value;
    }
    
    // Create HMAC-SHA256 signature with the app secret
    return strtoupper(hash_hmac('sha256', $signString, $appSecret));
}

// COMMENTED OUT - Bulk update test route (remove in production)
/*
Route::get('/bulk-update/test', function() {
    try {
        \Log::info('测试路由被访问');
        
        // 测试Storage
        $storageWorks = Storage::exists('bulk_updates');
        \Log::info('Storage测试', ['works' => $storageWorks]);
        
        // 测试BulkUpdateService
        $bulkService = app(\App\Services\BulkUpdateService::class);
        \Log::info('BulkUpdateService注入测试', ['service_exists' => !is_null($bulkService)]);
        
        // 测试ExcelProcessingService
        $excelService = app(\App\Services\ExcelProcessingService::class);
        \Log::info('ExcelProcessingService注入测试', ['service_exists' => !is_null($excelService)]);
        
        // 测试BulkUpdateTask模型
        $taskCount = \App\Models\BulkUpdateTask::count();
        \Log::info('BulkUpdateTask模型测试', ['count' => $taskCount]);
        
        return response()->json([
            'success' => true,
            'message' => '所有测试通过',
            'details' => [
                'storage_works' => $storageWorks,
                'bulk_service_exists' => !is_null($bulkService),
                'excel_service_exists' => !is_null($excelService),
                'task_count' => $taskCount
            ]
        ]);
        
    } catch (\Exception $e) {
        \Log::error('测试失败', [
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => '测试失败: ' . $e->getMessage()
        ], 500);
    }
})->middleware(['auth']);
*/

// COMMENTED OUT - Direct execute latest task route (remove in production)
/*
Route::post('/bulk-update/execute-latest', function() {
    try {
        // 获取最新的待执行任务
        $latestTask = \App\Models\BulkUpdateTask::where('status', 'pending')
            ->latest()
            ->first();
            
        if (!$latestTask) {
            return response()->json([
                'success' => false,
                'message' => '没有找到待执行的任务'
            ], 404);
        }
        
        // 执行任务
        $bulkService = app(\App\Services\BulkUpdateService::class);
        \App\Jobs\ProcessBulkUpdateJob::dispatch($latestTask->id);
        
        return response()->json([
            'success' => true,
            'message' => '任务已开始执行',
            'task_id' => $latestTask->id
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => '启动任务失败: ' . $e->getMessage()
        ], 500);
    }
})->middleware(['auth']);
*/

// COMMENTED OUT - View latest task status route (remove in production)
/*
Route::get('/bulk-update/latest-task', function() {
    try {
        $latestTask = \App\Models\BulkUpdateTask::latest()->first();
        
        if (!$latestTask) {
            return response()->json([
                'success' => false,
                'message' => '没有找到任务'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'task' => [
                'id' => $latestTask->id,
                'status' => $latestTask->status,
                'total_items' => $latestTask->total_items,
                'processed_items' => $latestTask->processed_items,
                'successful_items' => $latestTask->successful_items,
                'failed_items' => $latestTask->failed_items,
                'progress_percentage' => $latestTask->getProgressPercentage(),
                'created_at' => $latestTask->created_at,
                'started_at' => $latestTask->started_at,
                'completed_at' => $latestTask->completed_at
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => '获取任务状态失败: ' . $e->getMessage()
        ], 500);
    }
})->middleware(['auth']);
*/