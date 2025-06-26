# 推荐的系统命令

## 开发命令
```bash
# 安装依赖
composer install
npm install

# 启动开发环境
composer run dev  # 启动所有服务（推荐）
# 或分别启动：
php artisan serve       # Web服务器
php artisan queue:work  # 队列处理器
npm run dev            # 前端构建
php artisan pail       # 日志监控

# 数据库相关
php artisan migrate
php artisan migrate:refresh
php artisan db:seed

# 生成密钥
php artisan key:generate
```

## 生产环境命令
```bash
# 构建生产版本
npm run build

# 队列管理
php artisan queue:work --daemon
php artisan queue:restart
php artisan queue:failed
php artisan queue:retry all

# 缓存管理
php artisan config:cache
php artisan view:cache
php artisan route:cache
```

## 测试命令
```bash
# 运行测试
composer test
php artisan test

# 代码格式化和检查
./vendor/bin/pint  # Laravel Pint 代码格式化
```

## 系统工具命令 (Windows)
```cmd
# 文件操作
dir           # 列出目录内容
type filename # 查看文件内容
find "text"   # 搜索文本

# 进程管理
tasklist     # 查看进程
taskkill /PID <pid>  # 结束进程

# 网络
netstat -an  # 查看网络连接
```