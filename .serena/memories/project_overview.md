# Lazada ERP 系统项目概览

## 项目介绍
这是一个基于Laravel 12框架开发的Lazada电商管理系统，主要用于管理Lazada平台的产品、订单和进行批量操作。

## 技术栈
- **后端**: PHP 8.2+, Laravel 12.0
- **前端**: TailwindCSS, Alpine.js, Vite
- **数据库**: MySQL/SQLite
- **队列**: Redis (推荐) / Database
- **API集成**: Guzzle HTTP客户端，集成Lazada Open API
- **Excel处理**: PhpSpreadsheet

## 主要功能模块
1. **用户认证系统** - Laravel Breeze集成
2. **产品管理** - 同步、查看、库存调整
3. **订单管理** - 同步、状态更新、详情查看
4. **批量更新** - Excel文件批量更新产品标题
5. **系统设置** - Lazada API配置
6. **库存调整** - 库存变更记录和管理

## 核心服务类
- LazadaApiService - Lazada API集成
- BulkUpdateService - 批量更新业务逻辑
- ExcelProcessingService - Excel文件处理
- OrderService - 订单业务逻辑
- ProductService - 产品业务逻辑