<?php

namespace App\Http\Controllers;

use App\Services\LazadaApiService;
use Illuminate\Http\Request;

class LazadaAuthController extends Controller
{
    protected $lazadaApiService;

    public function __construct(LazadaApiService $lazadaApiService)
    {
        $this->lazadaApiService = $lazadaApiService;
    }

    public function redirect()
    {
        $url = $this->lazadaApiService->getAuthorizationUrl();
        return redirect($url);
    }

    public function callback(Request $request)
    {
        if ($request->has('error')) {
            return redirect()->route('settings.index')
                ->with('error', 'Authorization failed: ' . $request->error_description);
        }

        if (!$request->has('code')) {
            return redirect()->route('settings.index')
                ->with('error', 'No authorization code provided.');
        }

        $success = $this->lazadaApiService->getAccessToken($request->code);

        if ($success) {
            return redirect()->route('settings.index')
                ->with('success', 'Successfully connected to Lazada.');
        }

        return redirect()->route('settings.index')
            ->with('error', 'Failed to obtain access token.');
    }
}