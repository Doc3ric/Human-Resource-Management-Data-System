# HRDMS BUILD ORDER (for Claude Code — one module per session)

Build in PHASES. Within each phase, follow the order. Commit after each module.
Foundational modules MUST come before the features that depend on them.

## PHASE 0 — Discovery (do once, no code)
- Session 0: Read 00_SHARED_CONTEXT.md. Run ONLY Phase A discovery across the existing
  codebase. Produce the System Discovery Report. Commit it as DISCOVERY_REPORT.md. No code.

## PHASE 1 — Foundation (everything else depends on these)
1. M11  — Implementation Sequence / MySQL master schema (DDL) + Flask app factory + Blueprints.
          (Build the schema and skeleton first. This is the backbone.)
2. M4A  — RBAC Permission Matrix (master control surface). Build before feature modules so
          their access checks resolve against a real matrix.
3. M0   — Authentication, landing page, top-header layout, password quarantine.
4. M9   — IDCC pipeline core (8 stages).
5. M9A  — Multi-source capture + tiered extraction + duplicate detection (front door to IDCC).

## PHASE 2 — Core data gates & records
6. M1   — Active status gate (is_renewed).
7. M1A  — Synchronized renewal status & signal architecture.
8. M1B  — Lifecycle exclusion & records retention matrix.
9. M3   — Data integrity & plantilla sync.
10. M10 — Destination modules wiring (201/Leave/RACCS/Recruitment/LGU over one schema).

## PHASE 3 — Recruitment & deliberation
11. M2  — Recruitment view & NAP retention lifecycle.
12. M4  — RBAC: Appointment Encoder masking.
13. M5  — Split-screen deliberation workspace & screening.
14. M7  — Photo management.
15. M6  — TWG scoring, agenda prep, masking.
16. M6A — HRMPSB deliberation monitoring board.
17. M8  — Export, print, compliance layouts.

## PHASE 4 — HR operations
18. M2B — Leave violation monitoring, notices, payroll coordination.
19. M3A — Incident report with AI-assisted citation & counseling.
20. M10B— Learning & Development (training) module.

## PHASE 5 — Cross-cutting finish
21. M12 — Dynamic statistical scoreboard (system-wide). Build LAST so each module's
          canonical metrics already exist; includes the Phase-A audit/consolidation of
          any existing displays and the metric ownership registry.

## Per-session prompt template (paste into Claude Code)
"Read 00_SHARED_CONTEXT.md and modules/<MODULE>.md. Run Phase A discovery for this module
against the existing code, then Phase B assessment, then present a Phase C implementation
plan and STOP for my approval before writing code. Do not touch other modules."
