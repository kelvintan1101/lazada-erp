@extends('layouts.app')

@section('title', 'Bulk Update Product Titles')

@section('content')
<style>
/* Ensure notification close button is always visible */
.close-btn {
    opacity: 1 !important;
    visibility: visible !important;
    display: flex !important;
    z-index: 9999 !important;
}

/* Notification style optimization */
.notification-item {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
}

/* Fade out animation */
@keyframes fadeOut {
    0% {
        opacity: 1;
        transform: translateX(0) scale(1);
        filter: blur(0px);
    }
    50% {
        opacity: 0.5;
        transform: translateX(25px) scale(0.98);
        filter: blur(0.5px);
    }
    100% {
        opacity: 0;
        transform: translateX(50px) scale(0.95);
        filter: blur(1px);
    }
}

.notification-item.hide {
    animation: fadeOut 2s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}
</style>
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-2xl mx-auto px-4">

        <!-- File upload area -->
        <div id="upload-section" class="bg-white rounded-xl shadow-lg border border-gray-200 p-8">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-900 mb-3">Bulk Update Product Titles</h1>
                <p class="text-gray-600">Upload Excel/CSV file containing SKU and product titles</p>
            </div>

            <!-- Format hint -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-sm text-blue-700 font-medium">Must contain SKU and product title columns</span>
                    </div>
                    <a href="/templates/product_title_update_template.csv"
                       download="product_title_update_template.csv"
                       class="text-sm text-blue-600 hover:text-blue-800 font-medium underline">
                        Download Template
                    </a>
                </div>
            </div>

            <!-- File selection area -->
            <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-blue-400 hover:bg-blue-50 transition-all duration-200 mb-6">
                <input type="file" id="excel-file" accept=".xlsx,.xls,.csv" class="hidden">
                <div id="file-drop-zone" class="cursor-pointer">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    <p class="text-lg font-semibold text-gray-700 mb-1">Select file or drag and drop here</p>
                    <p class="text-sm text-gray-500">Supports .xlsx, .xls, .csv formats, max 10MB</p>
                </div>

                <!-- File info display -->
                <div id="file-info" class="hidden mt-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-sm text-green-700">
                            Selected: <span id="file-name" class="font-medium"></span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Upload button -->
            <div class="flex justify-center">
                <button id="upload-btn"
                        type="button"
                        class="w-full max-w-md px-6 py-3 font-semibold rounded-lg shadow-lg border-2 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2"
                        disabled>
                    <span id="upload-btn-text">Please select a file first</span>
                </button>
            </div>
        </div>



        <!-- Progress display area -->
        <div id="progress-section" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hidden">
            <!-- Circular progress bar -->
            <div class="flex justify-center mb-6">
                <div class="relative w-24 h-24">
                    <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                        <!-- Background circle -->
                        <circle
                            cx="50"
                            cy="50"
                            r="35"
                            stroke="#e5e7eb"
                            stroke-width="6"
                            fill="none"
                        />
                        <!-- Progress circle -->
                        <circle
                            id="progress-circle"
                            cx="50"
                            cy="50"
                            r="35"
                            stroke="url(#progressGradient)"
                            stroke-width="6"
                            fill="none"
                            stroke-linecap="round"
                            stroke-dasharray="219.8"
                            stroke-dashoffset="219.8"
                            class="transition-all duration-500 ease-out"
                        />
                        <!-- Gradient definition -->
                        <defs>
                            <linearGradient id="progressGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="0%" style="stop-color:#3b82f6;stop-opacity:1" />
                                <stop offset="100%" style="stop-color:#8b5cf6;stop-opacity:1" />
                            </linearGradient>
                        </defs>
                    </svg>
                    <!-- Percentage display -->
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="text-center">
                            <div id="progress-percentage" class="text-lg font-bold text-gray-800 leading-none">0%</div>
                            <div class="text-xs text-gray-500 mt-1">Complete</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status information -->
            <div class="text-center mb-6">
                <h3 id="status-message" class="text-lg font-semibold text-gray-800 mb-1">Ready to start...</h3>
                <p id="status-detail" class="text-sm text-gray-600">Initializing processing flow</p>
            </div>

            <!-- Statistics -->
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="text-center">
                    <div class="bg-blue-50 rounded-lg p-3">
                        <p class="text-2xl font-bold text-blue-600" id="total-count">0</p>
                        <p class="text-xs text-gray-600">Total</p>
                    </div>
                </div>
                <div class="text-center">
                    <div class="bg-green-50 rounded-lg p-3">
                        <p class="text-2xl font-bold text-green-600" id="success-count">0</p>
                        <p class="text-xs text-gray-600">Success</p>
                    </div>
                </div>
                <div class="text-center">
                    <div class="bg-red-50 rounded-lg p-3">
                        <p class="text-2xl font-bold text-red-600" id="failed-count">0</p>
                        <p class="text-xs text-gray-600">Failed</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Debug panel -->
        <div id="debug-panel" class="mt-6 bg-gray-100 border border-gray-300 rounded-lg p-4 text-sm">
            <h3 class="font-bold text-gray-700 mb-2">🔧 Debug Information</h3>
            <div class="space-y-1 text-gray-600">
                <div>Button Status: <span id="debug-btn-status" class="font-mono">Checking...</span></div>
                <div>Button Visibility: <span id="debug-btn-visibility" class="font-mono">Checking...</span></div>
                <div>File Selection Status: <span id="debug-file-status" class="font-mono">Not selected</span></div>
                <div class="mt-2">
                    <button onclick="window.testSimpleNotification()" class="bg-red-500 text-white px-3 py-1 rounded text-xs mr-2">Simple Test</button>
                    <button onclick="window.testNotification()" class="bg-blue-500 text-white px-3 py-1 rounded text-xs mr-2">Test Notification</button>
                    <button onclick="window.testSuccessNotification()" class="bg-green-500 text-white px-3 py-1 rounded text-xs mr-2">Test Success Notification</button>
                    <button onclick="window.debugButtonStatus()" class="bg-yellow-500 text-white px-3 py-1 rounded text-xs">Refresh Status</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Notification system -->
<div id="notification-container" style="position: fixed; bottom: 20px; right: 20px; z-index: 999999; max-width: 420px; pointer-events: none;">
    <!-- Notifications will be dynamically created here -->
</div>
@endsection

@push('styles')
<style>
/* Add pulse animation */
@keyframes pulse {
    0% { transform: translate(-50%, -50%) scale(1); }
    50% { transform: translate(-50%, -50%) scale(1.05); }
    100% { transform: translate(-50%, -50%) scale(1); }
}

/* Add notification enter animation */
@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOutRight {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}
/* Notification container style - complete rewrite */
#notification-container {
    position: fixed !important;
    bottom: 20px !important;
    right: 20px !important;
    z-index: 999999 !important;
    max-width: 420px !important;
    pointer-events: none !important;
    display: flex !important;
    flex-direction: column-reverse !important;
    gap: 16px !important;
}

/* Notification item style - complete rewrite */
.notification-item {
    pointer-events: auto !important;
    background: white !important;
    border: 2px solid #e5e7eb !important;
    border-radius: 12px !important;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25) !important;
    padding: 20px !important;
    width: 100% !important;
    max-width: 400px !important;
    position: relative !important;
    z-index: 1000000 !important;
    transform: translateX(100%) !important;
    opacity: 0 !important;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
    backdrop-filter: blur(10px) !important;
    color: #1f2937 !important;
}

/* Ensure text color inside notifications is correct */
.notification-item * {
    color: inherit !important;
}

.notification-item h4 {
    color: #111827 !important;
}

.notification-item p {
    color: #374151 !important;
}

.notification-item.show {
    transform: translateX(0) !important;
    opacity: 1 !important;
}

.notification-item.hide {
    transform: translateX(100%) !important;
    opacity: 0 !important;
}

/* Button style ensure display */
#upload-btn {
    display: block !important;
    visibility: visible !important;
    min-height: 48px !important;
}

/* Button state styles */
#upload-btn:disabled {
    background-color: #9CA3AF !important;
    color: #FFFFFF !important;
    border-color: #6B7280 !important;
    cursor: not-allowed !important;
    opacity: 1 !important;
}

#upload-btn:not(:disabled) {
    background-color: #2563EB !important;
    color: #FFFFFF !important;
    border-color: #1D4ED8 !important;
    cursor: pointer !important;
}

#upload-btn:not(:disabled):hover {
    background-color: #1D4ED8 !important;
    border-color: #1E40AF !important;
    transform: translateY(-1px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

/* Ensure button text display */
#upload-btn-text {
    display: inline !important;
    visibility: visible !important;
    font-weight: 600 !important;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded, initializing bulk update functionality...');

    // Get DOM elements
    const fileInput = document.getElementById('excel-file');
    const fileDropZone = document.getElementById('file-drop-zone');
    const fileInfo = document.getElementById('file-info');
    const fileName = document.getElementById('file-name');
    const uploadBtn = document.getElementById('upload-btn');
    const uploadBtnText = document.getElementById('upload-btn-text');

    // Check if key elements exist
    if (!fileInput || !fileDropZone || !uploadBtn || !uploadBtnText) {
        console.error('Key DOM elements not found', {
            fileInput: !!fileInput,
            fileDropZone: !!fileDropZone,
            uploadBtn: !!uploadBtn,
            uploadBtnText: !!uploadBtnText
        });
        return;
    }

    console.log('All DOM elements found:', {
        uploadBtn: uploadBtn,
        uploadBtnText: uploadBtnText,
        uploadBtnVisible: window.getComputedStyle(uploadBtn).display,
        uploadBtnOpacity: window.getComputedStyle(uploadBtn).opacity
    });

    let currentTaskId = null;
    let progressInterval = null;

    // Check CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        console.error('CSRF token not found');
        showNotification('error', 'Page Error', 'Please refresh the page and try again');
        return;
    }

    // Initialize button state
    disableUploadButton('Please select a file first');
    console.log('Button initialization complete, current state:', {
        disabled: uploadBtn.disabled,
        className: uploadBtn.className,
        text: uploadBtnText.textContent
    });

    console.log('Initialization complete, setting up event listeners...');

    // File selection handling
    fileDropZone.addEventListener('click', function() {
        console.log('Click file selection area');
        fileInput.click();
    });

    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        console.log('File selection changed:', file ? file.name : 'No file');

        if (file) {
            // Validate file type
            const allowedTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel', 'text/csv'];
            const allowedExtensions = ['.xlsx', '.xls', '.csv'];

            if (!allowedTypes.includes(file.type) && !allowedExtensions.some(ext => file.name.toLowerCase().endsWith(ext))) {
                showNotification('error', 'File Format Error', 'Please select Excel files (.xlsx, .xls) or CSV files');
                resetFileSelection();
                return;
            }

            // Validate file size (10MB)
            if (file.size > 10 * 1024 * 1024) {
                showNotification('error', 'File Too Large', 'File size cannot exceed 10MB');
                resetFileSelection();
                return;
            }

            // Display file information
            fileName.textContent = file.name;
            fileInfo.classList.remove('hidden');

            // Enable upload button
            enableUploadButton();
            updateDebugPanel();
            console.log('File validation passed, button enabled');
        } else {
            resetFileSelection();
            updateDebugPanel();
        }
    });

    // Helper functions
    function enableUploadButton() {
        console.log('Enable upload button');
        uploadBtn.disabled = false;
        uploadBtn.className = 'w-full max-w-md px-6 py-3 font-semibold rounded-lg shadow-lg border-2 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2';
        uploadBtnText.textContent = 'Start Upload and Update';

        // Force re-render
        uploadBtn.style.display = 'block';
        uploadBtn.style.visibility = 'visible';
    }

    function disableUploadButton(text = 'Please select a file first') {
        console.log('Disable upload button:', text);
        uploadBtn.disabled = true;
        uploadBtn.className = 'w-full max-w-md px-6 py-3 font-semibold rounded-lg shadow-lg border-2 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2';
        uploadBtnText.textContent = text;

        // Force re-render
        uploadBtn.style.display = 'block';
        uploadBtn.style.visibility = 'visible';
    }

    function resetFileSelection() {
        fileInput.value = '';
        fileInfo.classList.add('hidden');
        disableUploadButton();
        updateDebugPanel();
    }

    // Drag and drop upload
    fileDropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        fileDropZone.classList.add('border-blue-500', 'bg-blue-50');
    });

    fileDropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        fileDropZone.classList.remove('border-blue-500', 'bg-blue-50');
    });

    fileDropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        fileDropZone.classList.remove('border-blue-500', 'bg-blue-50');

        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            const event = new Event('change');
            fileInput.dispatchEvent(event);
        }
    });

    // File upload handling
    uploadBtn.addEventListener('click', function() {
        console.log('Click upload button');

        if (!fileInput.files[0]) {
            showNotification('error', 'Please Select File', 'Please select an Excel or CSV file first');
            return;
        }

        const formData = new FormData();
        formData.append('excel_file', fileInput.files[0]);

        // Update button state
        disableUploadButton('Uploading...');
        console.log('Starting file upload...');

        fetch('/bulk-update/upload', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    try {
                        const errorData = JSON.parse(text);
                        throw new Error(errorData.message || `HTTP error! Status: ${response.status}`);
                    } catch (e) {
                        throw new Error(`Server error (${response.status}): ${text.substring(0, 100)}...`);
                    }
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Upload response:', data);
            if (data.success) {
                currentTaskId = data.task_id;

                // Show start processing notification
                showNotification('info', 'Start Processing', `Uploaded ${data.total_items} products, starting update...`);

                // Hide upload area, show progress area
                document.getElementById('upload-section').classList.add('hidden');
                document.getElementById('progress-section').classList.remove('hidden');

                // Initialize progress display
                updateProgressDisplay({
                    status: 'pending',
                    progress_percentage: 0,
                    total_items: data.total_items,
                    processed_items: 0,
                    successful_items: 0,
                    failed_items: 0
                });

                // Auto execute task
                executeTaskAutomatically();
            } else {
                showNotification('error', 'Upload Failed', data.message || 'Unknown error');
            }
        })
        .catch(error => {
            console.error('Upload error:', error);
            if (error.message.includes('403')) {
                showNotification('error', 'Authorization Failed', 'No Lazada authorization. Please authorize Lazada in the settings page first.');
            } else if (error.message.includes('422')) {
                showNotification('error', 'File Error', 'File format or size does not meet requirements.');
            } else if (error.message.includes('500')) {
                showNotification('error', 'Server Error', 'Server error, please try again later.');
            } else {
                showNotification('error', 'Upload Failed', error.message);
            }
        })
        .finally(() => {
            enableUploadButton();
        });
    });



    // Completely rewritten notification system
    function showNotification(type, title, message, actions = []) {
        console.log('🔔 Show notification:', type, title, message);

        const container = document.getElementById('notification-container');
        if (!container) {
            console.error('❌ Notification container not found');
            alert(`Notification: ${title} - ${message}`); // Fallback solution
            return;
        }

        const notificationId = 'notification-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
        const notification = document.createElement('div');
        notification.id = notificationId;
        notification.className = 'notification-item';

        // Force set styles
        notification.style.cssText = `
            pointer-events: auto !important;
            background: white !important;
            border: 2px solid #d1d5db !important;
            border-radius: 12px !important;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25) !important;
            padding: 20px !important;
            width: 100% !important;
            max-width: 400px !important;
            position: relative !important;
            z-index: 999999 !important;
            transform: translateX(100%) !important;
            opacity: 0 !important;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
            margin-bottom: 16px !important;
            backdrop-filter: blur(10px) !important;
            color: #1f2937 !important;
        `;

        const iconColors = {
            success: 'text-green-700 bg-green-200',
            error: 'text-red-700 bg-red-200',
            info: 'text-blue-700 bg-blue-200'
        };

        const icons = {
            success: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>',
            error: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>',
            info: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'
        };

        let actionsHtml = '';
        if (actions.length > 0) {
            actionsHtml = '<div class="mt-3 flex space-x-2">';
            actions.forEach((action, index) => {
                actionsHtml += `<button data-action="${action.action}" class="${action.className}">${action.text}</button>`;
            });
            actionsHtml += '</div>';
        }

        notification.innerHTML = `
            <div class="notification-content flex items-start" style="color: #1f2937 !important; position: relative;">
                <div class="w-8 h-8 rounded-full flex items-center justify-center ${iconColors[type]} mr-3 flex-shrink-0" style="margin-top: 2px;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="stroke-width: 2.5;">
                        ${icons[type]}
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h4 style="color: #111827 !important; font-size: 16px !important; font-weight: bold !important; margin-bottom: 4px !important;">${title}</h4>
                    <p style="color: #374151 !important; font-size: 14px !important; line-height: 1.5 !important;">${message}</p>
                    ${actionsHtml}
                </div>

            </div>
        `;

        // Add to container
        container.appendChild(notification);
        console.log('✅ Notification added to container:', notification);
        console.log('📍 Container position:', container.getBoundingClientRect());
        console.log('📍 Notification position:', notification.getBoundingClientRect());

        // Notification will automatically start fading out after 6 seconds
        setTimeout(() => {
            hideNotification(notificationId);
        }, 6000);



        // Add action button events
        const actionButtons = notification.querySelectorAll('[data-action]');
        actionButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                const action = btn.getAttribute('data-action');
                console.log('🔵 Action button clicked:', action);
                if (action === 'download') {
                    downloadReport(currentTaskId);
                } else if (action === 'new-task') {
                    startNewTask();
                }
                hideNotification(notificationId);
            });
        });

        // Force show animation - execute immediately
        console.log('🎬 Starting show animation');
        setTimeout(() => {
            notification.style.transform = 'translateX(0) !important';
            notification.style.opacity = '1 !important';
            notification.style.animation = 'slideInRight 0.4s ease-out forwards !important';
            notification.classList.add('show');


            console.log('✨ Animation triggered');
        }, 50);

        // Auto disappear (unless there are action buttons)
        if (actions.length === 0) {
            setTimeout(() => {
                hideNotification(notificationId);
            }, 5000);
        }

        return notificationId;
    }

    function hideNotification(notificationId) {
        console.log('🌅 Starting elegant fade out notification:', notificationId);
        const notification = document.getElementById(notificationId);
        if (notification) {
            // Add fade-out CSS class to trigger animation
            notification.classList.add('hide');

            console.log('🎭 Fade out animation started, elegant removal in 2 seconds');
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                    console.log('✨ Notification elegantly removed:', notificationId);
                }
            }, 2000); // 2 seconds fade out time, synchronized with CSS animation
        }
    }

    // Update circular progress bar
    function updateCircularProgress(percentage) {
        const circle = document.getElementById('progress-circle');
        const circumference = 2 * Math.PI * 35; // r = 35
        const offset = circumference - (percentage / 100) * circumference;
        circle.style.strokeDashoffset = offset;
        
        document.getElementById('progress-percentage').textContent = percentage + '%';
    }

    // Update progress display function
    function updateProgressDisplay(task) {
        const totalCount = document.getElementById('total-count');
        const successCount = document.getElementById('success-count');
        const failedCount = document.getElementById('failed-count');
        const statusMessage = document.getElementById('status-message');
        const statusDetail = document.getElementById('status-detail');

        const percentage = task.progress_percentage || 0;
        updateCircularProgress(percentage);
        
        totalCount.textContent = task.total_items;
        successCount.textContent = task.successful_items;
        failedCount.textContent = task.failed_items;

        let status = '';
        let detail = '';
        
        switch (task.status) {
            case 'pending':
                status = 'Ready to start processing';
                detail = 'System is initializing processing workflow';
                break;
            case 'processing':
                status = `Processing.... (${task.processed_items}/${task.total_items})`;
                detail = `Completed ${task.successful_items} items, failed ${task.failed_items} items`;
                break;
            case 'completed':
                status = 'Update completed!';
                detail = `Successfully processed ${task.successful_items} products, failed ${task.failed_items} items`;
                break;
            case 'failed':
                status = 'Update failed';
                detail = 'Encountered errors during processing, please retry';
                break;
        }
        
        statusMessage.textContent = status;
        statusDetail.textContent = detail;
    }

    // Compatible with old function names
    function createNotification(type, title, message, actions = []) {
        return showNotification(type, title, message, actions);
    }

    // Show success notification
    function showSuccessNotification(task) {
        console.log('Show success notification:', task);

        const message = `🎉 Successfully processed ${task.successful_items} products${task.failed_items > 0 ? `, failed ${task.failed_items} items` : ''}`;

        const actions = [
            {
                text: '📥 Download Report',
                className: 'bg-blue-600 hover:bg-blue-700 text-white text-sm py-2 px-4 rounded-lg font-medium transition-colors shadow-md',
                action: 'download'
            },
            {
                text: '🔄 New Task',
                className: 'bg-green-600 hover:bg-green-700 text-white text-sm py-2 px-4 rounded-lg font-medium transition-colors shadow-md',
                action: 'new-task'
            }
        ];

        // Show large success notification
        showLargeSuccessNotification('✅ Bulk update completed!', message, actions);
    }

    // Large success notification
    function showLargeSuccessNotification(title, message, actions = []) {
        console.log('Show large success notification');

        const container = document.getElementById('notification-container');
        if (!container) {
            console.error('Notification container not found');
            return;
        }

        const notificationId = 'large-success-' + Date.now();
        const notification = document.createElement('div');
        notification.id = notificationId;
        notification.className = 'notification-item bg-gradient-to-r from-green-500 to-blue-600 border-0 rounded-xl shadow-2xl p-6 w-96 mb-4 text-white';

        // Ensure initial state
        notification.style.transform = 'translateX(100%)';
        notification.style.opacity = '0';
        notification.style.position = 'relative';
        notification.style.zIndex = '10001';
        notification.style.pointerEvents = 'auto';

        let actionsHtml = '';
        if (actions.length > 0) {
            actionsHtml = '<div class="mt-4 flex space-x-3 justify-center">';
            actions.forEach((action) => {
                actionsHtml += `<button data-action="${action.action}" class="${action.className}">${action.text}</button>`;
            });
            actionsHtml += '</div>';
        }

        notification.innerHTML = `
            <div class="text-center" style="position: relative;">
                <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="stroke-width: 3;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold mb-2">${title}</h3>
                <p class="text-white text-opacity-90 mb-2">${message}</p>
                ${actionsHtml}

            </div>
        `;

        notification.style.position = 'relative';

        // Add to container
        container.appendChild(notification);
        console.log('Large success notification added to container:', notification);

        // Large notification will start fading out after 8 seconds
        setTimeout(() => {
            hideNotification(notificationId);
        }, 8000);

        // Add action button events
        const actionButtons = notification.querySelectorAll('[data-action]');
        actionButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                const action = btn.getAttribute('data-action');
                if (action === 'download') {
                    downloadReport(currentTaskId);
                } else if (action === 'new-task') {
                    startNewTask();
                }
                hideNotification(notificationId);
            });
        });

        // Force show animation
        setTimeout(() => {
            console.log('Starting large success notification animation');
            notification.style.transform = 'translateX(0)';
            notification.style.opacity = '1';
            notification.classList.add('show');

        }, 50);

        // Auto disappear after 10 seconds
        setTimeout(() => {
            hideNotification(notificationId);
        }, 10000);

        return notificationId;
    }

    // Auto execute task function
    function executeTaskAutomatically() {
        fetch('/bulk-update/execute', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ task_id: currentTaskId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                startProgressMonitoring();
            } else {
                showNotification('error', 'Startup Failed', data.message);
                document.getElementById('progress-section').classList.add('hidden');
                document.getElementById('upload-section').classList.remove('hidden');
            }
        })
        .catch(error => {
            console.error('Startup error:', error);
            showNotification('error', 'Startup Failed', 'Task startup failed, please retry');
            document.getElementById('progress-section').classList.add('hidden');
            document.getElementById('upload-section').classList.remove('hidden');
        });
    }

    // Monitor progress
    function startProgressMonitoring() {
        progressInterval = setInterval(updateProgress, 2000); // Update every 2 seconds
        updateProgress(); // Update immediately once
    }

    function updateProgress() {
        fetch(`/bulk-update/status?task_id=${currentTaskId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const task = data.task;
                updateProgressDisplay(task);
                
                if (task.status === 'completed') {
                    clearInterval(progressInterval);
                    showSuccessNotification(task);
                    
                    // Return to upload page after a few seconds
                    setTimeout(() => {
                        document.getElementById('progress-section').classList.add('hidden');
                        document.getElementById('upload-section').classList.remove('hidden');
                        // Reset file selection
                        document.getElementById('excel-file').value = '';
                        document.getElementById('file-info').classList.add('hidden');
                        document.getElementById('upload-btn').disabled = true;
                    }, 3000);
                } else if (task.status === 'failed') {
                    clearInterval(progressInterval);
                    showNotification('error', 'Update Failed', task.error_message || 'Encountered errors during processing, please retry');

                    // Return to upload page
                    setTimeout(() => {
                        document.getElementById('progress-section').classList.add('hidden');
                        document.getElementById('upload-section').classList.remove('hidden');
                    }, 2000);
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    // Global functions for notification use
    function downloadReport(taskId) {
        console.log('Download report:', taskId);
        window.location.href = `/bulk-update/download-report?task_id=${taskId}`;
    }

    function startNewTask() {
        console.log('Start new task');
        document.getElementById('progress-section').classList.add('hidden');
        document.getElementById('upload-section').classList.remove('hidden');
        resetFileSelection();
    }

    // Debug functions
    function updateDebugPanel() {
        const debugBtnStatus = document.getElementById('debug-btn-status');
        const debugBtnVisibility = document.getElementById('debug-btn-visibility');
        const debugFileStatus = document.getElementById('debug-file-status');

        if (debugBtnStatus) {
            debugBtnStatus.textContent = uploadBtn.disabled ? 'Disabled' : 'Enabled';
        }

        if (debugBtnVisibility) {
            const style = window.getComputedStyle(uploadBtn);
            debugBtnVisibility.textContent = `display: ${style.display}, opacity: ${style.opacity}, visibility: ${style.visibility}`;
        }

        if (debugFileStatus) {
            debugFileStatus.textContent = fileInput.files.length > 0 ? `Selected: ${fileInput.files[0].name}` : 'Not selected';
        }
    }

    function debugButtonStatus() {
        console.log('=== Button Debug Information ===');
        console.log('Button element:', uploadBtn);
        console.log('Button disabled state:', uploadBtn.disabled);
        console.log('Button class name:', uploadBtn.className);
        console.log('Button styles:', window.getComputedStyle(uploadBtn));
        console.log('Button text:', uploadBtnText.textContent);
        updateDebugPanel();
    }

    // Test functions
    function testNotification() {
        console.log('Test notification display');
        const container = document.getElementById('notification-container');
        console.log('Notification container:', container);
        console.log('Container styles:', window.getComputedStyle(container));
        showNotification('success', 'Test Notification', 'This is a test notification to verify if the notification system is working properly');
    }

    function testSuccessNotification() {
        console.log('Test success notification');
        const mockTask = {
            successful_items: 5,
            failed_items: 1
        };
        showSuccessNotification(mockTask);
    }

    function testSimpleNotification() {
        console.log('🧪 Test simple notification');
        const container = document.getElementById('notification-container');
        if (!container) {
            console.error('❌ Notification container does not exist!');
            alert('Notification container does not exist!');
            return;
        }

        console.log('📦 Container info:', {
            element: container,
            style: window.getComputedStyle(container),
            position: container.getBoundingClientRect()
        });

        // Create a very obvious test notification
        const testNotification = document.createElement('div');
        testNotification.style.cssText = `
            position: fixed !important;
            top: 50% !important;
            left: 50% !important;
            transform: translate(-50%, -50%) !important;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            color: white !important;
            padding: 30px !important;
            border-radius: 15px !important;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3) !important;
            z-index: 9999999 !important;
            font-size: 18px !important;
            font-weight: bold !important;
            text-align: center !important;
            min-width: 300px !important;
            animation: pulse 2s infinite !important;
        `;
        testNotification.innerHTML = `
            <div>🔔 Test notification display successful!</div>
            <div style="font-size: 14px; margin-top: 10px; opacity: 0.9;">
                If you can see this notification, the notification system is working properly
            </div>
            <button onclick="this.parentElement.remove()" style="
                background: rgba(255,255,255,0.2);
                border: 1px solid rgba(255,255,255,0.3);
                color: white;
                padding: 8px 16px;
                border-radius: 8px;
                margin-top: 15px;
                cursor: pointer;
                font-size: 14px;
            ">Close</button>
        `;

        document.body.appendChild(testNotification);

        // Auto remove after 3 seconds
        setTimeout(() => {
            if (testNotification.parentElement) {
                testNotification.remove();
            }
        }, 5000);

        const testDiv = document.createElement('div');
        testDiv.innerHTML = `
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <span>🔴 Simple test notification - ${new Date().toLocaleTimeString()}</span>
                <button onclick="this.parentElement.parentElement.remove()" style="background: none; border: none; color: white; font-size: 18px; cursor: pointer;">×</button>
            </div>
        `;
        testDiv.style.cssText = `
            background: linear-gradient(135deg, #ff6b6b, #ee5a24) !important;
            color: white !important;
            padding: 16px !important;
            border-radius: 12px !important;
            margin-bottom: 12px !important;
            position: relative !important;
            z-index: 100000 !important;
            pointer-events: auto !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
            transform: translateX(0) !important;
            opacity: 1 !important;
            width: 100% !important;
            max-width: 380px !important;
        `;

        container.appendChild(testDiv);
        console.log('✅ Simple test notification added');

        setTimeout(() => {
            if (testDiv.parentNode) {
                testDiv.parentNode.removeChild(testDiv);
                console.log('🗑️ Simple test notification removed');
            }
        }, 5000);
    }

    // Expose functions to global scope
    window.downloadReport = downloadReport;
    window.startNewTask = startNewTask;
    window.showNotification = showNotification;
    window.hideNotification = hideNotification;
    window.testNotification = testNotification;
    window.testSuccessNotification = testSuccessNotification;
    window.testSimpleNotification = testSimpleNotification;
    window.debugButtonStatus = debugButtonStatus;
    window.updateDebugPanel = updateDebugPanel;

    // Initialize debug panel
    setTimeout(() => {
        updateDebugPanel();
        debugButtonStatus();
    }, 500);

    // Show welcome notification
    setTimeout(() => {
        console.log('Show welcome notification');
        showNotification('info', 'Page Load Complete', 'Bulk update functionality is ready! Button should be visible above.');
    }, 1000);

    console.log('Bulk update functionality initialization complete');
});
</script>
@endpush