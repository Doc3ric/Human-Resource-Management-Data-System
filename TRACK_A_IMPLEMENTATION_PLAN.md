# HRDMS Track A — Phase C Implementation Plan

## 1. Sequenced build plan

1. **Embed the Renewal Status Widget** (1A.5) into `plantilla/show.blade.php` (or edit, whichever is the personnel-inventory detail view), `batch-renewal/index.blade.php`, and `performance/index.blade.php`. No blocking dependency; can happen first.
2. **NAP Form 3 disposal-authorization gate** (1B.4): migration for `disposal_authorizations`, a small `DisposalAuthorizationService`, wire it into `ArchiveController::forceDelete()` and `bulkForceDelete()`, add the upload UI on the Archives page. Blocking dependency: none, but sequenced second since it changes real destructive behavior and deserves full attention on its own.
3. **Module 2 vacancy fill-lifecycle**: migration adding `is_filled`/`date_filled` to `plantilla_records`; a `VacancyLifecycleService` (Active/Archived/Valueless-Holding-Queue stage computation); a console command for the daily stage flip; a read-side "Recruitment View" page showing the three stages, reusing the Disposal View pattern from step 2 for the Valueless Holding Queue's eventual disposal. Depends on step 2's disposal-authorization mechanism (the Valueless Holding Queue's disposal reuses it, not a second gate).
4. **Certificate template customization** (10B.5) — deferred, not built this pass. Documented in Open Decisions below.

## 2. Migration strategy

Two new tables only, both additive: `disposal_authorizations` (polymorphic, references `documents` + `retention_schedule`) and two nullable columns (`is_filled`, `date_filled`) on `plantilla_records`. Nothing dropped, renamed, or made non-nullable. Rollback: each migration has a symmetric `down()`; each is its own commit, revertible independently via `git revert` without touching the others.

## 3. Preserve-functionality checklist

| Existing feature | Safeguard |
|---|---|
| Archives page force-delete for Plantilla/JobOrder/Casual/Permanent | Same route, same permission gate, same button — now requires a disposal authorization record to exist first (checked, not replaced). Existing archived/restore flows untouched. |
| Batch Renewal / Performance / Plantilla show pages | Widget is additive markup only, `@unless($isRenewed)`-gated so it renders nothing for already-renewed records — no existing content removed or restructured. |
| Vacant-position reporting (PWD/IP/position/vacant reports) | `is_filled`/`date_filled` are new nullable columns, default null; every existing query continues to work unchanged until it explicitly opts into the new fields. |
| Full test suite | Run after every step; must stay green throughout. |

## 4. Open decisions and the choice made on each

1. **Certificate template customization (10B.5)** — decision: defer. Core certificate issuance (individual + batch + resource-speaker, correctly populated, retained via IDCC) already works; a live-preview visual template editor is a substantial standalone UI feature, not a fix to something broken. Building it under the same time budget as the other three gaps would mean doing it shallowly. Recorded here rather than silently skipped.
2. **Disposal-authorization scope** — decision: build ONE shared mechanism (`disposal_authorizations` + `DisposalAuthorizationService`) used by both the personnel-records Archive disposal (gap 2) and the future Valueless Holding Queue disposal (gap 3), rather than two separate ad-hoc gates — consistent with the project's single-source-of-truth principle already applied elsewhere (renewal commit, metrics registry).
3. **Module 2's "Recruitment View"** — decision: build as a new Track-A-owned read-side page over `plantilla_records` only (its own blueprint/route, e.g. `/vacancy-lifecycle` or under the existing Plantilla area), never touching Track B's `/recruitment` blueprint or `applicants` table, resolving the boundary ambiguity noted in Phase B.
4. **PlantillaSyncValidator's existing read of `Applicant`** — decision: leave as-is. It's a pre-existing, working, tested boundary crossing (read-only); removing it to satisfy the letter of the ownership matrix would break real functionality for no gain. Flagged for whoever reviews across both tracks at the Phase 2 joint-integration sync point.

## 5. Plain-language walkthrough

After this pass: anywhere an employee's contract isn't yet renewed, the same warning badge — with the same "Request Renewal" button — appears consistently on their personnel record, in Batch Renewal, and in the Performance tracker, because it's one shared component reading one source of truth. Permanently deleting a personnel record from the Archive recycle bin now requires a records officer to first attach a scanned, signed NAP Form No. 3 (Authority to Dispose) — the system will not allow the hard-delete button to succeed without one on file, closing a real gap against this project's own no-unguarded-delete rule. Vacant plantilla positions now track when they were filled, and a new read-only view shows which ones are still active, which have aged into archived status, and which are old enough (2+ years past filling) to enter the valueless-holding queue for eventual lawful disposal — using the same disposal-authorization mechanism as recycle-bin records, not a second one. Certificate template customization remains on the fixed default layout for now, flagged as a deliberate scope cut, not an oversight.
