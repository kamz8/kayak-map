param(
    [string]$outputFile = "./docker/mysql/clean_dump.sql"
)

Write-Host "🔄 Eksport z czyszczeniem (PowerShell 5.x)..." -ForegroundColor Yellow

# Utwórz folder jeśli nie istnieje
$folder = Split-Path $outputFile -Parent
if (!(Test-Path $folder)) {
    New-Item -ItemType Directory -Force -Path $folder | Out-Null
}

try {
    # Eksport do pliku tymczasowego
    $tempFile = "./docker/mysql/temp_dump.sql"

    Write-Host "📤 Wykonuję eksport..." -ForegroundColor Blue

    docker exec mysqldump-tool mysqldump `
        -h db `
        -u admin `
        "-pPoké!moon95" `
        --default-character-set=utf8mb4 `
        --single-transaction `
        --routines `
        --triggers `
        --no-tablespaces `
        --skip-column-statistics `
        --add-drop-table `
        --compact `
        kayak_map 2>$null | Out-File -Encoding UTF8 -FilePath $tempFile

    if ($LASTEXITCODE -ne 0) {
        Write-Host "❌ Błąd podczas eksportu!" -ForegroundColor Red
        exit 1
    }

    Write-Host "🧹 Czyszczenie pliku..." -ForegroundColor Blue

    # Wczytaj zawartość
    $rawContent = [System.IO.File]::ReadAllText($tempFile, [System.Text.Encoding]::UTF8)

    # Usuń BOM jeśli istnieje
    if ($rawContent.StartsWith([char]0xFEFF)) {
        $rawContent = $rawContent.Substring(1)
    }

    # Usuń problematyczne znaki kontrolne
    $rawContent = $rawContent -replace '\0', ''

    # Podziel na linie i filtruj
    $lines = $rawContent -split "`r?`n"
    $cleanLines = @()

    foreach ($line in $lines) {
        # Pomiń problematyczne komentarze
        if ($line -match '^/\*!40101' -or
            $line -match '^/\*!40000' -or
            $line -match '^-- Dump completed' -or
            $line -match '^-- MySQL dump') {
            continue
        }
        $cleanLines += $line
    }

    # Utwórz nagłówek kompatybilny z phpMyAdmin
    $header = @"
-- phpMyAdmin SQL Dump
-- version 5.2.0
-- Host: db:3306
-- Generation Time: $(Get-Date -Format 'MMM dd, yyyy \a\t HH:mm:ss')
-- Server version: 10.11.2-MariaDB

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CON
