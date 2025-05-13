@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Settings</h2>
        <p class="text-gray-500">Configure your Lazada ERP settings</p>
    </div>

    <div class="grid grid-cols-1 gap-6">
        <!-- Lazada API Settings -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-700">Lazada API Configuration</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('settings.store') }}" method="POST">
                    @csrf
                    
                    <div class="space-y-6">
                        <div>
                            <label for="lazada_app_key" class="form-label required">App Key</label>
                            <input type="text" name="lazada_app_key" id="lazada_app_key" class="form-input" value="{{ $settings['lazada_app_key'] ?? '' }}" required>
                            <p class="mt-1 text-sm text-gray-500">
                                Your Lazada Open Platform App Key
                            </p>
                        </div>
                        
                        <div>
                            <label for="lazada_app_secret" class="form-label required">App Secret</label>
                            <input type="password" name="lazada_app_secret" id="lazada_app_secret" class="form-input" value="{{ $settings['lazada_app_secret'] ?? '' }}" required>
                            <p class="mt-1 text-sm text-gray-500">
                                Your Lazada Open Platform App Secret
                            </p>
                        </div>

                        <hr class="my-6">
                        
                        <div>
                            <label for="low_stock_threshold" class="form-label required">Low Stock Threshold</label>
                            <input type="number" name="low_stock_threshold" id="low_stock_threshold" class="form-input" value="{{ $settings['low_stock_threshold'] ?? '10' }}" min="1" required>
                            <p class="mt-1 text-sm text-gray-500">
                                Products with stock below this value will be marked as low stock
                            </p>
                        </div>

                        <div class="pt-4">
                            <button type="submit" class="btn btn-primary">Save Settings</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lazada Authorization -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-700">Lazada Authorization</h3>
            </div>
            <div class="card-body">
                @php
                    $token = \App\Models\LazadaToken::latest()->first();
                @endphp
                
                @if($token)
                    <div class="mb-6">
                        <p class="text-sm font-medium text-gray-500">Authorization Status</p>
                        <div class="mt-1 flex items-center">
                            <div class="rounded-full bg-green-100 p-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <span class="ml-2 text-green-700 font-medium">Authorized</span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Seller ID</p>
                            <p class="mt-1">{{ $token->seller_id_on_lazada }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Token Expires</p>
                            <p class="mt-1">{{ $token->expires_at->format('M d, Y H:i:s') }}</p>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <a href="{{ route('lazada.auth') }}" class="btn btn-secondary">Re-authorize with Lazada</a>
                    </div>
                @else
                    <div class="mb-6">
                        <p class="text-sm font-medium text-gray-500">Authorization Status</p>
                        <div class="mt-1 flex items-center">
                            <div class="rounded-full bg-red-100 p-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <span class="ml-2 text-red-700 font-medium">Not Authorized</span>
                        </div>
                    </div>
                    
                    <p class="text-gray-600 mb-6">
                        You need to authorize this application with your Lazada seller account to access the Lazada API.
                        Make sure you've entered your App Key and App Secret and saved the settings before authorizing.
                    </p>
                    
                    <a href="{{ route('lazada.auth') }}" class="btn btn-primary">Authorize with Lazada</a>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .required::after {
        content: " *";
        color: rgb(239 68 68);
    }
</style>
@endpush 