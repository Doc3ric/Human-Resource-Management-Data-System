# PGB-SPMIS (Web Edition) - LAN / Intranet deployment

Web-based, multi-user Strategic Performance Management Information System.
**Stack:** Python + Flask + **SQLite** (standalone single-file database `spmis.db`).
No database server to install - the database is one file beside the app.

## What it does
Browser-based SPMS automation across the four-stage cycle: register employees and
rating periods, capture Q/E/T and cross-rating per IPCR, **compute** the rating
(70% Part I + 30% Part II, CSC band 4.500-5.000, cross-rating 20/30/50,
intervening task max 0.5, cap at 5.00), roll up to division/office with the
teamwork cap, monitor submissions (6.2.22 flags), and export the Consolidated
Individual Performance Review Report (CSV) for CSC.

## Run it on the office server PC (one PC hosts; everyone else uses a browser)

### Windows (recommended for an LGU)
1. Install Python 3.10+ from python.org (tick **Add to PATH**).
2. Open Command Prompt in this folder and run:
   ```
   pip install -r requirements.txt
   python app.py            (first run creates spmis.db and the admin login)
   ```
3. For day-to-day multi-user use, run the production server instead:
   ```
   run_server.bat           (uses waitress on port 8080)
   ```
4. Find this PC's LAN address: `ipconfig` -> IPv4 Address, e.g. `192.168.1.10`.
5. Allow the port through Windows Firewall (one time):
   - Windows Defender Firewall -> Advanced -> Inbound Rules -> New Rule ->
     Port -> TCP 8080 (or 5000) -> Allow.
6. From any other PC on the LAN, open a browser to: `http://192.168.1.10:8080`

### First login
`admin` / `admin123`  -> change the password and the `app.secret_key` in `app.py`.

## Roles
- **admin** - full access (and user management once you extend `users`).
- **encoder** - data entry and computation.
- **viewer** - read-only (extend route checks with `session['role']` as needed).

## Multi-user notes
- SQLite runs in **WAL mode** (set automatically): many simultaneous readers plus a
  writer - ample for a PHRMO + raters workload.
- `waitress` is a stable, pure-Python server that runs well on Windows.
- For heavy concurrency (50+ simultaneous writers) migrate the database to
  PostgreSQL/MySQL; the SQL and engine port directly.

## Backup & security
- Back up `spmis.db` (and `spmis.db-wal`, `spmis.db-shm` if present) nightly - just copy the files.
- Keep the server PC and this folder access-restricted; cross-rating data is
  personal data under RA 10173.
- Optional: package as a single `.exe` with PyInstaller so no Python install is
  needed on the server (`pip install pyinstaller` then `pyinstaller --add-data "templates;templates" --add-data "static;static" --add-data "schema.sql;." app.py`).

## Validation (matches the manual)
Create an IPCR (Rank & File), add two indicators (Q5 E4) and (Q5 E4) -> P1 4.50;
add cross-rating group aves 4.14 / 3.54 / 3.78 -> P2 3.78; hours 0; Compute ->
**FNR 4.28, Very Satisfactory** (identical to the manual and the Excel engine).

## Settings module (administrators only)
Logged in as an **admin**, a gear icon appears in the sidebar. Settings let you change,
with immediate effect on the engine (no code edits, no restart):
- **Rating Scale** - the adjectival band (numeric ranges -> Outstanding ... Poor).
- **Weights & Caps** - Part I/II split (70/30), cross-rating weights, intervening-task max, rating cap.
- **MFOs** - the Major Final Outputs / Pillars.
- **Targets Library** - reusable OPCR/DPCR/IPCR success indicators + measures.
- **Rating Descriptions** - the Q/E/T operational definitions per score (a generic default
  plus per-target overrides). When encoding an IPCR, choose a Target to auto-fill the
  indicator and show its Q/E/T guide; click a cell to set that dimension's score.
Non-admin users never see Settings and are redirected if they try the URL.

## User management (admin) & My Account (everyone)
- **Settings -> User Management:** create accounts, set role (admin/encoder/viewer),
  reset a user's password, and activate/deactivate accounts. Safeguards prevent removing,
  demoting, or deactivating the **last active administrator**, and you cannot delete or
  deactivate your own account. Deactivated users cannot log in.
- **My Account** (top-right, all users): change your own password after confirming the current one.
- First run still seeds one admin: `admin` / `admin123` - create real accounts and change this immediately.
