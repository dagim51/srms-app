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

No additional agent packages are required. The project uses standard Laravel 11.x with custom services and traits.

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

## UI & Frontend Design Authority

For all UI/UX, styling, layout, typography, component design, and frontend interface tasks, exclusively apply the **Frontend Design** (`frontend-design`) skill:
- **Bespoke Aesthetics**: Design directly for the product domain without defaulting to generic AI templates.
- **Intentional Typography**: Deliberate type scale, appropriate weights, line length under 80 characters.
- **Curated Color Tokens**: Tight, domain-specific palettes with 4–6 named hex values.
- **Restrained Motion**: Subtle, user-triggered state transitions only; no perpetual or blanket hover transitions.
- **Intentional Copy**: Clear, conversational active voice with plain verbs.