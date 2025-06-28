import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

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
            // Check if GlobalNotification exists
            if (typeof GlobalNotification !== 'undefined') {
                GlobalNotification.info('Sync Started', 'Starting to sync products from Lazada...');
            }

            // Make API call
            const result = await GlobalAPI.get('/products/sync');

            // Handle result
            if (result.success && result.data.success) {
                if (typeof GlobalNotification !== 'undefined') {
                    GlobalNotification.success('Sync Complete', result.data.message);
                }
                setTimeout(() => window.location.reload(), 1500);
            } else {
                const errorMessage = result.data?.message || result.error || 'Unknown error occurred';
                if (typeof GlobalNotification !== 'undefined') {
                    GlobalNotification.error('Sync Failed', errorMessage);
                }
            }
        } catch (error) {
            console.error('Sync error:', error);
            if (typeof GlobalNotification !== 'undefined') {
                GlobalNotification.error('Sync Failed', 'An unexpected error occurred: ' + error.message);
            }
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
            // Check if GlobalNotification exists
            if (typeof GlobalNotification !== 'undefined') {
                GlobalNotification.info('Sync Started', 'Starting to sync orders from Lazada...');
            }

            // Make API call
            const result = await GlobalAPI.get('/orders/sync');

            // Handle result
            if (result.success && result.data.success) {
                if (typeof GlobalNotification !== 'undefined') {
                    GlobalNotification.success('Sync Complete', result.data.message);
                }
                setTimeout(() => window.location.reload(), 1500);
            } else {
                const errorMessage = result.data?.message || result.error || 'Unknown error occurred';
                if (typeof GlobalNotification !== 'undefined') {
                    GlobalNotification.error('Sync Failed', errorMessage);
                }
            }
        } catch (error) {
            console.error('Sync error:', error);
            if (typeof GlobalNotification !== 'undefined') {
                GlobalNotification.error('Sync Failed', 'An unexpected error occurred: ' + error.message);
            }
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
