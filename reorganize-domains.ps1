#!/usr/bin/env pwsh
# SRMS Domain Reorganization Script
# Moves files to app/Domains/ structure and updates namespaces

param(
    [switch]$DryRun = $false,
    [switch]$Verbose = $false
)

$basePath = "C:\Users\dell\Desktop\SRMS\srms-app"
$mappingPath = Join-Path $basePath "domain-mapping.json"

if (-not (Test-Path $mappingPath)) {
    Write-Error "Mapping file not found at $mappingPath"
    exit 1
}

$mapping = Get-Content $mappingPath | ConvertFrom-Json

function Write-VerboseMsg {
    param([string]$Message)
    if ($Verbose) { Write-Host "  $Message" -ForegroundColor Cyan }
}

function Move-File {
    param(
        [string]$Source,
        [string]$Destination,
        [string[]]$NamespaceReplacements
    )

    if (-not (Test-Path $Source)) {
        Write-Warning "Source not found: $Source"
        return
    }

    $destDir = Split-Path $Destination
    if (-not (Test-Path $destDir)) {
        if (-not $DryRun) {
            New-Item -ItemType Directory -Path $destDir -Force | Out-Null
        }
        Write-VerboseMsg "Created directory: $destDir"
    }

    if (-not $DryRun) {
        Move-Item -Path $Source -Destination $Destination -Force
        Write-VerboseMsg "Moved: $Source -> $Destination"
    } else {
        Write-Host "[DRY RUN] Would move: $Source -> $Destination" -ForegroundColor Yellow
    }

    # Update namespaces in the moved file
    if ($NamespaceReplacements -and $NamespaceReplacements.Count -gt 0) {
        if (-not $DryRun) {
            $content = Get-Content $Destination -Raw
            foreach ($replacement in $NamespaceReplacements) {
                $content = $content -replace $replacement[0], $replacement[1]
            }
            Set-Content $Destination -Value $content
            Write-VerboseMsg "Updated namespaces in: $Destination"
        }
    }
}

function Get-DomainForItem {
    param([string]$Type, [string]$Name)
    
    $mappingProperty = $mapping.$Type
    if (-not $mappingProperty) { return $null }
    
    foreach ($domain in $mappingProperty.PSObject.Properties) {
        if ($domain.Value -contains $Name) {
            return $domain.Name
        }
    }
    return $null
}

# Build namespace replacement mappings
$namespaceMap = @{
    "App\\Models\\" = @{}
    "App\\Http\\Controllers\\Api\\V1\\" = @{}
    "App\\Http\\Controllers\\Web\\" = @{}
    "App\\Http\\Resources\\" = @{}
    "App\\Http\\Requests\\" = @{}
    "App\\Services\\" = @{}
    "App\\Events\\" = @{}
    "App\\Listeners\\" = @{}
    "App\\Policies\\" = @{}
}

# Populate namespace map
foreach ($type in @("models", "controllers", "resources", "requests", "services", "events", "listeners", "policies")) {
    $mappingType = $type.ToLower()
    if ($mapping.$mappingType) {
        foreach ($domain in $mapping.$mappingType.PSObject.Properties) {
            foreach ($item in $domain.Value) {
                $oldNs = ""
                $newNs = "App\\Domains\\$($domain.Name)\\"
                
                switch ($type) {
                    "models" { $oldNs = "App\\Models\\"; $newNs += "Models\\" }
                    "controllers" { 
                        if ($domain.Value -contains "AuthController") { $oldNs = "App\\Http\\Controllers\\Api\\V1\\"; $newNs += "Http\\Controllers\\Api\\V1\\" }
                        else { $oldNs = "App\\Http\\Controllers\\Api\\V1\\"; $newNs += "Http\\Controllers\\Api\\V1\\" }
                    }
                    "resources" { $oldNs = "App\\Http\\Resources\\"; $newNs += "Http\\Resources\\" }
                    "requests" { $oldNs = "App\\Http\\Requests\\"; $newNs += "Http\\Requests\\" }
                    "services" { $oldNs = "App\\Services\\"; $newNs += "Services\\" }
                    "events" { $oldNs = "App\\Events\\"; $newNs += "Events\\" }
                    "listeners" { $oldNs = "App\\Listeners\\"; $newNs += "Listeners\\" }
                    "policies" { $oldNs = "App\\Policies\\"; $newNs += "Policies\\" }
                }
                $namespaceMap[$oldNs][$item] = "$newNs$item"
            }
        }
    }

Write-Host "Starting SRMS Domain Reorganization..." -ForegroundColor Green
Write-Host "Base Path: $basePath" -ForegroundColor Cyan
Write-Host "Dry Run: $DryRun" -ForegroundColor Cyan
Write-Host ""

# Process each type
foreach ($type in @("models", "controllers", "resources", "requests", "services", "events", "listeners", "policies")) {
    $mappingType = $type.ToLower()
    if (-not $mapping.$mappingType) { continue }
    
    Write-Host "Processing $type..." -ForegroundColor Magenta
    
    foreach ($domain in $mapping.$mappingType.PSObject.Properties) {
        $domainName = $domain.Name
        foreach ($item in $domain.Value) {
            $sourcePath = ""
            $destPath = ""
            $oldNs = ""
            $newNs = ""
            
            switch ($type) {
                "models" {
                    $sourcePath = Join-Path $basePath "app\Models\$item.php"
                    $destPath = Join-Path $basePath "app\Domains\$domainName\Models\$item.php"
                    $oldNs = "namespace App\\Models;"
                    $newNs = "namespace App\\Domains\\$domainName\\Models;"
                }
                "controllers" {
                    # Check both Api and Web
                    $apiPath = Join-Path $basePath "app\Http\Controllers\Api\V1\$item.php"
                    $webPath = Join-Path $basePath "app\Http\Controllers\Web\$item.php"
                    if (Test-Path $apiPath) {
                        $sourcePath = $apiPath
                        $destPath = Join-Path $basePath "app\Domains\$domainName\Http\Controllers\Api\V1\$item.php"
                        $oldNs = "namespace App\\Http\\Controllers\\Api\\V1;"
                        $newNs = "namespace App\\Domains\\$domainName\\Http\\Controllers\\Api\\V1;"
                    } elseif (Test-Path $webPath) {
                        $sourcePath = $webPath
                        $destPath = Join-Path $basePath "app\Domains\$domainName\Http\Controllers\Web\$item.php"
                        $oldNs = "namespace App\\Http\Controllers\Web;"
                        $newNs = "namespace App\\Domains\\$domainName\\Http\Controllers\Web;"
                    }
                }
                "resources" {
                    $sourcePath = Join-Path $basePath "app\Http\Resources\$item.php"
                    $destPath = Join-Path $basePath "app\Domains\$domainName\Http\Resources\$item.php"
                    $oldNs = "namespace App\\Http\\Resources;"
                    $newNs = "namespace App\\Domains\\$domainName\\Http\\Resources;"
                }
                "requests" {
                    $sourcePath = Join-Path $basePath "app\Http\Requests\$item.php"
                    $destPath = Join-Path $basePath "app\Domains\$domainName\Http\Requests\$item.php"
                    $oldNs = "namespace App\\Http\\Requests;"
                    $newNs = "namespace App\\Domains\\$domainName\\Http\\Requests;"
                }
                "services" {
                    $sourcePath = Join-Path $basePath "app\Services\$item.php"
                    $destPath = Join-Path $basePath "app\Domains\$domainName\Services\$item.php"
                    $oldNs = "namespace App\Services;"
                    $newNs = "namespace App\Domains\\$domainName\Services;"
                }
                "events" {
                    $sourcePath = Join-Path $basePath "app\Events\$item.php"
                    $destPath = Join-Path $basePath "app\Domains\$domainName\Events\$item.php"
                    $oldNs = "namespace App\Events;"
                    $newNs = "namespace App\Domains\\$domainName\Events;"
                }
                "listeners" {
                    $sourcePath = Join-Path $basePath "app\Listeners\$item.php"
                    $destPath = Join-Path $basePath "app\Domains\$domainName\Listeners\$item.php"
                    $oldNs = "namespace App\Listeners;"
                    $newNs = "namespace App\Domains\\$domainName\Listeners;"
                }
                "policies" {
                    $sourcePath = Join-Path $basePath "app\Policies\$item.php"
                    $destPath = Join-Path $basePath "app\Domains\$domainName\Policies\$item.php"
                    $oldNs = "namespace App\Policies;"
                    $newNs = "namespace App\Domains\\$domainName\Policies;"
                }
            }
            
            if ($sourcePath -and $destPath) {
                $replacements = @(
                    @($oldNs, "namespace App\\Domains\\$domainName\\"),
                    # Update use statements
                    @("use App\\Models\\", "use App\\Domains\\"),
                    @("use App\\Http\\Resources\\", "use App\\Domains\\"),
                    @("use App\\Http\\Requests\\", "use App\\Domains\\"),
                    @("use App\\Services\\", "use App\\Domains\\"),
                    @("use App\\Events\\", "use App\\Domains\\"),
                    @("use App\\Listeners\\", "use App\\Domains\\"),
                    @("use App\\Policies\\", "use App\\Domains\\"),
                    @("use App\\Http\\Controllers\\Api\\V1\\", "use App\\Domains\\"),
                    @("use App\\Http\\Controllers\\Web\\", "use App\\Domains\\"),
                    @("use App\\Http\\Resources\\", "use App\\Domains\\"),
                    @("use App\\Http\\Requests\\", "use App\\Domains\\"),
                    @("use App\\Services\\", "use App\\Domains\\"),
                    @("use App\\Events\\", "use App\\Domains\\"),
                    @("use App\\Listeners\\", "use App\\Domains\\"),
                    @("use App\\Policies\\", "use App\\Domains\\")
                )
                
                Move-File -Source $sourcePath -Destination $destPath -NamespaceReplacements $replacements
            }
        }
    }
}

Write-Host "Domain reorganization complete!" -ForegroundColor Green