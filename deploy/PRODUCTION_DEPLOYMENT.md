# SRMS Production Deployment Guide

## Overview
This guide covers production deployment for SRMS (Ethiopian Integrated Real Estate Marketplace).

## Architecture
- **App Server**: PHP 8.3+ with Nginx + PHP-FPM
- **Database**: PostgreSQL 15 + PostGIS 3.5 (managed: RDS/Aurora/Cloud SQL)
- **Cache/Queue**: Redis (ElastiCache / Azure Cache / GCP Memorystore)
- **Search**: PostgreSQL full-text + PostGIS
- **Storage**: S3-compatible (MinIO / AWS S3 / Wasabi)
- **Mail**: Failover (SMTP primary + log fallback)
- **SSL**: Let's Encrypt / ACM / Cloudflare

---

## Server Requirements
- **OS**: Ubuntu 22.04 LTS / 24.04 LTS
- **PHP**: 8.3+ with extensions: pgsql, redis, gd, bcmath, intl, zip, imagick
- **Nginx**: 1.24+
- **Supervisor**: For queue workers
- **Certbot**: For Let's Encrypt SSL

---

## Environment Setup

### 1. Clone & Install
```bash
git clone <repo> /var/www/srms
cd /var/www/srms
composer install --no-dev --optimize-autoloader --no-interaction
npm ci && npm run build
```

### 2. Environment Configuration
```bash
cp .env.example .env
# Edit .env with production values (see .env.example production section)
php artisan key:generate --force
```

### 3. Database
```bash
php artisan migrate --force --seed
```

### 3. Storage Link
```bash
php artisan storage:link
```

### 4. Permissions
```bash
chown -R www-data:www-data /var/www/srms/storage /var/www/srms/bootstrap/cache
chmod -R 775 /var/www/srms/storage /var/www/srms/bootstrap/cache
```

---

## Nginx Configuration
```nginx
server {
    listen 80;
    server_name srms.et www.srms.et;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name srms.et www.srms.et;
    root /var/www/srms/public;

    ssl_certificate /etc/letsencrypt/live/srms.et/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/srms.et/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";
    add_header Referrer-Policy "strict-origin-when-cross-origin";
    add_header Content-Security-Policy "default-src 'self' https: data: 'unsafe-inline' 'unsafe-eval';";
}
```

---

## Supervisor (Queue Workers)

### Install
```bash
sudo apt-get install -y supervisor
```

### Configuration
Copy `deploy/supervisor/srms-queue-worker.conf` to `/etc/supervisor/conf.d/srms-queue-worker.conf`

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl status srms-queue-worker:*
```

### Worker Tuning
| Environment | Workers | Sleep | Memory | Tries |
|-------------|---------|-------|--------|-------|
| Low volume  | 2       | 3s    | 256MB  | 3     |
| Medium      | 4       | 3s    | 256MB  | 3     |
| High        | 8       | 1s    | 512MB  | 3     |

---

## Redis Production (ElastiCache)

### Setup
1. Create ElastiCache Redis cluster (cluster mode disabled)
2. Enable: In-transit encryption, At-rest encryption, Auth token
3. Security group: Allow app servers on port 6379
4. Subnet group: Private subnets only

### Environment
```bash
REDIS_HOST=srms-prod.xxxxxx.use1.cache.amazonaws.com
REDIS_PASSWORD=secure_password
REDIS_PORT=6379
REDIS_CLIENT=phpredis
REDIS_PREFIX=srms-prod-database-
REDIS_PERSISTENT=false
REDIS_DB=0
REDIS_CACHE_DB=1
```

---

## Mail (Failover Strategy)

### Configuration
```bash
MAIL_MAILER=failover
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=your-mailgun-user
MAIL_PASSWORD=your-mailgun-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@srms.et"
MAIL_FROM_NAME="SRMS"
```

### Failover Behavior
- Primary: SMTP (Mailgun/SendGrid/SES)
- Fallback: Log (captured in logs for debugging)
- Timeout: 60 seconds per attempt

---

## SSL Certificate
```bash
sudo certbot --nginx -d srms.et -d www.srms.et
# Auto-renewal: certbot renew --dry-run
```

---

## Scheduler (Cron)
```bash
* * * * * cd /var/www/srms && php artisan schedule:run >> /dev/null 2>&1
```
Or use Supervisor: `srms-schedule` in supervisor config.

---

## Health Checks
```bash
# App
curl -f https://srms.et/health

# Queue
redis-cli -h $REDIS_HOST -p $REDIS_PORT -a $REDIS_PASSWORD llen queues:default

# Database
php artisan db:monitor
```

---

## Backup Strategy
- **Database**: Automated daily snapshots (7-day retention) + point-in-time recovery
- **Redis**: ElastiCache automatic snapshots (daily, 7-day)
- **Storage**: S3 versioning + cross-region replication
- **Code**: Git tags + container images

---

## Monitoring
- **APM**: Laravel Telescope (dev) / Sentry / DataDog (prod)
- **Logs**: Laravel Log / CloudWatch / Loki
- **Metrics**: Prometheus + Grafana (queue depth, job duration, error rate)
- **Uptime**: Pingdom / UptimeRobot / CloudWatch Synthetics

---

## Rollback Procedure
```bash
# Database
php artisan migrate:rollback --step=1 --force

# Code
git checkout <previous-tag>
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
```

---

## Security Checklist
- [ ] APP_DEBUG=false
- [ ] APP_ENV=production
- [ ] HTTPS enforced (HSTS)
- [ ] Secure cookies (SESSION_SECURE_COOKIE=true)
- [ ] CSP headers configured
- [ ] Rate limiting enabled
- [ ] WAF rules (Cloudflare / AWS WAF)
- [ ] Database backups encrypted
- [ ] Secrets in vault (not .env)
- [ ] Regular security updates