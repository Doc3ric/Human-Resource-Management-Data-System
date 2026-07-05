# HRDMS Implementation Spec — Chunked for Claude Code

## How to use
1. Always start a session by having Claude Code read `00_SHARED_CONTEXT.md`.
2. Then point it at ONE module file from `/modules`.
3. Let it run the discovery→assess→plan→approval→execute flow for that module only.
4. Commit after each module. Start a fresh session for the next.

## Recommended build order
(Foundational modules first; features after; the spec's Module 11 is the authoritative sequence.)

| # | File | Module |
|---|------|--------|
| 1 | modules/M0.md | MODULE 0 — AUTHENTICATION, LANDING PAGE, GLOBAL LAYOUT |
| 2 | modules/M1.md | MODULE 1 — ACTIVE STATUS GATE (CONTRACT RENEWALS) |
| 3 | modules/M1A.md | MODULE 1A — SYNCHRONIZED RENEWAL STATUS & SIGNAL ARCHITECTURE |
| 4 | modules/M1B.md | MODULE 1B — EMPLOYEE LIFECYCLE EXCLUSION & RECORDS RETENTION MATRIX |
| 5 | modules/M2.md | MODULE 2 — RECRUITMENT VIEW & NAP RETENTION |
| 6 | modules/M2B.md | MODULE 2B — LEAVE VIOLATION MONITORING, NOTICE GENERATION & PAYROLL COORDINATION |
| 7 | modules/M3.md | MODULE 3 — DATA INTEGRITY & PLANTILLA SYNC |
| 8 | modules/M3A.md | MODULE 3A — INCIDENT REPORT RECORDING WITH AI-ASSISTED CITATION & COUNSELING SUPPORT |
| 9 | modules/M4.md | MODULE 4 — RBAC: APPOINTMENT ENCODER (AE) |
| 10 | modules/M4A.md | MODULE 4A — RBAC PERMISSION MATRIX (MASTER CONTROL SURFACE) |
| 11 | modules/M5.md | MODULE 5 — SPLIT-SCREEN DELIBERATION WORKSPACE & SCREENING |
| 12 | modules/M6.md | MODULE 6 — TWG SCORING, AGENDA PREP, MASKING |
| 13 | modules/M6A.md | MODULE 6A — HRMPSB DELIBERATION MONITORING BOARD |
| 14 | modules/M7.md | MODULE 7 — PHOTO MANAGEMENT |
| 15 | modules/M8.md | MODULE 8 — EXPORT, PRINT, COMPLIANCE LAYOUTS |
| 16 | modules/M9.md | MODULE 9 — IDCC: INTELLIGENT DOCUMENT CAPTURE & CLASSIFICATION |
| 17 | modules/M9A.md | MODULE 9A — MULTI-SOURCE DOCUMENT CAPTURE & VISION ENRICHMENT (IDCC FRONT DOOR) |
| 18 | modules/M10.md | MODULE 10 — DESTINATION MODULES (over one schema) |
| 19 | modules/M10B.md | MODULE 10B — LEARNING & DEVELOPMENT (TRAINING) MODULE |
| 20 | modules/M11.md | MODULE 11 — IMPLEMENTATION SEQUENCE (strict; adapt to Phase A findings) |
| 21 | modules/M12.md | MODULE 12 — DYNAMIC STATISTICAL SCOREBOARD (SYSTEM-WIDE, EQUITABLE, NON-REDUNDANT) |
