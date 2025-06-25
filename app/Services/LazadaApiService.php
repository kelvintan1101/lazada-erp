<?php

namespace App\Services;

use App\Models\LazadaToken;
use App\Models\Setting;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class LazadaApiService
{
    private Client $client;
    private string $appKey;
    private string $appSecret;
    private string $apiUrl;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 30,
            'verify' => false,  // Skip SSL verification - only for testing!
            'http_errors' => false
        ]);
        
        $this->appKey = env('LAZADA_APP_KEY', Setting::getSetting('lazada_app_key', ''));
        $this->appSecret = env('LAZADA_APP_SECRET', Setting::getSetting('lazada_app_secret', ''));
        $this->apiUrl = 'https://api.lazada.com/rest';
    }

    /**
     * Get authorization URL for Lazada OAuth
     * 
     * @param string|null $state Optional state parameter for tracking
     * @return string The authorization URL
     */
    public function getAuthorizationUrl($state = null)
    {
        $params = [
            'response_type' => 'code',
            'client_id' => $this->appKey,
            'redirect_uri' => url('/lazada/callback'),
        ];
        
        if ($state) {
            $params['state'] = $state;
        }
        
        return 'https://auth.lazada.com/oauth/authorize?' . http_build_query($params);
    }

    /**
     * Exchange authorization code for access token
     * 
     * @param string $code The authorization code from Lazada
     * @return array The response data
     * @throws \Exception If the request fails
     */
    public function getAccessToken($code)
    {
        $params = [
            'app_key' => $this->appKey,
            'code' => $code,
            'sign_method' => 'sha256',
            'timestamp' => round(microtime(true) * 1000)
        ];
        
        return $this->makeRequest('/auth/token/create', $params);
    }
    
    /**
     * Refresh an expired access token
     * 
     * @param string $refreshToken The refresh token
     * @return array The response data
     * @throws \Exception If the request fails
     */
    public function refreshToken($refreshToken)
    {
        $params = [
            'app_key' => $this->appKey,
            'refresh_token' => $refreshToken,
            'sign_method' => 'sha256',
            'timestamp' => round(microtime(true) * 1000)
        ];
        
        return $this->makeRequest('/auth/token/refresh', $params);
    }
    
    /**
     * Make a request to the Lazada API
     * 
     * @param string $apiPath The API path (e.g. '/auth/token/create')
     * @param array $params The request parameters
     * @param string $method HTTP method to use (GET or POST)
     * @return array The response data
     * @throws \Exception If the request fails
     */
    public function makeRequest($apiPath, $params, $method = null)
    {
        // Get the latest token from database for authenticated requests
        $token = null;
        
        // Skip token for auth endpoints
        if (strpos($apiPath, '/auth/token/') === false) {
            $token = LazadaToken::latest()->first();
            if (!$token) {
                throw new \Exception('No Lazada token available. Please authorize with Lazada first.');
            }
        }
        
        // Always include these parameters in every request
        $params = array_merge([
            'app_key' => $this->appKey,
            'sign_method' => 'sha256',
            'timestamp' => round(microtime(true) * 1000)
        ], $params);
        
        // Add access token for authenticated requests
        if ($token && !isset($params['access_token'])) {
            $params['access_token'] = $token->access_token;
        }
        
        // Generate signature
        $sign = $this->generateSignature($apiPath, $params);
        $params['sign'] = $sign;
        
        try {
            // Determine HTTP method
            if ($method === null) {
                $method = (strpos($apiPath, 'token') !== false || 
                          strpos($apiPath, '/update') !== false || 
                          strpos($apiPath, '/create') !== false) ? 'POST' : 'GET';
            }
            
            // Use Malaysia domain for product API
            $apiDomain = $this->apiUrl;
            if (strpos($apiPath, '/products/') !== false || 
                strpos($apiPath, '/product/') !== false || 
                strpos($apiPath, '/orders/') !== false || 
                strpos($apiPath, '/order/') !== false) {
                $apiDomain = 'https://api.lazada.com.my/rest';
                Log::info('Using Malaysia domain for API: ' . $apiPath);
            }
            
            // Build the full URL with parameters for proper logging
            $fullUrl = $apiDomain . $apiPath . '?' . http_build_query($params);
            
            // Log the request
            Log::debug('Making Lazada API request', [
                'api_path' => $apiPath,
                'method' => $method,
                'domain' => $apiDomain,
                'url' => $fullUrl,
                'has_token' => !is_null($token)
            ]);
            
            // For GET requests, we'll use query parameters
            // For POST requests, we'll use form_params
            $options = [];
            if ($method === 'GET') {
                $options['query'] = $params;
            } else {
                $options['form_params'] = $params;
            }
            
            $response = $this->client->request($method, $apiDomain . $apiPath, $options);
            $responseBody = $response->getBody()->getContents();
            $data = json_decode($responseBody, true);
            
            // Log the response
            Log::debug('Lazada API response', [
                'api_path' => $apiPath,
                'status_code' => $response->getStatusCode(),
                'data' => $data
            ]);
            
            if (!$data) {
                Log::error('Invalid JSON response from Lazada API', [
                    'api_path' => $apiPath,
                    'response_body' => $responseBody
                ]);
                throw new \Exception('Invalid JSON response from Lazada API');
            }
            
            // For product API, the response structure is different
            if (strpos($apiPath, '/products/') !== false && isset($data['data'])) {
                return $data;
            }
            
            // For other APIs, check the code
            if (!isset($data['code']) || $data['code'] !== '0') {
                Log::error('Lazada API error', [
                    'api_path' => $apiPath,
                    'params' => array_diff_key($params, ['access_token' => '']),
                    'response' => $data
                ]);
                
                throw new \Exception($data['message'] ?? 'Failed to get Lazada response');
            }
            
            return $data;
        } catch (GuzzleException $e) {
            Log::error('Lazada request failed', [
                'api_path' => $apiPath,
                'error' => $e->getMessage()
            ]);
            
            throw new \Exception('Failed to connect to Lazada API: ' . $e->getMessage());
        }
    }
    
    /**
     * Generate signature for Lazada API
     * 
     * @param string $apiPath The API path
     * @param array $params The request parameters
     * @return string The generated signature
     */
    private function generateSignature($apiPath, $params)
    {
        // Sort parameters by key alphabetically
        ksort($params);

        // Create the signature string
        $signString = $apiPath;
        foreach ($params as $key => $value) {
            // For stringifying arrays/objects
            if (is_array($value) || is_object($value)) {
                $value = json_encode($value);
            }
            $signString .= $key . $value;
        }

        // Create HMAC-SHA256 signature
        return strtoupper(hash_hmac('sha256', $signString, $this->appSecret));
    }
    
    /**
     * Save token to database
     * 
     * @param array $tokenData The token data from Lazada
     * @return LazadaToken The saved token
     */
    public function saveToken($tokenData)
    {
        $expiresAt = now()->addSeconds($tokenData['expires_in']);
        
        return LazadaToken::updateOrCreate(
            [
                'seller_id_on_lazada' => $tokenData['country_user_info']['seller_id'] ?? '',
            ],
            [
                'access_token' => $tokenData['access_token'],
                'refresh_token' => $tokenData['refresh_token'],
                'expires_at' => $expiresAt,
                'country_user_info' => $tokenData['country_user_info'] ?? null,
            ]
        );
    }

    // Specific API endpoints for Products
    public function getProducts($offset = 0, $limit = 50)
    {
        return $this->makeRequest('/products/get', [
            'offset' => $offset,
            'limit' => $limit,
        ]);
    }

    public function updateProductStock($lazadaProductId, $sellerSku, $quantity)
    {
        return $this->makeRequest('/product/stock/update', [
            'product_id' => $lazadaProductId,
            'seller_sku' => $sellerSku,
            'quantity' => $quantity,
        ], 'POST');
    }

    // Specific API endpoints for Orders
    public function getOrders($status = null, $startTime = null, $endTime = null, $offset = 0, $limit = 10)
    {
        // Default to first day of current month if no start time provided
        if (!$startTime) {
            $startTime = now()->startOfMonth();
        }
        
        $params = [
            'offset' => $offset,
            'limit' => $limit,
            'sort_direction' => 'DESC'
        ];

        if ($status) {
            $params['status'] = $status;
        }

        if ($startTime) {
            $params['created_after'] = $startTime->format('Y-m-d\TH:i:s\+08:00');
        }

        if ($endTime) {
            $params['created_before'] = $endTime->format('Y-m-d\TH:i:s\+08:00');
        }
        
        Log::info('Fetching orders with params', ['params' => $params]);

        return $this->makeRequest('/orders/get', $params);
    }

    public function getOrderItems($orderId)
    {
        return $this->makeRequest('/order/items/get', [
            'order_id' => $orderId,
        ]);
    }

    public function updateOrderStatus($orderId, $status)
    {
        // This is a simplified example - different status updates might need different endpoints
        $endpoint = '/order/'; 
        
        switch ($status) {
            case 'packed':
                $endpoint .= 'pack';
                break;
            case 'ready_to_ship':
                $endpoint .= 'rts';
                break;
            default:
                return null;
        }
        
        return $this->makeRequest($endpoint, [
            'order_id' => $orderId,
        ], 'POST');
    }
}
