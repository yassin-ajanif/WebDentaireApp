@echo off
REM Stops whatever is listening on port 8000 (Laravel artisan serve).

powershell -NoProfile -ExecutionPolicy Bypass -Command "$c = Get-NetTCPConnection -LocalPort 8000 -State Listen -ErrorAction SilentlyContinue; if (-not $c) { Write-Host 'Rien sur le port 8000.'; exit 0 }; $c | ForEach-Object { Stop-Process -Id $_.OwningProcess -Force -ErrorAction SilentlyContinue }; Write-Host 'Serveur arrete.'"

pause
