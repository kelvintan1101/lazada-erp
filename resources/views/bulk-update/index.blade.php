@extends('layouts.app')

@section('title', '批量更新产品标题')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-2xl mx-auto px-4">

        <!-- 文件上传区域 -->
        <div id="upload-section" class="bg-white rounded-xl shadow-lg border border-gray-200 p-8">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-900 mb-3">批量更新产品标题</h1>
                <p class="text-gray-600">上传包含SKU和产品标题的Excel/CSV文件</p>
            </div>

            <!-- 格式提示 -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-sm text-blue-700 font-medium">需要包含SKU和产品标题列</span>
                    </div>
                    <a href="/templates/product_title_update_template.csv"
                       download="产品标题更新模板.csv"
                       class="text-sm text-blue-600 hover:text-blue-800 font-medium underline">
                        下载模板
                    </a>
                </div>
            </div>

            <!-- 文件选择区域 -->
            <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-blue-400 hover:bg-blue-50 transition-all duration-200 mb-6">
                <input type="file" id="excel-file" accept=".xlsx,.xls,.csv" class="hidden">
                <div id="file-drop-zone" class="cursor-pointer">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    <p class="text-lg font-semibold text-gray-700 mb-1">选择文件或拖拽到这里</p>
                    <p class="text-sm text-gray-500">支持 .xlsx, .xls, .csv 格式，最大10MB</p>
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
            <div class="flex justify-center">
                <button id="upload-btn"
                        type="button"
                        class="w-full max-w-md px-6 py-3 font-semibold rounded-lg shadow-lg border-2 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2"
                        disabled>
                    <span id="upload-btn-text">请先选择文件</span>
                </button>
            </div>
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

        <!-- 调试面板 -->
        <div id="debug-panel" class="mt-6 bg-gray-100 border border-gray-300 rounded-lg p-4 text-sm">
            <h3 class="font-bold text-gray-700 mb-2">🔧 调试信息</h3>
            <div class="space-y-1 text-gray-600">
                <div>按钮状态: <span id="debug-btn-status" class="font-mono">检查中...</span></div>
                <div>按钮可见性: <span id="debug-btn-visibility" class="font-mono">检查中...</span></div>
                <div>文件选择状态: <span id="debug-file-status" class="font-mono">未选择</span></div>
                <div class="mt-2">
                    <button onclick="window.testSimpleNotification()" class="bg-red-500 text-white px-3 py-1 rounded text-xs mr-2">简单测试</button>
                    <button onclick="window.testNotification()" class="bg-blue-500 text-white px-3 py-1 rounded text-xs mr-2">测试通知</button>
                    <button onclick="window.testSuccessNotification()" class="bg-green-500 text-white px-3 py-1 rounded text-xs mr-2">测试成功通知</button>
                    <button onclick="window.debugButtonStatus()" class="bg-yellow-500 text-white px-3 py-1 rounded text-xs">刷新状态</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 通知系统 -->
<div id="notification-container" style="position: fixed; bottom: 20px; right: 20px; z-index: 999999; max-width: 420px; pointer-events: none;">
    <!-- 通知将在这里动态创建 -->
</div>
@endsection

@push('styles')
<style>
/* 添加脉冲动画 */
@keyframes pulse {
    0% { transform: translate(-50%, -50%) scale(1); }
    50% { transform: translate(-50%, -50%) scale(1.05); }
    100% { transform: translate(-50%, -50%) scale(1); }
}

/* 添加通知进入动画 */
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
/* 通知容器样式 - 完全重写 */
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

/* 通知项样式 - 完全重写 */
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

/* 确保通知内的文字颜色正确 */
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

/* 按钮样式确保显示 */
#upload-btn {
    display: block !important;
    visibility: visible !important;
    min-height: 48px !important;
}

/* 按钮状态样式 */
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

/* 确保按钮文本显示 */
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
    console.log('页面加载完成，初始化批量更新功能...');

    // 获取DOM元素
    const fileInput = document.getElementById('excel-file');
    const fileDropZone = document.getElementById('file-drop-zone');
    const fileInfo = document.getElementById('file-info');
    const fileName = document.getElementById('file-name');
    const uploadBtn = document.getElementById('upload-btn');
    const uploadBtnText = document.getElementById('upload-btn-text');

    // 检查关键元素是否存在
    if (!fileInput || !fileDropZone || !uploadBtn || !uploadBtnText) {
        console.error('关键DOM元素未找到', {
            fileInput: !!fileInput,
            fileDropZone: !!fileDropZone,
            uploadBtn: !!uploadBtn,
            uploadBtnText: !!uploadBtnText
        });
        return;
    }

    console.log('所有DOM元素找到:', {
        uploadBtn: uploadBtn,
        uploadBtnText: uploadBtnText,
        uploadBtnVisible: window.getComputedStyle(uploadBtn).display,
        uploadBtnOpacity: window.getComputedStyle(uploadBtn).opacity
    });

    let currentTaskId = null;
    let progressInterval = null;

    // 检查CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        console.error('CSRF token not found');
        showNotification('error', '页面错误', '请刷新页面重试');
        return;
    }

    // 初始化按钮状态
    disableUploadButton('请先选择文件');
    console.log('按钮初始化完成，当前状态:', {
        disabled: uploadBtn.disabled,
        className: uploadBtn.className,
        text: uploadBtnText.textContent
    });

    console.log('初始化完成，设置事件监听器...');

    // 文件选择处理
    fileDropZone.addEventListener('click', function() {
        console.log('点击文件选择区域');
        fileInput.click();
    });

    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        console.log('文件选择变化:', file ? file.name : '无文件');

        if (file) {
            // 验证文件类型
            const allowedTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel', 'text/csv'];
            const allowedExtensions = ['.xlsx', '.xls', '.csv'];

            if (!allowedTypes.includes(file.type) && !allowedExtensions.some(ext => file.name.toLowerCase().endsWith(ext))) {
                showNotification('error', '文件格式错误', '请选择Excel文件（.xlsx, .xls）或CSV文件');
                resetFileSelection();
                return;
            }

            // 验证文件大小 (10MB)
            if (file.size > 10 * 1024 * 1024) {
                showNotification('error', '文件过大', '文件大小不能超过10MB');
                resetFileSelection();
                return;
            }

            // 显示文件信息
            fileName.textContent = file.name;
            fileInfo.classList.remove('hidden');

            // 启用上传按钮
            enableUploadButton();
            updateDebugPanel();
            console.log('文件验证通过，按钮已启用');
        } else {
            resetFileSelection();
            updateDebugPanel();
        }
    });

    // 辅助函数
    function enableUploadButton() {
        console.log('启用上传按钮');
        uploadBtn.disabled = false;
        uploadBtn.className = 'w-full max-w-md px-6 py-3 font-semibold rounded-lg shadow-lg border-2 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2';
        uploadBtnText.textContent = '开始上传并更新';

        // 强制重新渲染
        uploadBtn.style.display = 'block';
        uploadBtn.style.visibility = 'visible';
    }

    function disableUploadButton(text = '请先选择文件') {
        console.log('禁用上传按钮:', text);
        uploadBtn.disabled = true;
        uploadBtn.className = 'w-full max-w-md px-6 py-3 font-semibold rounded-lg shadow-lg border-2 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2';
        uploadBtnText.textContent = text;

        // 强制重新渲染
        uploadBtn.style.display = 'block';
        uploadBtn.style.visibility = 'visible';
    }

    function resetFileSelection() {
        fileInput.value = '';
        fileInfo.classList.add('hidden');
        disableUploadButton();
        updateDebugPanel();
    }

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

    // 上传文件处理
    uploadBtn.addEventListener('click', function() {
        console.log('点击上传按钮');

        if (!fileInput.files[0]) {
            showNotification('error', '请选择文件', '请先选择一个Excel或CSV文件');
            return;
        }

        const formData = new FormData();
        formData.append('excel_file', fileInput.files[0]);

        // 更新按钮状态
        disableUploadButton('上传中...');
        console.log('开始上传文件...');

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
            console.log('上传响应:', data);
            if (data.success) {
                currentTaskId = data.task_id;

                // 显示开始处理通知
                showNotification('info', '开始处理', `已上传 ${data.total_items} 个产品，正在开始更新...`);

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
                showNotification('error', '上传失败', data.message || '未知错误');
            }
        })
        .catch(error => {
            console.error('上传错误:', error);
            if (error.message.includes('403')) {
                showNotification('error', '授权失败', '没有Lazada授权。请先在设置页面进行Lazada授权。');
            } else if (error.message.includes('422')) {
                showNotification('error', '文件错误', '文件格式或大小不符合要求。');
            } else if (error.message.includes('500')) {
                showNotification('error', '服务器错误', '服务器错误，请稍后重试。');
            } else {
                showNotification('error', '上传失败', error.message);
            }
        })
        .finally(() => {
            enableUploadButton();
        });
    });

    // 完全重写的通知系统
    function showNotification(type, title, message, actions = []) {
        console.log('🔔 显示通知:', type, title, message);

        const container = document.getElementById('notification-container');
        if (!container) {
            console.error('❌ 通知容器未找到');
            alert(`通知: ${title} - ${message}`); // 备用方案
            return;
        }

        const notificationId = 'notification-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
        const notification = document.createElement('div');
        notification.id = notificationId;
        notification.className = 'notification-item';

        // 强制设置样式
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
            <div class="flex items-start" style="color: #1f2937 !important;">
                <div class="w-10 h-10 rounded-full flex items-center justify-center ${iconColors[type]} mr-4 flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        ${icons[type]}
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h4 style="color: #111827 !important; font-size: 16px !important; font-weight: bold !important; margin-bottom: 4px !important;">${title}</h4>
                    <p style="color: #374151 !important; font-size: 14px !important; line-height: 1.5 !important;">${message}</p>
                    ${actionsHtml}
                </div>
                <button class="close-btn ml-3 flex-shrink-0 p-1 rounded-full transition-colors" style="color: #6b7280 !important;" onmouseover="this.style.color='#1f2937'" onmouseout="this.style.color='#6b7280'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        `;

        // 添加到容器
        container.appendChild(notification);
        console.log('✅ 通知已添加到容器:', notification);
        console.log('📍 容器位置:', container.getBoundingClientRect());
        console.log('📍 通知位置:', notification.getBoundingClientRect());

        // 添加关闭事件
        const closeBtn = notification.querySelector('.close-btn');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                console.log('🔴 点击关闭按钮');
                hideNotification(notificationId);
            });
        }

        // 添加操作按钮事件
        const actionButtons = notification.querySelectorAll('[data-action]');
        actionButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                const action = btn.getAttribute('data-action');
                console.log('🔵 点击操作按钮:', action);
                if (action === 'download') {
                    downloadReport(currentTaskId);
                } else if (action === 'new-task') {
                    startNewTask();
                }
                hideNotification(notificationId);
            });
        });

        // 强制显示动画 - 立即执行
        console.log('🎬 开始显示动画');
        setTimeout(() => {
            notification.style.transform = 'translateX(0) !important';
            notification.style.opacity = '1 !important';
            notification.style.animation = 'slideInRight 0.4s ease-out forwards !important';
            notification.classList.add('show');
            console.log('✨ 动画已触发');
        }, 50);

        // 自动消失（除非有操作按钮）
        if (actions.length === 0) {
            setTimeout(() => {
                hideNotification(notificationId);
            }, 5000);
        }

        return notificationId;
    }

    function hideNotification(notificationId) {
        console.log('隐藏通知:', notificationId);
        const notification = document.getElementById(notificationId);
        if (notification) {
            notification.style.animation = 'slideOutRight 0.3s ease-in forwards !important';
            notification.style.transform = 'translateX(100%)';
            notification.style.opacity = '0';
            notification.classList.add('hide');
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                    console.log('通知已移除:', notificationId);
                }
            }, 300);
        }
    }

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

    // 兼容旧的函数名
    function createNotification(type, title, message, actions = []) {
        return showNotification(type, title, message, actions);
    }

    // 显示成功通知
    function showSuccessNotification(task) {
        console.log('显示成功通知:', task);

        const message = `🎉 成功处理 ${task.successful_items} 个产品${task.failed_items > 0 ? `，失败 ${task.failed_items} 个` : ''}`;

        const actions = [
            {
                text: '📥 下载报告',
                className: 'bg-blue-600 hover:bg-blue-700 text-white text-sm py-2 px-4 rounded-lg font-medium transition-colors shadow-md',
                action: 'download'
            },
            {
                text: '🔄 新任务',
                className: 'bg-green-600 hover:bg-green-700 text-white text-sm py-2 px-4 rounded-lg font-medium transition-colors shadow-md',
                action: 'new-task'
            }
        ];

        // 显示大型成功通知
        showLargeSuccessNotification('✅ 批量更新完成！', message, actions);
    }

    // 大型成功通知
    function showLargeSuccessNotification(title, message, actions = []) {
        console.log('显示大型成功通知');

        const container = document.getElementById('notification-container');
        if (!container) {
            console.error('通知容器未找到');
            return;
        }

        const notificationId = 'large-success-' + Date.now();
        const notification = document.createElement('div');
        notification.id = notificationId;
        notification.className = 'notification-item bg-gradient-to-r from-green-500 to-blue-600 border-0 rounded-xl shadow-2xl p-6 w-96 mb-4 text-white';

        // 确保初始状态
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
            <div class="text-center">
                <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold mb-2">${title}</h3>
                <p class="text-white text-opacity-90 mb-2">${message}</p>
                ${actionsHtml}
                <button class="close-btn absolute top-2 right-2 text-white text-opacity-70 hover:text-opacity-100 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        `;

        notification.style.position = 'relative';

        // 添加到容器
        container.appendChild(notification);
        console.log('大型成功通知已添加到容器:', notification);

        // 添加关闭事件
        const closeBtn = notification.querySelector('.close-btn');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                hideNotification(notificationId);
            });
        }

        // 添加操作按钮事件
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

        // 强制显示动画
        setTimeout(() => {
            console.log('开始显示大型成功通知动画');
            notification.style.transform = 'translateX(0)';
            notification.style.opacity = '1';
            notification.classList.add('show');
        }, 50);

        // 10秒后自动消失
        setTimeout(() => {
            hideNotification(notificationId);
        }, 10000);

        return notificationId;
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
                showNotification('error', '启动失败', data.message);
                document.getElementById('progress-section').classList.add('hidden');
                document.getElementById('upload-section').classList.remove('hidden');
            }
        })
        .catch(error => {
            console.error('启动错误:', error);
            showNotification('error', '启动失败', '任务启动失败，请重试');
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
                    showNotification('error', '更新失败', task.error_message || '处理过程中遇到错误，请重试');

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
    function downloadReport(taskId) {
        console.log('下载报告:', taskId);
        window.location.href = `/bulk-update/download-report?task_id=${taskId}`;
    }

    function startNewTask() {
        console.log('开始新任务');
        document.getElementById('progress-section').classList.add('hidden');
        document.getElementById('upload-section').classList.remove('hidden');
        resetFileSelection();
    }

    // 调试函数
    function updateDebugPanel() {
        const debugBtnStatus = document.getElementById('debug-btn-status');
        const debugBtnVisibility = document.getElementById('debug-btn-visibility');
        const debugFileStatus = document.getElementById('debug-file-status');

        if (debugBtnStatus) {
            debugBtnStatus.textContent = uploadBtn.disabled ? '禁用' : '启用';
        }

        if (debugBtnVisibility) {
            const style = window.getComputedStyle(uploadBtn);
            debugBtnVisibility.textContent = `display: ${style.display}, opacity: ${style.opacity}, visibility: ${style.visibility}`;
        }

        if (debugFileStatus) {
            debugFileStatus.textContent = fileInput.files.length > 0 ? `已选择: ${fileInput.files[0].name}` : '未选择';
        }
    }

    function debugButtonStatus() {
        console.log('=== 按钮调试信息 ===');
        console.log('按钮元素:', uploadBtn);
        console.log('按钮禁用状态:', uploadBtn.disabled);
        console.log('按钮类名:', uploadBtn.className);
        console.log('按钮样式:', window.getComputedStyle(uploadBtn));
        console.log('按钮文本:', uploadBtnText.textContent);
        updateDebugPanel();
    }

    // 测试函数
    function testNotification() {
        console.log('测试通知显示');
        const container = document.getElementById('notification-container');
        console.log('通知容器:', container);
        console.log('容器样式:', window.getComputedStyle(container));
        showNotification('success', '测试通知', '这是一个测试通知，用于验证通知系统是否正常工作');
    }

    function testSuccessNotification() {
        console.log('测试成功通知');
        const mockTask = {
            successful_items: 5,
            failed_items: 1
        };
        showSuccessNotification(mockTask);
    }

    function testSimpleNotification() {
        console.log('🧪 测试简单通知');
        const container = document.getElementById('notification-container');
        if (!container) {
            console.error('❌ 通知容器不存在!');
            alert('通知容器不存在!');
            return;
        }

        console.log('📦 容器信息:', {
            element: container,
            style: window.getComputedStyle(container),
            position: container.getBoundingClientRect()
        });

        // 创建一个非常明显的测试通知
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
            <div>🔔 测试通知显示成功！</div>
            <div style="font-size: 14px; margin-top: 10px; opacity: 0.9;">
                如果您能看到这个通知，说明通知系统正常工作
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
            ">关闭</button>
        `;

        document.body.appendChild(testNotification);

        // 3秒后自动移除
        setTimeout(() => {
            if (testNotification.parentElement) {
                testNotification.remove();
            }
        }, 5000);

        const testDiv = document.createElement('div');
        testDiv.innerHTML = `
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <span>🔴 简单测试通知 - ${new Date().toLocaleTimeString()}</span>
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
        console.log('✅ 简单测试通知已添加');

        setTimeout(() => {
            if (testDiv.parentNode) {
                testDiv.parentNode.removeChild(testDiv);
                console.log('🗑️ 简单测试通知已移除');
            }
        }, 5000);
    }

    // 将函数暴露到全局作用域
    window.downloadReport = downloadReport;
    window.startNewTask = startNewTask;
    window.showNotification = showNotification;
    window.hideNotification = hideNotification;
    window.testNotification = testNotification;
    window.testSuccessNotification = testSuccessNotification;
    window.testSimpleNotification = testSimpleNotification;
    window.debugButtonStatus = debugButtonStatus;
    window.updateDebugPanel = updateDebugPanel;

    // 初始化调试面板
    setTimeout(() => {
        updateDebugPanel();
        debugButtonStatus();
    }, 500);

    // 显示欢迎通知
    setTimeout(() => {
        console.log('显示欢迎通知');
        showNotification('info', '页面加载完成', '批量更新功能已准备就绪！按钮应该在上方可见。');
    }, 1000);

    console.log('批量更新功能初始化完成');
});
</script>
@endpush