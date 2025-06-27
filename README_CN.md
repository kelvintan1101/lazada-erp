# Lazada ERP 系统

一个全面的基于 Laravel 的电商管理系统，专门为 Lazada 平台集成而设计。采用现代化的 PHP 8.2 和 Laravel 12.0 构建，具备先进的批量操作、实时同步和智能 API 管理功能。

## 🚀 核心功能

### 📦 产品管理
- **实时产品同步** - 与 Lazada 平台自动同步
- **库存管理** - 跟踪和调整库存水平，包含审计跟踪
- **批量标题更新** - 通过 Excel 导入批量更新产品标题，包含 API 限制控制
- **产品详情** - 全面的产品信息和历史记录追踪

### 🛒 订单管理
- **订单同步** - 从 Lazada 自动同步订单数据
- **状态管理** - 更新和跟踪订单状态变化
- **订单分析** - 详细的订单信息和报告
- **多状态支持** - 高效处理各种订单状态

### 🔧 系统管理
- **Lazada API 配置** - 安全的 API 凭证管理
- **令牌管理** - 自动令牌刷新和验证
- **用户角色管理** - 管理员和用户访问控制
- **系统设置** - 灵活的配置管理

### ⚡ 高级批量操作
- **Excel 文件处理** - 支持 .xlsx、.xls 和 .csv 格式
- **数据验证** - 全面的 SKU 和产品数据验证
- **API 限制** - 智能 API 调用节流（每秒 1-2 个请求）
- **异步处理** - 基于队列的大数据集处理
- **进度跟踪** - 实时进度监控和报告
- **错误处理** - 强大的错误恢复和详细日志记录
- **报告生成** - 可下载的详细操作报告

## 🏗️ 技术架构

### 技术栈
- **后端**: PHP 8.2+ 搭配 Laravel 12.0
- **前端**: TailwindCSS + Alpine.js + Vite
- **数据库**: MySQL/SQLite 带优化索引
- **队列系统**: Redis（推荐）/ 数据库队列
- **API 集成**: Guzzle HTTP 客户端搭配 Lazada Open API
- **Excel 处理**: PhpSpreadsheet 带内存优化
- **身份验证**: Laravel Breeze 带基于角色的访问控制

### 架构亮点
- **服务层模式** - 业务逻辑的清晰分离
- **仓储模式** - 抽象的数据访问层
- **事件驱动架构** - 解耦的系统组件
- **API 优先设计** - RESTful API 端点
- **基于队列的处理** - 可扩展的后台作业处理
- **全面日志记录** - 详细的操作跟踪

## 🛠️ 安装与设置

### 系统要求
- PHP 8.2 或更高版本
- Composer 2.x
- Node.js 18+ 和 NPM
- MySQL 8.0+ 或 SQLite
- Redis 服务器（推荐用于队列处理）

### 安装步骤

1. **克隆仓库**
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

4. **数据库配置**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=lazada_erp
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Lazada API 配置**
   ```env
   LAZADA_APP_KEY=your_app_key
   LAZADA_APP_SECRET=your_app_secret
   ```

6. **队列配置**
   ```env
   QUEUE_CONNECTION=redis
   REDIS_HOST=127.0.0.1
   REDIS_PORT=6379
   ```

7. **数据库迁移**
   ```bash
   php artisan migrate
   php artisan db:seed --class=AdminUserSeeder
   ```

8. **启动服务**
   ```bash
   # 一键启动所有服务
   composer run dev
   
   # 或者分别启动：
   php artisan serve          # Web 服务器 (http://localhost:8000)
   php artisan queue:work     # 队列处理器
   npm run dev               # 前端构建（热重载）
   php artisan pail          # 实时日志
   ```

## 📚 API 文档

### Lazada API 集成
系统集成了以下 Lazada API 端点：
- `/auth/token/create` - 访问令牌生成
- `/auth/token/refresh` - 令牌刷新
- `/products/get` - 产品数据获取
- `/product/update` - 产品信息更新
- `/orders/get` - 订单数据同步
- `/order/items/get` - 订单项详情

### 内部 API 端点
- `POST /bulk-update/upload` - Excel 文件上传
- `POST /bulk-update/execute/{taskId}` - 执行批量更新任务
- `GET /bulk-update/status/{taskId}` - 任务状态监控
- `GET /bulk-update/download-report/{taskId}` - 下载操作报告

### 认证流程
```php
// 1. 重定向到 Lazada 授权页面
$authUrl = $lazadaService->getAuthorizationUrl();

// 2. 处理回调并交换代码获取令牌
$tokenData = $lazadaService->getAccessToken($authCode);

// 3. 保存令牌供后续 API 调用使用
$lazadaService->saveToken($tokenData);
```

## 🔄 批量更新流程

### Excel 文件格式
下载模板：`/templates/product_title_update_template.csv`

必需列：
- `SKU` - 产品 SKU（数字）
- `产品标题` - 新产品标题（最大 255 字符）

### 处理工作流
1. **文件上传与验证** - 格式和数据验证
2. **数据解析** - 提取和验证产品信息
3. **队列处理** - 带限制的异步 API 更新
4. **进度监控** - 实时状态更新
5. **报告生成** - 详细的成功/失败分析

### API 限制策略
```php
// 智能限制实现
foreach ($products as $index => $product) {
    if ($index > 0) {
        sleep(2); // API 调用间隔 2 秒
    }
    $result = $this->lazadaApiService->updateProduct($product);
    // 处理结果并更新进度
}
```

## 🏛️ 项目结构

```
lazada-erp/
├── app/
│   ├── Console/Commands/          # Artisan 命令
│   │   └── RefreshLazadaToken.php # 令牌刷新自动化
│   ├── Http/Controllers/          # 请求处理
│   │   ├── Auth/                 # 认证控制器
│   │   ├── BulkUpdateController.php # 批量操作
│   │   ├── LazadaAuthController.php # Lazada OAuth
│   │   ├── ProductController.php  # 产品管理
│   │   └── OrderController.php    # 订单管理
│   ├── Jobs/                     # 队列作业
│   │   └── ProcessBulkUpdateJob.php # 异步批量处理
│   ├── Models/                   # Eloquent 模型
│   │   ├── Product.php          # 产品模型
│   │   ├── Order.php            # 订单模型
│   │   ├── BulkUpdateTask.php   # 任务跟踪
│   │   └── LazadaToken.php      # 令牌管理
│   ├── Services/                # 业务逻辑
│   │   ├── LazadaApiService.php # Lazada API 集成
│   │   ├── BulkUpdateService.php # 批量操作逻辑
│   │   ├── ExcelProcessingService.php # Excel 处理
│   │   ├── ProductService.php   # 产品业务逻辑
│   │   └── OrderService.php     # 订单业务逻辑
│   └── Middleware/              # 自定义中间件
│       ├── AdminMiddleware.php  # 管理员访问控制
│       └── CheckLazadaToken.php # 令牌验证
├── resources/views/             # Blade 模板
├── database/migrations/         # 数据库架构
├── routes/                     # 路由定义
└── public/templates/           # 文件模板
```

## 🔒 安全功能

### API 安全
- **HMAC-SHA256 签名** - 安全的 API 请求签名
- **令牌管理** - 自动令牌刷新和验证
- **速率限制** - 防止 API 滥用
- **输入验证** - 全面的数据清理

### 应用安全
- **CSRF 保护** - Laravel 内置 CSRF 保护
- **XSS 防护** - Blade 模板转义
- **SQL 注入保护** - Eloquent ORM 参数化查询
- **基于角色的访问** - 管理员中间件和用户权限

## 📊 性能优化

### 数据库优化
- **索引列** - 在 SKU、order_id 等字段上优化查询
- **连接池** - 高效的数据库连接
- **查询优化** - 预加载和查询减少

### 缓存策略
```php
// 频繁访问数据的 Redis 缓存
Cache::remember('products_list', 3600, function () {
    return Product::with('adjustments')->get();
});
```

### 队列性能
- **作业批处理** - 高效的批量处理
- **失败作业处理** - 自动重试机制
- **内存管理** - 针对大数据集的优化

## 🧪 测试

### 运行测试
```bash
# 运行所有测试
composer test

# 运行特定测试类型
php artisan test --filter=Feature
php artisan test --filter=Unit

# 生成覆盖率报告
./vendor/bin/pest --coverage
```

### 测试覆盖率
- **单元测试** - 服务层和业务逻辑
- **功能测试** - API 端点和用户工作流
- **集成测试** - Lazada API 集成
- **浏览器测试** - 端到端用户场景

## 🚀 部署

### 生产配置
```env
APP_ENV=production
APP_DEBUG=false
QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

### 服务器要求
- **Web 服务器** - Nginx 或 Apache 搭配 PHP-FPM
- **PHP 扩展** - mbstring、openssl、PDO、tokenizer、XML、ctype、JSON
- **数据库** - MySQL 8.0+ 搭配 InnoDB 引擎
- **Redis** - 版本 6.0+ 用于缓存和队列
- **SSL 证书** - Lazada API 集成必需

### 部署命令
```bash
# 部署到生产环境
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build

# 启动队列工作进程
php artisan queue:restart
supervisor start laravel-worker
```

## 🔧 配置

### 队列工作进程
```bash
# 队列工作进程的 Supervisor 配置
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
numprocs=8
```

### 计划任务
```bash
# 添加到 crontab 用于自动令牌刷新
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

## 📈 监控与日志

### 日志通道
- **API 日志** - Lazada API 请求/响应跟踪
- **批量更新日志** - 详细的批量操作监控
- **错误日志** - 系统错误跟踪和调试
- **性能日志** - 查询和响应时间监控

### 健康检查
```php
// 监控系统健康状态
php artisan health:check
php artisan queue:monitor
php artisan lazada:token:check
```

## 🤝 贡献

### 开发工作流
1. Fork 仓库
2. 创建功能分支 (`git checkout -b feature/amazing-feature`)
3. 提交变更 (`git commit -m '添加惊人功能'`)
4. 推送到分支 (`git push origin feature/amazing-feature`)
5. 开启 Pull Request

### 代码标准
- **PSR-12** - PHP 编码标准
- **Laravel Pint** - 代码格式化 (`./vendor/bin/pint`)
- **PHPStan** - 静态分析
- **Pest** - 测试框架

## 📞 支持与文档

### 常用命令
```bash
# 开发助手
composer run dev          # 启动所有开发服务
php artisan tinker        # 交互式 shell
php artisan route:list    # 查看所有路由
php artisan config:clear  # 清除配置缓存

# 维护
php artisan down          # 启用维护模式
php artisan up           # 禁用维护模式
php artisan migrate:status # 检查迁移状态
```

### 环境配置
确保您的 `.env` 文件包含所有必需的配置：
```env
# 应用程序
APP_NAME="Lazada ERP"
APP_ENV=local
APP_KEY=base64:...

# 数据库
DB_CONNECTION=mysql
DB_DATABASE=lazada_erp

# Lazada API
LAZADA_APP_KEY=your_app_key
LAZADA_APP_SECRET=your_app_secret

# 队列和缓存
QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1

# 邮件（用于通知）
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
```

## 📄 许可证

本项目基于 [MIT 许可证](https://opensource.org/licenses/MIT) 开源。

## 🎯 开发路线图

### 计划功能
- [ ] **高级分析仪表板** - 销售洞察和性能指标
- [ ] **多平台支持** - Shopee 和其他市场集成
- [ ] **价格管理系统** - 动态定价和竞争对手分析
- [ ] **库存预测** - AI 驱动的库存水平预测
- [ ] **移动应用** - React Native 移动应用程序
- [ ] **API 速率优化** - 高级节流和批处理
- [ ] **实时通知** - 基于 WebSocket 的实时更新
- [ ] **高级报告** - 可定制的报告构建器

### 版本历史
- **v1.0.0** - 核心功能的初始版本
- **v1.1.0** - 增强的批量更新功能
- **v1.2.0** - 改进的 API 集成和错误处理
- **v2.0.0** - Laravel 12 升级和性能优化

---

**使用 Laravel 12.0 和现代 PHP 实践精心开发 ❤️**