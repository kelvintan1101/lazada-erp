<?php

namespace App\Http\Middleware;

use App\Models\LazadaToken;
use App\Services\LazadaApiService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

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
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Lazada integration not setup. Please authorize with Lazada.'], 403);
            }
            
            return redirect()->route('settings.index')->with('error', 'Lazada integration not setup. Please authorize with Lazada.');
        }

        // If token is expiring soon, try to refresh it
        if ($token->isExpiringSoon()) {
            $refreshed = $this->lazadaApiService->refreshToken();
            
            if (!$refreshed) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Lazada token expired. Please reauthorize.'], 403);
                }
                
                return redirect()->route('settings.index')->with('error', 'Lazada token expired. Please reauthorize.');
            }
        }

        return $next($request);
    }
}