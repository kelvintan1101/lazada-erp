@extends('layouts.app')

@section('title', '批量更新产品标题')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-3xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">批量更新产品标题</h1>
        
        <!-- 文件上传区域 -->
        <div id="upload-section" class="bg-white rounded-lg shadow-sm p-4 mb-4">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">上传Excel文件</h2>
            
            <div class="mb-3">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-medium text-blue-800 text-sm mb-1">格式要求</h3>
                            <p class="text-xs text-blue-700">Excel/CSV，包含SKU和产品标题列，≤10MB</p>
                        </div>
                        <a href="/templates/product_title_update_template.csv"
                           download="产品标题更新模板.csv"
                           class="text-xs text-blue-600 hover:text-blue-800 font-medium whitespace-nowrap">
                            📁 下载模板
                        </a>
                    </div>
                </div>
            </div>

            <div class="border-2 border-dashed border-blue-300 rounded-lg p-6 text-center bg-gradient-to-b from-blue-50 to-white hover:from-blue-100 hover:to-blue-50 transition-all duration-200">
                <input type="file" id="excel-file" accept=".xlsx,.xls,.csv" class="hidden">
                <div id="file-drop-zone" class="cursor-pointer">
                    <svg class="mx-auto h-12 w-12 text-blue-400 mb-3" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <p class="text-lg font-semibold text-blue-600 mb-1">点击选择文件或拖拽</p>
                    <p class="text-sm text-gray-500">支持 Excel 和 CSV 格式</p>
                </div>
                <div id="file-info" class="hidden mt-3 p-2 bg-green-50 border border-green-200 rounded-lg">
                    <svg class="w-4 h-4 text-green-500 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm text-green-700">已选择: <span id="file-name" class="font-medium"></span></span>
                </div>
            </div>

            <button id="upload-btn" class="mt-4 w-full bg-gradient-to-r from-green-600 to-green-700 text-white py-3 px-6 rounded-lg font-semibold hover:from-green-700 hover:to-green-800 disabled:bg-gray-400 disabled:cursor-not-allowed transition-all duration-200" disabled>
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                开始上传并更新
            </button>
        </div>

        <!-- 任务信息区域 -->
        <div id="task-section" class="bg-white rounded-lg shadow-sm p-4 mb-4 hidden">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">任务信息</h2>
            
            <div id="task-info" class="space-y-2 text-sm">
                <!-- 任务信息将在这里显示 -->
            </div>

            <div id="warnings-section" class="hidden mt-3">
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                    <h3 class="font-medium text-yellow-800 mb-2 text-sm">⚠️ 注意事项</h3>
                    <ul id="warnings-list" class="text-xs text-yellow-700 space-y-1">
                        <!-- 警告信息将在这里显示 -->
                    </ul>
                </div>
            </div>

            <button id="execute-btn" class="mt-3 w-full bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-sm">
                开始执行更新
            </button>
        </div>

        <!-- 进度显示区域 - 重新设计更人性化 -->
        <div id="progress-section" class="bg-white rounded-lg shadow-sm p-4 hidden">
            <div class="text-center mb-3">
                <h2 id="progress-title" class="text-lg font-bold text-gray-800 mb-1">更新进度</h2>
                <p id="progress-subtitle" class="text-xs text-gray-600">正在为您处理产品信息，请稍候...</p>
            </div>
            
            <!-- 圆形进度条 -->
            <div class="flex justify-center mb-4">
                <div class="relative w-20 h-20 mx-auto">
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
                    <div class="absolute inset-0 flex items-center justify-center p-1">
                        <div class="text-center">
                            <div id="progress-percentage" class="text-base font-bold text-gray-800 leading-none mb-0.5">0%</div>
                            <div class="text-xs text-gray-500 leading-none">完成</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 统计信息卡片 -->
            <div class="grid grid-cols-3 gap-2 mb-4">
                <div class="bg-white rounded-lg shadow-sm p-3 border-l-4 border-blue-500">
                    <div class="text-center">
                        <p class="text-xs font-medium text-gray-600 mb-1">总数量</p>
                        <p id="total-count" class="text-xl font-bold text-gray-900">0</p>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-3 border-l-4 border-green-500">
                    <div class="text-center">
                        <p class="text-xs font-medium text-gray-600 mb-1">成功</p>
                        <p id="success-count" class="text-xl font-bold text-green-600">0</p>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-3 border-l-4 border-red-500">
                    <div class="text-center">
                        <p class="text-xs font-medium text-gray-600 mb-1">失败</p>
                        <p id="failed-count" class="text-xl font-bold text-red-600">0</p>
                    </div>
                </div>
            </div>

            <!-- 状态消息 -->
            <div class="bg-gray-50 rounded-lg p-3 mb-3">
                <div class="flex items-center space-x-2">
                    <div id="status-icon" class="flex-shrink-0">
                        <div class="w-4 h-4 border-2 border-blue-200 border-t-blue-600 rounded-full animate-spin"></div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p id="status-message" class="text-sm font-medium text-gray-800 truncate">准备开始...</p>
                        <p id="status-detail" class="text-xs text-gray-600">系统正在初始化处理流程</p>
                    </div>
                </div>
            </div>

            <!-- 实时日志显示 -->
            <div id="log-section" class="bg-gray-50 rounded-lg p-3 mb-3 hidden">
                <h3 class="text-sm font-semibold text-gray-800 mb-2">处理日志</h3>
                <div id="log-container" class="bg-white rounded-lg p-2 h-24 overflow-y-auto text-xs font-mono">
                    <div class="text-gray-600">等待开始处理...</div>
                </div>
            </div>

            <!-- 完成操作按钮 -->
            <div id="completed-actions" class="hidden">
                <div class="bg-green-50 border border-green-200 rounded-lg p-3 mb-3">
                    <div class="flex items-center justify-center mb-2">
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-2">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="text-center">
                            <h3 class="text-sm font-semibold text-green-800">更新完成！</h3>
                            <p class="text-xs text-green-700">产品标题更新任务已成功完成</p>
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-2">
                    <button id="download-report-btn" class="bg-blue-600 text-white py-2 px-4 rounded-lg text-sm font-medium hover:bg-blue-700 transition-all duration-200 flex items-center justify-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        下载报告
                    </button>
                    <button id="new-task-btn" class="bg-gray-600 text-white py-2 px-4 rounded-lg text-sm font-medium hover:bg-gray-700 transition-all duration-200 flex items-center justify-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        新任务
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('excel-file');
    const fileDropZone = document.getElementById('file-drop-zone');
    const fileInfo = document.getElementById('file-info');
    const fileName = document.getElementById('file-name');
    const uploadBtn = document.getElementById('upload-btn');
    const executeBtn = document.getElementById('execute-btn');
    const downloadReportBtn = document.getElementById('download-report-btn');
    const newTaskBtn = document.getElementById('new-task-btn');
    
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
                alert('请选择Excel文件（.xlsx, .xls）或CSV文件');
                return;
            }
            
            // 验证文件大小 (10MB)
            if (file.size > 10 * 1024 * 1024) {
                alert('文件大小不能超过10MB');
                return;
            }
            
            fileName.textContent = file.name;
            fileInfo.classList.remove('hidden');
            uploadBtn.disabled = false;
            uploadBtn.classList.remove('bg-gray-400');
            uploadBtn.classList.add('bg-green-600', 'hover:bg-green-700');
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
            alert('请先选择文件');
            return;
        }

        const formData = new FormData();
        formData.append('excel_file', fileInput.files[0]);

        uploadBtn.disabled = true;
        uploadBtn.textContent = '上传中...';
        uploadBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
        uploadBtn.classList.add('bg-gray-400');

        console.log('开始上传文件:', fileInput.files[0].name);

        fetch('/bulk-update/upload', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => {
            console.log('上传响应状态:', response.status);
            if (!response.ok) {
                // 尝试获取错误详情
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
            console.log('上传响应数据:', data);
            if (data.success) {
                currentTaskId = data.task_id;
                showTaskInfo(data);
                
                // 直接自动执行任务，不显示确认页面
                document.getElementById('upload-section').classList.add('hidden');
                document.getElementById('progress-section').classList.remove('hidden');
                
                // 显示任务开始信息
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
                alert('上传失败: ' + (data.message || '未知错误'));
            }
        })
        .catch(error => {
            console.error('上传错误:', error);
            // 显示更详细的错误信息
            if (error.message.includes('403')) {
                alert('上传失败：没有Lazada授权。请先在设置页面进行Lazada授权。');
            } else if (error.message.includes('422')) {
                alert('上传失败：文件格式或大小不符合要求。请检查文件格式是否为Excel或CSV，且大小不超过10MB。');
            } else if (error.message.includes('500')) {
                alert('上传失败：服务器错误。请稍后重试或联系管理员。');
            } else {
                alert('上传失败：' + error.message + '。请检查网络连接并重试。');
            }
        })
        .finally(() => {
            uploadBtn.disabled = false;
            uploadBtn.innerHTML = `
                <svg class="w-6 h-6 inline-block mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                🚀 开始上传并更新产品标题
            `;
            uploadBtn.classList.remove('bg-gray-400');
            uploadBtn.classList.add('bg-green-600', 'hover:bg-green-700');
        });
    });

    // 显示任务信息
    function showTaskInfo(data) {
        const taskInfo = document.getElementById('task-info');
        taskInfo.innerHTML = `
            <div class="flex justify-between">
                <span class="text-gray-600">任务ID:</span>
                <span class="font-medium">${data.task_id}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">有效产品数量:</span>
                <span class="font-medium">${data.valid_products}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">总项目数:</span>
                <span class="font-medium">${data.total_items}</span>
            </div>
        `;

        if (data.warnings && data.warnings.length > 0) {
            const warningsSection = document.getElementById('warnings-section');
            const warningsList = document.getElementById('warnings-list');
            
            warningsList.innerHTML = data.warnings.map(warning => `<li>• ${warning}</li>`).join('');
            warningsSection.classList.remove('hidden');
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

    // 添加日志消息
    function addLogMessage(message, type = 'info') {
        const logContainer = document.getElementById('log-container');
        const logSection = document.getElementById('log-section');
        
        if (logSection.classList.contains('hidden')) {
            logSection.classList.remove('hidden');
        }
        
        const timestamp = new Date().toLocaleTimeString();
        const colorClass = type === 'success' ? 'text-green-600' : 
                          type === 'error' ? 'text-red-600' : 
                          type === 'warning' ? 'text-yellow-600' : 'text-gray-600';
        
        const logMessage = document.createElement('div');
        logMessage.className = `${colorClass} mb-1`;
        logMessage.innerHTML = `[${timestamp}] ${message}`;
        
        logContainer.appendChild(logMessage);
        logContainer.scrollTop = logContainer.scrollHeight;
    }

    // 更新状态图标
    function updateStatusIcon(status) {
        const statusIcon = document.getElementById('status-icon');
        
        switch (status) {
            case 'pending':
                statusIcon.innerHTML = '<div class="w-8 h-8 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin"></div>';
                break;
            case 'processing':
                statusIcon.innerHTML = '<div class="w-8 h-8 border-4 border-green-200 border-t-green-600 rounded-full animate-spin"></div>';
                break;
            case 'completed':
                statusIcon.innerHTML = '<div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center"><svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></div>';
                break;
            case 'failed':
                statusIcon.innerHTML = '<div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center"><svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></div>';
                break;
        }
    }

    // 更新进度显示函数
    function updateProgressDisplay(task) {
        const totalCount = document.getElementById('total-count');
        const successCount = document.getElementById('success-count');
        const failedCount = document.getElementById('failed-count');
        const progressTitle = document.getElementById('progress-title');
        const progressSubtitle = document.getElementById('progress-subtitle');
        const statusMessage = document.getElementById('status-message');
        const statusDetail = document.getElementById('status-detail');

        const percentage = task.progress_percentage || 0;
        updateCircularProgress(percentage);
        
        totalCount.textContent = task.total_items;
        successCount.textContent = task.successful_items;
        failedCount.textContent = task.failed_items;

        updateStatusIcon(task.status);

        let status = '';
        let detail = '';
        
        switch (task.status) {
            case 'pending':
                status = '准备开始处理';
                detail = '系统正在初始化处理流程';
                if (progressTitle) progressTitle.textContent = '准备更新';
                if (progressSubtitle) progressSubtitle.textContent = '系统正在初始化处理流程...';
                addLogMessage('任务已创建，准备开始处理...', 'info');
                break;
            case 'processing':
                status = `正在处理中... (${task.processed_items}/${task.total_items})`;
                detail = `已完成 ${task.successful_items} 个，失败 ${task.failed_items} 个`;
                if (progressTitle) progressTitle.textContent = `更新进度 - ${percentage}%`;
                if (progressSubtitle) progressSubtitle.textContent = `正在处理第 ${task.processed_items} / ${task.total_items} 个产品`;
                if (task.processed_items > 0) {
                    addLogMessage(`处理进度：${task.processed_items}/${task.total_items}`, 'info');
                }
                break;
            case 'completed':
                status = '更新完成！';
                detail = `成功处理 ${task.successful_items} 个产品，失败 ${task.failed_items} 个`;
                if (progressTitle) progressTitle.textContent = '更新完成';
                if (progressSubtitle) progressSubtitle.textContent = `已成功处理 ${task.successful_items} 个产品，失败 ${task.failed_items} 个`;
                addLogMessage('所有产品处理完成！', 'success');
                break;
            case 'failed':
                status = '任务失败';
                detail = '处理过程中遇到错误，请查看日志';
                if (progressTitle) progressTitle.textContent = '更新失败';
                if (progressSubtitle) progressSubtitle.textContent = '处理过程中遇到错误，请查看详细日志';
                addLogMessage('任务执行失败，请检查错误信息', 'error');
                break;
        }
        
        statusMessage.textContent = status;
        statusDetail.textContent = detail;
    }

    // 自动执行任务函数
    function executeTaskAutomatically() {
        console.log('自动执行任务，任务ID:', currentTaskId);
        addLogMessage('开始自动执行任务...', 'info');
        
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
                console.log('任务自动启动成功');
                addLogMessage('任务已启动，开始处理产品...', 'success');
                startProgressMonitoring();
            } else {
                addLogMessage('自动启动失败: ' + data.message, 'error');
                alert('自动启动失败: ' + data.message);
                // 如果自动启动失败，显示任务确认页面
                document.getElementById('progress-section').classList.add('hidden');
                document.getElementById('task-section').classList.remove('hidden');
            }
        })
        .catch(error => {
            console.error('自动启动错误:', error);
            addLogMessage('自动启动失败，请手动执行', 'error');
            alert('自动启动失败，请手动执行');
            // 如果自动启动失败，显示任务确认页面
            document.getElementById('progress-section').classList.add('hidden');
            document.getElementById('task-section').classList.remove('hidden');
        });
    }

    // 执行任务（手动触发，作为备用）
    executeBtn.addEventListener('click', function() {
        executeBtn.disabled = true;
        executeBtn.textContent = '启动中...';

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
                document.getElementById('task-section').classList.add('hidden');
                document.getElementById('progress-section').classList.remove('hidden');
                startProgressMonitoring();
            } else {
                alert('启动失败: ' + data.message);
                executeBtn.disabled = false;
                executeBtn.textContent = '开始执行更新';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('启动失败，请重试');
            executeBtn.disabled = false;
            executeBtn.textContent = '开始执行更新';
        });
    });

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
                
                if (task.status === 'completed' || task.status === 'failed') {
                    clearInterval(progressInterval);
                    showCompletedActions();
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            addLogMessage('获取进度信息失败', 'error');
        });
    }

    function showCompletedActions() {
        document.getElementById('completed-actions').classList.remove('hidden');
        
        // 添加完成动画效果
        setTimeout(() => {
            document.getElementById('completed-actions').scrollIntoView({ 
                behavior: 'smooth', 
                block: 'nearest' 
            });
        }, 500);
    }

    // 下载报告
    downloadReportBtn.addEventListener('click', function() {
        addLogMessage('开始下载报告...', 'info');
        window.location.href = `/bulk-update/download-report?task_id=${currentTaskId}`;
    });

    // 创建新任务
    newTaskBtn.addEventListener('click', function() {
        addLogMessage('刷新页面创建新任务...', 'info');
        location.reload();
    });
});
</script>
@endpush