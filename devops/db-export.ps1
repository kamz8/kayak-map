# export-db.ps1
$DATE = Get-Date -Format "yyyyMMdd_HHmmss"
$BACKUP_FILE = "backup_$DATE.sql"

Write-Host "Eksport bazy danych..." -ForegroundColor Yellow

docker-compose exec mysqldump mysqldump `
    -h mariadb `
    -u admin `
    -p"Poké!moon95" `
    --column-statistics=0 `
    --default-character-set=utf8mb4 `
    --single-transaction `
    kayak_map 2>$null | Out-File -FilePath $BACKUP_FILE -Encoding UTF8

if ($LASTEXITCODE -eq 0) {
    $size = [math]::Round((Get-Item $BACKUP_FILE -ErrorAction SilentlyContinue).Length / 1KB, 2)
    Write-Host "✅ OK: $BACKUP_FILE ($size KB)" -ForegroundColor Green
} else {
    Write-Host "❌ Błąd: $LASTEXITCODE" -ForegroundColor Red
}
