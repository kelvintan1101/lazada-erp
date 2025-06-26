@extends('layouts.app')

@section('title', '批量更新产品标题')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-2xl mx-auto">
        
        <!-- 文件上传区域 -->
        <div id="upload-section" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="text-center mb-6">
                <h2 class="text-xl font-bold text-gray-900 mb-2">批量更新产品标题</h2>
                <p class="text-sm text-gray-600">上传包含SKU和产品标题的Excel/CSV文件</p>
            </div>
            
            <!-- 简化的格式提示 -->
            <div class="bg-blue-50 rounded-lg p-3 mb-6 flex items-center justify-between">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm text-blue-700">需要包含SKU和产品标题列</span>
                </div>
                <a href="/templates/product_title_update_template.csv"
                   download="产品标题更新模板.csv"
                   class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                    下载模板
                </a>
            </div>

            <!-- 简化的文件选择区域 -->
            <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-blue-400 hover:bg-blue-50 transition-all duration-200">
                <input type="file" id="excel-file" accept=".xlsx,.xls,.csv" class="hidden">
                <div id="file-drop-zone" class="cursor-pointer">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    <p class="text-lg font-semibold text-gray-700 mb-1">选择文件或拖拽到这里</p>
                    <p class="text-sm text-gray-500">支持 .xlsx, .xls, .csv 格式</p>
                </div>
                
                <!-- 文件信息显示 -->
                <div id="file-info" class="hidden mt-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-sm text-green-700">
                            已选择：<span id="file-name" class="font-medium"></span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- 上传按钮 -->
            <button id="upload-btn" class="mt-6 w-full bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white py-3 px-6 rounded-xl font-semibold transition-all duration-200 shadow-sm">
                <span id="upload-btn-text">开始上传并更新</span>
            </button>
        </div>



        <!-- 进度显示区域 -->
        <div id="progress-section" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hidden">
            <!-- 圆形进度条 -->
            <div class="flex justify-center mb-6">
                <div class="relative w-24 h-24">
                    <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                        <!-- 背景圆环 -->
                        <circle
                            cx="50"
                            cy="50"
                            r="35"
                            stroke="#e5e7eb"
                            stroke-width="6"
                            fill="none"
                        />
                        <!-- 进度圆环 -->
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
                        <!-- 渐变定义 -->
                        <defs>
                            <linearGradient id="progressGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="0%" style="stop-color:#3b82f6;stop-opacity:1" />
                                <stop offset="100%" style="stop-color:#8b5cf6;stop-opacity:1" />
                            </linearGradient>
                        </defs>
                    </svg>
                    <!-- 百分比显示 -->
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="text-center">
                            <div id="progress-percentage" class="text-lg font-bold text-gray-800 leading-none">0%</div>
                            <div class="text-xs text-gray-500 mt-1">完成</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 状态信息 -->
            <div class="text-center mb-6">
                <h3 id="status-message" class="text-lg font-semibold text-gray-800 mb-1">准备开始...</h3>
                <p id="status-detail" class="text-sm text-gray-600">正在初始化处理流程</p>
            </div>

            <!-- 统计信息 -->
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="text-center">
                    <div class="bg-blue-50 rounded-lg p-3">
                        <p class="text-2xl font-bold text-blue-600" id="total-count">0</p>
                        <p class="text-xs text-gray-600">总数量</p>
                    </div>
                </div>
                <div class="text-center">
                    <div class="bg-green-50 rounded-lg p-3">
                        <p class="text-2xl font-bold text-green-600" id="success-count">0</p>
                        <p class="text-xs text-gray-600">成功</p>
                    </div>
                </div>
                <div class="text-center">
                    <div class="bg-red-50 rounded-lg p-3">
                        <p class="text-2xl font-bold text-red-600" id="failed-count">0</p>
                        <p class="text-xs text-gray-600">失败</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 通知系统 -->
<div id="notification-container" class="fixed top-4 right-4 z-50 space-y-3 pointer-events-none">
    <!-- 通知将在这里动态创建 -->
</div>
@endsection

@push('styles')
<style>
#notification-container {
    max-height: calc(100vh - 2rem);
    overflow: visible;
}

.notification-item {
    margin-bottom: 0.75rem !important;
}

.notification-item:last-child {
    margin-bottom: 0 !important;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('excel-file');
    const fileDropZone = document.getElementById('file-drop-zone');
    const fileInfo = document.getElementById('file-info');
    const fileName = document.getElementById('file-name');
    const uploadBtn = document.getElementById('upload-btn');

    
    let currentTaskId = null;
    let progressInterval = null;

    // 检查CSRF token是否存在
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        console.error('CSRF token not found');
        alert('页面加载错误，请刷新页面重试');
        return;
    }

    // 文件选择处理
    fileDropZone.addEventListener('click', () => fileInput.click());
    
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // 验证文件类型
            const allowedTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 
                                'application/vnd.ms-excel', 'text/csv'];
            const allowedExtensions = ['.xlsx', '.xls', '.csv'];
            
            if (!allowedTypes.includes(file.type) && !allowedExtensions.some(ext => file.name.toLowerCase().endsWith(ext))) {
                createNotification('error', '文件格式错误', '请选择Excel文件（.xlsx, .xls）或CSV文件');
                return;
            }
            
            // 验证文件大小 (10MB)
            if (file.size > 10 * 1024 * 1024) {
                createNotification('error', '文件过大', '文件大小不能超过10MB');
                return;
            }
            
            fileName.textContent = file.name;
            fileInfo.classList.remove('hidden');
            
            // 启用上传按钮
            uploadBtn.disabled = false;
            uploadBtn.classList.remove('disabled:bg-gray-300');
            uploadBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');
        }
    });

    // 拖拽上传
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

    // 上传文件
    uploadBtn.addEventListener('click', function() {
        if (!fileInput.files[0]) {
            createNotification('error', '请选择文件', '请先选择一个Excel或CSV文件');
            return;
        }

        const formData = new FormData();
        formData.append('excel_file', fileInput.files[0]);

        // 更新按钮状态
        const uploadBtnText = document.getElementById('upload-btn-text');
        uploadBtn.disabled = true;
        uploadBtnText.textContent = '上传中...';
        uploadBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
        uploadBtn.classList.add('bg-gray-400');

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
                        throw new Error(errorData.message || `HTTP错误! 状态: ${response.status}`);
                    } catch (e) {
                        throw new Error(`服务器错误 (${response.status}): ${text.substring(0, 100)}...`);
                    }
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                currentTaskId = data.task_id;
                
                // 显示开始处理通知
                createNotification('info', '开始处理', `已上传 ${data.total_items} 个产品，正在开始更新...`);
                
                // 隐藏上传区域，显示进度区域
                document.getElementById('upload-section').classList.add('hidden');
                document.getElementById('progress-section').classList.remove('hidden');
                
                // 初始化进度显示
                updateProgressDisplay({
                    status: 'pending',
                    progress_percentage: 0,
                    total_items: data.total_items,
                    processed_items: 0,
                    successful_items: 0,
                    failed_items: 0
                });
                
                // 自动执行任务
                executeTaskAutomatically();
            } else {
                createNotification('error', '上传失败', data.message || '未知错误');
            }
        })
        .catch(error => {
            console.error('上传错误:', error);
            if (error.message.includes('403')) {
                createNotification('error', '授权失败', '没有Lazada授权。请先在设置页面进行Lazada授权。');
            } else if (error.message.includes('422')) {
                createNotification('error', '文件错误', '文件格式或大小不符合要求。');
            } else if (error.message.includes('500')) {
                createNotification('error', '服务器错误', '服务器错误，请稍后重试。');
            } else {
                createNotification('error', '上传失败', error.message);
            }
        })
        .finally(() => {
            uploadBtn.disabled = false;
            uploadBtnText.textContent = '开始上传并更新';
            uploadBtn.classList.remove('bg-gray-400');
            uploadBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');
        });
    });

    // 更新圆形进度条
    function updateCircularProgress(percentage) {
        const circle = document.getElementById('progress-circle');
        const circumference = 2 * Math.PI * 35; // r = 35
        const offset = circumference - (percentage / 100) * circumference;
        circle.style.strokeDashoffset = offset;
        
        document.getElementById('progress-percentage').textContent = percentage + '%';
    }

    // 更新进度显示函数
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
                status = '准备开始处理';
                detail = '系统正在初始化处理流程';
                break;
            case 'processing':
                status = `正在处理中... (${task.processed_items}/${task.total_items})`;
                detail = `已完成 ${task.successful_items} 个，失败 ${task.failed_items} 个`;
                break;
            case 'completed':
                status = '更新完成！';
                detail = `成功处理 ${task.successful_items} 个产品，失败 ${task.failed_items} 个`;
                break;
            case 'failed':
                status = '更新失败';
                detail = '处理过程中遇到错误，请重试';
                break;
        }
        
        statusMessage.textContent = status;
        statusDetail.textContent = detail;
    }

    // 创建通知
    function createNotification(type, title, message, actions = []) {
        const container = document.getElementById('notification-container');
        const notificationId = 'notification-' + Date.now();
        
        const notification = document.createElement('div');
        notification.id = notificationId;
        notification.className = 'notification-item bg-white border border-gray-200 rounded-lg shadow-lg p-4 w-80 transform transition-all duration-300 translate-x-full opacity-0 pointer-events-auto';
        
        const iconColors = {
            success: 'text-green-600 bg-green-100',
            error: 'text-red-600 bg-red-100',
            info: 'text-blue-600 bg-blue-100'
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
            <div class="flex items-start">
                <div class="w-8 h-8 rounded-full flex items-center justify-center ${iconColors[type]} mr-3 flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        ${icons[type]}
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="text-sm font-semibold text-gray-900">${title}</h4>
                    <p class="text-sm text-gray-600 mt-1">${message}</p>
                    ${actionsHtml}
                </div>
                <button class="close-btn ml-2 text-gray-400 hover:text-gray-600 flex-shrink-0 p-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        `;
        
        // 先添加到容器
        container.appendChild(notification);
        
        // 添加关闭按钮事件监听器
        const closeBtn = notification.querySelector('.close-btn');
        closeBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            closeNotification(notificationId);
        });
        
        // 添加操作按钮事件监听器
        const actionButtons = notification.querySelectorAll('[data-action]');
        actionButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const action = btn.getAttribute('data-action');
                if (action === 'download') {
                    window.downloadReport(currentTaskId);
                } else if (action === 'new-task') {
                    window.startNewTask();
                }
                closeNotification(notificationId);
            });
        });
        
        // 动画显示
        setTimeout(() => {
            notification.classList.remove('translate-x-full', 'opacity-0');
        }, 100);
        
        // 自动消失（除非有操作按钮）
        if (actions.length === 0) {
            const autoCloseTimer = setTimeout(() => {
                closeNotification(notificationId);
            }, 5000);
            
            // 鼠标悬停时暂停自动关闭
            notification.addEventListener('mouseenter', () => {
                clearTimeout(autoCloseTimer);
            });
        }
        
        return notificationId;
    }

    // 关闭通知
    function closeNotification(notificationId) {
        const notification = document.getElementById(notificationId);
        if (notification && notification.parentNode) {
            notification.classList.add('translate-x-full', 'opacity-0');
            setTimeout(() => {
                try {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                } catch (e) {
                    console.log('通知已被移除');
                }
            }, 300);
        }
    }
    
    // 确保函数在全局作用域
    window.closeNotification = closeNotification;

    // 显示成功通知
    function showSuccessNotification(task) {
        const message = `成功处理 ${task.successful_items} 个产品${task.failed_items > 0 ? `，失败 ${task.failed_items} 个` : ''}`;
        
        const actions = [
            {
                text: '下载报告',
                className: 'bg-blue-600 hover:bg-blue-700 text-white text-xs py-1 px-3 rounded font-medium transition-colors',
                action: 'download'
            },
            {
                text: '新任务',
                className: 'bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs py-1 px-3 rounded font-medium transition-colors',
                action: 'new-task'
            }
        ];
        
        createNotification('success', '更新完成！', message, actions);
    }

    // 自动执行任务函数
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
                createNotification('error', '启动失败', data.message);
                document.getElementById('progress-section').classList.add('hidden');
                document.getElementById('upload-section').classList.remove('hidden');
            }
        })
        .catch(error => {
            console.error('启动错误:', error);
            createNotification('error', '启动失败', '任务启动失败，请重试');
            document.getElementById('progress-section').classList.add('hidden');
            document.getElementById('upload-section').classList.remove('hidden');
        });
    }

    // 监控进度
    function startProgressMonitoring() {
        progressInterval = setInterval(updateProgress, 2000); // 每2秒更新一次
        updateProgress(); // 立即更新一次
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
                    
                    // 几秒后返回上传页面
                    setTimeout(() => {
                        document.getElementById('progress-section').classList.add('hidden');
                        document.getElementById('upload-section').classList.remove('hidden');
                        // 重置文件选择
                        document.getElementById('excel-file').value = '';
                        document.getElementById('file-info').classList.add('hidden');
                        document.getElementById('upload-btn').disabled = true;
                    }, 3000);
                } else if (task.status === 'failed') {
                    clearInterval(progressInterval);
                    createNotification('error', '更新失败', task.error_message || '处理过程中遇到错误，请重试');
                    
                    // 返回上传页面
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

    // 全局函数供通知使用
    window.downloadReport = function(taskId) {
        window.location.href = `/bulk-update/download-report?task_id=${taskId}`;
    };

    window.startNewTask = function() {
        document.getElementById('progress-section').classList.add('hidden');
        document.getElementById('upload-section').classList.remove('hidden');
        // 重置文件选择
        document.getElementById('excel-file').value = '';
        document.getElementById('file-info').classList.add('hidden');
        document.getElementById('upload-btn').disabled = true;
        document.getElementById('upload-btn').classList.remove('bg-blue-600', 'hover:bg-blue-700');
        document.getElementById('upload-btn').classList.add('disabled:bg-gray-300');
    };
});
</script>
@endpush