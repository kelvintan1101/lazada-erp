# Lazada ERP 系统

基于Laravel框架开发的Lazada电商管理系统，支持产品管理、订单处理和批量操作。

## 主要功能

### 🛍️ 产品管理
- 从Lazada同步产品信息
- 库存管理和调整
- **批量更新产品标题** - 通过Excel文件批量修改产品名称

### 📦 订单管理
- 订单同步和状态更新
- 订单详情查看

### ⚙️ 系统设置
- Lazada API配置
- 系统参数设置

## 批量产品标题更新功能

### 功能特点
- **Excel文件上传** - 支持.xlsx, .xls, .csv格式
- **数据验证** - 自动验证SKU和产品标题格式
- **API限制处理** - 智能控制API调用频率，避免超出Lazada限制
- **异步处理** - 大批量数据使用队列异步处理，避免超时
- **实时进度** - 显示更新进度和成功/失败统计
- **详细报告** - 可下载详细的更新结果报告
- **错误处理** - 完善的错误处理和重试机制

### 使用方法

1. **准备Excel文件**
   - 下载模板文件：`/templates/product_title_update_template.csv`
   - 文件必须包含两列：`SKU` 和 `产品标题`
   - 第一行为表头

2. **上传和执行**
   - 访问 `/bulk-update` 页面
   - 上传Excel文件
   - 系统会验证文件格式和数据
   - 点击"开始执行更新"启动批量更新

3. **监控进度**
   - 实时查看更新进度
   - 查看成功/失败统计
   - 下载详细报告

### 技术实现

#### API限制处理
- 每个API调用间隔1秒，确保不超出Lazada API限制
- 实现重试机制处理临时失败
- 详细的日志记录便于问题排查

#### 用户体验优化
- 异步队列处理避免页面超时
- 实时进度更新
- 友好的错误提示
- 可下载的结果报告

#### 数据安全
- 文件上传验证
- SKU存在性检查
- 事务处理确保数据一致性

## 安装和配置

### 环境要求
- PHP 8.2+
- Laravel 12.0+
- MySQL/SQLite数据库
- Redis（用于队列处理）

### 安装步骤

1. **克隆项目**
   ```bash
   git clone <repository-url>
   cd lazada-erp
   ```

2. **安装依赖**
   ```bash
   composer install
   npm install
   ```

3. **环境配置**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **配置数据库**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=lazada_erp
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **配置Lazada API**
   ```env
   LAZADA_APP_KEY=your_app_key
   LAZADA_APP_SECRET=your_app_secret
   ```

6. **运行迁移**
   ```bash
   php artisan migrate
   ```

7. **配置队列**
   ```env
   QUEUE_CONNECTION=redis
   ```

8. **启动服务**
   ```bash
   # 启动Web服务器
   php artisan serve

   # 启动队列处理器
   php artisan queue:work

   # 启动前端构建
   npm run dev
   ```

## 项目结构

### 核心文件
- `app/Services/LazadaApiService.php` - Lazada API集成
- `app/Services/BulkUpdateService.php` - 批量更新业务逻辑
- `app/Services/ExcelProcessingService.php` - Excel文件处理
- `app/Jobs/ProcessBulkUpdateJob.php` - 异步队列任务
- `app/Http/Controllers/BulkUpdateController.php` - 批量更新控制器

### 数据模型
- `app/Models/Product.php` - 产品模型
- `app/Models/BulkUpdateTask.php` - 批量更新任务模型
- `app/Models/LazadaToken.php` - Lazada认证令牌

### 前端界面
- `resources/views/bulk-update/index.blade.php` - 批量更新页面

## API文档

### Lazada API集成
系统集成了以下Lazada API端点：
- `/product/update` - 更新产品信息
- `/products/get` - 获取产品列表
- `/auth/token/create` - 获取访问令牌

### 内部API
- `POST /bulk-update/upload` - 上传Excel文件
- `POST /bulk-update/execute` - 执行批量更新
- `GET /bulk-update/status` - 获取任务状态
- `GET /bulk-update/download-report` - 下载结果报告

## 许可证

本项目基于 [MIT license](https://opensource.org/licenses/MIT) 开源。
