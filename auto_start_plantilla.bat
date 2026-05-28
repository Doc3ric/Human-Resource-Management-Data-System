@echo off
title HDMS- Human Resource Data Management System Server

:: ── Step 1: Start Laragon minimized ──────────────────────────────────────────
echo Starting Laragon...
start /min "" "C:\laragon\laragon.exe" /start

:: ── Step 2: Wait for Laragon services to initialize (10 seconds) ─────────────
echo Waiting for Laragon services to start...
timeout /t 10 /nobreak > nul

:: ── Step 3: Start Laravel artisan server ─────────────────────────────────────
echo Starting HDMS- Human Resource Data Management System Server...
cd "C:\HDMS- Human Resource Data Management System\CSC_PLANTILLA"
php artisan serve --host=0.0.0.0 --port=8000
