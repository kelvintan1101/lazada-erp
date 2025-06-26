# 代码风格和约定

## PHP代码风格
- 遵循PSR-4自动加载标准
- 使用Laravel Pint进行代码格式化
- 类命名采用PascalCase（如：LazadaApiService）
- 方法名采用camelCase（如：getAccessToken）
- 常量采用SNAKE_CASE（如：LAZADA_APP_KEY）

## Laravel约定
- 控制器后缀：Controller（如：ProductController）
- 模型单数命名（如：Product, Order）
- 服务类后缀：Service（如：LazadaApiService）
- 任务类后缀：Job（如：ProcessBulkUpdateJob）
- 中间件后缀：Middleware（如：CheckLazadaToken）

## 数据库约定
- 表名使用复数蛇形命名（如：bulk_update_tasks）
- 外键命名：{表名}_id（如：user_id）
- 时间戳字段：created_at, updated_at

## 前端约定
- 使用TailwindCSS进行样式设计
- Alpine.js处理交互逻辑
- Blade模板引擎
- 组件化设计（components目录）

## API设计
- RESTful API设计原则
- JSON响应格式
- 适当的HTTP状态码
- API限制和错误处理