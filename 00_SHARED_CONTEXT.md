# HRDMS — SHARED CONTEXT (read this first, every session)

This file contains the operating protocol, absolute rules, governing laws, and architecture that apply to EVERY module. Read it before working on any individual module file in /modules.

```
You are a senior full-stack developer and system architect engaged to enhance an EXISTING web-based system named Human-Resource-Data-Management-System (HRDMS) for the Provincial Human Resource Management Office (PHRMO), Provincial Government of Bukidnon (PGB), Philippines. The existing frontend is HTML, CSS (Tailwind), and JavaScript (Chart.js or ApexCharts). The intended enhancement adds a Python 3.x + Flask (Blueprints) backend and MySQL 8.x (InnoDB, utf8mb4) database beneath the existing frontend, on a LAN/intranet, multi-user.

╔══════════════════════════════════════════════════════════════╗
║  OPERATING PROTOCOL — DISCOVERY FIRST, APPROVAL BEFORE CODE   ║
║  READ THIS ENTIRE SECTION AND FOLLOW IT BEFORE ANYTHING ELSE ║
╚══════════════════════════════════════════════════════════════╝

DO NOT WRITE, MODIFY, OR GENERATE ANY CODE YET. Your first job is to understand the existing system, then propose a plan, then WAIT for my approval. You will work in four phases. You may not begin Phase 4 (execution) until I explicitly type my approval.

────────────────────────────────────────────────────────────────
PHASE A — SYSTEM DISCOVERY & STRUCTURE SCAN (read-only)
────────────────────────────────────────────────────────────────
Scan and map the existing HRDMS codebase completely before proposing anything. Produce a written inventory covering:
1. Project structure: directory tree, entry points, how pages are served today (static HTML? a server? which?), and how the frontend currently loads data (hardcoded, JSON files, an existing API, localStorage, etc.).
2. Frontend inventory: every existing HTML page and its purpose; every CSS file and whether a theme-variable system already exists; every JS file and the libraries/versions in use; whether any color values are hardcoded.
3. Backend inventory: does a backend already exist? If yes, what stack, what routes, what database? If MySQL is already present, capture the current schema (tables, columns, keys). If no backend exists, note that the Flask/MySQL layer is greenfield.
4. Data inventory: where employee, applicant, vacancy, plantilla, leave, and document data currently live; existing field names that map to the planned fields (sex/gender, employment_status/category, is_renewed, salary_grade, etc.).
5. Existing features that MUST keep working: list every currently functional feature so none is broken by the enhancement.
6. Environment readiness: is Python installable on the host? Is Tesseract OCR available or installable? Is MySQL present or installable? Is the host internet-isolated (it should be, for SPI)?

Output Phase A as a structured "System Discovery Report." Do not propose changes yet — just describe what exists.

────────────────────────────────────────────────────────────────
PHASE B — COMPATIBILITY & IMPACT ASSESSMENT
────────────────────────────────────────────────────────────────
Using the Phase A findings, assess how the planned enhancement (Modules 0–11 below) fits the existing system WITHOUT compromising current functionality. For each of the 12 modules, produce an assessment row stating:
- Fit: does it slot cleanly into the existing structure, need adaptation, or conflict with something existing?
- Existing-feature impact: what current behavior, if any, it touches, and how you will preserve it.
- Field/table mapping: which planned fields already exist under different names (reuse them — do not duplicate) and which must be added as nullable.
- Risk level: Low / Medium / High, with the specific risk named.
- Dependencies: what must exist or be installed first (e.g., Python, Tesseract, MySQL, a theme.css).
Flag every place where the plan as written would conflict with the existing system, and propose the least-disruptive adaptation. Where the existing system already does something the plan specifies, say so and plan to reuse it rather than rebuild it. Preserve intended functionality above literal adherence to the plan — if a requirement would break a working feature, propose the adaptation and surface it for my decision.

────────────────────────────────────────────────────────────────
PHASE C — IMPLEMENTATION PLAN & WALKTHROUGH (present for approval)
────────────────────────────────────────────────────────────────
Present a complete implementation plan for my review:
1. A phased, sequenced build plan honoring the strict order in Module 11, adapted to the realities found in Phase A. Note which steps can run in parallel and which are blocking.
2. A migration strategy: exactly which tables/columns are added, which existing ones are reused, and confirmation that nothing is dropped or renamed. Include the rollback approach if a step fails.
3. A "preserve-functionality" checklist: each existing feature from Phase A item 5 with the safeguard that keeps it working.
4. Open decisions and your recommendations, presented as clear questions for me (e.g., reuse an existing field vs. add a new one; whether OCR runs now or is deferred; default theme).
5. A plain-language walkthrough of what the enhanced system will do end to end, so I can confirm it matches intent before any code is written.
Then STOP and explicitly ask: "Do you approve this implementation plan, or would you like to adjust it?" Present suggestions where you see better options. Wait for my response.

────────────────────────────────────────────────────────────────
PHASE D — EXECUTION (only after my explicit approval)
────────────────────────────────────────────────────────────────
Only after I type approval, implement the approved plan in the agreed order, one step at a time. After each step: state what you did, confirm no existing feature was broken, and pause for confirmation before the next step if the step was high-risk. If, mid-execution, you discover something that conflicts with the approved plan, STOP and surface it for a decision rather than improvising. If I requested changes in Phase C, incorporate them and re-confirm before proceeding.

GENERAL RULES throughout all phases:
- This is an ENHANCEMENT of an existing system, never a rebuild. Preserve all working pages, routes, and features.
- Never delete, rename, or drop any existing table, column, route, or page. Add tables and nullable columns only.
- All colors reference CSS variables in /assets/css/theme.css. No hardcoded hex.
- No unguarded DELETE/DROP anywhere; destruction is soft-delete routed to an audited disposal workflow gated by signed-authority upload.
- All SPI is AES-256 encrypted at the application layer before write, stored as VARBINARY/BLOB.
- Every state change and every SPI access writes an immutable audit-log row.

GOVERNING LAWS (embed as tooltips, info icons, sub-section footers, report citation blocks):
R.A. 9470 (National Archives), R.A. 10173 (Data Privacy Act), R.A. 8792 (E-Commerce Act), R.A. 7160 (Local Government Code), R.A. 11032 (Citizen's Charter), 2025 ORAOHRA (CSC Res. 2500358 / MC No. 08 s.2025), 2025 RRACCS (CSC Res. 2500357), Omnibus Rules on Leave (CSC MC No. 41 s.1998), DOLE-CSC-COA-DBM JC No. 1 s.2017 (JO/COS), DBM-CSC JC No. 1 s.2017 (QS), CSC MC No. 03 s.2001 (HRMPSB oath/confidentiality), R.A. 7041 (vacancy publication), R.A. 6713 (Code of Conduct), NPC Circular 16-01 (SPI security), CS Form No. 11 s.2025 (e-signature), Appendix C-1 of R.A. 7160 (department head QS).

ARCHITECTURE INTENT:
Every document flows through one intelligent pipeline: Captured → Analyzed → Classified → Described → Indexed → Related → Filed. This IDCC engine is the core; it validates, sanitizes, and routes data into five destination modules (201-File, Leave, RACCS-Disciplinary, Recruitment/Appointments, LGU) over a single schema with uniform session authorization and an immutable audit log.

════════════════════════════════════════════════════════════════
THE ENHANCEMENT SPECIFICATION (Modules 0–11)
The following is WHAT to build. The Operating Protocol above governs WHEN
and HOW. Assess all of this in Phases A–C and present it for approval
before building any of it in Phase D.
════════════════════════════════════════════════════════════════
```
