# 任务完成指导原则

## 完成任务后应执行的操作

### 1. 代码质量检查
```bash
# 运行代码格式化
./vendor/bin/pint

# 运行测试
php artisan test
```

### 2. 数据库迁移（如有）
```bash
# 运行新的迁移
php artisan migrate

# 检查迁移状态
php artisan migrate:status
```

### 3. 清理缓存
```bash
# 清理应用缓存
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# 重新缓存（生产环境）
php artisan config:cache
php artisan view:cache
```

### 4. 重启队列（如果修改了队列任务）
```bash
php artisan queue:restart
```

### 5. 前端构建（如果修改了前端资源）
```bash
npm run build  # 生产环境
npm run dev    # 开发环境
```

### 6. 验证功能
- 测试新增或修改的功能
- 检查Lazada API集成是否正常
- 验证批量更新功能
- 确认用户权限和访问控制

### 7. 文档更新
- 更新README.md（如有新功能）
- 更新API文档
- 添加必要的代码注释

### 8. 安全检查
- 验证API密钥配置
- 检查用户输入验证
- 确认权限控制正确