# HRDMS Audit Report — Master Spec (v20) vs. Live System

**Audited against:** `HRDMS_UNIFIED_MASTER_PROMPT_v20.txt`
**Live system:** `C:\laragon\www\Human-Resource-Management-Data-System` (Laravel/PHP/MySQL — not the Flask/Python stack the spec originally assumed; verified against functional equivalents)
**Method:** 6 independent research passes covering all 12 modules + sub-modules, each verifying claims against actual controller/model/migration/view code, not assumptions.

**Overall:** Core data model, RBAC scaffolding, IDCC pipeline structure, deliberation workspace, incident reporting, and training module are substantially built and functionally sound. The two biggest categories of gap are (1) **enforcement gaps** — validators and guardrails exist as code but aren't wired to actually block the action the spec requires, and (2) **infrastructure not present in this environment** — Tesseract OCR isn't installed, no scanner/Bluetooth/vision-tier capture exists beyond webcam + basic upload.

---

## MODULE 0 — Authentication, Landing Page, Global Layout
| # | Requirement | Verdict | Evidence |
|---|---|---|---|
| 0.1 | Password complexity quarantine, bcrypt, forced upgrade screen | **DONE** | `PasswordPolicy.php`, `RegisteredUserController.php:44-58`, `ForcePasswordChange.php` middleware |
| 0.2 | Horizontal top header nav (not left drawer), hamburger <1024px | **NOT DONE** | Still a left sidebar (`dashboard-app.blade.php`, 240px fixed); no responsive breakpoint converts it to a top header |

## MODULE 1 / 1A / 1B — Renewal Gate, Signal Architecture, Lifecycle & Retention
| # | Requirement | Verdict | Evidence |
|---|---|---|---|
| 1.1 | is_renewed query-level exclusion | **DONE** | `PlantillaRecord::scopeFilled()` |
| 1.2 | Batch Renewal disabled checkboxes for unrenewed | **PARTIAL** | Controller returns the flag; blade enforcement not fully verified |
| 1.3 | Internal JO/contractual→permanent station-renewal hard stop | **NOT DONE** | No such gate found in recruitment lifecycle |
| 1A.1–1A.7 | Single source of truth, signal table, strength rules, per-module behavior, shared widget, single commit path, RBAC commit authority | **DONE** | `RenewalCommitService`, `RenewalSignal` model/table, `renewal-status-widget.blade.php`, `RenewalAuthority.php` — this is one of the most faithfully-built parts of the system |
| 1A.8 | Request-to-Renew flow | **PARTIAL** | Button exists; full notification chain to PHRMO not verified |
| 1A.9 | Compliance tooltips | **PARTIAL** | No inline legal-basis tooltips confirmed |
| 1B.1 | lifecycle_status + effective date + basis | **DONE** | Migration + `scopeFilled()` |
| 1B.2 | Live-report exclusion extended to all lifecycle states | **PARTIAL** | Enforced via `scopeFilled()`, but GAD Analytics may use a bare query bypassing it |
| 1B.3 | Archival-on-separation (ARCHIVED status) | **NOT DONE** | No ARCHIVED state; soft-deletes exist but aren't used as the archival mechanism |
| 1B.4 | Lawful disposal via NAP Form No. 1/3 workflow | **NOT DONE** | No disposal queue or hard-delete lock behind a signed NAP Form No. 3 |
| 1B.5 | Portable Retention & Disposal Matrix | **DONE** | `retention_schedule` table + model, matches spec's field list |
| 1B.6 | Lifecycle/retention dashboard | **NOT DONE** | No scoreboard for lifecycle-state counts or disposal-eligibility warnings |
| 1B.7 | RBAC/audit on lifecycle changes | **PARTIAL** | Activity logging present; dedicated `lifecycle_manage`/`disposal_approve` bits not confirmed |
| 1B.8 | Compliance citations in matrix/disposal view | **NOT DONE** | `legal_basis` field stored but not surfaced in UI |

## MODULE 2 / 2B / 3 / 3A — Recruitment Retention, Leave Violations, Plantilla Sync, Incident Reports
| # | Requirement | Verdict | Evidence |
|---|---|---|---|
| 2.1 | Sort by date_created DESC | **PARTIAL** | Field-name mismatch (`applied_at`/`created_at` used inconsistently) |
| 2.2 | 3-stage lifecycle (Active/Archived/Valueless Queue) | **DONE** | `RecruitmentLifecycleService`, daily cron |
| 2.3 | Anti-auto-delete, NAP Form No. 3 lock | **DONE** | `DisposalAuthorizationService`, never hard-deletes |
| 2B.1 | VL/SL + DTR/attendance capture | **PARTIAL — architectural gap** | **No DTR/attendance data source exists in this system at all.** Only AWOL and habitual absenteeism (from leave applications) are detected; tardiness/undertime detection is not implemented because there's nothing to read minute-level attendance from. This was already known from prior work in this project. |
| 2B.2 | Configurable, seeded violation thresholds | **PARTIAL** | `LeaveViolationThreshold` table exists but detection service uses hardcoded class constants instead of reading it |
| 2B.3 | Dynamic violation watchlist | **PARTIAL** | Watchlist exists; missing VL/SL balance columns and full filter/export set |
| 2B.4 | Letter generation per violation/tier | **DONE** | `LeaveViolationLetterService` |
| 2B.5 | Auto-recording via IDCC on issuance | **DONE** | Confirmed writing document + activity log |
| 2B.6 | Payroll/office coordination letters | **PARTIAL** | Referenced in controller; full generation logic not independently confirmed this pass (already fixed once this session — see conversation history) |
| 2B.7 | Due-process response-window enforcement | **PARTIAL** | Status fields exist; no automatic timer/escalation enforcement |
| 2B.8 | RBAC gating (view vs issue vs payroll) | **DONE** | Separate permission checks confirmed |
| 2B.9 | Compliance citations | **DONE** | Embedded in letters |
| 2B.10 | Leave-filing violations (late filing, etc.) | **PARTIAL** | Method exists (`scanFilingViolations()`) but not fully verified for all 5 sub-types |
| 2B.11 | Filing-violation letters + lifecycle | **PARTIAL** | Same status-tracking gap as 2B.7 |
| 3.1 | Vacant positions dropdown (DISTINCT, ordered) | **DONE** | `RecruitmentController::create()` |
| 3.2 | Master Plantilla mismatch **blocks** save | **PARTIAL — real gap** | `PlantillaSyncValidator::checkSync()` exists and can detect mismatches, but is not wired to actually abort the save in `RecruitmentController::store()`. The check exists; the block doesn't. |
| 3A.1–3A.8 | Incident capture, AI-advisory draft, human review, dual track, counseling, audit, RBAC, compliance | **DONE (all 8)** | This is the most completely and faithfully built module in the system — `RulesAdvisoryEngine`, `IncidentReportController::finalize()`, `CounselingRecord`, three-tier RBAC (create/finalize/escalate), non-removable advisory label |

## MODULE 4 / 4A — RBAC (AE Masking + Permission Matrix)
| # | Requirement | Verdict | Evidence |
|---|---|---|---|
| 4.1 | AE session hides restricted tools | **PARTIAL** | Route-level blocking works; view-level masking inconsistent (some links to forbidden routes not hidden) |
| 4.2 | application_letter_attachment NOT NULL + override reason | **DONE** (mostly) | Enforced in `RecruitmentController::store()`; minor gap — override reason not strictly rejected-if-blank in all paths |
| 4A.1 | Add/Edit/Delete auto-enables View | **DONE** | `RolePermissionController::applyViewLock()` |
| 4A.2 | Per-user assignment + audit | **DONE** | `userOverrides()`, self-escalation blocked, `ActivityLog` on every change |
| 4A.3 | Matrix enumerates all spec sections/sub-modules | **PARTIAL** | 39 sub-modules exist across 9 sections — real and useful, but doesn't match the spec's ~15-section structure, and **no auto-registration** of new sub-modules (manual array edit required) |
| 4A.4 | Sensitive-bit guardrails (raccs_access, spi_unmask, renewal_commit, etc.) visually flagged | **NOT DONE** | **These bits don't exist as literal matrix columns at all** — only generic view/add/edit/delete/archive actions. The underlying guardrails (MFA on RACCS, SPI logging) work independently in their own controllers, but the RBAC matrix itself doesn't expose or flag them as the spec's master control surface intends |
| 4A.5 | Every route+view checks matrix; unrendered not disabled; audit detail | **PARTIAL** | Routes are gated; views often check role (`isAppointmentEncoder()`) rather than granular permissions; audit logs are generic text, not structured actor/target/bit/timestamp |
| 4A.6 | Collapsible grid, search, per-user summary, theme.css, danger accents | **PARTIAL** | Grid + module filter exist; no user search, no danger-color flagging of sensitive bits, mostly hardcoded colors not theme.css variables |

## MODULE 5 / 6 / 6A / 7 / 8 — Deliberation Workspace, TWG Scoring, Monitoring Board, Photo, Export
| # | Requirement | Verdict | Evidence |
|---|---|---|---|
| 5.1–5.3 | Fixed header, split-screen, all 8 profile accordions | **DONE** | Fully verified line-by-line against `deliberation-profile.blade.php` — every accordion (Education, Eligibility, Training, Awards, IPCR, Length of Service, Demerit, EETE) present with the specified badges/footers |
| 5.4 | Exam routing (9-month window, PGB/External/Exempt classification, clustering) | **PARTIAL** | Core filtering/classification present; exempted-positions list is referenced but not hardcoded as enforced logic |
| 6.1–6.4, 6.6 | Dynamic criteria, auto-scoring, scoring sheet, certification gate, blind scoring ID | **DONE** | `TwgRatingCriterion`/`TwgScore` tables, `BlindScoringId` class, lock-after-submit with Chairperson-only unlock |
| 6.5 | Agenda prep (Part I/II) | **PARTIAL** | Generation and matrix exist; "Action Taken" input column not in schema yet |
| 6.7 | CER level auto-selector | **NOT DONE** | No `position_level` field; CER export doesn't branch by level |
| 6A.1–6A.7 | Monitoring board (3 views, live polling, summary strip) | **DONE** | Drawer/tab UI, 10s polling, masked IDs throughout |
| 6A.8–6A.10 | Personal banner, Chairperson Remind, closing guard, snapshot export | **NOT DONE** | None of the three Chairperson-facing controls (Remind, closing guard, export) are implemented — only the passive completion display exists |
| 7 | Photo enforcement (intake/chips/submit) + crop + 201 mirror | **DONE** | `PhotoEnforcementService`, Cropper.js integration, 201-import with confirmation checkbox |
| 8.1–8.4 | Export Layouts A/B/C/D | **DONE** | All four confirmed with correct columns/masking/footers |
| 8.5 | Citation overlays throughout | **PARTIAL** | Present on some layouts, not verified everywhere |

## MODULE 9 / 9A — IDCC Pipeline & Multi-Source Capture
| # | Requirement | Verdict | Evidence |
|---|---|---|---|
| Stage 1 Ingestion | **DONE** | SHA-256, manual-review queue, batch support |
| Stage 2 OCR | **PARTIAL — operational gap, not a code gap** | Tesseract driver code is correct and gracefully degrades, but **Tesseract is not actually installed on this host**. Every image document currently fails OCR and lands in manual_review. This matches what was already known from earlier work in this project. |
| Stage 3 Classification | **DONE** | `DocumentClassifier` |
| Stage 4 SPI Triage | **DONE** | Regex/keyword detection, AES-256-GCM, `tbl_spi_access_log` |
| Stage 5 RACCS Wall | **DONE** | Query-layer exclusion from FULLTEXT, MFA-gated, separate access log |
| Stage 6 Relationship Mapping | **DONE** | `DocumentRelation` model |
| Stage 7 Auto-Foldering | **DONE** | Query-layer partitioning; no hard-delete for standard users |
| Stage 8 Retention | **DONE** | `DocumentRetentionRule` with permanent/temporary flags |
| 9A.1 Hardware capture | **PARTIAL** | Webcam capture genuinely works (WebRTC). **Scanner (TWAIN/WIA) and Bluetooth are permission-bit stubs only — no actual driver/receiver code exists.** |
| 9A.2 Formats/multi-page | **DONE** | |
| 9A.3 Pre-processing (deskew/denoise/crop) | **NOT DONE** | Zero evidence of any image pre-processing pipeline |
| 9A.4 Tiered extraction | **PARTIAL — one tier only** | Only Tier 1 (Tesseract) exists. Tier 2 (handwriting/vision model) and Tier 3 (external Vision LLM + privacy pre-check + opt-in) are **not built at all** — confirmed by an explicit code comment in `OcrManager.php` stating they're deferred |
| 9A.5 Pipeline continuity | **DONE** (by default, since only Tier 1 exists) | |
| 9A.6 Universal attachment widget | **NOT DONE** | IDCC upload is a standalone page, not a reusable widget embedded in 201/leave/incident/LGU/training attachment fields as the spec requires |
| 9A.7 RBAC & audit on capture | **PARTIAL** | Capture is logged; `bluetooth_capture`/`external_vision_optin` bits exist in the permission list but gate nothing real since those features don't exist |
| 9A.10 Duplicate-attachment detection | **PARTIAL** | SHA-256 exact-duplicate detection genuinely works and is RBAC-scoped in its disclosure (doesn't leak restricted-doc locations) — but it **hard-blocks** the upload rather than offering the spec's Use-Existing/Overwrite/Keep-Both resolution UI. Near-duplicate (perceptual) detection doesn't exist. |

## MODULE 10 / 10B / 12 — Destination Modules, Training/L&D, Scoreboard
| # | Requirement | Verdict | Evidence |
|---|---|---|---|
| M1 201-File/Records | **PARTIAL** | Single doc table + soft-delete exist; no explicit "modification disabled" enforcement found |
| M2 Leave + JO/COS guardrail | **DONE** | `LeaveGuardrail.php` blocks JO/COS with the exact JC No. 1 s.2017 citation |
| M3 RACCS-Confidential | **DONE** | MFA + role gate + dedicated access log |
| M4 Recruitment/Appointments | **PARTIAL** | QS validation + Appendix C-1 flagging done; e-signature/PNPKI is a non-blocking stub (as intended); **no PRC/CSC/SC online eligibility verification with logged status trail** |
| M5 LGU Documents | **DONE** | EO/Memo/Ordinance registry + Citizen's Charter countdown |
| 10B.1–10B.4, 10B.6–10B.8 | Training capture, attendance, certificates (individual+batch+resource-speaker), auto-link, history, scoreboard | **DONE** | Comprehensive — this module is well-built |
| 10B.5 | No-code certificate template customization | **NOT DONE** | Template is a hardcoded Blade view; no named templates, no live preview, no visual editor |
| 10B.9 | Training-specific RBAC bits | **NOT DONE** | No `training_create`/`certificate_generate`/etc. bits found |
| Cross-cutting JO/COS guardrail | **DONE** | Enforced in `LeaveGuardrail` |
| 12.1 One shared scoreboard component | **DONE** | `x-scoreboard-tile` component, no bespoke duplicates for registered metrics |
| 12.2 Equitable tiering (Full/Compact/Inline) | **DONE** | Present across Dashboard, Training, GAD |
| 12.3 Canonical ownership / non-redundancy | **PARTIAL — known, longstanding gap** | `MetricRegistry` correctly owns ~9 metrics, but `DashboardController` still independently computes dozens of its own ad-hoc counts outside the registry (this matches what was already known from prior work in this project) |
| 12.4 Display standard | **DONE** | Count-up animation, plain-language captions, WCAG markers present |
| 12.5 Privacy/RBAC inside component | **NOT VERIFIED** | Gating appears to happen at controller level, not demonstrably inside the shared component itself |
| 12.6 Intelligent not noisy | **PARTIAL** | Some deltas/exceptions surfaced, but static low-value counts (PWD, solo parent) still always shown |
| 12.7 De-duplication audit & consolidation | **NOT DONE** | No Phase A/B/C consolidation map was ever produced; the ad-hoc `DashboardController` counts from 12.3 remain unconsolidated |
| 12.8 Metric Ownership Registry completeness | **PARTIAL** | ~9 of 15+ spec'd metrics have a registered canonical owner; the rest (age brackets, funding-status vacancy breakdowns, RBAC sensitive-bit holder counts) are not registered anywhere |

---

## Executive Summary — Where This Actually Stands

**Solidly built and production-credible:**
- Renewal signal architecture (Module 1A) — genuinely matches the spec's single-source-of-truth design
- Incident Report module (3A) — all 8 sub-requirements done, including the AI-advisory/human-review split
- Deliberation workspace (5) — all 8 profile accordions faithfully implemented
- TWG scoring + blind ID (6.1–6.4, 6.6) — dynamic, not hardcoded
- IDCC core pipeline structure (Stages 1, 3–8) — schema and logic are correct
- Training/L&D data model and certificate generation (10B.1–10B.4, 10B.6–10B.8)
- Photo enforcement (7), Export layouts (8)

**Real gaps worth prioritizing, roughly by risk:**
1. **RBAC matrix doesn't expose the sensitive bits it's supposed to be the control surface for** (4A.3/4A.4) — `renewal_commit`, `raccs_access`, `spi_unmask` etc. aren't matrix columns, even though the underlying protections work in their own controllers. This means there's no single place an Administrator can see/govern who holds these powers, which is the entire point of Module 4A.
2. **Plantilla Mismatch Block doesn't actually block** (3.2) — the validator can detect a mismatch but nothing stops the save.
3. **Tesseract isn't installed** (Stage 2) — every scanned/photographed document currently fails OCR on this host. This is an environment/ops fix, not a code fix.
4. **No archival/disposal workflow** (1B.3, 1B.4) — records never transition to ARCHIVED and there's no NAP Form No. 3-gated disposal path, despite the retention matrix itself (1B.5) being built.
5. **Scoreboard consolidation was never done** (12.3/12.7) — `DashboardController` still computes many of its own numbers outside `MetricRegistry`, which is exactly the redundancy Module 12 exists to eliminate.
6. **Global top-nav layout never happened** (0.2) — still a left sidebar, not the horizontal header the spec calls for.
7. **9A capture is webcam-only** — scanner, Bluetooth, handwriting/vision-tier OCR, and the universal attachment widget are all unbuilt; only exact-hash duplicate detection exists, without the resolution UI the spec requires.
8. **Monitoring Board is read-only** (6A.8–6A.10) — Chairperson controls (Remind, closing guard, snapshot export) don't exist yet, only the passive completion matrix.
9. **Leave violation detection is architecturally limited** (2B.1) — there is no DTR/attendance data source in this system at all, so tardiness/undertime detection isn't possible without first building that data pipeline; only AWOL and habitual absenteeism (leave-record-based) are detected.

This reflects an honest, code-verified state — not an optimistic reading of file names or docblocks. Several sub-agents explicitly flagged places where a class or table exists with the right name but isn't actually wired into the enforcement path the spec requires (3.2, 2B.2, 4A.4 being the clearest examples).
