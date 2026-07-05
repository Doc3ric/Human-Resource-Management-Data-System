# MULTI_CODER.md — Coordinating Claude Code + Google Gemini on HRDMS

This project may be built/reviewed by more than one AI coder. Follow these rules to avoid
merge collisions, duplicated infrastructure, and drift from the single-source-of-truth design.
The golden rule: **never have two coders writing to the same branch or the same shared file at the same time.**

────────────────────────────────────────────────────────
## THE THREE SAFE MODES (pick one; don't freelance)
────────────────────────────────────────────────────────

### MODE B (RECOMMENDED) — Build with one, review with the other
- Claude Code BUILDS a module. Commit it.
- Gemini then REVIEWS the committed code (read-only): bugs, SQL correctness, RBAC logic,
  SPI handling, security gaps, citation accuracy. Gemini writes NO code — it reports findings.
- You decide which findings to apply; the builder applies them.
- Zero merge conflict (only one writer). Best quality-to-pain ratio. Can alternate per module.

### MODE A — Parallel build, split by MODULE ownership (only if you need speed)
- One tool OWNS the foundation and builds it FIRST (see ownership table below).
- After foundation is committed, the two tools build NON-OVERLAPPING modules on SEPARATE branches.
- Merge at module completion, never mid-build. One owner per shared file, always.

### MODE C — Split by LAYER
- One tool backend (Flask/MySQL), the other frontend (HTML/CSS/JS), against a written API contract.
- Define endpoints up front so both build to the same contract. Clean boundary required.

────────────────────────────────────────────────────────
## FOUNDATION = SINGLE-OWNED, BUILT FIRST (all modes)
────────────────────────────────────────────────────────
These are shared infrastructure. ONE tool builds them, they are committed, THEN anything else starts.
Never let a second tool modify these in parallel:
- MySQL master schema / DDL (M11)
- Flask app factory + Blueprint registration (M11)
- RBAC permission matrix (M4A)
- theme.css variable system (M0 layout / cross-cutting)
- IDCC pipeline (M9) + capture front door (M9A)
- Shared services: metrics service, scoreboard component (M12), renewal-commit service (M1A),
  capture widget (M9A) — each built ONCE, owned by ONE tool.

Recommended foundation owner: **Claude Code** (legal-citation + SPI discipline is heaviest here).

────────────────────────────────────────────────────────
## SUGGESTED MODULE OWNERSHIP (Mode A)
────────────────────────────────────────────────────────
CLAUDE CODE (foundation + legally-sensitive, citation-heavy, SPI/RACCS):
  M11 schema/skeleton · M4A RBAC · M0 auth · M9 IDCC · M9A capture ·
  M1 / M1A / M1B status+renewal+retention · M2 NAP retention ·
  M2B leave violations · M3A incidents · M5/M6/M6A deliberation+scoring+monitoring ·
  M10 destination wiring (RACCS/Leave guardrails)

GEMINI (self-contained features against the committed foundation):
  M10B Training/L&D · M8 export/print layouts · M3 plantilla sync ·
  M7 photo management · M4 AE masking · (M5.4 screening report if split cleanly)

BUILD LAST, single-owned: M12 scoreboard (consolidates all modules; needs them to exist first).

Rule: a feature module is built only AFTER the foundation it depends on is committed.
Cross-check dependencies in BUILD_ORDER.md before assigning.

────────────────────────────────────────────────────────
## GIT RULES (non-negotiable with two coders)
────────────────────────────────────────────────────────
1. main = always working, reviewed code only. Never code directly on main.
2. One BRANCH per module: e.g.  feature/M4A-rbac-matrix , feature/M10B-training.
3. One tool per branch. Two tools never share a branch.
4. Commit at module boundaries with clear messages: "M4A: RBAC matrix — schema+UI+enforcement".
5. Pull/rebase on main BEFORE starting a new module so you build on the latest foundation.
6. Merge a module to main only after it builds, passes its QA, and (Mode B) is reviewed.
7. If both tools must touch a shared file, STOP — that file has one owner; route the change to them.
8. Tag foundation milestones (e.g. v-foundation) so feature branches have a known-good base.

────────────────────────────────────────────────────────
## GIVE BOTH TOOLS THE SAME RULES
────────────────────────────────────────────────────────
Both Claude Code and Gemini must read the SAME spec files (plain Markdown, tool-agnostic):
- 00_SHARED_CONTEXT.md  (protocol, laws, architecture, absolute rules)
- the specific modules/<MODULE>.md being worked on
- CLAUDE.md rules apply to BOTH tools even though the filename says Claude — treat it as the
  project ruleset. (Optionally copy it to GEMINI.md / AGENTS.md so Gemini auto-loads it too.)

────────────────────────────────────────────────────────
## PROMPT TEMPLATES
────────────────────────────────────────────────────────

### Build prompt (either tool, Mode A/C)
"Read 00_SHARED_CONTEXT.md and modules/<MODULE>.md. You are on branch feature/<MODULE>.
Run Phase A discovery against the existing code, then Phase B assessment, then present a
Phase C plan and STOP for my approval before writing code. Build ONLY this module. Do not
modify foundation/shared files owned elsewhere (schema, RBAC, theme.css, IDCC, shared services)
— if you need a change there, list it for me instead of editing it."

### Review prompt (Gemini, Mode B — read-only)
"Read 00_SHARED_CONTEXT.md and modules/<MODULE>.md. Review the committed code for <MODULE>
WITHOUT modifying anything. Check: correctness vs the spec, SQL/schema integrity, RBAC
enforcement, SPI/AES-256 + audit-log coverage, no unguarded DELETE/DROP, no hardcoded hex
(theme.css only), legal-citation accuracy, and the single-source-of-truth rules (no duplicate
metrics/services). Report findings as a prioritized list: blocking / should-fix / nice-to-have.
Do not write code; propose changes for the builder to apply."

────────────────────────────────────────────────────────
## WHAT NOT TO DO
────────────────────────────────────────────────────────
- Don't run both tools writing on the same branch or same file simultaneously.
- Don't let either tool rebuild shared infrastructure (metrics, scoreboard, capture, renewal
  commit) — those are single-owned and referenced, never duplicated.
- Don't start a feature module before its foundation dependency is merged to main.
- Don't skip the Phase C approval stop, regardless of tool.
- Don't let two tools each invent their own schema columns for the same concept — foundation owner decides schema.
