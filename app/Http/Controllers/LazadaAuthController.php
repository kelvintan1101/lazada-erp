<?php

namespace App\Http\Controllers;

use App\Services\LazadaApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LazadaAuthController extends Controller
{
    protected $lazadaApiService;

    public function __construct(LazadaApiService $lazadaApiService)
    {
        $this->lazadaApiService = $lazadaApiService;
    }

    /**
     * Redirect to Lazada authorization page
     */
    public function redirect(Request $request)
    {
        $state = auth()->id(); // Use user ID as state to identify the user when callback is received
        $authUrl = $this->lazadaApiService->getAuthorizationUrl($state);
        
        return redirect($authUrl);
    }

    /**
     * Process callback from Lazada
     */
    public function callback(Request $request)
    {
        try {
            $code = $request->query('code');
            $state = $request->query('state');
            
            if (!$code) {
                throw new \Exception('Lazada authorization code is missing');
            }
            
            $response = $this->lazadaApiService->getAccessToken($code);
            
            if ($response['code'] === '0') {
                // Store token in database
                $this->lazadaApiService->saveToken($response);
                
                return view('lazada.callback', [
                    'success' => true,
                    'message' => 'Authorization successful'
                ]);
            } else {
                throw new \Exception($response['message'] ?? 'Failed to get access token');
            }
        } catch (\Exception $error) {
            Log::error('Lazada authorization error', [
                'error' => $error->getMessage(),
                'trace' => $error->getTraceAsString()
            ]);
            
            return view('lazada.callback', [
                'success' => false,
                'error' => $error->getMessage()
            ]);
        }
    }

    /**
     * Debug callback for testing
     */
    public function debugCallback(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Debug callback received',
            'query_params' => $request->query(),
            'code' => $request->query('code')
        ]);
    }

    /**
     * Manually generate a token with code
     */
    public function generateToken(Request $request)
    {
        try {
            $code = $request->input('code');
            if (!$code) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authorization code is required',
                ]);
            }
            
            $response = $this->lazadaApiService->getAccessToken($code);
            
            if ($response['code'] === '0') {
                $this->lazadaApiService->saveToken($response);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Token generated successfully',
                    'access_token' => $response['access_token'],
                    'refresh_token' => $response['refresh_token'],
                    'expires_in' => $response['expires_in'],
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $response['message'] ?? 'Failed to get access token',
                    'response' => $response,
                ]);
            }
        } catch (\Exception $error) {
            Log::error('Lazada token generation error', [
                'error' => $error->getMessage(),
                'trace' => $error->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $error->getMessage(),
            ], 500);
        }
    }
}