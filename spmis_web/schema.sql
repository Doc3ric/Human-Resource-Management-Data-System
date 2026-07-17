-- =====================================================================
-- PGB-SPMIS (Web Edition) — SQLite schema + seed data
-- Standalone single-file database. Mirrors the corrected SPMS manual.
-- =====================================================================
PRAGMA journal_mode=WAL;   -- allow concurrent readers + a writer (LAN multi-user)

CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  username TEXT UNIQUE NOT NULL,
  pw_hash  TEXT NOT NULL,
  fullname TEXT,
  role     TEXT NOT NULL DEFAULT 'encoder'   -- admin | encoder | viewer
);

CREATE TABLE IF NOT EXISTS office (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  code TEXT, name TEXT NOT NULL, parent_id INTEGER
);

CREATE TABLE IF NOT EXISTS mfo (
  id INTEGER PRIMARY KEY AUTOINCREMENT, no INTEGER, name TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS band (
  id INTEGER PRIMARY KEY AUTOINCREMENT, lower_bound REAL NOT NULL, adjectival TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS param (
  name TEXT PRIMARY KEY, value REAL NOT NULL
);

CREATE TABLE IF NOT EXISTS rater_weight (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  role_type TEXT NOT NULL, rater_group TEXT NOT NULL, weight REAL NOT NULL
);

CREATE TABLE IF NOT EXISTS employee (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  emp_no TEXT, last_name TEXT, first_name TEXT, position TEXT,
  office_id INTEGER, role_type TEXT DEFAULT 'Rank & File',
  status TEXT, active INTEGER DEFAULT 1
);

CREATE TABLE IF NOT EXISTS period (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT, sem_no INTEGER, ryear INTEGER, start_date TEXT, end_date TEXT
);

CREATE TABLE IF NOT EXISTS opcr (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  office_id INTEGER, period_id INTEGER,
  office_fnr REAL, office_adjectival TEXT, final_rating_by TEXT, status TEXT DEFAULT 'Draft'
);

CREATE TABLE IF NOT EXISTS dpcr (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  office_id INTEGER, period_id INTEGER, opcr_id INTEGER,
  div_fnr REAL, div_adjectival TEXT, status TEXT DEFAULT 'Draft'
);

CREATE TABLE IF NOT EXISTS ipcr (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  emp_id INTEGER, period_id INTEGER, dpcr_id INTEGER,
  its_hours REAL DEFAULT 0,
  p1 REAL, p2 REAL, eps1 REAL, eps2 REAL, its REAL,
  fnr REAL, adjectival TEXT, final_rating_by TEXT, status TEXT DEFAULT 'Draft'
);

CREATE TABLE IF NOT EXISTS success_indicator (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  ipcr_id INTEGER NOT NULL, mfo_id INTEGER,
  indicator_text TEXT, q REAL, e REAL, t REAL, remarks TEXT,
  measure TEXT, template_id INTEGER
);

CREATE TABLE IF NOT EXISTS cross_rating (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  ipcr_id INTEGER NOT NULL, rater_group TEXT, rater_name TEXT, factor_ave REAL
);

CREATE TABLE IF NOT EXISTS submission (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  office_id INTEGER, emp_id INTEGER, form_type TEXT, period_id INTEGER,
  due_date TEXT, date_submitted TEXT, status_flag TEXT
);

-- ===================== Settings module (editable config) =====================
-- Reusable target templates for OPCR/DPCR/IPCR (the "Targets library").
CREATE TABLE IF NOT EXISTS si_template (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  level TEXT NOT NULL DEFAULT 'IPCR',          -- OPCR | DPCR | IPCR
  mfo_id INTEGER,
  indicator_text TEXT NOT NULL,
  measure TEXT,
  active INTEGER DEFAULT 1
);

-- Operational definitions of each numerical score (1-5) per dimension (Q/E/T).
-- template_id NULL = the GENERIC default rubric; non-null = override for one target.
CREATE TABLE IF NOT EXISTS rating_rubric (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  template_id INTEGER,                          -- NULL = generic default
  dimension TEXT NOT NULL,                      -- Q | E | T
  score INTEGER NOT NULL,                       -- 5..1
  descriptor TEXT
);

-- At most one OPCR / DPCR record per office+period, so "finalize" can upsert.
CREATE UNIQUE INDEX IF NOT EXISTS ux_opcr_office_period ON opcr(office_id, period_id);
CREATE UNIQUE INDEX IF NOT EXISTS ux_dpcr_office_period ON dpcr(office_id, period_id);

