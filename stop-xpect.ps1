$ErrorActionPreference = 'Stop'
Set-Location $PSScriptRoot

& docker compose stop app
if ($LASTEXITCODE -ne 0) {
    throw "Could not stop the Xpect app container (exit code $LASTEXITCODE)."
}

Write-Host 'Xpect app stopped. Its database and Docker volumes are preserved.'
