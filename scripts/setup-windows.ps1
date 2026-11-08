<#
  Luxury Store - preparacion de una maquina nueva (Windows + Laragon)

  Uso:
    powershell -ExecutionPolicy Bypass -File .\scripts\setup-windows.ps1

  Requisitos previos (instalar antes de ejecutar):
    - PHP 8.2+ en el PATH
    - Composer en el PATH
    - Node.js 20+ / npm en el PATH
    - MySQL 8 (Laragon) en el PATH o iniciado
    - VS Code con el comando "code" disponible
#>

$ErrorActionPreference = 'Stop'
Set-Location (Split-Path -Parent $PSScriptRoot)
Write-Host "Proyecto: $(Get-Location)" -ForegroundColor Cyan

function Test-Tool([string]$name, [string]$hint) {
    if (Get-Command $name -ErrorAction SilentlyContinue) { return $true }
    Write-Warning "Falta '$name'. $hint"
    return $false
}

$hasPhp      = Test-Tool php      'Instala PHP 8.2+ y agregalo al PATH.'
$hasComposer = Test-Tool composer 'Instala Composer.'
$hasNpm      = Test-Tool npm      'Instala Node.js 20+.'
$hasCode     = Test-Tool code     'Instala VS Code y agrega el comando code al PATH.'

if (-not $hasPhp) { exit 1 }

# 1) .env
if (-not (Test-Path '.env')) {
    Copy-Item '.env.example' '.env'
    Write-Host 'Creado .env desde .env.example' -ForegroundColor Green
    php artisan key:generate
} else {
    Write-Host '.env ya existe, no se modifica.' -ForegroundColor Yellow
}

# 2) Dependencias PHP
if ($hasComposer) {
    composer install --no-interaction
}

# 3) Dependencias JS
if ($hasNpm) {
    npm install
}

# 4) Base de datos
Write-Host 'Creando base de datos luxury_store (si no existe)...' -ForegroundColor Cyan
$dbReady = $false
try {
    mysql -u root -e "CREATE DATABASE IF NOT EXISTS luxury_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    $dbReady = ($LASTEXITCODE -eq 0)
} catch {
    $dbReady = $false
}
if (-not $dbReady) {
    Write-Warning 'No se pudo conectar a MySQL. Inicia MySQL en Laragon y ejecuta: php artisan migrate --seed'
}

# 5) Migraciones + seed
if ($dbReady) {
    php artisan migrate --seed --force
}

# 6) Enlace de storage (puede requerir permisos en Windows)
try {
    php artisan storage:link
} catch {
    Write-Warning 'No se pudo crear el enlace de storage. Ejecuta "php artisan storage:link" como administrador.'
}

# 7) Assets
if ($hasNpm) {
    npm run build
}

# 8) Extensiones de VS Code
if ($hasCode -and (Test-Path '.vscode\extensions.json')) {
    $recs = (Get-Content '.vscode\extensions.json' -Raw | ConvertFrom-Json).recommendations
    foreach ($ext in $recs) {
        $out = code --install-extension $ext --force 2>&1 | Out-String
        if ($out -match 'successfully installed|already installed') {
            Write-Host "OK   $ext" -ForegroundColor Green
        } else {
            Write-Warning "No se pudo instalar $ext"
        }
    }
}

Write-Host ''
Write-Host 'Listo.' -ForegroundColor Cyan
Write-Host 'Revisa .env (DB_*, APP_URL, STRIPE_*) y recarga Laragon para crear luxury-store.test' -ForegroundColor Cyan
