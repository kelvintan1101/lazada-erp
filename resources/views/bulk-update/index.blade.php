@extends('layouts.app')

@section('title', '批量更新产品标题')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">批量更新产品标题</h1>
        
        <!-- Lazada授权状态检查 -->
        <div id="lazada-status-section" class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">系统状态检查</h2>
            <div id="lazada-status" class="flex items-center p-4 rounded-lg">
                <div class="animate-spin mr-3">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none" stroke-dasharray="32" stroke-dashoffset="32">
                            <animate attributeName="stroke-dashoffset" dur="1s" values="32;0" repeatCount="indefinite"/>
                        </circle>
                    </svg>
                </div>
                <span class="text-gray-600">正在检查Lazada授权状态...</span>
            </div>
        </div>

        <!-- 文件上传区域 -->
        <div id="upload-section" class="bg-white rounded-lg shadow-md p-6 mb-6 hidden">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">上传Excel文件</h2>
            
            <div class="mb-4">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <h3 class="font-medium text-blue-800 mb-2">文件格式要求：</h3>
                    <ul class="text-sm text-blue-700 space-y-1">
                        <li>• 支持Excel文件（.xlsx, .xls）和CSV文件</li>
                        <li>• 第一行必须是表头</li>
                        <li>• 必须包含"SKU"或"SKU ID"列</li>
                        <li>• 必须包含"Product Name"或"产品标题"列</li>
                        <li>• 文件大小不超过10MB</li>
                    </ul>
                    <div class="mt-3">
                        <a href="/templates/product_title_update_template.csv"
                           download="产品标题更新模板.csv"
                           class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800 font-medium">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            📁 下载CSV模板文件 (SKU ID + Product Name 格式)
                        </a>
                    </div>
                </div>
            </div>

            <div class="border-2 border-dashed border-blue-300 rounded-lg p-8 text-center bg-gradient-to-b from-blue-50 to-white hover:from-blue-100 hover:to-blue-50 transition-all duration-300">
                <input type="file" id="excel-file" accept=".xlsx,.xls,.csv" class="hidden">
                <div id="file-drop-zone" class="cursor-pointer">
                    <svg class="mx-auto h-16 w-16 text-blue-400 mb-4" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <p class="text-xl font-semibold text-blue-600 mb-2">点击选择文件或拖拽文件到此处</p>
                    <p class="text-md text-blue-500 mb-2">支持 Excel 和 CSV 格式</p>
                    <p class="text-sm text-gray-500">(.xlsx, .xls, .csv 格式，最大10MB)</p>
                </div>
                <div id="file-info" class="hidden mt-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                    <svg class="w-5 h-5 text-green-500 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm text-green-700">已选择文件: <span id="file-name" class="font-medium"></span></span>
                </div>
            </div>

            <button id="upload-btn" class="mt-6 w-full bg-gradient-to-r from-green-600 to-green-700 text-white py-4 px-8 rounded-lg font-bold text-xl shadow-xl hover:from-green-700 hover:to-green-800 disabled:bg-gray-400 disabled:cursor-not-allowed transform hover:scale-105 transition-all duration-200 border-2 border-green-500" disabled>
                <svg class="w-6 h-6 inline-block mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                🚀 开始上传并更新产品标题
            </button>
        </div>

        <!-- 任务信息区域 -->
        <div id="task-section" class="bg-white rounded-lg shadow-md p-6 mb-6 hidden">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">任务信息</h2>
            
            <div id="task-info" class="space-y-3">
                <!-- 任务信息将在这里显示 -->
            </div>

            <div id="warnings-section" class="hidden mt-4">
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <h3 class="font-medium text-yellow-800 mb-2">⚠️ 注意事项：</h3>
                    <ul id="warnings-list" class="text-sm text-yellow-700 space-y-1">
                        <!-- 警告信息将在这里显示 -->
                    </ul>
                </div>
            </div>

            <button id="execute-btn" class="mt-4 w-full bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed">
                开始执行更新
            </button>
        </div>

        <!-- 进度显示区域 -->
        <div id="progress-section" class="bg-white rounded-lg shadow-md p-6 hidden">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">更新进度</h2>
            
            <div class="space-y-4">
                <div>
                    <div class="flex justify-between text-sm text-gray-600 mb-1">
                        <span>整体进度</span>
                        <span id="progress-text">0%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div id="progress-bar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 text-center">
                    <div class="bg-gray-50 rounded-lg p-3">
                        <div class="text-2xl font-bold text-gray-800" id="total-count">0</div>
                        <div class="text-sm text-gray-600">总数</div>
                    </div>
                    <div class="bg-green-50 rounded-lg p-3">
                        <div class="text-2xl font-bold text-green-600" id="success-count">0</div>
                        <div class="text-sm text-gray-600">成功</div>
                    </div>
                    <div class="bg-red-50 rounded-lg p-3">
                        <div class="text-2xl font-bold text-red-600" id="failed-count">0</div>
                        <div class="text-sm text-gray-600">失败</div>
                    </div>
                </div>

                <div id="status-message" class="text-center text-gray-600">
                    准备开始...
                </div>
            </div>

            <div id="completed-actions" class="mt-6 hidden">
                <div class="flex space-x-4">
                    <button id="download-report-btn" class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700">
                        下载详细报告
                    </button>
                    <button id="new-task-btn" class="flex-1 bg-gray-600 text-white py-2 px-4 rounded-lg hover:bg-gray-700">
                        创建新任务
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

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

    // 检查Lazada授权状态
    checkLazadaAuth();

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
            alert('请先选择文件');
            return;
        }

        const formData = new FormData();
        formData.append('excel_file', fileInput.files[0]);

        uploadBtn.disabled = true;
        uploadBtn.textContent = '上传中...';
        uploadBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
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
                document.getElementById('upload-section').classList.add('hidden');
                document.getElementById('task-section').classList.remove('hidden');
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

    // 执行任务
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
        });
    }

    function updateProgressDisplay(task) {
        const progressBar = document.getElementById('progress-bar');
        const progressText = document.getElementById('progress-text');
        const totalCount = document.getElementById('total-count');
        const successCount = document.getElementById('success-count');
        const failedCount = document.getElementById('failed-count');
        const statusMessage = document.getElementById('status-message');

        const percentage = task.progress_percentage || 0;
        progressBar.style.width = percentage + '%';
        progressText.textContent = percentage + '%';
        
        totalCount.textContent = task.total_items;
        successCount.textContent = task.successful_items;
        failedCount.textContent = task.failed_items;

        let status = '';
        switch (task.status) {
            case 'pending':
                status = '等待开始...';
                break;
            case 'processing':
                status = `正在处理... (${task.processed_items}/${task.total_items})`;
                break;
            case 'completed':
                status = '更新完成！';
                break;
            case 'failed':
                status = '任务失败';
                break;
        }
        statusMessage.textContent = status;
    }

    function showCompletedActions() {
        document.getElementById('completed-actions').classList.remove('hidden');
    }

    // 下载报告
    downloadReportBtn.addEventListener('click', function() {
        window.location.href = `/bulk-update/download-report?task_id=${currentTaskId}`;
    });

    // 创建新任务
    newTaskBtn.addEventListener('click', function() {
        location.reload();
    });

    // 检查Lazada授权状态
    function checkLazadaAuth() {
        const lazadaStatus = document.getElementById('lazada-status');
        const uploadSection = document.getElementById('upload-section');
        
        // 简单的授权检查 - 尝试访问需要授权的接口
        fetch('/bulk-update/auth-check', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (response.status === 403) {
                // 没有授权
                lazadaStatus.innerHTML = `
                    <div class="flex items-center p-4 bg-red-50 border border-red-200 rounded-lg">
                        <svg class="w-6 h-6 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        <div class="flex-1">
                            <p class="text-red-800 font-medium">Lazada授权未配置</p>
                            <p class="text-red-600 text-sm mt-1">请先在设置页面进行Lazada授权，然后再使用批量更新功能。</p>
                        </div>
                        <a href="/settings" class="ml-4 bg-red-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-red-700">
                            前往设置
                        </a>
                    </div>
                `;
                return;
            } else if (response.ok) {
                // 授权正常
                lazadaStatus.innerHTML = `
                    <div class="flex items-center p-4 bg-green-50 border border-green-200 rounded-lg">
                        <svg class="w-6 h-6 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-green-800 font-medium">Lazada授权正常，可以开始上传文件</p>
                    </div>
                `;
                // 显示上传区域
                uploadSection.classList.remove('hidden');
                return;
            } else {
                throw new Error('检查授权状态失败');
            }
        })
        .catch(error => {
            // 网络错误或其他问题，假设未授权
            console.error('授权检查失败:', error);
            lazadaStatus.innerHTML = `
                <div class="flex items-center p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                    <div class="flex-1">
                        <p class="text-yellow-800 font-medium">无法检查授权状态</p>
                        <p class="text-yellow-600 text-sm mt-1">请确保网络连接正常，或检查Lazada授权是否配置正确。</p>
                    </div>
                    <button onclick="checkLazadaAuth()" class="ml-4 bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-yellow-700">
                        重新检查
                    </button>
                </div>
            `;
            // 仍然显示上传区域，让用户尝试
            uploadSection.classList.remove('hidden');
        });
    }
});
@endsection
