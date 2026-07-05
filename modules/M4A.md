# MODULE 4A — RBAC PERMISSION MATRIX (MASTER CONTROL SURFACE)

> Read 00_SHARED_CONTEXT.md first. Build only this module in this session. Follow the discovery→assess→plan→approval→execute protocol from the shared context. Do not modify other modules.

```
MODULE 4A — RBAC PERMISSION MATRIX (MASTER CONTROL SURFACE)
Additive to Module 4. The RBAC matrix is the single administrative surface that enumerates EVERY module and sub-module in the system, grouped by main-module section, where the Administrator assigns permissions per user freely. It is the authoritative map of system capabilities; every access decision elsewhere reads from it.
4A.1 Structure: a hierarchical matrix organized by Main Module (section header) → Sub-Module (row), with permission columns. Sections collapse/expand. Each sub-module row exposes permission bits: View/Access, Add, Edit, Delete, plus feature-specific bits where applicable (e.g., renewal_commit, raccs_access, spi_unmask, evaluation_unlock, disposal_approve, remind_members, generate_agenda). Logical dependency: if Add, Edit, or Delete is checked, the system auto-enables, locks, and saves the corresponding View/Access bit.
4A.2 Assignment model: the Administrator assigns permissions per user (and may use role templates as presets that pre-fill the matrix, still editable per user). Assignment is free-form within the matrix — any user may be granted any combination — but every grant is recorded. The matrix renders the same section grouping for every user so the Administrator navigates a consistent map.
4A.3 The matrix must enumerate all current sections and sub-modules, including at minimum:
- AUTHENTICATION & ADMIN (Module 0): User Registration, Password/Account Management, Global Layout Settings, RBAC Matrix itself (only Administrators may hold Edit on the RBAC sub-module), Audit Log Viewer.
- RENEWAL & ACTIVE STATUS (Modules 1, 1A): Active Status Gate, Batch Renewal, Renewal Status Widget, Renewal Signal review, Renewal Commit (renewal_commit bit — default PHRMO/Appointments only), Request-to-Renew.
- RECRUITMENT & RETENTION (Modules 2, 3): Recruitment View Table, Archived/Lifecycle View, Valueless Holding Queue, Disposal View (disposal_approve bit), Vacant Positions Dropdown, Master Plantilla Sync.
- APPOINTMENT ENCODER (Module 4): Applicant Entry Sheets, Application Letter / Override, with the AE-masked items (TWG Scoring, HRMPSB Deliberation, Batch Renewal, Contract Config) shown but lockable/hidden per role.
- DELIBERATION WORKSPACE (Modules 5, 6, 6A): Open Deliberation Panel, Left Applicant Profile, Examination Routing/Screening, TWG Scoring Sheet, Agenda Preparation (generate_agenda bit), Evaluation Unlock (evaluation_unlock — Chairperson), Monitoring Board, Remind Members (remind_members — Chairperson/Secretary), CER Template.
- PHOTO MANAGEMENT (Module 7): Photo Upload, 201-File Import.
- EXPORT & PRINT (Module 8): Examiner's Copy (full PII), Public Posting (masked), Scoring Sheet, Comparative Assessment Matrix, Monitoring Snapshot.
- IDCC & DOCUMENTS (Modules 9, 10 M1): Document Ingestion, OCR/Index, Classification, SPI Unmask (spi_unmask bit), 201-File/Records, Disposal Workflow.
- LEAVE (Module 10 M2): Leave Ledger, Leave Application, Accrual Processing.
- DISCIPLINARY / RACCS (Module 10 M3): Case Registry, RACCS-Confidential Access (raccs_access bit — MFA-gated, Disciplining Authority/Legal Officer/CODI only).
- APPOINTMENTS (Module 10 M4): EETE Evaluation, Department-Head QS Validation, E-Signature/PNPKI, Eligibility Verification.
- LGU DOCUMENTS (Module 10 M5): EO/Memo/Ordinance Registry, Service Request Tracking, Citizen's Charter Timers.
- GAD & ANALYTICS: GAD Analytics modules, Executive Dashboard, Personnel Inventory, Performance Management/SPMS.
(The matrix must auto-include any sub-module added later; new sub-modules register into the matrix under their main-module section so the map stays complete.)
4A.4 Sensitive-bit guardrails: high-risk bits (raccs_access, spi_unmask, renewal_commit, disposal_approve, evaluation_unlock) are visually flagged in the matrix and, where law requires, gated by additional controls regardless of the checkbox (e.g., raccs_access still requires MFA per Module 9 Stage 5; spi_unmask still writes to tbl_spi_access_log). Granting a checkbox never bypasses a statutory control — it authorizes the role to attempt the action, which the underlying guardrail still validates.
4A.5 Enforcement: every backend route and frontend element checks the matrix before rendering/executing. Unauthorized elements are not rendered (not merely disabled) for clean masking, consistent with Module 4. Every permission change (grant/revoke/role-template edit) writes to tbl_audit_log with actor, target user, sub-module, bit, and timestamp.
4A.6 UI: render as a collapsible per-section grid; provide search/filter by module or user; show an at-a-glance summary per user of granted sections; only Administrators may edit the RBAC sub-module itself (a user cannot escalate their own permissions). Use theme.css variables throughout; flag sensitive bits with var(--color-danger) accents.
```
