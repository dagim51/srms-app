<#
.SYNOPSIS
    SRMS Dedicated Local Testing Workflow Runner for PowerShell.
.DESCRIPTION
    Runs the SRMS test suite locally with support for SQLite (default fast pass) or
    PostgreSQL 15 + PostGIS (via Docker, matching CI), plus Pint and PHPStan pre-flight checks.
.EXAMPLE
    .\scripts\test-local.ps1
    .\scripts\test-local.ps1 -Database pgsql
    .\scripts\test-local.ps1 -Suite unit
    .\scripts\test-local.ps1 -Filter CommissionEngineTest
    .\scripts\test-local.ps1 -Check
#>
param (
    [ValidateSet('sqlite', 'pgsql')]
    [string]$Database = 'sqlite',

    [ValidateSet('all', 'unit', 'feature')]
    [string]$Suite = 'all',

    [string]$Filter = '',

    [switch]$Check,

    [switch]$Fix
)

$ErrorActionPreference = 'Stop'
$startTime = Get-Date

Write-Host "==================================================" -ForegroundColor Cyan
Write-Host "   SRMS Local Test Suite Runner                   " -ForegroundColor Cyan
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host " Target Database: $Database" -ForegroundColor Yellow
Write-Host " Test Suite:      $Suite" -ForegroundColor Yellow
if ($Filter) {
    Write-Host " Filter:          $Filter" -ForegroundColor Yellow
}
Write-Host " Pre-flight Gate: $(if ($Check) { 'Enabled (Pint + PHPStan)' } else { 'Disabled' })" -ForegroundColor Yellow
Write-Host "--------------------------------------------------" -ForegroundColor DarkGray

# 1. Handle PostgreSQL + PostGIS via Docker if requested
if ($Database -eq 'pgsql') {
    Write-Host "`n[1/3] Preparing PostgreSQL 15 + PostGIS container..." -ForegroundColor Cyan
    
    # Check if Docker daemon is accessible
    $dockerCheck = docker ps 2>&1
    if ($LASTEXITCODE -ne 0) {
        Write-Host "ERROR: Docker daemon is not running." -ForegroundColor Red
        Write-Host "Please start Docker Desktop and retry, or use '-Database sqlite' for local testing." -ForegroundColor Yellow
        exit 1
    }

    # Start the test postgres container
    docker compose -f docker-compose.test.yml up -d
    if ($LASTEXITCODE -ne 0) {
        Write-Host "ERROR: Failed to start docker-compose.test.yml" -ForegroundColor Red
        exit 1
    }

    Write-Host "Waiting for PostGIS healthcheck..." -ForegroundColor Gray
    $healthy = $false
    for ($i = 0; $i -lt 15; $i++) {
        $status = docker inspect --format="{{.State.Health.Status}}" srms-postgres-test 2>$null
        if ($status -eq "healthy") {
            $healthy = $true
            break
        }
        Start-Sleep -Seconds 2
    }

    if (-not $healthy) {
        Write-Host "WARNING: PostGIS healthcheck timed out. Attempting connection anyway..." -ForegroundColor Yellow
    }

    # Set environment variables for testing-pgsql
    $env:DB_CONNECTION = "testing-pgsql"
    $env:DB_HOST = "127.0.0.1"
    $env:DB_PORT = "5432"
    $env:DB_DATABASE = "ethio_real_estate_test"
    $env:DB_USERNAME = "postgres"
    $env:DB_PASSWORD = "password"

    Write-Host "Migrating PostgreSQL test database..." -ForegroundColor Gray
    php artisan migrate --force
    if ($LASTEXITCODE -ne 0) {
        Write-Host "ERROR: PostgreSQL migration failed." -ForegroundColor Red
        exit 1
    }
} else {
    # SQLite mode: remove any forced env variables to use phpunit.xml defaults
    Remove-Item Env:\DB_CONNECTION -ErrorAction SilentlyContinue
    Remove-Item Env:\DB_HOST -ErrorAction SilentlyContinue
    Remove-Item Env:\DB_PORT -ErrorAction SilentlyContinue
    Remove-Item Env:\DB_DATABASE -ErrorAction SilentlyContinue
}

# 2. Clear config cache to ensure fresh testing configuration
php artisan config:clear --quiet

# 3. Build PHPUnit command arguments
$testArgs = @("artisan", "test")
if ($Suite -eq 'unit') {
    $testArgs += "--testsuite=Unit"
} elseif ($Suite -eq 'feature') {
    $testArgs += "--testsuite=Feature"
}
if ($Filter) {
    $testArgs += "--filter=$Filter"
}

Write-Host "`n[2/3] Running PHPUnit tests..." -ForegroundColor Cyan
& php $testArgs
$testExitCode = $LASTEXITCODE

# 4. Optional: Run Auto-fix or Pre-flight checks (Pint + PHPStan)
$pintExitCode = 0
$phpstanExitCode = 0

if ($Fix) {
    Write-Host "`nRunning Laravel Pint code auto-formatter..." -ForegroundColor Cyan
    & .\vendor\bin\pint
}

if ($Check) {
    Write-Host "`n[3/3] Running Code Quality & Static Analysis Checks..." -ForegroundColor Cyan

    Write-Host "`n-> Laravel Pint (Code Style):" -ForegroundColor Gray
    & .\vendor\bin\pint --test
    $pintExitCode = $LASTEXITCODE

    Write-Host "`n-> PHPStan (Static Analysis Level 5):" -ForegroundColor Gray
    & .\vendor\bin\phpstan analyse --memory-limit=2G
    $phpstanExitCode = $LASTEXITCODE
}

# 5. Summary Report
$elapsed = (Get-Date) - $startTime
Write-Host "`n==================================================" -ForegroundColor Cyan
Write-Host "   Local Test Workflow Summary                    " -ForegroundColor Cyan
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host " Total Duration: $([Math]::Round($elapsed.TotalSeconds, 2)) seconds" -ForegroundColor Gray

if ($testExitCode -eq 0) {
    Write-Host " PHPUnit Tests:   PASSED" -ForegroundColor Green
} else {
    Write-Host " PHPUnit Tests:   FAILED (Exit code $testExitCode)" -ForegroundColor Red
}

if ($Check) {
    if ($pintExitCode -eq 0) {
        Write-Host " Laravel Pint:    PASSED" -ForegroundColor Green
    } else {
        Write-Host " Laravel Pint:    FAILED (Run '.\vendor\bin\pint' to auto-fix)" -ForegroundColor Red
    }

    if ($phpstanExitCode -eq 0) {
        Write-Host " PHPStan Analysis: PASSED" -ForegroundColor Green
    } else {
        Write-Host " PHPStan Analysis: FAILED" -ForegroundColor Red
    }
}

$overallSuccess = ($testExitCode -eq 0) -and ($pintExitCode -eq 0) -and ($phpstanExitCode -eq 0)
if ($overallSuccess) {
    Write-Host "`nOverall: READY TO COMMIT & PUSH! 🚀" -ForegroundColor Green
    exit 0
} else {
    Write-Host "`nOverall: ONE OR MORE CHECKS FAILED." -ForegroundColor Red
    exit 1
}
