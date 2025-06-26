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

<!-- 成功完成弹窗 -->
<div id="success-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md mx-4 transform scale-95 transition-all duration-300">
        <div class="text-center">
            <!-- 成功图标 -->
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            
            <!-- 标题和内容 -->
            <h3 class="text-xl font-bold text-gray-900 mb-2">更新完成！</h3>
            <p class="text-gray-600 mb-6" id="success-message">产品标题更新任务已成功完成</p>
            
            <!-- 统计信息 -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600">成功：</span>
                        <span class="font-semibold text-green-600" id="modal-success-count">0</span>
                    </div>
                    <div>
                        <span class="text-gray-600">失败：</span>
                        <span class="font-semibold text-red-600" id="modal-failed-count">0</span>
                    </div>
                </div>
            </div>
            
            <!-- 操作按钮 -->
            <div class="flex space-x-3">
                <button id="modal-download-btn" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg font-medium transition-colors">
                    下载报告
                </button>
                <button id="modal-new-task-btn" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-4 rounded-lg font-medium transition-colors">
                    新任务
                </button>
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
            alert('请先选择文件');
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
                alert('上传失败: ' + (data.message || '未知错误'));
            }
        })
        .catch(error => {
            console.error('上传错误:', error);
            if (error.message.includes('403')) {
                alert('上传失败：没有Lazada授权。请先在设置页面进行Lazada授权。');
            } else if (error.message.includes('422')) {
                alert('上传失败：文件格式或大小不符合要求。');
            } else if (error.message.includes('500')) {
                alert('上传失败：服务器错误。请稍后重试。');
            } else {
                alert('上传失败：' + error.message);
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

    // 显示成功弹窗
    function showSuccessModal(task) {
        const modal = document.getElementById('success-modal');
        const successMessage = document.getElementById('success-message');
        const modalSuccessCount = document.getElementById('modal-success-count');
        const modalFailedCount = document.getElementById('modal-failed-count');
        
        successMessage.textContent = `成功处理了 ${task.successful_items} 个产品，失败 ${task.failed_items} 个`;
        modalSuccessCount.textContent = task.successful_items;
        modalFailedCount.textContent = task.failed_items;
        
        modal.classList.remove('hidden');
        modal.querySelector('.bg-white').classList.add('scale-100');
        modal.querySelector('.bg-white').classList.remove('scale-95');
    }

    // 关闭弹窗
    function closeModal() {
        const modal = document.getElementById('success-modal');
        modal.classList.add('hidden');
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
                alert('启动失败: ' + data.message);
                document.getElementById('progress-section').classList.add('hidden');
                document.getElementById('upload-section').classList.remove('hidden');
            }
        })
        .catch(error => {
            console.error('启动错误:', error);
            alert('启动失败，请重试');
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
                    showSuccessModal(task);
                } else if (task.status === 'failed') {
                    clearInterval(progressInterval);
                    alert('任务失败：' + task.error_message || '处理过程中遇到错误');
                    // 返回上传页面
                    document.getElementById('progress-section').classList.add('hidden');
                    document.getElementById('upload-section').classList.remove('hidden');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    // 弹窗按钮事件监听
    document.getElementById('modal-download-btn').addEventListener('click', function() {
        window.location.href = `/bulk-update/download-report?task_id=${currentTaskId}`;
        closeModal();
    });

    document.getElementById('modal-new-task-btn').addEventListener('click', function() {
        closeModal();
        location.reload();
    });

    // 点击弹窗外部关闭
    document.getElementById('success-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
});
</script>
@endpush