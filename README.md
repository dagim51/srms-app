# Ethiopian Real Estate Marketplace

## Project Overview
An integrated real estate marketplace, short-stay, property management, and development platform.

The application follows a **Modular Domain-Driven Architecture** under `app/Domains/` with a backward-compatible Forwarder Pattern. For detailed architectural guidelines, domain boundaries, and coding standards, refer to [ARCHITECTURE.md](file:///c:/Users/dell/Desktop/SRMS/srms-app/ARCHITECTURE.md).

## Development Setup (Local)
This project uses Laravel and SQLite for local development (or PostgreSQL if preferred).

1. Clone the repository.
2. Install PHP dependencies:
   ```bash
   composer install
   ```
3. Install frontend dependencies:
   ```bash
   npm install
   ```
4. Copy the environment file:
   ```bash
   cp .env.example .env
   ```
5. Generate the application key:
   ```bash
   php artisan key:generate
   ```
6. Run the database migrations (SQLite is default for zero-setup local dev):
   ```bash
   php artisan migrate
   ```
7. Start the development server:
   ```bash
   php artisan serve
   ```
8. In a separate terminal, compile assets:
   ```bash
   npm run dev
   ```

## CI/CD and Code Quality Standards
This project follows strict engineering standards. A GitHub Actions pipeline automatically validates every pull request and push to the `main` branch.

The CI pipeline runs the following checks:
1. **Dependency Installation:** Validates `composer.json` and `package.json`.
2. **Environment Configuration:** Verifies `.env.example` handles all required config.
3. **Database Migrations:** Tests `php artisan migrate` against a PostgreSQL instance.
4. **Automated Tests:** Executes `php artisan test` (PHPUnit/Pest).
5. **Formatting:** Enforces code style using Laravel Pint (`./vendor/bin/pint --test`).
6. **Static Analysis:** Validates types and architecture via Larastan (`./vendor/bin/phpstan analyse`).
7. **Frontend Build:** Ensures assets compile cleanly (`npm run build`).

### Pre-Commit Checklist
Before committing or creating a pull request, please ensure you run:
- `./vendor/bin/pint` to format your code.
- `./vendor/bin/phpstan analyse --memory-limit=2G` to check for type errors.
- `php artisan test` to verify no regressions were introduced.
