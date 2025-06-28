@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Global Notification System Test</h2>
        <p class="text-gray-500">Test all notification types and features</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Basic Notifications -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold">Basic Notifications</h3>
            </div>
            <div class="card-body space-y-3">
                <button onclick="testSuccess()" class="btn btn-success w-full">
                    Success Notification
                </button>
                <button onclick="testError()" class="btn btn-danger w-full">
                    Error Notification
                </button>
                <button onclick="testInfo()" class="btn btn-primary w-full">
                    Info Notification
                </button>
                <button onclick="testWarning()" class="btn btn-secondary w-full">
                    Warning Notification
                </button>
            </div>
        </div>

        <!-- Duration Tests -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold">Duration Tests</h3>
            </div>
            <div class="card-body space-y-3">
                <button onclick="testShortDuration()" class="btn btn-primary w-full">
                    Short (2s)
                </button>
                <button onclick="testLongDuration()" class="btn btn-primary w-full">
                    Long (10s)
                </button>
                <button onclick="testPermanent()" class="btn btn-primary w-full">
                    Permanent (No Auto-hide)
                </button>
                <button onclick="testDefault()" class="btn btn-primary w-full">
                    Default (5s)
                </button>
            </div>
        </div>

        <!-- Management Tests -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold">Management</h3>
            </div>
            <div class="card-body space-y-3">
                <button onclick="testMultiple()" class="btn btn-primary w-full">
                    Show Multiple
                </button>
                <button onclick="clearAll()" class="btn btn-danger w-full">
                    Clear All
                </button>
                <button onclick="showCount()" class="btn btn-secondary w-full">
                    Show Count
                </button>
                <button onclick="testQueue()" class="btn btn-primary w-full">
                    Test Queue (6 notifications)
                </button>
            </div>
        </div>

        <!-- Real-world Examples -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold">Real-world Examples</h3>
            </div>
            <div class="card-body space-y-3">
                <button onclick="simulateStockUpdate()" class="btn btn-success w-full">
                    Stock Update Success
                </button>
                <button onclick="simulateStockError()" class="btn btn-danger w-full">
                    Stock Update Error
                </button>
                <button onclick="simulateSync()" class="btn btn-primary w-full">
                    Simulate Sync Process
                </button>
                <button onclick="simulateBulkUpdate()" class="btn btn-primary w-full">
                    Simulate Bulk Update
                </button>
            </div>
        </div>

        <!-- Backward Compatibility -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold">Backward Compatibility</h3>
            </div>
            <div class="card-body space-y-3">
                <button onclick="testSyncManager()" class="btn btn-primary w-full">
                    Test syncManager.showNotification
                </button>
                <button onclick="testOldStyle()" class="btn btn-primary w-full">
                    Test Old Style Call
                </button>
                <button onclick="testFallback()" class="btn btn-secondary w-full">
                    Test Fallback
                </button>
            </div>
        </div>

        <!-- Configuration Tests -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold">Configuration</h3>
            </div>
            <div class="card-body space-y-3">
                <button onclick="changeConfig()" class="btn btn-primary w-full">
                    Change Default Duration
                </button>
                <button onclick="changeMaxNotifications()" class="btn btn-primary w-full">
                    Change Max Notifications
                </button>
                <button onclick="resetConfig()" class="btn btn-secondary w-full">
                    Reset Configuration
                </button>
                <button onclick="showConfig()" class="btn btn-secondary w-full">
                    Show Current Config
                </button>
            </div>
        </div>
    </div>

    <!-- Test Results -->
    <div class="mt-8">
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold">Test Console</h3>
            </div>
            <div class="card-body">
                <div id="test-console" class="bg-gray-100 p-4 rounded-lg font-mono text-sm h-40 overflow-y-auto">
                    <div class="text-gray-600">Click buttons above to test notifications...</div>
                </div>
                <button onclick="clearConsole()" class="btn btn-secondary mt-2">Clear Console</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Test console logging
    function log(message) {
        const console = document.getElementById('test-console');
        const timestamp = new Date().toLocaleTimeString();
        const logEntry = document.createElement('div');
        logEntry.innerHTML = `<span class="text-gray-500">[${timestamp}]</span> ${message}`;
        console.appendChild(logEntry);
        console.scrollTop = console.scrollHeight;
    }

    // Basic notification tests
    window.testSuccess = function() {
        const id = GlobalNotification.success('Success!', 'This is a success notification');
        log(`Success notification shown with ID: ${id}`);
    };

    window.testError = function() {
        const id = GlobalNotification.error('Error!', 'This is an error notification');
        log(`Error notification shown with ID: ${id}`);
    };

    window.testInfo = function() {
        const id = GlobalNotification.info('Information', 'This is an info notification');
        log(`Info notification shown with ID: ${id}`);
    };

    window.testWarning = function() {
        const id = GlobalNotification.warning('Warning!', 'This is a warning notification');
        log(`Warning notification shown with ID: ${id}`);
    };

    // Duration tests
    window.testShortDuration = function() {
        const id = GlobalNotification.info('Short Duration', 'This will disappear in 2 seconds', 2000);
        log(`Short duration notification (2s) shown with ID: ${id}`);
    };

    window.testLongDuration = function() {
        const id = GlobalNotification.info('Long Duration', 'This will disappear in 10 seconds', 10000);
        log(`Long duration notification (10s) shown with ID: ${id}`);
    };

    window.testPermanent = function() {
        const id = GlobalNotification.warning('Permanent', 'This will not auto-hide (click X to close)', 0);
        log(`Permanent notification shown with ID: ${id}`);
    };

    window.testDefault = function() {
        const id = GlobalNotification.info('Default Duration', 'This uses default 5 second duration');
        log(`Default duration notification shown with ID: ${id}`);
    };

    // Management tests
    window.testMultiple = function() {
        GlobalNotification.success('First', 'First notification');
        GlobalNotification.info('Second', 'Second notification');
        GlobalNotification.warning('Third', 'Third notification');
        log('Multiple notifications shown');
    };

    window.clearAll = function() {
        const count = GlobalNotification.getActiveCount();
        GlobalNotification.clear();
        log(`Cleared all notifications (${count} were active)`);
    };

    window.showCount = function() {
        const count = GlobalNotification.getActiveCount();
        const ids = GlobalNotification.getActiveNotifications();
        log(`Active notifications: ${count} (IDs: ${ids.join(', ')})`);
        GlobalNotification.info('Count', `Currently ${count} active notifications`);
    };

    window.testQueue = function() {
        for (let i = 1; i <= 6; i++) {
            GlobalNotification.info(`Notification ${i}`, `This is notification number ${i}`);
        }
        log('6 notifications added to test queue management (max 5 should be shown)');
    };

    // Real-world examples
    window.simulateStockUpdate = function() {
        GlobalNotification.success('Stock Updated', 'Product stock quantity updated successfully');
        log('Simulated stock update success');
    };

    window.simulateStockError = function() {
        GlobalNotification.error('Update Failed', 'Could not update stock quantity. Please try again.');
        log('Simulated stock update error');
    };

    window.simulateSync = function() {
        GlobalNotification.info('Sync Started', 'Starting to sync products from Lazada...');
        setTimeout(() => {
            GlobalNotification.success('Sync Complete', 'Successfully synced 150 products');
        }, 2000);
        log('Simulated sync process (start + completion after 2s)');
    };

    window.simulateBulkUpdate = function() {
        GlobalNotification.info('Processing Started', 'Bulk update is now processing...');
        setTimeout(() => {
            GlobalNotification.info('Processing', 'Processed 50 of 100 items...');
        }, 1500);
        setTimeout(() => {
            GlobalNotification.success('Bulk Update Complete', 'Successfully processed 95 items, 5 failed');
        }, 3000);
        log('Simulated bulk update process (start + progress + completion)');
    };

    // Backward compatibility tests
    window.testSyncManager = function() {
        if (window.syncManager && window.syncManager.showNotification) {
            const id = window.syncManager.showNotification('success', 'Sync Manager', 'Called via syncManager.showNotification');
            log(`syncManager.showNotification called, ID: ${id}`);
        } else {
            log('syncManager.showNotification not available');
        }
    };

    window.testOldStyle = function() {
        // Simulate old style call
        GlobalNotification.show('info', 'Old Style', 'Called via GlobalNotification.show()');
        log('Old style GlobalNotification.show() called');
    };

    window.testFallback = function() {
        // Test what happens when GlobalNotification is not available
        const originalGN = window.GlobalNotification;
        window.GlobalNotification = null;
        
        try {
            if (window.syncManager && window.syncManager.showNotification) {
                window.syncManager.showNotification('warning', 'Fallback Test', 'Testing fallback mechanism');
                log('Fallback mechanism tested (GlobalNotification temporarily disabled)');
            }
        } finally {
            window.GlobalNotification = originalGN;
        }
    };

    // Configuration tests
    window.changeConfig = function() {
        GlobalNotification.setConfig({ duration: 8000 });
        GlobalNotification.info('Config Changed', 'Default duration changed to 8 seconds');
        log('Configuration changed: duration = 8000ms');
    };

    window.changeMaxNotifications = function() {
        GlobalNotification.setConfig({ maxNotifications: 3 });
        GlobalNotification.info('Config Changed', 'Max notifications changed to 3');
        log('Configuration changed: maxNotifications = 3');
    };

    window.resetConfig = function() {
        GlobalNotification.setConfig({
            duration: 5000,
            maxNotifications: 5,
            animations: true
        });
        GlobalNotification.info('Config Reset', 'Configuration reset to defaults');
        log('Configuration reset to defaults');
    };

    window.showConfig = function() {
        const config = GlobalNotification.config;
        const configStr = JSON.stringify(config, null, 2);
        log(`Current configuration: ${configStr}`);
        GlobalNotification.info('Current Config', 'Check console for details');
    };

    // Console management
    window.clearConsole = function() {
        document.getElementById('test-console').innerHTML = '<div class="text-gray-600">Console cleared...</div>';
    };

    log('Notification test page loaded. GlobalNotification available: ' + (!!window.GlobalNotification));
});
</script>
@endpush
