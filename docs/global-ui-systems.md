# Global UI Systems Documentation

## Overview

This document covers two global UI systems that provide consistent user experience across the application:

1. **Global Loading System** - Centralized loading indicators
2. **Global Notification System** - Unified notification management

---

# Global Loading System

## Overview

The Global Loading System provides a centralized, reusable loading indicator that can be called from anywhere in the application. It displays as an overlay in the middle of the page with a spinner and customizable text messages.

## Features

- **Global Overlay**: Full-screen overlay with centered loading dialog
- **Customizable Messages**: Main text and sub-text with predefined types
- **Promise Wrapping**: Automatic show/hide for async operations
- **Navigation Integration**: Smooth loading transitions between pages
- **Minimum Display Time**: Prevents flickering for fast operations
- **Type-based Presets**: Predefined messages for common operations

## Basic Usage

### Simple Show/Hide

```javascript
// Show loading with default message
GlobalLoading.show();

// Show with custom messages
GlobalLoading.show('Processing...', 'Please wait while we handle your request');

// Hide loading
GlobalLoading.hide();
```

### Type-based Loading

```javascript
// Form submission
GlobalLoading.showForm();
GlobalLoading.showForm('Submitting form...', 'Processing your data');

// Data synchronization
GlobalLoading.showSync();
GlobalLoading.showSync('Syncing products...', 'Updating from Lazada API');

// File upload
GlobalLoading.showUpload();
GlobalLoading.showUpload('Uploading file...', 'Processing Excel data');

// Save operations
GlobalLoading.showSave();

// Delete operations
GlobalLoading.showDelete();
```

## Advanced Usage

### Promise Wrapping

Automatically show loading during async operations:

```javascript
// Wrap a fetch request
const result = await GlobalLoading.wrap(
    fetch('/api/sync-products').then(r => r.json()),
    'Syncing products...',
    'This may take a few moments',
    'sync'
);

// Wrap any promise
const data = await GlobalLoading.wrap(
    someAsyncOperation(),
    'Processing...',
    'Please wait'
);
```

### Navigation with Loading

```javascript
// Navigate with loading overlay
GlobalLoading.navigateTo('/products');

// Navigate with custom message
GlobalLoading.navigateTo('/bulk-update', 'Loading bulk update...', 'Preparing interface');
```

### Dynamic Text Updates

```javascript
// Show initial loading
GlobalLoading.show('Starting process...', 'Initializing');

// Update text during operation
GlobalLoading.updateText('Processing data...', 'Step 2 of 3');

// Update again
GlobalLoading.updateText('Almost done...', 'Finalizing');

// Hide when complete
GlobalLoading.hide();
```

## Configuration

### Default Messages

The system includes predefined messages for common operations:

```javascript
const defaultMessages = {
    default: { main: 'Loading...', sub: 'Please wait' },
    form: { main: 'Submitting...', sub: 'Processing your request' },
    sync: { main: 'Syncing...', sub: 'Updating data' },
    redirect: { main: 'Loading page...', sub: 'Please wait' },
    save: { main: 'Saving...', sub: 'Please wait' },
    delete: { main: 'Deleting...', sub: 'Please wait' },
    upload: { main: 'Uploading...', sub: 'Please wait' }
};
```

### Custom Configuration

```javascript
// Update configuration
GlobalLoading.setConfig({
    minDisplayTime: 500,     // Minimum display time in ms
    autoHideDelay: 3000,     // Auto-hide after 3 seconds
    defaultMessages: {
        custom: { main: 'Custom loading...', sub: 'Custom message' }
    }
});
```

## Real-world Examples

### Form Submission

```javascript
document.getElementById('myForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    try {
        GlobalLoading.showForm('Submitting form...', 'Processing your data');
        
        const response = await fetch('/submit', {
            method: 'POST',
            body: new FormData(this)
        });
        
        const result = await response.json();
        
        if (result.success) {
            GlobalLoading.updateText('Success!', 'Redirecting...');
            setTimeout(() => {
                GlobalLoading.navigateTo('/success');
            }, 1000);
        } else {
            GlobalLoading.hide();
            // Show error notification
        }
    } catch (error) {
        GlobalLoading.hide();
        // Handle error
    }
});
```

### AJAX Data Sync

```javascript
async function syncProducts() {
    try {
        const result = await GlobalLoading.wrap(
            fetch('/api/sync-products', { method: 'POST' }).then(r => r.json()),
            'Syncing products...',
            'Fetching latest data from Lazada',
            'sync'
        );
        
        // Show success notification
        GlobalNotification.success('Sync Complete', 'Products synced successfully!');
        
        // Refresh page data
        location.reload();
    } catch (error) {
        GlobalNotification.error('Sync Failed', error.message);
    }
}
```

### File Upload with Progress

```javascript
async function uploadFile(file) {
    GlobalLoading.showUpload('Uploading file...', 'Preparing upload');
    
    try {
        const formData = new FormData();
        formData.append('file', file);
        
        GlobalLoading.updateText('Uploading...', 'Transferring file');
        
        const response = await fetch('/upload', {
            method: 'POST',
            body: formData
        });
        
        GlobalLoading.updateText('Processing...', 'Analyzing file content');
        
        const result = await response.json();
        
        GlobalLoading.updateText('Complete!', 'File processed successfully');
        
        setTimeout(() => {
            GlobalLoading.hide();
            // Handle success
        }, 1000);
        
    } catch (error) {
        GlobalLoading.hide();
        // Handle error
    }
}
```

## Utility Methods

```javascript
// Check if loading is currently visible
if (GlobalLoading.isShowing()) {
    console.log('Loading is active');
}

// Get current loading type
const currentType = GlobalLoading.getCurrentType();

// Method chaining
GlobalLoading
    .show('Step 1...', 'Starting process')
    .updateText('Step 2...', 'Processing data')
    .hide();
```

## Integration with Existing Code

### Backward Compatibility

The system provides a backward compatibility alias:

```javascript
// Both work the same way
GlobalLoading.show();
LoadingManager.show();  // Alias for backward compatibility
```

### Automatic Link Loading

The system automatically adds loading to internal navigation links. To disable for specific links:

```html
<!-- This link will show loading automatically -->
<a href="/products">Products</a>

<!-- This link will NOT show loading -->
<a href="/products" class="no-loading">Products</a>

<!-- External links are ignored -->
<a href="https://external-site.com">External</a>
```

## Best Practices

1. **Use appropriate types**: Choose the right loading type for the operation
2. **Provide meaningful messages**: Give users context about what's happening
3. **Handle errors**: Always hide loading in error cases
4. **Don't nest**: Avoid showing multiple loading overlays
5. **Minimum display time**: The system prevents flickering automatically
6. **Update progress**: Use `updateText()` for multi-step operations

## Troubleshooting

### Loading Not Showing

```javascript
// Check if elements exist
if (!GlobalLoading.overlay) {
    console.error('Global loading overlay not found in DOM');
    GlobalLoading.init(); // Try to reinitialize
}
```

### Loading Stuck

```javascript
// Force hide if stuck
GlobalLoading.hide();

// Check current state
console.log('Is showing:', GlobalLoading.isShowing());
console.log('Current type:', GlobalLoading.getCurrentType());
```

## CSS Customization

The loading overlay uses inline styles but can be customized:

```css
/* Override loading overlay styles */
#global-loading {
    background-color: rgba(0, 0, 0, 0.8) !important;
}

#global-loading > div {
    background-color: #f8f9fa !important;
    border-radius: 8px !important;
}

/* Customize spinner */
#global-loading-spinner {
    border-top-color: #28a745 !important;
}
```

---

# Global Notification System

## Overview

The Global Notification System provides a centralized, reusable notification system that can be called from anywhere in the application. It displays notifications in the bottom-right corner with consistent styling and animations.

## Features

- **4 Notification Types**: success, error, info, warning
- **Auto-dismiss**: Configurable duration with auto-hide
- **Manual Dismiss**: Click X button to close
- **Queue Management**: Automatic handling of multiple notifications
- **Animations**: Smooth slide-in/slide-out effects
- **Responsive Design**: Works on all screen sizes

## Basic Usage

### Simple Notifications

```javascript
// Show success notification
GlobalNotification.success('Success!', 'Operation completed successfully');

// Show error notification
GlobalNotification.error('Error!', 'Something went wrong');

// Show info notification
GlobalNotification.info('Information', 'Here is some useful information');

// Show warning notification
GlobalNotification.warning('Warning!', 'Please be careful');
```

### Generic Show Method

```javascript
// Generic method with all parameters
GlobalNotification.show(type, title, message, duration);

// Examples
GlobalNotification.show('success', 'Saved!', 'Data saved successfully', 3000);
GlobalNotification.show('error', 'Failed!', 'Could not save data', 0); // 0 = no auto-hide
```

## Advanced Usage

### Custom Duration

```javascript
// Show for 10 seconds
GlobalNotification.success('Long Message', 'This will stay for 10 seconds', 10000);

// Show permanently (no auto-hide)
GlobalNotification.error('Critical Error', 'Manual dismiss required', 0);

// Use default duration (5 seconds)
GlobalNotification.info('Default', 'Uses default 5 second duration');
```

### Managing Notifications

```javascript
// Get notification ID for manual control
const notificationId = GlobalNotification.success('Saved!', 'Data saved');

// Hide specific notification
GlobalNotification.hide(notificationId);

// Clear all notifications
GlobalNotification.clear();

// Check active notifications
const activeCount = GlobalNotification.getActiveCount();
const activeIds = GlobalNotification.getActiveNotifications();
```

### Configuration

```javascript
// Update global configuration
GlobalNotification.setConfig({
    duration: 7000,           // Default duration in ms
    maxNotifications: 3,      // Maximum notifications shown at once
    position: 'bottom-right', // Position (future feature)
    animations: true          // Enable/disable animations
});
```

## Real-world Examples

### Form Submission Success

```javascript
// After successful form submission
fetch('/api/save', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            GlobalNotification.success('Form Submitted', 'Your data has been saved successfully');
        } else {
            GlobalNotification.error('Submission Failed', data.message);
        }
    })
    .catch(error => {
        GlobalNotification.error('Network Error', 'Please check your connection and try again');
    });
```

### Stock Update (Edit Stock Page)

```javascript
// Stock update success
GlobalNotification.success('Stock Updated', 'Product stock quantity updated successfully');

// Stock update error
GlobalNotification.error('Update Failed', 'Could not update stock quantity');
```

### Bulk Operations

```javascript
// Start notification
GlobalNotification.info('Processing Started', 'Bulk update is now processing...');

// Progress notification (replace previous)
const progressId = GlobalNotification.info('Processing', 'Processed 50 of 100 items...');

// Completion notification
GlobalNotification.success('Bulk Update Complete', 'Successfully processed 95 items, 5 failed');
```

### Data Sync Operations

```javascript
// Sync start
GlobalNotification.info('Sync Started', 'Starting to sync products from Lazada...');

// Sync success
GlobalNotification.success('Sync Complete', 'Successfully synced 150 products');

// Sync error
GlobalNotification.error('Sync Failed', 'Network error occurred. Please try again.');
```

## Integration with Existing Code

### Consistent Usage Across Application

All pages now use GlobalNotification directly for consistent behavior:

```javascript
// Products and Orders sync
GlobalNotification.info('Sync Started', 'Starting to sync products from Lazada...');
GlobalNotification.success('Sync Complete', 'Successfully synced 150 products');

// Stock updates
GlobalNotification.success('Stock Updated', 'Product stock quantity updated successfully');

// Bulk operations
GlobalNotification.info('Processing Started', 'Bulk update is now processing...');
GlobalNotification.success('Bulk Update Complete', 'Successfully processed 95 items, 5 failed');
```

### Migration from Custom Notifications

```javascript
// Old custom notification (edit-stock page)
showNotification('Stock updated successfully', 'success');

// New GlobalNotification
GlobalNotification.success('Stock Updated', 'Stock updated successfully');

// Old syncManager calls
window.syncManager.showNotification('success', 'Title', 'Message');

// New direct GlobalNotification calls
GlobalNotification.success('Title', 'Message');
```

## Notification Types and Styling

### Success Notifications
- **Color**: Green theme
- **Icon**: Checkmark
- **Use for**: Successful operations, confirmations

### Error Notifications
- **Color**: Red theme
- **Icon**: X mark
- **Use for**: Errors, failures, validation issues

### Info Notifications
- **Color**: Blue theme
- **Icon**: Information circle
- **Use for**: General information, process updates

### Warning Notifications
- **Color**: Yellow theme
- **Icon**: Warning triangle
- **Use for**: Warnings, cautions, important notices

## Best Practices

1. **Use Appropriate Types**: Choose the right notification type for the context
2. **Clear Titles**: Use concise, descriptive titles
3. **Helpful Messages**: Provide actionable information in messages
4. **Reasonable Duration**: Use appropriate auto-hide timing
5. **Don't Spam**: Avoid showing too many notifications at once
6. **Handle Errors**: Always show notifications for error states
7. **Confirm Actions**: Show success notifications for important actions

## Troubleshooting

### Notifications Not Showing

```javascript
// Check if GlobalNotification is available
if (!window.GlobalNotification) {
    console.error('GlobalNotification not loaded');
}

// Check if container exists
if (!GlobalNotification.container) {
    GlobalNotification.init();
}
```

### Notifications Stuck

```javascript
// Clear all notifications
GlobalNotification.clear();

// Check active notifications
console.log('Active notifications:', GlobalNotification.getActiveCount());
```

### Styling Issues

```css
/* Override notification container position */
#global-notification-container {
    bottom: 30px !important;
    right: 30px !important;
}

/* Customize notification appearance */
.notification-success {
    background-color: #f0f9ff !important;
    border-color: #0ea5e9 !important;
}
```

## API Reference

### Methods

- `GlobalNotification.show(type, title, message, duration)` - Show notification
- `GlobalNotification.success(title, message, duration)` - Show success notification
- `GlobalNotification.error(title, message, duration)` - Show error notification
- `GlobalNotification.info(title, message, duration)` - Show info notification
- `GlobalNotification.warning(title, message, duration)` - Show warning notification
- `GlobalNotification.hide(notificationId)` - Hide specific notification
- `GlobalNotification.clear()` - Clear all notifications
- `GlobalNotification.setConfig(config)` - Update configuration
- `GlobalNotification.getActiveCount()` - Get number of active notifications
- `GlobalNotification.getActiveNotifications()` - Get array of active notification IDs

### Configuration Options

- `duration` (number): Default auto-hide duration in milliseconds (default: 5000)
- `maxNotifications` (number): Maximum notifications shown at once (default: 5)
- `position` (string): Position of notification container (default: 'bottom-right')
- `animations` (boolean): Enable/disable animations (default: true)
