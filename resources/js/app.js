import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Sync functionality with loading states
window.syncManager = {
    // Show notification function (similar to bulk-update)
    showNotification(type, title, message, duration = 5000) {
        const notificationContainer = document.getElementById('notification-container') || this.createNotificationContainer();
        const notificationId = 'notification-' + Date.now();

        const typeClasses = {
            'success': 'bg-green-50 border-green-200 text-green-800',
            'error': 'bg-red-50 border-red-200 text-red-800',
            'info': 'bg-blue-50 border-blue-200 text-blue-800',
            'warning': 'bg-yellow-50 border-yellow-200 text-yellow-800'
        };

        const iconSvgs = {
            'success': '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>',
            'error': '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>',
            'info': '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>',
            'warning': '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>'
        };

        const notification = document.createElement('div');
        notification.id = notificationId;
        notification.className = `max-w-sm w-full ${typeClasses[type]} border rounded-lg shadow-lg p-4 transform translate-x-full opacity-0 transition-all duration-300 ease-in-out mb-4`;
        notification.style.cssText = 'position: relative !important; z-index: 999999 !important; pointer-events: auto !important;';
        notification.innerHTML = `
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    ${iconSvgs[type]}
                </div>
                <div class="ml-3 flex-1">
                    <h3 class="text-sm font-medium">${title}</h3>
                    <p class="mt-1 text-sm">${message}</p>
                </div>
            </div>
        `;

        notificationContainer.appendChild(notification);

        // Show animation
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
            notification.style.opacity = '1';
        }, 50);

        // Auto hide
        setTimeout(() => {
            this.hideNotification(notificationId);
        }, duration);

        return notificationId;
    },

    hideNotification(notificationId) {
        const notification = document.getElementById(notificationId);
        if (notification) {
            notification.style.transform = 'translateX(full)';
            notification.style.opacity = '0';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }
    },

    createNotificationContainer() {
        const container = document.createElement('div');
        container.id = 'notification-container';
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

    // Sync products function
    syncProducts(button) {
        if (button.disabled) return;

        // Set loading state
        this.setButtonLoading(button, 'Syncing Products...');

        // Show start notification
        this.showNotification('info', 'Sync Started', 'Starting to sync products from Lazada...');

        // Make AJAX request
        fetch('/products/sync', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showNotification('success', 'Sync Complete', data.message);
                // Reload page to show updated products
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                this.showNotification('error', 'Sync Failed', data.message || 'Unknown error occurred');
                this.resetButton(button, 'Sync Products');
            }
        })
        .catch(error => {
            console.error('Sync error:', error);
            this.showNotification('error', 'Sync Failed', 'Network error occurred. Please try again.');
            this.resetButton(button, 'Sync Products');
        });
    },

    // Sync orders function
    syncOrders(button) {
        if (button.disabled) return;

        // Set loading state
        this.setButtonLoading(button, 'Syncing Orders...');

        // Show start notification
        this.showNotification('info', 'Sync Started', 'Starting to sync orders from Lazada...');

        // Make AJAX request
        fetch('/orders/sync', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showNotification('success', 'Sync Complete', data.message);
                // Reload page to show updated orders
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                this.showNotification('error', 'Sync Failed', data.message || 'Unknown error occurred');
                this.resetButton(button, 'Sync Orders');
            }
        })
        .catch(error => {
            console.error('Sync error:', error);
            this.showNotification('error', 'Sync Failed', 'Network error occurred. Please try again.');
            this.resetButton(button, 'Sync Orders');
        });
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

// Expose notification system globally for use in other pages
window.NotificationSystem = {
    show: function(type, title, message) {
        return LazadaERP.showNotification(type, title, message);
    }
};
