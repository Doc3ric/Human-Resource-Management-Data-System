# HRDMS — Phase A System Discovery Report

**Scope:** Read-only discovery per `HRDMS_PHASE0_FOUNDATION.txt` Phase A, executed against the working tree at commit `13d9026` on branch `foundation` (plus 396 uncommitted files — see §8).
**Verified:** Laravel `12.51.0` confirmed via `php artisan --version`; full test suite run (`php artisan test`, sqlite `:memory:` per `phpunit.xml`) — **205 passed / 3 failed / 542 assertions**, no data touched.

## ⚠️ Critical framing finding

The spec (`HRDMS_PHASE0_FOUNDATION.txt`, `00_SHARED_CONTEXT.md`) assumes a static HTML/Tailwind/Chart.js frontend with a Python 3.x + Flask (Blueprints) backend to be added beneath it. **That assumption does not match reality.** This is a mature **Laravel 12 / PHP 8.2** application: full Eloquent MVC, ~140 migrations, ~60 controllers, ~60 models, ~200 Blade views, a real MySQL schema, Spatie RBAC, Spatie Activitylog, and a working IDCC document pipeline. There is no Flask, no Python, no static frontend loading from JSON/localStorage anywhere in the repo.

More importantly: **the modules this spec wants "layered on" (RBAC matrix, IDCC pipeline, leave ledger, disciplinary/RACCS, LGU documents, training, statistical scoreboard) already exist in the codebase in substantial working form**, evidently built by a prior session directly in Laravel instead of bolting on Flask. This is corroborated by:
- `app/Support/{Idcc,Leave,Incident,Metrics,Raccs,Renewal,Training}/*` service classes already implementing the spec's pipelines.
- Dedicated feature tests already named for exactly these modules: `RbacRouteCutoverTest`, `IdccPipelineTest`, `MetricRegistryTest`, `LeaveModuleTest`, `LeaveViolationTest`, `DisciplinaryCaseController`-backed tests, `RaccsMfaTest`, `ESignatureAndDisciplinaryTest`, `TrainingModuleTest`, `LguDocumentTest`, `PasswordComplexityGateTest`, `PhotoEnforcementTest`, `AppointmentEncoderMaskingTest`, `BlindScoringViewsTest`, `TwgDynamicScoringTest`, `DeliberationWorkspaceTest`, `ExamRoutingTest`, `RenewalGateTest`, `LifecycleRetentionTest`, `PlantillaSyncValidatorTest`, `DashboardLayoutTest`.
- All of the above pass (see §8) — this is not aspirational, it is running, tested code.

**Consequence for Phase B/C:** the job is a gap analysis against what's already built, not fresh construction. Treat `HRDMS_PHASE0_FOUNDATION.txt`'s Flask/Python framing as superseded; its functional intent (schema, RBAC matrix, IDCC core, scoreboard component, Module 0 auth/layout) is what still governs.

## 1. Project structure

- **Laravel `^12.0`**, PHP `^8.2` (composer.json). No `routes/api.php` — no JSON API surface exists or is needed; server-rendered Blade only.
- Key packages: `laravel/breeze` (auth scaffold), `spatie/laravel-permission` (RBAC), `spatie/laravel-activitylog` (audit log), `barryvdh/laravel-dompdf`, `maatwebsite/excel`, `phpoffice/phpword`, `pragmarx/google2fa-laravel` + `bacon/bacon-qr-code` (TOTP MFA), `smalot/pdfparser` (PDF text extraction for IDCC).
- Rendering: 100% server-side Blade + Alpine.js + vanilla JS + Chart.js (via CDN `<script>` tags in ~53 views, not npm). No Inertia/Vue/React/ApexCharts.
- Frontend build: Vite + TailwindCSS 3 (`resources/css/app.css`, `resources/js/app.js`).
- Entry points: `routes/web.php` (830 lines, single real route file), `routes/auth.php` (Breeze), `routes/console.php`, `bootstrap/app.php` (Laravel 12 style — middleware aliases registered here, no `Kernel.php`).
- Non-standard root files to flag (not part of app runtime): `check_users.php`, `replace.php`, `temp_create.blade.php`, `test_enc.php`, `test_encoding.php`, `update_media_query.php`, `update_sidebar.php`, `update_sidebar_html.php`, a `scratch/` dir, `composer.phar`. Leave untouched — out of scope, but noted so they aren't mistaken for live code.

## 2. Frontend inventory

**Theme system — already matches the spec's "CSS variables only, no hardcoded hex" requirement, already built:**
- `public/css/theme-variables.css` — full CSS custom-property system: default (Navy Blue) + 4 alternate themes (`body.emerald-night`, `body.theme-financial`, `body.theme-corona`, `body.theme-light`). Variables: `--color-primary`, `--color-accent`, `--color-sidebar-*`, `--color-topbar-*`, `--color-page-bg`, `--color-surface`, `--color-border`, `--color-text-*`, `--color-btn-primary-*`, `--color-link*`, `--color-focus-ring`, `--color-nav-active-*`, `--color-danger*`. Documents a WCAG 2.1 AA contrast target.
- `public/css/theme-default.css`, `theme-corona.css`, `theme-financial.css`, `public/css/emerald-night.css` — per-theme companions.
- **No further theme-variable system build needed.** New/changed markup should consume `var(--color-*)` only.

**JS:** Alpine.js, Axios, Chart.js (CDN). No jQuery, no ApexCharts.

**Blade views (~200 files)** grouped by domain — Auth, layouts/components (including `scoreboard-tile.blade.php`, `renewal-status-widget.blade.php`, `twg-score-panel.blade.php`, `signature-pad.blade.php`, `applicant-profile.blade.php`), core personnel inventory (Plantilla/All Data/Permanent/Casual/Job Orders), salary/step-increment, retirement, recruitment + HRMPSB deliberation/TWG, **leave & discipline** (leave, leave-violations, disciplinary, incidents), **IDCC** (idcc/index|show|print|preview-text), LGU/training, RBAC/admin (users, role-matrix, administration, panel-members, mfa), system ops (activity-logs, audit-logs, archives, rollback, backup, imports, retention-schedule, contract-status, batch-renewal, gad, search, setup wizard), exports.

**Correction (superseded finding):** an earlier pass of this report flagged Module 0.2 (top-header nav) as not implemented, based on `resources/views/layouts/dashboard-app.blade.php` (882 lines), which does still contain a left-sidebar CSS/markup implementation. Further checking found that file is **dead code — zero live views reference it** (`grep` for `layouts.dashboard-app`/`extends('layouts.dashboard-app')` across `resources/views` and `app` returns nothing). The actual, live layout is a same-named-but-different Blade **component**, `resources/views/components/dashboard-app.blade.php` (invoked as `<x-dashboard-app>`), used by **97 views** — effectively the entire authenticated app. It already implements the spec's top-header requirement: its own in-file comments read "Replaces the left slide drawer. Below 1024px, .sidebar-nav..." and "Below 1024px: hamburger expands a horizontal-bar dropdown," matching Module 0.2 verbatim (the `.sidebar-*` class names are legacy naming carried over onto the new horizontal-header markup, not an actual sidebar). `DashboardLayoutTest` already asserts this directly ("the dashboard renders through the shared horizontal-header layout, not its own duplicated sidebar") and passes. **Module 0.2 is already fully implemented; no gap.** The remaining 8 views not on `<x-dashboard-app>` extend `layouts.app` (Breeze's plain top-nav layout, also sidebar-free), not the orphaned file. The orphaned `layouts/dashboard-app.blade.php` is left in place per the "never delete existing" rule but should be understood as dead code, alongside `EmployeeController`/`PositionController`/etc.

## 3. Backend inventory

**Controllers** (`app/Http/Controllers/`, ~60): full CRUD/report/export controllers for Plantilla, Job Orders, Casual, Permanent, Retirement, Contract Status, Batch Renewal, Recruitment, HRMPSB Interview/TWG/TWG-Dynamic, Deliberation, Exam Routing, Panel (separate guard), Performance/IPCR, GAD Analytics, Users + RBAC (`RolePermissionController`), Activity Logs, Audit Logs, Rollback, Backup, Archives, Imports, Employee Codes, Global Search, Employee Profile, Organizational Units, Panel Members, MFA, **Leave** (`LeaveController`, `LeaveViolationController`), **Disciplinary** (`DisciplinaryCaseController`), **Incident Reports** (`IncidentReportController`), **LGU Documents** (`LguDocumentController`), **Training** (`TrainingController`), **IDCC** (`IdccController`), Retention Schedule, Setup Wizard.

**Dead code, not routed in `web.php` — do not build on, do not delete (spec forbids dropping):** `EmployeeController`, `PositionController`, `AppointmentController`, and the `employees`/`positions`/`organizational_units`(routing unclear)/`appointments` tables they front — superseded by `plantilla_records`.

**Middleware:** `RoleMiddleware.php` (custom `role:` alias → Spatie role names), `ForcePasswordChange.php` (global on `web` group). `bootstrap/app.php` registers Spatie's `permission`, `spatie.role`, `role_or_permission` aliases. **Confirmed in-progress, incomplete cutover:** `routes/web.php` has **52 `'role:'` usages vs 82 `'permission:'` usages** — some `role:` usages are plausibly intentional super-admin-only statutory/destructive gates (e.g. `delete-all`, rollback, backup) consistent with Module 4A.4's "sensitive-bit guardrails," others look like un-migrated leftovers. Needs line-by-line judgment, not a blanket conversion (Phase B/C item).

**Routes (`routes/web.php`):** guest (`/`, `/privacy-policy`, `/setup/*`), then one big `auth` group covering everything, including **Leave, Leave Violations, Disciplinary (RACCS), Incident Reports, LGU Documents, Training, MFA, IDCC, Retention Schedule** — all already routed and reachable. Separate `panel` prefix group on a second guard (`auth:panel`) for external HRMPSB/TWG evaluators. No `routes/api.php`.

**Auth:** Laravel Breeze base, heavily customized — admin-gated registration (`is_approved`), forced first-login password change (`must_change_password`), TOTP MFA (`google2fa_secret`/`google2fa_enabled`) gated to RACCS access, and a password-complexity gate:
- `app/Support/PasswordPolicy.php::meetsGate()` computes `meets_complexity_gate`, wired in `UserController::store/update`, `RegisteredUserController`, and `ForcePasswordChangeController` — **Module 0.1's password quarantine is already implemented**, not a gap.

**RBAC:** `spatie/laravel-permission` is the real, installed system (`permissions`, `roles`, `model_has_permissions`, `model_has_roles`, `role_has_permissions` tables). `RolePermissionController` implements a full module × action permission matrix (Dashboard & GAD Analytics, Personnel Records, Appointment, Employee Development, Welfare & Benefits, **Leave Administration**, **IDCC & Documents**, **Discipline**, System & Administration; actions view/add/edit/delete/archive) — **this is Module 4A's RBAC matrix, already built and under active test** (`RoleMatrixEnhancementTest`, `RbacRouteCutoverTest`, `AppointmentEncoderMaskingTest`, `BlindScoringViewsTest`).

## 4. Database schema

MySQL confirmed (`.env` `DB_CONNECTION=mysql`, `DB_DATABASE=plantilla`; migrations use MySQL-only DDL guarded by driver checks). ~140 migrations. Key tables:

- **`users`** — Spatie-role-driven (`role` string column, migrated off a hand-rolled enum via `2026_06_26_093518_create_permission_tables.php` + `..._093539_migrate_roles_to_spatie.php` + `..._100739_alter_users_table_change_role_to_string.php`), plus `is_approved`, `must_change_password`, `meets_complexity_gate`, `google2fa_secret`, `google2fa_enabled`.
- **`plantilla_records`** — the unified personnel master table (single most important table). Has, already under these or equivalent names, essentially every field the spec's Step 1 asks to add nullable: `sex` (renamed from `gender`), `employment_status`, `is_pwd`, `is_renewed`, `is_vacant`, `solo_parent`, `indigenous_people`, `salary_grade`, `lifecycle_status`, retirement/separation fields, soft-deletes. **`JobOrder` and `CasualEmployee` are NOT separate tables** — `2026_06_23_105416_normalize_plantilla_records_schema.php` merged them into `plantilla_records`; `app/Models/JobOrder.php`/`CasualEmployee.php` now `extend PlantillaRecord` with a global scope on `employment_status`. Any future work must not assume separate tables.
- **`employees`, `positions`, `organizational_units`, `appointments`** — earlier normalized schema, superseded, controllers unrouted (see §3).
- **`documents`** (`2026_07_02_100001_create_documents_table.php`) — the IDCC core table: `sha256_hash`, `capture_source`, `extraction_tier`, `ocr_text` (FULLTEXT), `ocr_confidence`, `ocr_status`, `doc_type_code`, `importance_class`, `privacy_tier`, `abstract_description`, `is_spi`, `is_raccs`, `encrypted`, polymorphic `personnel_id`/`personnel_type`, lifecycle `status`, soft-deletes. Plus `document_relations`, `document_duplicate_resolutions`, `document_retention_rules`.
- **`spi_access_log`, `raccs_access_log`** — immutable, append-only access-audit tables for SPI and RACCS-confidential access specifically.
- **`retention_schedule`** — NAP GRDS (record_category, time_value, disposition_action, legal_basis, nap_grds_item_ref).
- **`leave_types`, `leave_balances`, `leave_applications`, `leave_violations`, `leave_violation_thresholds`** — full leave ledger + AWOL/habitual-absence violation workflow.
- **`disciplinary_cases`** — RACCS case registry (case_no, offense_classification light/less_grave/grave, status workflow, decision/penalty).
- **`incident_reports`, `counseling_records`** — incident intake with AI/rules-advisory fields and human finalize step.
- **`lgu_documents`, `service_requests`** — EO/Memo/Ordinance registry + Citizen's-Charter-timed requests.
- **`trainings`, `training_participants`, `training_certificates`** — L&D module.
- **`renewal_signals`, `contract_renewals`** — Active Status Gate / renewal signal architecture.
- **`e_signatures`** — polymorphic e-signature table with `pnpki_status` hook.
- **`applicants` + full recruitment/deliberation subsystem** (`applicant_evaluations`, `applicant_hrmpsb_scores`, `hrmpsb_rating_scales`, `hrmpsb_signatories`, `interview_evaluations`, `panel_members`, `exam_schedules`, `twg_rating_criteria`, `twg_scores`, `twg_score_submissions`, `twg_score_history`).
- **Two parallel audit-log systems:** hand-rolled `activity_logs` (+ `ActivityLogController`) and Spatie's `activity_log` (+ `AuditLogController`). Both wired to real UI. Real technical debt, but the spec's "never drop existing" rule means both stay; consolidation (if any) is additive/UI-level only.
- **`backup_logs`, `settings`, `ipcr_ratings`, `service_records`.**

## 5. Existing features that MUST keep working

Dashboard (live stats), Plantilla/Inventory (CRUD, reports, PWD/IP/position/vacant, Form 9/33, service record, quick-add, promote), All Data, Permanent, Job Orders, Casual Employees, Salary Grade + SSL Schedule management, Step Increment/Longevity hub (NOSI/NOLP/NOSA/loyalty notices, PDF+DOCX), Retirement (auto/manual/history), Contract Status dashboard, Batch/individual Contract Renewal, Recruitment (CRUD/import/receipts), HRMPSB Interview + comparative report + signatories, TWG scoring (fixed + Dynamic), split-screen Deliberation workspace, Exam Routing, Panel Portal (separate guard), Performance/IPCR + import/export, GAD Analytics, User Management + RBAC Matrix + per-user overrides, Activity Logs, Audit Logs, DB Rollback, Backup/Recovery, Archives/Recycle Bin, Import wizard, Employee Code generation, Global Search, Employee 201 Profile + photo, Panel Member management, MFA, **Leave Application + Balances**, **Leave Violation Monitoring + letters**, **Disciplinary Cases (RACCS)**, **Incident Reports + advisory + counseling**, **LGU Documents + Service Requests**, **Training/L&D**, **IDCC pipeline** (upload/OCR/SPI/RACCS detection/rotate/delete/relate/reprocess), **Retention Schedule**, Setup Wizard, forced password change.

## 6. Environment

Laragon on Windows (`C:\laragon\www\...`), PHP `^8.2`, MySQL confirmed as DB driver. No Python/Flask anywhere — irrelevant to this stack. Test suite runs against sqlite `:memory:` (`phpunit.xml`), isolated from the real `plantilla` MySQL database — safe to run repeatedly.

## 7. Existing hits for RBAC / audit / SPI / IDCC / leave / RACCS / scoreboard

All seven already exist, at varying completeness:
1. **RBAC matrix** — `RolePermissionController` + Spatie tables. Route-level `role:`→`permission:` cutover in progress, not finished (§3).
2. **Audit log** — two parallel systems (hand-rolled + Spatie), plus dedicated `spi_access_log`/`raccs_access_log`.
3. **SPI encryption** — `app/Support/Idcc/DocumentEncryptor.php` (AES-256-GCM, keyed from `APP_KEY`) + `SpiDetector.php` (regex/keyword SPI detection).
4. **IDCC pipeline** — `app/Support/Idcc/{IdccPipeline,OcrManager,OcrDriverInterface,TesseractOcrDriver,NativeTextExtractionDriver,PdfTextExtractionDriver,DocumentClassifier,DuplicateDetectionService,ImageRotationService,DocumentAccessPolicy,SpiDetector,DocumentEncryptor}.php` — essentially Module 9 built end-to-end, covered by `IdccPipelineTest` (passing).
5. **Leave ledger** — full ledger + `LeaveAccrualService`, `LeaveApplicationService`, `LeaveGuardrail`, `LeaveViolationDetectionService`, `LeaveViolationLetterService`, covered by `LeaveModuleTest`/`LeaveViolationTest`/`LeaveFilingViolationTest` (passing).
6. **Disciplinary/RACCS** — `disciplinary_cases`, `RaccsMfaGate`, `ESignatureService`, `raccs_access_log`, covered by `RaccsMfaTest`/`ESignatureAndDisciplinaryTest` (passing).
7. **Scoreboard** — `app/Support/Metrics/MetricRegistry.php` implements the Module 12.3/12.8 metric-ownership pattern for 8 canonical metrics, with its own doc-comment admitting it's a **partial start**: "NOT a full audit of every pre-existing count... e.g. DashboardController's dozens of ad-hoc counts." Confirmed real, honestly-flagged gap — see Phase B/C. `scoreboard-tile.blade.php` component exists. Covered by `MetricRegistryTest` (passing).

## 8. Working-tree / test-suite ground truth

- `git status --short` — **396 changed files** (165 modified, 231 untracked) relative to `HEAD` (`13d9026`), all on branch `foundation`. Nothing of the above is safely "done" until committed — treat as a real risk, checkpoint before further work.
- `php artisan test` — **205 passed, 3 failed, 542 assertions**, ~95s, no DB side effects (sqlite `:memory:`). Failures, all pre-existing scaffold/behavior mismatches, not regressions from this session:
  1. `Tests\Feature\ExampleTest > it returns a successful response` — asserts `GET /` returns 200; app now redirects unauthenticated visitors (expected — stale Breeze scaffold test).
  2. `Tests\Feature\Auth\AuthenticationTest > users can logout` — asserts post-logout redirect is `/`; actual is `/login` (reasonable intended behavior, test not updated).
  3. `Tests\Feature\ProfileTest > user can delete their account` — asserts `$user->fresh()` is `null` after self-delete; the user still exists with `deleted_at` set — **account deletion is now a soft-delete**, consistent with the cross-cutting "no unguarded DELETE, destruction is soft-delete" rule, but the Breeze scaffold test wasn't updated to match.

None of these three represent broken functionality — they represent stale tests asserting the old (pre-hardening) behavior. Flagged as a Phase D quick-fix, not a functional risk.
