@extends('layouts.app')

@section('title', '批量更新产品标题')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">批量更新产品标题</h1>
        
        <!-- 文件上传区域 -->
        <div id="upload-section" class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">上传Excel文件</h2>
            
            <div class="mb-4">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <h3 class="font-medium text-blue-800 mb-2">文件格式要求：</h3>
                    <ul class="text-sm text-blue-700 space-y-1">
                        <li>• 支持Excel文件（.xlsx, .xls）和CSV文件</li>
                        <li>• 第一行必须是表头</li>
                        <li>• 必须包含"SKU"列和"产品标题"列（或类似名称）</li>
                        <li>• 文件大小不超过10MB</li>
                    </ul>
                    <div class="mt-3">
                        <a href="/templates/product_title_update_template.csv"
                           download="产品标题更新模板.csv"
                           class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            下载Excel模板文件
                        </a>
                    </div>
                </div>
            </div>

            <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
                <input type="file" id="excel-file" accept=".xlsx,.xls,.csv" class="hidden">
                <div id="file-drop-zone" class="cursor-pointer">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <p class="text-lg text-gray-600 mb-2">点击选择文件或拖拽文件到此处</p>
                    <p class="text-sm text-gray-500">支持 .xlsx, .xls, .csv 格式</p>
                </div>
                <div id="file-info" class="hidden mt-4">
                    <p class="text-sm text-gray-600">已选择文件: <span id="file-name" class="font-medium"></span></p>
                </div>
            </div>

            <button id="upload-btn" class="mt-4 w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed" disabled>
                上传文件
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

    // 文件选择处理
    fileDropZone.addEventListener('click', () => fileInput.click());
    
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            fileName.textContent = file.name;
            fileInfo.classList.remove('hidden');
            uploadBtn.disabled = false;
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
            fileName.textContent = files[0].name;
            fileInfo.classList.remove('hidden');
            uploadBtn.disabled = false;
        }
    });

    // 上传文件
    uploadBtn.addEventListener('click', function() {
        const formData = new FormData();
        formData.append('excel_file', fileInput.files[0]);

        uploadBtn.disabled = true;
        uploadBtn.textContent = '上传中...';

        fetch('/bulk-update/upload', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentTaskId = data.task_id;
                showTaskInfo(data);
                document.getElementById('upload-section').classList.add('hidden');
                document.getElementById('task-section').classList.remove('hidden');
            } else {
                alert('上传失败: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('上传失败，请重试');
        })
        .finally(() => {
            uploadBtn.disabled = false;
            uploadBtn.textContent = '上传文件';
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
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
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
});
</script>
@endsection
