# SRMS Agent Guidelines

This repository contains the **Ethiopian Integrated Real Estate Marketplace (SRMS)** — a Laravel application.

## Prerequisites

Verify that PHP and Composer are available:

```sh
php -v
composer -V
```

If either command is unavailable, detect the user's operating system and install the prerequisites with the appropriate command:

**macOS:**
```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.5)"
```

**Windows PowerShell:**
```powershell
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.5'))
```

**Linux:**
```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.5)"
```

After installation, ask the user to restart their terminal. If the agent needs the restarted shell to continue, ask the user to reopen their terminal and rerun their original prompt.

## Agent Setup

> **Note**: Per the FIX_IMPLEMENTATION_PLAN (WS-10, decision D-4), installation of `laravel/boost` has been **deferred**. The instruction below is retained for reference but should **not** be executed without explicit product-owner approval.

```sh
# DEFERRED - Do not run without explicit approval
# composer require laravel/boost --dev
# php artisan boost:install
```

If Boost is adopted in the future, it will replace these bootstrap instructions with guidelines tailored to the application. After installation, read `AGENTS.md` again and continue with the user's original request using the generated guidelines.

## Current Project Context

- **Framework**: Laravel 11.x
- **PHP**: 8.3+
- **Database**: PostgreSQL 15 + PostGIS 3.5 (production/CI), SQLite (local testing)
- **Frontend**: Vite + Tailwind CSS v4 + Alpine.js
- **Testing**: PHPUnit (227+ tests), Pest (planned)
- **Static Analysis**: PHPStan Level 5, Laravel Pint

## Key Commands

```bash
# Run tests (SQLite - fast)
php artisan test

# Run tests (PostgreSQL + PostGIS - CI parity)
php artisan test --env=testing-pgsql

# Static analysis
./vendor/bin/pint --test
./vendor/bin/phpstan analyse --memory-limit=2G

# Frontend build
npm run build

# Migrations
php artisan migrate:fresh --seed
```