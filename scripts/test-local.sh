#!/usr/bin/env bash
# ==============================================================================
# SRMS Dedicated Local Testing Workflow Runner for Bash / Linux / macOS / WSL
# ==============================================================================
# Usage:
#   ./scripts/test-local.sh                    # Fast local test with SQLite
#   ./scripts/test-local.sh --database=pgsql   # Test with PostgreSQL 15 + PostGIS (Docker)
#   ./scripts/test-local.sh --suite=unit       # Run only Unit tests
#   ./scripts/test-local.sh --filter=CommissionEngineTest
#   ./scripts/test-local.sh --check            # Full pre-commit check (Tests + Pint + PHPStan)
#   ./scripts/test-local.sh --fix              # Auto-format with Pint before testing
# ==============================================================================

set -e

DATABASE="sqlite"
SUITE="all"
FILTER=""
CHECK=false
FIX=false

for arg in "$@"; do
  case $arg in
    --database=*)
      DATABASE="${arg#*=}"
      ;;
    --suite=*)
      SUITE="${arg#*=}"
      ;;
    --filter=*)
      FILTER="${arg#*=}"
      ;;
    --check)
      CHECK=true
      ;;
    --fix)
      FIX=true
      ;;
    *)
      ;;
  esac
done

echo "=================================================="
echo "   SRMS Local Test Suite Runner (Bash)            "
echo "=================================================="
echo " Target Database: $DATABASE"
echo " Test Suite:      $SUITE"
[ -n "$FILTER" ] && echo " Filter:          $FILTER"
echo " Pre-flight Gate: $CHECK"
echo "--------------------------------------------------"

if [ "$DATABASE" = "pgsql" ]; then
    echo -e "\n[1/3] Preparing PostgreSQL 15 + PostGIS container..."
    if ! docker ps > /dev/null 2>&1; then
        echo "ERROR: Docker daemon is not running."
        echo "Please start Docker and retry, or use '--database=sqlite' for local testing."
        exit 1
    fi

    docker compose -f docker-compose.test.yml up -d

    echo "Waiting for PostGIS healthcheck..."
    for i in {1..15}; do
        STATUS=$(docker inspect --format="{{.State.Health.Status}}" srms-postgres-test 2>/dev/null || true)
        if [ "$STATUS" = "healthy" ]; then
            break
        fi
        sleep 2
    done

    export DB_CONNECTION=testing-pgsql
    export DB_HOST=127.0.0.1
    export DB_PORT=5432
    export DB_DATABASE=ethio_real_estate_test
    export DB_USERNAME=postgres
    export DB_PASSWORD=password

    echo "Migrating PostgreSQL test database..."
    php artisan migrate --force
else
    unset DB_CONNECTION DB_HOST DB_PORT DB_DATABASE DB_USERNAME DB_PASSWORD
fi

# Clear config cache
php artisan config:clear --quiet

TEST_ARGS=()
if [ "$SUITE" = "unit" ]; then
    TEST_ARGS+=("--testsuite=Unit")
elif [ "$SUITE" = "feature" ]; then
    TEST_ARGS+=("--testsuite=Feature")
fi

if [ -n "$FILTER" ]; then
    TEST_ARGS+=("--filter=$FILTER")
fi

echo -e "\n[2/3] Running PHPUnit tests..."
php artisan test "${TEST_ARGS[@]}"

if [ "$FIX" = true ]; then
    echo -e "\nRunning Laravel Pint code auto-formatter..."
    ./vendor/bin/pint
fi

if [ "$CHECK" = true ]; then
    echo -e "\n[3/3] Running Code Quality & Static Analysis Checks..."
    echo -e "\n-> Laravel Pint (Code Style):"
    ./vendor/bin/pint --test

    echo -e "\n-> PHPStan (Static Analysis Level 5):"
    ./vendor/bin/phpstan analyse --memory-limit=2G
fi

echo -e "\n=================================================="
echo "   All Local Test Checks Succeeded! 🚀            "
echo "=================================================="
