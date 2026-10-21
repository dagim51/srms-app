# SRMS Production Redis Configuration

## Recommended: Managed Redis (ElastiCache / Azure Cache / GCP Memorystore)

### Environment Variables
```bash
# Production Redis (ElastiCache example)
REDIS_HOST=srms-prod.xxxxxx.use1.cache.amazonaws.com
REDIS_PASSWORD=secure_redis_password_here
REDIS_PORT=6379
REDIS_CLIENT=phpredis
REDIS_CLUSTER=redis
REDIS_PREFIX=srms-prod-database-
REDIS_PERSISTENT=false
REDIS_DB=0
REDIS_CACHE_DB=1

# Session & Cache
SESSION_DRIVER=redis
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_DOMAIN=.srms.et
CACHE_STORE=redis
CACHE_PREFIX=srms_prod_

# Queue
QUEUE_CONNECTION=redis
```

### Connection Security
- **Encryption in Transit**: Enable TLS (ElastiCache in-transit encryption)
- **Authentication**: Use AUTH with strong password
- **Network**: Place in private subnet, security group allows only app servers
- **Backup**: Enable automatic snapshots (daily, 7-day retention)

### PHPRedis Configuration (config/database.php)
```php
'redis' => [
    'client' => env('REDIS_CLIENT', 'phpredis'),
    'options' => [
        'cluster' => env('REDIS_CLUSTER', 'redis'),
        'prefix' => env('REDIS_PREFIX', 'srms-prod-database-'),
        'persistent' => env('REDIS_PERSISTENT', false),
    ],
    'default' => [
        'url' => env('REDIS_URL'),
        'host' => env('REDIS_HOST'),
        'username' => env('REDIS_USERNAME'),
        'password' => env('REDIS_PASSWORD'),
        'port' => env('REDIS_PORT', '6379'),
        'database' => env('REDIS_DB', '0'),
        'max_retries' => env('REDIS_MAX_RETRIES', 3),
        'backoff_algorithm' => env('REDIS_BACKOFF_ALGORITHM', 'decorrelated_jitter'),
        'backoff_base' => env('REDIS_BACKOFF_BASE', 100),
        'backoff_cap' => env('REDIS_BACKOFF_CAP', 1000),
    ],
    'cache' => [
        // ... separate cache DB
        'database' => env('REDIS_CACHE_DB', '1'),
    ],
],
```

### Queue Worker Tuning (High Volume)
```bash
# High volume: more workers, shorter sleep
php artisan queue:work redis --sleep=1 --tries=3 --max-time=3600 --memory=512

# With horizon (if installed)
php artisan horizon
```

### Health Checks
```bash
# Test connection
redis-cli -h $REDIS_HOST -p $REDIS_PORT -a $REDIS_PASSWORD ping

# Monitor queue
redis-cli -h $REDIS_HOST -p $REDIS_PORT -a $REDIS_PASSWORD llen queues:default

# Monitor memory
redis-cli -h $REDIS_HOST -p $REDIS_PORT -a $REDIS_PASSWORD info memory
```