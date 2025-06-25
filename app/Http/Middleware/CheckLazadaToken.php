<?php

namespace App\Http\Middleware;

use App\Models\LazadaToken;
use App\Services\LazadaApiService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class CheckLazadaToken
{
    protected $lazadaApiService;

    public function __construct(LazadaApiService $lazadaApiService)
    {
        $this->lazadaApiService = $lazadaApiService;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $token = LazadaToken::latest()->first();

        // If no token exists, redirect to settings to set up Lazada integration
        if (!$token) {
            Log::warning('No Lazada token found in database');
            
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Lazada integration not setup. Please authorize with Lazada.'], 403);
            }
            
            return redirect()->route('lazada.auth')
                ->with('error', 'Lazada integration not setup. Please authorize with Lazada.');
        }

        // If token is expired
        if ($token->isExpired()) {
            // Try to refresh the token
            try {
                Log::info('Token is expired, trying to refresh');
                $response = $this->lazadaApiService->refreshToken($token->refresh_token);
                
                if ($response && isset($response['code']) && $response['code'] === '0') {
                    // Save the refreshed token
                    $this->lazadaApiService->saveToken($response);
                    Log::info('Lazada token refreshed successfully');
                } else {
                    // If refresh failed, delete the old token to force re-auth
                    $token->delete();
                    Log::warning('Failed to refresh Lazada token, deleted old token', ['response' => $response ?? 'None']);
                    
                    if ($request->expectsJson()) {
                        return response()->json(['message' => 'Lazada token expired. Please reauthorize.'], 403);
                    }
                    
                    return redirect()->route('lazada.auth')
                        ->with('error', 'Your Lazada token has expired. Please authorize again.');
                }
            } catch (\Exception $e) {
                Log::error('Exception refreshing Lazada token', ['error' => $e->getMessage()]);
                
                // If refresh throws exception, delete the old token
                $token->delete();
                
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Error refreshing Lazada token: ' . $e->getMessage()], 403);
                }
                
                return redirect()->route('lazada.auth')
                    ->with('error', 'Error with Lazada connection: ' . $e->getMessage());
            }
        }

        return $next($request);
    }
}