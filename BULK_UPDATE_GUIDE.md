# Lazada 批量产品标题更新功能指南

## 📖 功能概述

这个功能允许用户通过上传Excel文件批量更新Lazada产品的标题/名称，具备完善的API限制处理、进度监控和错误报告功能。

## 🔄 系统运作流程

### 1. 文件上传与验证
```
用户上传Excel → 格式验证 → 数据解析 → SKU验证 → 创建任务
```

### 2. 异步批量处理
```
任务入队 → 队列处理 → API调用 → 进度更新 → 结果记录
```

### 3. 实时监控
```
前端轮询 → 状态更新 → 进度显示 → 完成报告
```

## 🏗️ 技术实现架构

### 核心组件
- **LazadaApiService** - Lazada API集成，处理产品更新
- **ExcelProcessingService** - Excel文件解析和数据验证
- **BulkUpdateService** - 批量更新业务逻辑协调
- **ProcessBulkUpdateJob** - 异步队列任务处理
- **BulkUpdateController** - HTTP请求和响应处理

### 数据库设计
- **bulk_update_tasks** 表 - 跟踪任务状态和进度
- **products** 表 - 本地产品数据（用于SKU验证）

### API限制处理
- 每次API调用间隔1秒，避免超出Lazada限制
- 实现重试机制处理临时失败
- 详细日志记录便于问题排查

## 📋 安装和配置步骤

### 步骤1: 安装依赖包
```bash
composer install
```
*安装 PhpSpreadsheet 和 Guzzle HTTP 依赖*

### 步骤2: 运行数据库迁移
```bash
php artisan migrate
```
*创建 bulk_update_tasks 表*

### 步骤3: 配置环境变量
在 `.env` 文件中确保以下配置：

```env
# Lazada API配置
LAZADA_APP_KEY=your_app_key
LAZADA_APP_SECRET=your_app_secret

# 队列配置（推荐使用Redis）
QUEUE_CONNECTION=redis
# 或使用数据库队列
# QUEUE_CONNECTION=database

# 如果使用Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 步骤4: 启动队列处理器
```bash
# 启动队列工作器（必须保持运行）
php artisan queue:work

# 或者使用supervisor管理（生产环境推荐）
php artisan queue:work --daemon
```

### 步骤5: 启动Web服务器
```bash
php artisan serve
```

### 步骤6: 编译前端资源（如果需要）
```bash
npm install
npm run dev
```

## 📁 Excel文件格式要求

### 必需列
文件必须包含以下列（支持中英文列名）：
- **SKU列**: `SKU` / `Seller SKU` / `卖家SKU` / `商品SKU`
- **标题列**: `产品标题` / `Product Title` / `Name` / `商品标题` / `产品名称`

### 示例格式
```csv
SKU,产品标题
ABC123,新的产品标题示例1
DEF456,新的产品标题示例2
GHI789,新的产品标题示例3
```

### 文件限制
- 支持格式：`.xlsx`, `.xls`, `.csv`
- 最大文件大小：10MB
- 第一行必须是表头
- 产品标题不能超过255字符

## 🚀 使用操作流程

### 1. 准备Excel文件
- 下载模板：访问 `/templates/product_title_update_template.csv`
- 填写SKU和新的产品标题
- 确保SKU在系统中存在

### 2. 访问批量更新页面
```
http://your-domain/bulk-update
```

### 3. 上传文件
- 点击上传区域或拖拽文件
- 系统自动验证文件格式
- 显示验证结果和警告信息

### 4. 执行批量更新
- 确认任务信息
- 点击"开始执行更新"
- 任务进入队列异步处理

### 5. 监控进度
- 实时查看更新进度
- 监控成功/失败统计
- 查看详细状态信息

### 6. 下载结果报告
- 任务完成后下载CSV格式报告
- 包含每个产品的更新状态和错误信息

## ⚙️ 系统配置优化

### 队列配置优化
```php
// config/queue.php
'connections' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => env('REDIS_QUEUE', 'default'),
        'retry_after' => 3600, // 1小时超时
        'block_for' => null,
    ],
]
```

### API调用频率调整
在 `LazadaApiService::batchUpdateProductTitles` 中调整延迟：
```php
// 当前设置：每秒1个请求
sleep(1);

// 可根据Lazada实际限制调整
// sleep(0.5); // 每秒2个请求
```

## 🔍 故障排除

### 常见问题

**1. 任务卡在pending状态**
- 检查队列处理器是否运行：`php artisan queue:work`
- 查看队列状态：`php artisan queue:failed`

**2. API调用失败**
- 检查Lazada Token是否有效
- 验证API Key和Secret配置
- 查看日志：`storage/logs/laravel.log`

**3. Excel文件解析失败**
- 确认文件格式正确
- 检查必需列是否存在
- 验证文件大小不超过10MB

**4. SKU不存在错误**
- 先同步产品数据：访问 `/products/sync`
- 确认SKU在本地数据库中存在

### 日志查看
```bash
# 查看应用日志
tail -f storage/logs/laravel.log

# 查看队列日志
php artisan queue:failed
```

## 🔒 安全考虑

### 文件上传安全
- 限制文件类型和大小
- 文件存储在非公开目录
- 上传后立即验证文件内容

### API安全
- 使用HTTPS进行API调用
- 妥善保管API密钥
- 实现请求签名验证

### 数据保护
- 敏感信息不记录在日志中
- 定期清理临时文件
- 数据库连接使用加密

## 📊 性能监控

### 关键指标
- API调用成功率
- 平均处理时间
- 队列任务积压情况
- 错误率统计

### 监控命令
```bash
# 查看队列状态
php artisan queue:monitor

# 查看失败任务
php artisan queue:failed

# 重试失败任务
php artisan queue:retry all
```

## 🎯 最佳实践

1. **小批量测试** - 首次使用时先测试少量产品
2. **定期备份** - 更新前备份重要数据
3. **监控日志** - 定期检查错误日志
4. **性能优化** - 根据实际情况调整API调用频率
5. **用户培训** - 确保用户了解Excel格式要求

---

## 📞 技术支持

如遇到问题，请检查：
1. 队列处理器运行状态
2. Lazada API配置
3. 数据库连接
4. 文件权限设置
5. 系统日志信息

系统设计充分考虑了API限制、用户体验和错误处理，是一个生产就绪的批量更新解决方案！
