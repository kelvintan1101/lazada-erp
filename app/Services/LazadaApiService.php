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
    private ?LazadaToken $token;
    private string $appKey;
    private string $appSecret;
    private string $apiUrl;

    public function __construct()
    {
        $this->client = new Client();
        $this->token = LazadaToken::latest()->first();
        $this->appKey = Setting::getSetting('lazada_app_key', '');
        $this->appSecret = Setting::getSetting('lazada_app_secret', '');
        
        // Lazada API base URL - this is an example, check Lazada's documentation for the correct URL
        $this->apiUrl = 'https://api.lazada.com/rest';
    }

    public function getAuthorizationUrl()
    {
        // This is an example, check Lazada's documentation for the correct URL and parameters
        $callbackUrl = route('lazada.callback');
        $url = 'https://auth.lazada.com/oauth/authorize';
        
        return $url . '?' . http_build_query([
            'response_type' => 'code',
            'client_id' => $this->appKey,
            'redirect_uri' => $callbackUrl,
        ]);
    }

    public function getAccessToken($authorizationCode)
    {
        try {
            $response = $this->client->post('https://auth.lazada.com/rest/auth/token/create', [
                'form_params' => [
                    'client_id' => $this->appKey,
                    'client_secret' => $this->appSecret,
                    'code' => $authorizationCode,
                    'grant_type' => 'authorization_code',
                    'redirect_uri' => 'https://techsolution11.online/lazada/callback',
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            
            if (isset($data['access_token'])) {
                $this->saveToken($data);
                return true;
            }
            
            Log::error('Failed to get access token from Lazada', $data);
            return false;
        } catch (GuzzleException $e) {
            Log::error('Exception during Lazada token retrieval: ' . $e->getMessage());
            return false;
        }
    }

    public function refreshToken()
    {
        if (!$this->token) {
            Log::error('No token to refresh');
            return false;
        }

        try {
            $response = $this->client->post('https://auth.lazada.com/rest/auth/token/refresh', [
                'form_params' => [
                    'client_id' => $this->appKey,
                    'client_secret' => $this->appSecret,
                    'refresh_token' => $this->token->refresh_token,
                    'grant_type' => 'refresh_token',
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            
            if (isset($data['access_token'])) {
                $this->saveToken($data);
                return true;
            }
            
            Log::error('Failed to refresh token', $data);
            return false;
        } catch (GuzzleException $e) {
            Log::error('Exception during Lazada token refresh: ' . $e->getMessage());
            return false;
        }
    }

    private function saveToken(array $data)
    {
        // Calculate expiry time based on expires_in (in seconds)
        $expiresAt = now()->addSeconds($data['expires_in'] ?? 7200);
        
        LazadaToken::updateOrCreate(
            ['seller_id_on_lazada' => $data['country_user_info']['seller_id'] ?? ''],
            [
                'access_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token'],
                'expires_at' => $expiresAt,
                'country_user_info' => $data['country_user_info'] ?? null,
            ]
        );
        
        // Refresh the token instance
        $this->token = LazadaToken::latest()->first();
    }

    public function makeRequest(string $endpoint, array $parameters = [], string $method = 'GET')
    {
        // Check if token is valid or try to refresh
        if (!$this->token || $this->token->isExpired()) {
            if (!$this->token || !$this->refreshToken()) {
                Log::error('No valid token for Lazada API request');
                return null;
            }
        }

        // Add common parameters
        $parameters = array_merge($parameters, [
            'app_key' => $this->appKey,
            'timestamp' => (string)now()->timestamp,
            'sign_method' => 'sha256',
        ]);

        // Sort parameters by key
        ksort($parameters);

        // Create the signature string
        $signString = $endpoint;
        foreach ($parameters as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
            $signString .= $key . $value;
        }

        // Calculate signature
        $parameters['sign'] = hash_hmac('sha256', $signString, $this->appSecret);

        try {
            $options = [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token->access_token,
                ],
            ];

            if ($method === 'GET') {
                $options['query'] = $parameters;
                $response = $this->client->get($this->apiUrl . $endpoint, $options);
            } else {
                $options['form_params'] = $parameters;
                $response = $this->client->post($this->apiUrl . $endpoint, $options);
            }

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Lazada API request failed: ' . $e->getMessage(), [
                'endpoint' => $endpoint,
                'method' => $method,
                'parameters' => $parameters,
            ]);
            return null;
        }
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
    public function getOrders($status = null, $startTime = null, $endTime = null, $offset = 0, $limit = 50)
    {
        $params = [
            'offset' => $offset,
            'limit' => $limit,
        ];

        if ($status) {
            $params['status'] = $status;
        }

        if ($startTime) {
            $params['created_after'] = $startTime->toIso8601String();
        }

        if ($endTime) {
            $params['created_before'] = $endTime->toIso8601String();
        }

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