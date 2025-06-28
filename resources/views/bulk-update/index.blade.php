@extends('layouts.app')

@section('title', 'Bulk Update Product Titles')

@section('content')
<style>
/* Custom styles for bulk update page */
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
            <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-blue-400 hover:bg-blue-50 transition-all duration-200 mb-6 relative">
                <input type="file" id="excel-file" accept=".xlsx,.xls,.csv" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 10;">
                <div id="file-drop-zone" class="cursor-pointer relative pointer-events-none">
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


    </div>
</div>

<!-- Notification system will be handled by app.js -->
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
/* Remove custom notification container styles - will use app.js unified system */

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



    let currentTaskId = null;
    let progressInterval = null;

    // Check CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        console.error('CSRF token not found');
        GlobalNotification.error('Page Error', 'Please refresh the page and try again');
        return;
    }

    // Initialize button state
    disableUploadButton('Please select a file first');

    // File selection handling - file input is now directly clickable
    // No need for click handler since file input covers the drop zone

    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];

        if (file) {
            // Validate file type
            const allowedTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel', 'text/csv'];
            const allowedExtensions = ['.xlsx', '.xls', '.csv'];

            if (!allowedTypes.includes(file.type) && !allowedExtensions.some(ext => file.name.toLowerCase().endsWith(ext))) {
                GlobalNotification.error('File Format Error', 'Please select Excel files (.xlsx, .xls) or CSV files');
                resetFileSelection();
                return;
            }

            // Validate file size (10MB)
            if (file.size > 10 * 1024 * 1024) {
                GlobalNotification.error('File Too Large', 'File size cannot exceed 10MB');
                resetFileSelection();
                return;
            }

            // Display file information
            fileName.textContent = file.name;
            fileInfo.classList.remove('hidden');

            // Enable upload button
            enableUploadButton();
        } else {
            resetFileSelection();
        }
    });

    // Helper functions
    function enableUploadButton() {
        uploadBtn.disabled = false;
        uploadBtn.className = 'w-full max-w-md px-6 py-3 font-semibold rounded-lg shadow-lg border-2 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2';
        uploadBtnText.textContent = 'Start Upload and Update';

        // Force re-render
        uploadBtn.style.display = 'block';
        uploadBtn.style.visibility = 'visible';
    }

    function disableUploadButton(text = 'Please select a file first') {
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
    uploadBtn.addEventListener('click', async function() {
        if (!fileInput.files[0]) {
            GlobalNotification.error('Please Select File', 'Please select an Excel or CSV file first');
            return;
        }

        // Update button state
        disableUploadButton('Uploading...');

        // Show loading
        GlobalLoading.show('upload');

        // Prepare form data
        const formData = new FormData();
        formData.append('excel_file', fileInput.files[0]);

        // Make API call
        const result = await GlobalAPI.post('/bulk-update/upload', formData);

        // Hide loading
        GlobalLoading.hide();

        if (result.success && result.data.success) {
            currentTaskId = result.data.task_id;

            // Debug: Log upload response
            console.log('Upload response:', result.data);
            console.log('Task ID assigned:', currentTaskId);

            // Show success notification
            GlobalNotification.success('Upload Complete', result.data.message);

            // Show start processing notification
            GlobalNotification.info('Start Processing', `Uploaded ${result.data.total_items} products, starting update...`);

            // Hide upload area, show progress area
            document.getElementById('upload-section').classList.add('hidden');
            document.getElementById('progress-section').classList.remove('hidden');

            // Initialize progress display
            updateProgressDisplay({
                status: 'pending',
                progress_percentage: 0,
                total_items: result.data.total_items,
                processed_items: 0,
                successful_items: 0,
                failed_items: 0
            });

            // Auto execute task with a small delay to ensure database transaction is complete
            setTimeout(() => {
                executeTaskAutomatically();
            }, 1000); // 1 second delay
        } else {
            const errorMessage = result.data?.message || result.error || 'Upload failed';
            GlobalNotification.error('Upload Failed', errorMessage);
        }

        // Restore button state
        enableUploadButton();
    });



    // All notifications now use GlobalNotification directly
    // No wrapper function needed

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

    // Show success notification using GlobalNotification
    function showSuccessNotification(task) {
        const message = `Successfully processed ${task.successful_items} products${task.failed_items > 0 ? `, failed ${task.failed_items} items` : ''}`;

        // Use GlobalNotification directly
        GlobalNotification.success('Bulk Update Completed', message);
    }

    // Auto execute task function
    async function executeTaskAutomatically() {
        try {
            // Check CSRF token before making request
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken) {
                GlobalNotification.error('Page Error', 'Please refresh the page and try again');
                return;
            }

            // Debug: Log the task ID being sent
            console.log('Executing task with ID:', currentTaskId);
            console.log('Task ID type:', typeof currentTaskId);

            // First, let's check if the task exists by checking its status
            console.log('Checking task status first...');
            const statusCheck = await GlobalAPI.get(`/bulk-update/status?task_id=${currentTaskId}`);
            console.log('Status check result:', statusCheck);

            const result = await GlobalAPI.post('/bulk-update/execute', { task_id: currentTaskId });

            if (result.success && result.data.success) {
                startProgressMonitoring();
            } else {
                const errorMessage = result.data?.message || result.error || 'Task startup failed, please retry';
                GlobalNotification.error('Startup Failed', errorMessage);
                document.getElementById('progress-section').classList.add('hidden');
                document.getElementById('upload-section').classList.remove('hidden');
            }
        } catch (error) {
            console.error('Execute task error:', error);
            console.error('Current task ID:', currentTaskId);
            GlobalNotification.error('Execution Error', 'Failed to start task. Please refresh the page and try again.');
            document.getElementById('progress-section').classList.add('hidden');
            document.getElementById('upload-section').classList.remove('hidden');
        }
    }

    // Monitor progress
    function startProgressMonitoring() {
        progressInterval = setInterval(updateProgress, 2000); // Update every 2 seconds
        updateProgress(); // Update immediately once
    }

    async function updateProgress() {
        const result = await GlobalAPI.get(`/bulk-update/status?task_id=${currentTaskId}`);

        if (result.success && result.data.success) {
            const task = result.data.task;
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
                GlobalNotification.error('Update Failed', task.error_message || 'Encountered errors during processing, please retry');

                // Return to upload page
                setTimeout(() => {
                    document.getElementById('progress-section').classList.add('hidden');
                    document.getElementById('upload-section').classList.remove('hidden');
                }, 2000);
            }
        } else {
            console.error('Progress check error:', result.error);
        }
    }

    // Global functions for notification use
    function downloadReport(taskId) {
        window.location.href = `/bulk-update/download-report?task_id=${taskId}`;
    }

    function startNewTask() {
        document.getElementById('progress-section').classList.add('hidden');
        document.getElementById('upload-section').classList.remove('hidden');
        resetFileSelection();
    }





    // Expose functions to global scope
    window.downloadReport = downloadReport;
    window.startNewTask = startNewTask;
});
</script>
@endpush