# SRMS Production Deployment Guide

## 1. Secrets Management
- Ensure `.env` is NOT committed to version control.
- Use a secure secret manager (e.g., AWS Secrets Manager, HashiCorp Vault) for `APP_KEY`, `DB_PASSWORD`, `STRIPE_SECRET`, etc.
- Set `APP_ENV=production` and `APP_DEBUG=false`.

## 2. Security
- API tokens (`api.token` middleware) are hashed (SHA-256) in the database.
- Database credentials must use restricted privileges (e.g., no DROP TABLE rights for the app user).
- HTTPS/TLS 1.3 must be enforced at the Load Balancer level.
- Rate limiting is configured in `AppServiceProvider`:
  - `api`: 60 requests / minute per user/IP.
  - `webhooks`: 200 requests / minute per IP.

## 3. Performance & Databases
- Critical queries have been optimized by appending targeted indexes (`owner_user_id`, `created_at`, `status`).
- Run `php artisan config:cache`, `php artisan route:cache`, and `php artisan view:cache` during deployment.
- Ensure PostgreSQL configuration is tuned (e.g., `shared_buffers`, `work_mem`).

## 4. Queues
- Change `QUEUE_CONNECTION=sync` to `QUEUE_CONNECTION=redis` in `.env`.
- Use Laravel Horizon for queue monitoring.
- Ensure supervisor is running `php artisan queue:work` daemon.

## 5. Logging and Monitoring
- Log channel should be set to `daily` in `config/logging.php` or shipped to an external APM (Datadog, New Relic) using `stderr`.
- Use health check endpoints (`/api/health`) for Uptime Kuma or AWS Route53.

## 6. Backups
- Database backups must be automated daily using tools like `pg_dump` and stored in an offsite S3 bucket with versioning enabled.
- Verify recovery procedures quarterly.
