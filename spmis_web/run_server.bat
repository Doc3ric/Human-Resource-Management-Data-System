@echo off
REM ============================================================
REM  PGB-SPMIS  - start the LAN server (Windows)
REM  Other PCs reach it at  http://THIS-PC-IP:8080
REM ============================================================
echo Starting PGB-SPMIS on port 8080 ...
python -m waitress --host=0.0.0.0 --port=8080 app:app
pause
