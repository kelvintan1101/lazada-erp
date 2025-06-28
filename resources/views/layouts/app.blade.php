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

    <!-- Global Loading Overlay -->
    <div id="global-loading" class="fixed inset-0 bg-white bg-opacity-90 flex items-center justify-center z-50 hidden">
        <div class="text-center">
            <div class="animate-spin rounded-full h-16 w-16 border-4 border-gray-300 border-t-blue-600 mx-auto"></div>
            <p class="mt-4 text-gray-600 text-sm font-medium">Processing...</p>
        </div>
    </div>

    <!-- Global Loading Script -->
    <script>
        // Enhanced loading manager
        window.LoadingManager = {
            overlay: null,
            messageElement: null,

            init() {
                this.overlay = document.getElementById('global-loading');
                this.messageElement = this.overlay?.querySelector('p');
            },

            show(message = 'Processing...') {
                if (!this.overlay) this.init();
                if (this.messageElement) {
                    this.messageElement.textContent = message;
                }
                this.overlay.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            },

            hide() {
                if (!this.overlay) this.init();
                this.overlay.classList.add('hidden');
                document.body.style.overflow = '';
            },

            // Smooth page transition
            navigateTo(url, message = 'Loading...') {
                this.show(message);
                setTimeout(() => {
                    window.location.href = url;
                }, 200); // Quick transition
            }
        };

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            window.LoadingManager.init();
        });

        // Auto-hide loading on page load
        window.addEventListener('load', function() {
            window.LoadingManager.hide();
        });

        // Add smooth loading to all internal links (optional)
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (link &&
                link.href &&
                link.href.startsWith(window.location.origin) &&
                !link.hasAttribute('download') &&
                !link.classList.contains('no-loading') &&
                link.getAttribute('target') !== '_blank') {

                // Skip if it's a hash link or has onclick handler
                if (link.href.includes('#') || link.onclick) return;

                e.preventDefault();
                window.LoadingManager.navigateTo(link.href);
            }
        });
    </script>

    <!-- Additional Scripts -->
    @stack('scripts')
</body>
</html>