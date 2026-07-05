# CLAUDE.md — HRDMS Project Memory (Claude Code auto-reads this)

## What this project is
Enhancement of an EXISTING web-based Human-Resource-Data-Management-System (HRDMS) for PHRMO, Provincial Government of Bukidnon. Existing frontend: HTML/CSS (Tailwind)/JS (Chart.js/ApexCharts). Adding a Python 3.x + Flask (Blueprints) backend and MySQL 8.x (InnoDB, utf8mb4). LAN/intranet, multi-user.

## ABSOLUTE RULES (never violate, every session)
1. ENHANCEMENT, not rebuild. Preserve all existing pages, routes, working features.
2. Never delete/rename/drop any existing table, column, route, or page. Add nullable columns / new tables only. Read existing structures as-is.
3. All colors via CSS variables in /assets/css/theme.css. No hardcoded hex.
4. No unguarded DELETE/DROP. Destruction is soft-delete to an audited disposal workflow gated by signed-authority upload (NAP Form No. 3).
5. All SPI encrypted AES-256 at app layer before write (VARBINARY/BLOB). Passwords bcrypt. MFA (pyotp) on disciplinary/SPI views.
6. Every state change and SPI access writes an immutable audit-log row.
7. On-premise by default; external AI only for explicitly non-SPI docs after privacy pre-check + admin opt-in (see M9A).

## HOW TO WORK (per session)
- This is a 21-module build. Work ONE module per session.
- ALWAYS read `00_SHARED_CONTEXT.md` first (operating protocol, laws, architecture).
- Then read the ONE module file from `/modules` you are told to build.
- Follow the protocol: Phase A discovery (scan existing code) → Phase B compatibility assessment → Phase C plan + STOP for my approval → Phase D execute after approval.
- Do NOT build other modules. Do NOT skip the approval stop.
- Commit after each module with a clear message. Re-read CLAUDE.md if context resets.

## Build order (foundational first)
See `BUILD_ORDER.md`. Do not jump ahead; later modules depend on earlier foundations (schema, RBAC matrix, IDCC pipeline).

## Source of truth
The full unified spec is `HRDMS_UNIFIED_MASTER_PROMPT_v20.txt` (kept for reference). The chunked per-module files in `/modules` are what you build from session to session.

## Multi-coder note
If more than one AI coder is used (e.g. Claude Code + Gemini), follow `MULTI_CODER.md`:
one owner per shared/foundation file, one branch per module, build foundation first,
prefer build-with-one / review-with-the-other. These rules apply to every tool.
