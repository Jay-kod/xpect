$ErrorActionPreference = 'Stop'
Set-Location $PSScriptRoot

function Invoke-Compose {
    param([Parameter(Mandatory = $true)][string[]]$Arguments)

    & docker compose @Arguments
    if ($LASTEXITCODE -ne 0) {
        throw "docker compose $($Arguments -join ' ') failed with exit code $LASTEXITCODE."
    }
}

docker info --format '{{.ServerVersion}}' *> $null
if ($LASTEXITCODE -ne 0) {
    throw 'Docker Desktop is not running. Start Docker Desktop, wait for it to finish loading, then run .\start-xpect.ps1 again.'
}

if (-not (Test-Path '.env')) {
    Copy-Item '.env.example' '.env'
    Write-Host 'Created .env from .env.example.'
}

$appKeyLine = Select-String -Path '.env' -Pattern '^APP_KEY=' | Select-Object -First 1
if (-not $appKeyLine) {
    throw '.env is missing APP_KEY. Add APP_KEY= to .env and run this script again.'
}

if ($appKeyLine.Line -eq 'APP_KEY=') {
    Write-Host 'Generating the application key...'
    Invoke-Compose @('run', '--rm', 'app', 'php', 'artisan', 'key:generate')
}

Write-Host 'Building the Xpect app image...'
Invoke-Compose @('build', 'app')

Write-Host 'Applying database migrations and seeding a new database...'
Invoke-Compose @(
    'run', '--rm', 'app', 'sh', '-c',
    'set -e; php artisan migrate --force; if [ ! -f /data/.xpect-seeded ]; then php artisan db:seed --force; touch /data/.xpect-seeded; fi'
)

if (-not (Test-Path 'public/build/manifest.json')) {
    Write-Host 'Building the frontend assets...'
    Invoke-Compose @('run', '--rm', 'assets')
}

Write-Host 'Starting the Xpect web server...'
Invoke-Compose @('up', '-d', 'app')

$ready = $false
for ($attempt = 0; $attempt -lt 30; $attempt++) {
    try {
        $response = Invoke-WebRequest -Uri 'http://127.0.0.1:8081/' -UseBasicParsing -TimeoutSec 5
        if ($response.StatusCode -eq 200) {
            $ready = $true
            break
        }
    } catch {
        Start-Sleep -Seconds 2
    }
}

if (-not $ready) {
    Invoke-Compose @('logs', '--no-color', '--tail', '60', 'app')
    throw 'Xpect did not become ready at http://127.0.0.1:8081. Check the app logs above.'
}

Write-Host 'Xpect is ready: http://127.0.0.1:8081'
