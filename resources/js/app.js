import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Initialize all global systems when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.GlobalNotification.init();
    window.GlobalLoading.init();
});

// Backward compatibility aliases
window.LoadingManager = window.GlobalLoading;
window.NotificationManager = window.GlobalNotification;
window.APIManager = window.GlobalAPI;

// Global API System - Pure API calls without UI coupling
window.GlobalAPI = {
    // Configuration
    config: {
        baseURL: '',
        timeout: 30000,
        retries: 3,
        retryDelay: 1000
    },

    // Get default headers
    getDefaultHeaders() {
        const headers = {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        };

        // Add CSRF token if available
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            headers['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
        }

        return headers;
    },

    // Make API request with standardized error handling
    async request(url, options = {}) {
        const defaultOptions = {
            method: 'GET',
            headers: this.getDefaultHeaders(),
            ...options
        };

        // Merge headers
        if (options.headers) {
            defaultOptions.headers = { ...defaultOptions.headers, ...options.headers };
        }

        try {
            // Add timeout to prevent hanging
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), this.config.timeout);

            const response = await fetch(url, {
                ...defaultOptions,
                signal: controller.signal
            });

            clearTimeout(timeoutId);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            return { success: true, data, response };
        } catch (error) {
            console.error('API request failed:', error);
            if (error.name === 'AbortError') {
                return { success: false, error: 'Request timeout', originalError: error };
            }
            return { success: false, error: error.message, originalError: error };
        }
    },

    // Convenience methods for different HTTP verbs
    async get(url, options = {}) {
        return this.request(url, { ...options, method: 'GET' });
    },

    async post(url, data = null, options = {}) {
        const postOptions = { ...options, method: 'POST' };

        if (data) {
            if (data instanceof FormData) {
                postOptions.body = data;
            } else {
                postOptions.headers = {
                    'Content-Type': 'application/json',
                    ...postOptions.headers
                };
                postOptions.body = JSON.stringify(data);
            }
        }

        return this.request(url, postOptions);
    },

    async put(url, data = null, options = {}) {
        const putOptions = { ...options, method: 'PUT' };

        if (data) {
            if (data instanceof FormData) {
                putOptions.body = data;
            } else {
                putOptions.headers = {
                    'Content-Type': 'application/json',
                    ...putOptions.headers
                };
                putOptions.body = JSON.stringify(data);
            }
        }

        return this.request(url, putOptions);
    },

    async delete(url, options = {}) {
        return this.request(url, { ...options, method: 'DELETE' });
    },

    // Configuration methods
    setConfig(newConfig) {
        this.config = { ...this.config, ...newConfig };
        return this;
    },

    // Utility methods
    handleError(error, defaultMessage = 'An error occurred') {
        const message = error?.message || error || defaultMessage;
        console.error('GlobalAPI Error:', error);
        return message;
    }
};

// Global Notification System
window.GlobalNotification = {
    // Elements
    container: null,

    // Initialize notification system
    init() {
        this.createContainer();
    },

    // Create notification container
    createContainer() {
        // Use existing container from app.blade.php if available
        this.container = document.getElementById('global-notification-container');

        if (!this.container) {
            // Fallback: create new container if not found
            this.container = document.createElement('div');
            this.container.id = 'global-notification-container';
            this.container.className = 'fixed bottom-4 right-4 z-50 space-y-2';
            document.body.appendChild(this.container);
        }
    },

    // Show notification
    show(type, title, message, duration = 5000) {
        this.createContainer();

        const id = 'notification-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
        const notification = this.createNotification(id, type, title, message);

        this.container.appendChild(notification);

        // Auto hide after duration
        if (duration > 0) {
            setTimeout(() => this.hide(id), duration);
        }

        return id;
    },

    // Create notification element
    createNotification(id, type, title, message) {
        const colors = {
            success: 'bg-green-50 border-green-200 text-green-800',
            error: 'bg-red-50 border-red-200 text-red-800',
            warning: 'bg-yellow-50 border-yellow-200 text-yellow-800',
            info: 'bg-blue-50 border-blue-200 text-blue-800'
        };

        const icons = {
            success: `<svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>`,
            error: `<svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>`,
            warning: `<svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>`,
            info: `<svg class="w-5 h-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
            </svg>`
        };

        const notification = document.createElement('div');
        notification.id = id;
        notification.className = `notification-item max-w-sm w-full ${colors[type]} border rounded-lg shadow-lg p-4 transform transition-all duration-300 ease-in-out translate-x-full opacity-0`;
        notification.style.cssText = 'pointer-events: auto; position: relative; z-index: 9999;';

        notification.innerHTML = `
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    ${icons[type]}
                </div>
                <div class="ml-3 w-0 flex-1">
                    <p class="text-sm font-medium">${title}</p>
                    <p class="mt-1 text-sm">${message}</p>
                </div>
                <div class="ml-4 flex-shrink-0 flex">
                    <button class="inline-flex text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-600 transition ease-in-out duration-150" onclick="window.GlobalNotification.hide('${id}')">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        `;

        // Trigger animation after a brief delay
        setTimeout(() => {
            notification.classList.remove('translate-x-full', 'opacity-0');
        }, 10);

        return notification;
    },

    // Hide notification
    hide(id) {
        const notification = document.getElementById(id);
        if (notification) {
            notification.classList.add('translate-x-full', 'opacity-0');
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }
    },

    // Convenience methods
    success(title, message, duration = 5000) {
        return this.show('success', title, message, duration);
    },

    error(title, message, duration = 8000) {
        return this.show('error', title, message, duration);
    },

    warning(title, message, duration = 6000) {
        return this.show('warning', title, message, duration);
    },

    info(title, message, duration = 5000) {
        return this.show('info', title, message, duration);
    }
};

// Global Loading System
window.GlobalLoading = {
    // Elements
    overlay: null,

    // Initialize loading system
    init() {
        this.createOverlay();
    },

    // Create loading overlay
    createOverlay() {
        if (this.overlay) return;

        this.overlay = document.createElement('div');
        this.overlay.id = 'global-loading-overlay';
        this.overlay.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden';
        this.overlay.innerHTML = `
            <div class="bg-white rounded-lg p-6 flex items-center space-x-4 shadow-xl">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <span class="text-gray-700 font-medium" id="loading-text">Loading...</span>
            </div>
        `;
        document.body.appendChild(this.overlay);
    },

    // Show loading
    show(text = 'Loading...') {
        this.createOverlay();
        const textElement = this.overlay.querySelector('#loading-text');
        if (textElement) {
            textElement.textContent = text;
        }
        this.overlay.classList.remove('hidden');
    },

    // Hide loading
    hide() {
        if (this.overlay) {
            this.overlay.classList.add('hidden');
        }
    },

    // Check if loading is visible
    isVisible() {
        return this.overlay && !this.overlay.classList.contains('hidden');
    }
};

// Sync functionality with loading states
window.syncManager = {

    // Sync products function
    async syncProducts(button) {
        if (button.disabled) return;

        // Manage button state
        const originalText = button.textContent;
        button.disabled = true;
        button.textContent = 'Syncing Products...';

        try {
            // Show notification
            GlobalNotification.info('Sync Started', 'Starting to sync products from Lazada...');

            // Make API call
            const result = await GlobalAPI.get('/products/sync');

            // Handle result
            if (result.success && result.data.success) {
                GlobalNotification.success('Sync Complete', result.data.message);
                setTimeout(() => window.location.reload(), 1500);
            } else {
                const errorMessage = result.data?.message || result.error || 'Unknown error occurred';
                GlobalNotification.error('Sync Failed', errorMessage);
            }
        } catch (error) {
            console.error('Sync error:', error);
            GlobalNotification.error('Sync Failed', 'An unexpected error occurred: ' + error.message);
        } finally {
            // Always restore button
            button.disabled = false;
            button.textContent = originalText;
        }
    },

    // Sync orders function
    async syncOrders(button) {
        if (button.disabled) return;

        // Manage button state
        const originalText = button.textContent;
        button.disabled = true;
        button.textContent = 'Syncing Orders...';

        try {
            // Show notification
            GlobalNotification.info('Sync Started', 'Starting to sync orders from Lazada...');

            // Make API call
            const result = await GlobalAPI.get('/orders/sync');

            // Handle result
            if (result.success && result.data.success) {
                GlobalNotification.success('Sync Complete', result.data.message);
                setTimeout(() => window.location.reload(), 1500);
            } else {
                const errorMessage = result.data?.message || result.error || 'Unknown error occurred';
                GlobalNotification.error('Sync Failed', errorMessage);
            }
        } catch (error) {
            console.error('Sync error:', error);
            GlobalNotification.error('Sync Failed', 'An unexpected error occurred: ' + error.message);
        } finally {
            // Always restore button
            button.disabled = false;
            button.textContent = originalText;
        }
    },

    // Set button to loading state
    setButtonLoading(button, text) {
        // Store original content and classes for restoration
        button.dataset.originalContent = button.innerHTML;
        button.dataset.originalClass = button.className;

        // Disable button and add loading class
        button.disabled = true;
        button.className = button.dataset.originalClass + ' btn-loading';

        button.innerHTML = `
            <svg class="animate-spin h-5 w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-white font-medium">${text}</span>
        `;

        // Force the button to maintain its blue background with important styles
        button.style.setProperty('background-color', '#4f46e5', 'important'); // indigo-600
        button.style.setProperty('color', 'white', 'important');
        button.style.setProperty('border-color', '#4f46e5', 'important');
    },

    // Reset button to normal state
    resetButton(button, text) {
        button.disabled = false;

        // Remove inline styles
        button.style.removeProperty('background-color');
        button.style.removeProperty('color');
        button.style.removeProperty('border-color');

        // Restore original content and classes if available
        if (button.dataset.originalContent) {
            button.innerHTML = button.dataset.originalContent;
            button.className = button.dataset.originalClass || button.className.replace(' btn-loading', '');
            delete button.dataset.originalContent;
            delete button.dataset.originalClass;
        } else {
            // Remove loading class
            button.className = button.className.replace(' btn-loading', '');
            button.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                </svg>
                ${text}
            `;
        }
    }
};

// Notification system is now handled by GlobalNotification in app.blade.php
// Backward compatibility will be maintained through syncManager.showNotification
