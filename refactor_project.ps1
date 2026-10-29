<#
.SYNOPSIS
  Refactor OnlySport project structure into clean frontend (mobile_app) and backend separation.
#>

$ErrorActionPreference = "Stop"
$rootDir = $PSScriptRoot
Write-Host "Refactoring OnlySport project at: $rootDir" -ForegroundColor Cyan

# 1. Ensure target directories exist
$backendDir = Join-Path $rootDir "backend"
$mobileDir = Join-Path $rootDir "mobile_app"

if (-not (Test-Path $backendDir)) {
    New-Item -ItemType Directory -Path $backendDir -Force | Out-Null
}
if (-not (Test-Path $mobileDir)) {
    New-Item -ItemType Directory -Path $mobileDir -Force | Out-Null
}

# 2. Clean up cache and build directories
Write-Host "`n[1/4] Cleaning up cache and build directories..." -ForegroundColor Yellow
$foldersToClean = @(".dart_tool", "build", ".idea")
foreach ($folder in $foldersToClean) {
    $targetPath = Join-Path $rootDir $folder
    if (Test-Path $targetPath) {
        Write-Host "  Removing $folder..."
        Remove-Item -Path $targetPath -Recurse -Force -ErrorAction SilentlyContinue
    }
}

# 3. Move Backend files
Write-Host "`n[2/4] Moving backend files to backend/..." -ForegroundColor Yellow
$adminSource = Join-Path $rootDir "admin"
$adminTarget = Join-Path $backendDir "admin"
if (Test-Path $adminSource) {
    if (-not (Test-Path $adminTarget)) {
        Move-Item -Path $adminSource -Destination $adminTarget -Force
        Write-Host "  Moved admin/ -> backend/admin/" -ForegroundColor Green
    } else {
        Write-Host "  backend/admin/ already exists. Copying contents..."
        Copy-Item -Path "$adminSource\*" -Destination $adminTarget -Recurse -Force
        Remove-Item -Path $adminSource -Recurse -Force
    }
}

# Remove root docker files if backend equivalents exist
$rootDockerFile = Join-Path $rootDir "Dockerfile"
if (Test-Path $rootDockerFile) {
    Remove-Item -Path $rootDockerFile -Force
    Write-Host "  Cleaned up root Dockerfile (now located at backend/Dockerfile)" -ForegroundColor Green
}
$rootCompose = Join-Path $rootDir "docker-compose.yml"
if (Test-Path $rootCompose) {
    Remove-Item -Path $rootCompose -Force
    Write-Host "  Cleaned up root docker-compose.yml (now located at backend/docker-compose.yml)" -ForegroundColor Green
}

# 4. Move Mobile App (Flutter) files
Write-Host "`n[3/4] Moving Flutter files to mobile_app/..." -ForegroundColor Yellow
$flutterItems = @(
    "lib",
    "android",
    "web",
    "pubspec.yaml",
    "pubspec.lock",
    "analysis_options.yaml",
    ".metadata",
    "onlysport_app.iml"
)

foreach ($item in $flutterItems) {
    $source = Join-Path $rootDir $item
    $dest = Join-Path $mobileDir $item
    if (Test-Path $source) {
        if (Test-Path $dest) {
            Remove-Item -Path $dest -Recurse -Force -ErrorAction SilentlyContinue
        }
        Move-Item -Path $source -Destination $dest -Force
        Write-Host "  Moved $item -> mobile_app/$item" -ForegroundColor Green
    }
}

# 5. Summary
Write-Host "`n[4/4] Project refactoring completed successfully!" -ForegroundColor Cyan
Write-Host @"
==================================================
New Directory Structure:
DB/
├── backend/
│   ├── admin/             # PHP dashboard, API endpoints, CSS, .htaccess
│   ├── Dockerfile         # Apache + PHP 8.2 Docker configuration
│   ├── docker-compose.yml # MySQL 8.4, phpMyAdmin, and Apache web service
│   ├── database_schema.sql# Database tables initialization
│   └── seed_products.sql  # 68 soccer cleat seed products
│
├── mobile_app/
│   ├── lib/               # Flutter source code (models, screens, services)
│   ├── android/           # Android native platform project
│   ├── web/               # Flutter web runner
│   ├── pubspec.yaml       # Flutter dependencies
│   └── analysis_options.yaml
│
├── .gitignore             # Root gitignore updated for backend & mobile_app
└── README.md
==================================================

How to run Backend:
  cd backend
  docker compose up -d

How to run Mobile App:
  cd mobile_app
  flutter pub get
  flutter run
"@ -ForegroundColor White
