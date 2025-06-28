<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Lazada ERP') }}</title>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.12.0/dist/cdn.min.js"></script>
    
    <!-- Additional Styles -->
    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen">
        @include('layouts.navigation')

        <!-- Page Content -->
        <main class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                @if (session('success'))
                    <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Page Heading -->
                @if (isset($header))
                    <header class="bg-white shadow mb-6">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endif

                <!-- Main Content -->
                @yield('content')
            </div>
        </main>
        
        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <p class="text-center text-gray-500 text-sm">
                    &copy; {{ date('Y') }} Lazada ERP. All rights reserved.
                </p>
            </div>
        </footer>
    </div>

    <!-- Global Loading System -->
    <div id="global-loading" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.7); display: none; align-items: center; justify-content: center; z-index: 9999;">
        <div style="background-color: white; border-radius: 12px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); padding: 2rem; text-align: center; max-width: 320px; margin: 1rem;">
            <div id="global-loading-spinner" style="width: 64px; height: 64px; border: 4px solid #e5e7eb; border-top: 4px solid #2563eb; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto;"></div>
            <p id="global-loading-main-text" style="margin-top: 1rem; color: #374151; font-weight: 500; font-size: 16px;">Loading...</p>
            <p id="global-loading-sub-text" style="margin-top: 0.5rem; color: #6b7280; font-size: 14px;">Please wait</p>
        </div>
    </div>

    <style>
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>

    <!-- Global Notification System -->
    <div id="global-notification-container" style="position: fixed; bottom: 20px; right: 20px; z-index: 999999; max-width: 420px; pointer-events: none; display: flex; flex-direction: column-reverse; gap: 16px;"></div>

    <!-- All global JavaScript systems (GlobalAPI, GlobalNotification, GlobalLoading) are now handled in app.js for better Laravel standards and consistency -->

    <!-- Additional Scripts -->
    @stack('scripts')
</body>
</html>