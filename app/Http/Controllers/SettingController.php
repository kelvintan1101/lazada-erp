<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\LazadaApiService;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    protected $lazadaApiService;

    public function __construct(LazadaApiService $lazadaApiService)
    {
        $this->lazadaApiService = $lazadaApiService;
    }

    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        return view('settings.index', compact('settings'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'lazada_app_key' => 'required|string',
            'lazada_app_secret' => 'required|string',
            'low_stock_threshold' => 'required|integer|min:1',
        ]);

        Setting::setSetting('lazada_app_key', $request->lazada_app_key);
        Setting::setSetting('lazada_app_secret', $request->lazada_app_secret);
        Setting::setSetting('low_stock_threshold', $request->low_stock_threshold);

        return redirect()->route('settings.index')->with('success', 'Settings updated successfully.');
    }
}