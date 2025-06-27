# Lazada ERP System

A comprehensive Laravel-based e-commerce management system designed specifically for Lazada platform integration. Built with modern PHP 8.2 and Laravel 12.0, featuring advanced bulk operations, real-time synchronization, and intelligent API management.

## 🚀 Key Features

### 📦 Product Management
- **Real-time Product Sync** - Automatic synchronization with Lazada platform
- **Inventory Management** - Track and adjust stock levels with audit trails
- **Bulk Title Updates** - Mass update product titles via Excel import with API rate limiting
- **Product Details** - Comprehensive product information and history tracking

### 🛒 Order Management
- **Order Synchronization** - Automatic order data sync from Lazada
- **Status Management** - Update and track order status changes
- **Order Analytics** - Detailed order information and reporting
- **Multi-status Support** - Handle various order states efficiently

### 🔧 System Administration
- **Lazada API Configuration** - Secure API credential management
- **Token Management** - Automated token refresh and validation
- **User Role Management** - Admin and user access control
- **System Settings** - Flexible configuration management

### ⚡ Advanced Bulk Operations
- **Excel File Processing** - Support for .xlsx, .xls, and .csv formats
- **Data Validation** - Comprehensive SKU and product data validation
- **API Rate Limiting** - Intelligent API call throttling (1-2 requests/second)
- **Asynchronous Processing** - Queue-based processing for large datasets
- **Progress Tracking** - Real-time progress monitoring and reporting
- **Error Handling** - Robust error recovery and detailed logging
- **Report Generation** - Downloadable detailed operation reports

## 🏗️ Technical Architecture

### Technology Stack
- **Backend**: PHP 8.2+ with Laravel 12.0
- **Frontend**: TailwindCSS + Alpine.js + Vite
- **Database**: MySQL/SQLite with optimized indexing
- **Queue System**: Redis (recommended) / Database Queue
- **API Integration**: Guzzle HTTP Client with Lazada Open API
- **Excel Processing**: PhpSpreadsheet with memory optimization
- **Authentication**: Laravel Breeze with role-based access

### Architecture Highlights
- **Service Layer Pattern** - Clean separation of business logic
- **Repository Pattern** - Abstracted data access layer
- **Event-Driven Architecture** - Decoupled system components
- **API-First Design** - RESTful API endpoints
- **Queue-Based Processing** - Scalable background job handling
- **Comprehensive Logging** - Detailed operation tracking

## 🛠️ Installation & Setup

### Prerequisites
- PHP 8.2 or higher
- Composer 2.x
- Node.js 18+ and NPM
- MySQL 8.0+ or SQLite
- Redis Server (recommended for queue processing)

### Installation Steps

1. **Clone the Repository**
   ```bash
   git clone <repository-url>
   cd lazada-erp
   ```

2. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment Configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database Configuration**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=lazada_erp
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Lazada API Configuration**
   ```env
   LAZADA_APP_KEY=your_app_key
   LAZADA_APP_SECRET=your_app_secret
   ```

6. **Queue Configuration**
   ```env
   QUEUE_CONNECTION=redis
   REDIS_HOST=127.0.0.1
   REDIS_PORT=6379
   ```

7. **Database Migration**
   ```bash
   php artisan migrate
   php artisan db:seed --class=AdminUserSeeder
   ```

8. **Start Services**
   ```bash
   # Start all services with one command
   composer run dev
   
   # Or start individually:
   php artisan serve          # Web server (http://localhost:8000)
   php artisan queue:work     # Queue processor
   npm run dev               # Frontend build (hot reload)
   php artisan pail          # Real-time logs
   ```

## 📚 API Documentation

### Lazada API Integration
The system integrates with the following Lazada API endpoints:
- `/auth/token/create` - Access token generation
- `/auth/token/refresh` - Token refresh
- `/products/get` - Product data retrieval
- `/product/update` - Product information updates
- `/orders/get` - Order data synchronization
- `/order/items/get` - Order item details

### Internal API Endpoints
- `POST /bulk-update/upload` - Excel file upload
- `POST /bulk-update/execute/{taskId}` - Execute bulk update task
- `GET /bulk-update/status/{taskId}` - Task status monitoring
- `GET /bulk-update/download-report/{taskId}` - Download operation report

### Authentication Flow
```php
// 1. Redirect to Lazada authorization
$authUrl = $lazadaService->getAuthorizationUrl();

// 2. Handle callback and exchange code for token
$tokenData = $lazadaService->getAccessToken($authCode);

// 3. Save token for future API calls
$lazadaService->saveToken($tokenData);
```

## 🔄 Bulk Update Process

### Excel File Format
Download template: `/templates/product_title_update_template.csv`

Required columns:
- `SKU` - Product SKU (numeric)
- `Product Title` - New product title (max 255 characters)

### Processing Workflow
1. **File Upload & Validation** - Format and data validation
2. **Data Parsing** - Extract and validate product information
3. **Queue Processing** - Asynchronous API updates with rate limiting
4. **Progress Monitoring** - Real-time status updates
5. **Report Generation** - Detailed success/failure analysis

### API Rate Limiting Strategy
```php
// Intelligent rate limiting implementation
foreach ($products as $index => $product) {
    if ($index > 0) {
        sleep(2); // 2-second delay between API calls
    }
    $result = $this->lazadaApiService->updateProduct($product);
    // Process result and update progress
}
```

## 🏛️ Project Structure

```
lazada-erp/
├── app/
│   ├── Console/Commands/          # Artisan commands
│   │   └── RefreshLazadaToken.php # Token refresh automation
│   ├── Http/Controllers/          # Request handling
│   │   ├── Auth/                 # Authentication controllers
│   │   ├── BulkUpdateController.php # Bulk operations
│   │   ├── LazadaAuthController.php # Lazada OAuth
│   │   ├── ProductController.php  # Product management
│   │   └── OrderController.php    # Order management
│   ├── Jobs/                     # Queue jobs
│   │   └── ProcessBulkUpdateJob.php # Async bulk processing
│   ├── Models/                   # Eloquent models
│   │   ├── Product.php          # Product model
│   │   ├── Order.php            # Order model
│   │   ├── BulkUpdateTask.php   # Task tracking
│   │   └── LazadaToken.php      # Token management
│   ├── Services/                # Business logic
│   │   ├── LazadaApiService.php # Lazada API integration
│   │   ├── BulkUpdateService.php # Bulk operations logic
│   │   ├── ExcelProcessingService.php # Excel handling
│   │   ├── ProductService.php   # Product business logic
│   │   └── OrderService.php     # Order business logic
│   └── Middleware/              # Custom middleware
│       ├── AdminMiddleware.php  # Admin access control
│       └── CheckLazadaToken.php # Token validation
├── resources/views/             # Blade templates
├── database/migrations/         # Database schema
├── routes/                     # Route definitions
└── public/templates/           # File templates
```

## 🔒 Security Features

### API Security
- **HMAC-SHA256 Signature** - Secure API request signing
- **Token Management** - Automatic token refresh and validation
- **Rate Limiting** - API abuse prevention
- **Input Validation** - Comprehensive data sanitization

### Application Security
- **CSRF Protection** - Laravel's built-in CSRF protection
- **XSS Prevention** - Blade template escaping
- **SQL Injection Protection** - Eloquent ORM parameterized queries
- **Role-Based Access** - Admin middleware and user permissions

## 📊 Performance Optimizations

### Database Optimizations
- **Indexed Columns** - Optimized queries on SKU, order_id, etc.
- **Connection Pooling** - Efficient database connections
- **Query Optimization** - Eager loading and query reduction

### Caching Strategy
```php
// Redis caching for frequently accessed data
Cache::remember('products_list', 3600, function () {
    return Product::with('adjustments')->get();
});
```

### Queue Performance
- **Job Batching** - Efficient bulk processing
- **Failed Job Handling** - Automatic retry mechanisms
- **Memory Management** - Optimized for large datasets

## 🧪 Testing

### Running Tests
```bash
# Run all tests
composer test

# Run specific test types
php artisan test --filter=Feature
php artisan test --filter=Unit

# Generate coverage report
./vendor/bin/pest --coverage
```

### Test Coverage
- **Unit Tests** - Service layer and business logic
- **Feature Tests** - API endpoints and user workflows
- **Integration Tests** - Lazada API integration
- **Browser Tests** - End-to-end user scenarios

## 🚀 Deployment

### Production Configuration
```env
APP_ENV=production
APP_DEBUG=false
QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

### Server Requirements
- **Web Server** - Nginx or Apache with PHP-FPM
- **PHP Extensions** - mbstring, openssl, PDO, tokenizer, XML, ctype, JSON
- **Database** - MySQL 8.0+ with InnoDB engine
- **Redis** - Version 6.0+ for caching and queues
- **SSL Certificate** - Required for Lazada API integration

### Deployment Commands
```bash
# Deploy to production
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build

# Start queue workers
php artisan queue:restart
supervisor start laravel-worker
```

## 🔧 Configuration

### Queue Workers
```bash
# Supervisor configuration for queue workers
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
numprocs=8
```

### Scheduled Tasks
```bash
# Add to crontab for automated token refresh
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

## 📈 Monitoring & Logging

### Log Channels
- **API Logs** - Lazada API request/response tracking
- **Bulk Update Logs** - Detailed bulk operation monitoring
- **Error Logs** - System error tracking and debugging
- **Performance Logs** - Query and response time monitoring

### Health Checks
```php
// Monitor system health
php artisan health:check
php artisan queue:monitor
php artisan lazada:token:check
```

## 🤝 Contributing

### Development Workflow
1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Code Standards
- **PSR-12** - PHP coding standards
- **Laravel Pint** - Code formatting (`./vendor/bin/pint`)
- **PHPStan** - Static analysis
- **Pest** - Testing framework

## 📞 Support & Documentation

### Useful Commands
```bash
# Development helpers
composer run dev          # Start all development services
php artisan tinker        # Interactive shell
php artisan route:list    # View all routes
php artisan config:clear  # Clear configuration cache

# Maintenance
php artisan down          # Enable maintenance mode
php artisan up           # Disable maintenance mode
php artisan migrate:status # Check migration status
```

### Environment Configuration
Ensure your `.env` file includes all required configuration:
```env
# Application
APP_NAME="Lazada ERP"
APP_ENV=local
APP_KEY=base64:...

# Database
DB_CONNECTION=mysql
DB_DATABASE=lazada_erp

# Lazada API
LAZADA_APP_KEY=your_app_key
LAZADA_APP_SECRET=your_app_secret

# Queue & Cache
QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1

# Mail (for notifications)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
```

## 📄 License

This project is licensed under the [MIT License](https://opensource.org/licenses/MIT).

## 🎯 Roadmap

### Planned Features
- [ ] **Advanced Analytics Dashboard** - Sales insights and performance metrics
- [ ] **Multi-platform Support** - Shopee and other marketplace integrations
- [ ] **Price Management System** - Dynamic pricing and competitor analysis
- [ ] **Inventory Forecasting** - AI-powered stock level predictions
- [ ] **Mobile App** - React Native mobile application
- [ ] **API Rate Optimization** - Advanced throttling and batching
- [ ] **Real-time Notifications** - WebSocket-based live updates
- [ ] **Advanced Reporting** - Customizable report builder

### Version History
- **v1.0.0** - Initial release with core functionality
- **v1.1.0** - Enhanced bulk update features
- **v1.2.0** - Improved API integration and error handling
- **v2.0.0** - Laravel 12 upgrade and performance optimizations

---

**Developed with ❤️ using Laravel 12.0 and modern PHP practices**