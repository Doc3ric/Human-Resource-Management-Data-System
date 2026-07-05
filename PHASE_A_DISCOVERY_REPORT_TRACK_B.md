# HRDMS — Phase A System Discovery Report (Track B)

**Scope:** Discovery of Track B modules against the existing codebase (Laravel 12 / PHP 8.2).

## Findings on Track B Modules

Based on the existing codebase (which completed Phase 0 Foundation), the status of Track B modules is as follows:

1. **Module 4 (RBAC: Appointment Encoder):** `AppointmentEncoderMaskingTest` exists and passes. AE masking and application letter attachment logic appear to be fully implemented.
2. **Module 5 (Split-Screen Deliberation Workspace):** The UI shell exists (`resources/views/recruitment/deliberation/show.blade.php`) and is covered by `DeliberationWorkspaceTest`. The left/right panel division (45%/55%) and applicant chip selector are implemented.
3. **Module 6 (TWG Scoring, Agenda Prep, Masking):** Auto-scoring and TWG evaluations are implemented, backed by `TwgDynamicScoringService` and verified by `TwgDynamicScoringTest` and `BlindScoringViewsTest`.
4. **Module 6A (HRMPSB Deliberation Monitoring Board):** **MISSING.** No views, endpoints, or components exist for the real-time monitoring board. The `hrmpsb_panel_members` table or equivalent may exist but the UI for tracking completion states is absent.
5. **Module 7 (Photo Management):** `PhotoEnforcementTest` exists and passes. Mandatory photo checks are implemented.
6. **Module 8 (Export, Print, Compliance Layouts):** **MISSING / INCOMPLETE.** The three export buttons ("Print Applicant Profile", "Print Scoring Sheet", "Export All Applicants — Comparative Assessment") are missing from the `show.blade.php` header bar. The corresponding PDF/CSV endpoints for Layouts A, C, and D need to be verified or created.
7. **Module 9A (Multi-Source Document Capture & Vision Enrichment):** **MISSING.** The IDCC backend pipeline exists, but the universal front-door capture layer (Webcam, TWAIN scanner bridge, Bluetooth) and the Tiered Extraction rules (local vs external LLM gating) are not built.
8. **Module 10 (Track B: M3, M4, M5):**
    *   M3 (RACCS-Confidential): Implemented (verified by `RaccsMfaTest` and `ESignatureAndDisciplinaryTest`).
    *   M4 (Appointments): The E-signature PNPKI hook is partially there. However, validation for "QS deficiency vs. R.A. 7160 Appendix C-1" is missing (no schema or validation logic exists for it).
    *   M5 (LGU Documents): Implemented (verified by `LguDocumentTest`).

## Conclusion
The core of the recruitment and deliberation process is already built in Laravel. Track B's execution will focus strictly on filling the identified gaps: Module 6A (Monitoring Board), Module 8 (Export buttons/layouts), Module 9A (Multi-Source Capture UI and tier routing), and Module 10's Appointment QS validation.
