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

    <!-- Global Loading Script -->
    <script>
        // Global Notification System
        window.GlobalNotification = {
            // Elements
            container: null,
            notifications: new Map(),

            // Configuration
            config: {
                duration: 5000,
                maxNotifications: 5,
                position: 'bottom-right', // bottom-right, bottom-left, top-right, top-left
                animations: true
            },

            // Initialize
            init() {
                this.container = document.getElementById('global-notification-container');
                if (!this.container) {
                    this.container = this.createContainer();
                }
            },

            // Create notification container
            createContainer() {
                const container = document.createElement('div');
                container.id = 'global-notification-container';
                container.style.cssText = `
                    position: fixed !important;
                    bottom: 20px !important;
                    right: 20px !important;
                    z-index: 999999 !important;
                    max-width: 420px !important;
                    pointer-events: none !important;
                    display: flex !important;
                    flex-direction: column-reverse !important;
                    gap: 16px !important;
                `;
                document.body.appendChild(container);
                return container;
            },

            // Show notification
            show(type, title, message, duration = null) {
                if (!this.container) this.init();

                const notificationId = 'notification-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
                const finalDuration = duration || this.config.duration;

                // Remove oldest notification if we have too many
                if (this.notifications.size >= this.config.maxNotifications) {
                    const oldestId = this.notifications.keys().next().value;
                    this.hide(oldestId);
                }

                const typeConfig = this.getTypeConfig(type);
                const notification = this.createNotificationElement(notificationId, typeConfig, title, message);

                this.container.appendChild(notification);
                this.notifications.set(notificationId, {
                    element: notification,
                    timeout: null,
                    type: type
                });

                // Show animation
                if (this.config.animations) {
                    setTimeout(() => {
                        notification.style.transform = 'translateX(0)';
                        notification.style.opacity = '1';
                    }, 50);
                }

                // Auto hide
                if (finalDuration > 0) {
                    const timeout = setTimeout(() => {
                        this.hide(notificationId);
                    }, finalDuration);
                    this.notifications.get(notificationId).timeout = timeout;
                }

                return notificationId;
            },

            // Hide notification
            hide(notificationId) {
                const notificationData = this.notifications.get(notificationId);
                if (!notificationData) return;

                const { element, timeout } = notificationData;

                // Clear timeout
                if (timeout) {
                    clearTimeout(timeout);
                }

                // Hide animation
                if (this.config.animations) {
                    element.style.transform = 'translateX(100%)';
                    element.style.opacity = '0';
                    setTimeout(() => {
                        if (element.parentNode) {
                            element.parentNode.removeChild(element);
                        }
                        this.notifications.delete(notificationId);
                    }, 300);
                } else {
                    if (element.parentNode) {
                        element.parentNode.removeChild(element);
                    }
                    this.notifications.delete(notificationId);
                }
            },

            // Get type configuration
            getTypeConfig(type) {
                const configs = {
                    'success': {
                        classes: 'bg-green-50 border-green-200 text-green-800',
                        icon: '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>'
                    },
                    'error': {
                        classes: 'bg-red-50 border-red-200 text-red-800',
                        icon: '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>'
                    },
                    'info': {
                        classes: 'bg-blue-50 border-blue-200 text-blue-800',
                        icon: '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>'
                    },
                    'warning': {
                        classes: 'bg-yellow-50 border-yellow-200 text-yellow-800',
                        icon: '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>'
                    }
                };
                return configs[type] || configs['info'];
            },

            // Create notification element
            createNotificationElement(id, typeConfig, title, message) {
                const notification = document.createElement('div');
                notification.id = id;
                notification.className = `max-w-sm w-full ${typeConfig.classes} border rounded-lg shadow-lg p-4 transform translate-x-full opacity-0 transition-all duration-300 ease-in-out mb-4`;
                notification.style.cssText = 'position: relative !important; z-index: 999999 !important; pointer-events: auto !important;';

                notification.innerHTML = `
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            ${typeConfig.icon}
                        </div>
                        <div class="ml-3 flex-1">
                            <h3 class="text-sm font-medium">${title}</h3>
                            <p class="mt-1 text-sm">${message}</p>
                        </div>
                        <div class="ml-4 flex-shrink-0">
                            <button onclick="GlobalNotification.hide('${id}')" class="inline-flex text-gray-400 hover:text-gray-600 focus:outline-none">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                `;

                return notification;
            },

            // Convenient methods for different types
            success(title, message, duration = null) {
                return this.show('success', title, message, duration);
            },

            error(title, message, duration = null) {
                return this.show('error', title, message, duration);
            },

            info(title, message, duration = null) {
                return this.show('info', title, message, duration);
            },

            warning(title, message, duration = null) {
                return this.show('warning', title, message, duration);
            },

            // Clear all notifications
            clear() {
                for (const [id] of this.notifications) {
                    this.hide(id);
                }
            },

            // Configuration methods
            setConfig(newConfig) {
                this.config = { ...this.config, ...newConfig };
                return this;
            },

            // Utility methods
            getActiveCount() {
                return this.notifications.size;
            },

            getActiveNotifications() {
                return Array.from(this.notifications.keys());
            }
        };

        // Global Loading System
        window.GlobalLoading = {
            // Elements
            overlay: null,
            mainText: null,
            subText: null,
            spinner: null,

            // State
            isVisible: false,
            currentType: 'default',

            // Configuration
            config: {
                minDisplayTime: 300, // Minimum time to show loading (prevents flicker)
                autoHideDelay: 0,    // Auto-hide after X ms (0 = manual hide)
                defaultMessages: {
                    default: { main: 'Loading...', sub: 'Please wait' },
                    form: { main: 'Submitting...', sub: 'Processing your request' },
                    sync: { main: 'Syncing...', sub: 'Updating data' },
                    redirect: { main: 'Loading page...', sub: 'Please wait' },
                    save: { main: 'Saving...', sub: 'Please wait' },
                    delete: { main: 'Deleting...', sub: 'Please wait' },
                    upload: { main: 'Uploading...', sub: 'Please wait' }
                }
            },

            // Initialize
            init() {
                this.overlay = document.getElementById('global-loading');
                this.mainText = document.getElementById('global-loading-main-text');
                this.subText = document.getElementById('global-loading-sub-text');
                this.spinner = document.getElementById('global-loading-spinner');

                if (!this.overlay) {
                    console.warn('GlobalLoading: Loading overlay not found in DOM');
                }
            },

            // Core show method
            show(mainText = null, subText = null, type = 'default') {
                if (!this.overlay) this.init();
                if (!this.overlay) return this;

                // Use default messages if not provided
                const messages = this.config.defaultMessages[type] || this.config.defaultMessages.default;
                const finalMainText = mainText || messages.main;
                const finalSubText = subText || messages.sub;

                // Update text content
                if (this.mainText) this.mainText.textContent = finalMainText;
                if (this.subText) this.subText.textContent = finalSubText;

                // Show overlay
                this.overlay.style.display = 'flex';
                this.isVisible = true;
                this.currentType = type;

                // Auto-hide if configured
                if (this.config.autoHideDelay > 0) {
                    setTimeout(() => this.hide(), this.config.autoHideDelay);
                }

                return this;
            },

            // Core hide method
            hide() {
                if (!this.overlay) this.init();
                if (!this.overlay) return this;

                this.overlay.style.display = 'none';
                this.isVisible = false;
                this.currentType = 'default';

                return this;
            },

            // Convenient methods for different contexts
            showForm(mainText = null, subText = null) {
                return this.show(mainText, subText, 'form');
            },

            showSync(mainText = null, subText = null) {
                return this.show(mainText, subText, 'sync');
            },

            showSave(mainText = null, subText = null) {
                return this.show(mainText, subText, 'save');
            },

            showDelete(mainText = null, subText = null) {
                return this.show(mainText, subText, 'delete');
            },

            showUpload(mainText = null, subText = null) {
                return this.show(mainText, subText, 'upload');
            },

            showRedirect(mainText = null, subText = null) {
                return this.show(mainText, subText, 'redirect');
            },

            // Update text while loading is visible
            updateText(mainText, subText = null) {
                if (this.isVisible) {
                    if (this.mainText && mainText) this.mainText.textContent = mainText;
                    if (this.subText && subText) this.subText.textContent = subText;
                }
                return this;
            },

            // Navigation with loading
            navigateTo(url, mainText = null, subText = null) {
                this.showRedirect(mainText, subText);
                setTimeout(() => {
                    window.location.href = url;
                }, 200);
                return this;
            },

            // Promise-based loading for async operations
            async wrap(promise, mainText = null, subText = null, type = 'default') {
                this.show(mainText, subText, type);
                try {
                    const result = await promise;
                    this.hide();
                    return result;
                } catch (error) {
                    this.hide();
                    throw error;
                }
            },

            // Configuration methods
            setConfig(newConfig) {
                this.config = { ...this.config, ...newConfig };
                return this;
            },

            // Utility methods
            isShowing() {
                return this.isVisible;
            },

            getCurrentType() {
                return this.currentType;
            }
        };

        // Initialize GlobalLoading and GlobalNotification on page load
        document.addEventListener('DOMContentLoaded', function() {
            window.GlobalLoading.init();
            window.GlobalNotification.init();
        });

        // Auto-hide loading on page load
        window.addEventListener('load', function() {
            window.GlobalLoading.hide();
        });

        // Backward compatibility aliases
        window.LoadingManager = window.GlobalLoading;
        window.NotificationManager = window.GlobalNotification;

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