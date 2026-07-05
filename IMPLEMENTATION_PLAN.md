# HRDMS — Phase C Implementation Plan

Scope: close the real remaining gaps identified in `PHASE_B_COMPATIBILITY_ASSESSMENT.md`. This is a gap-closing pass, not a rebuild — 6 of 8 Phase 0 items are already complete and untouched by this plan.

## 1. Sequenced build plan

Steps 1–4 are independent of each other (no shared files) and could run in parallel; run sequentially anyway for a clean, reviewable commit history. Step 5 depends on nothing but is last because it's the highest-uncertainty item.

1. **Checkpoint commit** — commit the 396 files of pre-existing uncommitted work (prior session's module builds) plus the three phase docs, as a single honest checkpoint distinct from this session's own changes. *Blocking: everything below should land on top of a clean baseline.*
2. **Fix 3 stale scaffold tests** (`ExampleTest`, `AuthenticationTest::users_can_logout`, `ProfileTest::user_can_delete_their_account`) to assert the app's actual, intentional behavior (auth-gated home, `/login` redirect on logout, soft-delete on self-delete). Mechanical, zero functional-code change.
3. **Finish the `role:`→`permission:` middleware cutover** in `routes/web.php`, line-by-line: convert usages that map onto an existing matrix permission; leave statutory/destructive super-admin-only gates (rollback, backup, delete-all, retirement processing) on `role:` per Module 4A.4, with an inline comment explaining why each survivor stays.
4. **Wire `DashboardController`'s ad-hoc counts to `MetricRegistry`** for the metrics that already have a canonical entry (workforce sex/PWD/solo-parent, renewal not-renewed, leave violations, incidents by track, training hours, LGU overdue). Leave counts with no registry entry alone.
5. **Verify/add Step 6 sample JSON validation payloads** (A: appointment QS deficiency + PNPKI pending; B: 33-A permanent retention) as feature tests, alongside the already-covered C/D scenarios.
6. **Module 0.2 top-header nav** — attempt the conversion in `dashboard-app.blade.php`, then use the `run` skill to launch the app and screenshot several representative pages (dashboard, a list page, a form page, a report/print page) at both desktop and sub-1024px widths before considering this done. If the `run` skill cannot drive this Laragon/MySQL app in this environment, stop after the code change, leave it **uncommitted or on a clearly-labeled branch**, and hand off a documented visual-QA follow-up rather than merging an unverified whole-app chrome change.

## 2. Migration strategy

**No migrations in this plan add, drop, or rename any table or column.** Everything above is route middleware, a service-layer read path, test assertions, and Blade/CSS layout — no schema touched. This satisfies the "never delete/rename/drop; add nullable columns/tables only" rule trivially, because nothing here needs a new column.

**Rollback approach:** every step is an independent git commit; any step can be reverted with `git revert` without affecting the others, since none share modified files except step 6 (layout only) which touches nothing else does.

## 3. Preserve-functionality checklist

| Existing feature | Safeguard |
|---|---|
| Every route currently gated by `role:` | Re-verified against `RolePermissionController`'s matrix before any middleware swap; statutory/destructive gates explicitly left alone. |
| Dashboard stats page | `php artisan test` (full suite) run after the `MetricRegistry` wiring change; manual note added anywhere a displayed number is expected to shift (registry value is the corrected one per its own in-code comment). |
| All ~200 authenticated pages' navigation | `dashboard-app.blade.php` is edited once, in place — no route, controller, or page removed; every existing nav link/route target is preserved, only the chrome markup/CSS around it changes. Verified via `run` skill screenshots before being called done; if unverifiable, left uncommitted rather than merged blind. |
| Logout / account-deletion / guest-home behavior | Tests updated to match already-correct app behavior, not the other way around — no controller change. |
| Full test suite | Baseline 205/208 passing (3 pre-existing stale failures) re-confirmed at 208/208 after step 2, and re-run after every subsequent step. |

## 4. Open decisions and the choice made on each

1. **Flask/Python vs. Laravel** — decided in Phase B: discard Flask, build in Laravel. Reasoning there.
2. **Two parallel audit-log systems (`activity_logs` hand-rolled vs. Spatie `activity_log`)** — decision: **leave both**, do not consolidate in this pass. Neither can be dropped (rule #2), and unifying which one new code writes to is a larger, separately-scoped decision that touches many controllers outside this plan's gap list. Flagging as a follow-up, not silently ignoring it.
3. **`role:` survivors** — decision: keep role-gating for actions that are (a) destructive/irreversible (delete-all, rollback, backup restore) or (b) already explicitly commented in the code as intentionally role-only. Everything else migrates to `permission:`.
4. **DashboardController consolidation scope** — decision: only touch counts that already have a `MetricRegistry` entry. Do not attempt a full 12.7 audit/consolidation of every ad-hoc count in `DashboardController` in this pass — that is explicitly Module 12's own full-scope job (Phase 5 per `BUILD_ORDER.md`), and this plan is Phase 0 gap-closing, not Module 12 delivery.
5. **Module 0.2 nav rewrite verification** — decision: attempt it, but gate "done" on actual visual verification via the `run` skill rather than on code review alone, since a whole-app chrome change is exactly the class of UI change the project's own standards require browser verification for before it's reported complete.

## 5. Plain-language walkthrough

After this pass: every route that should be permission-gated for a real HR-office role is (except the small, deliberate set of super-admin-only destructive actions, which stay locked to the `super_admin` role itself, matching how the RBAC matrix's own guardrail rules describe sensitive bits). The dashboard's headline numbers for workforce composition, renewal status, leave violations, incidents, training, and LGU overdue requests are computed in exactly one place (`MetricRegistry`) and simply displayed wherever needed, so two screens can never disagree about the same figure. The IDCC pipeline's four representative document scenarios (appointment with a qualification-standards gap, a permanent 201-file record, a blocked RACCS access attempt, and a routine PDS ingestion) each have an automated test proving the system behaves as the legal/audit spec requires. Three leftover scaffold tests that were asserting outdated behavior now assert what the app actually — and correctly — does. And, if verifiable in this environment, the primary navigation moves from a left sidebar to a persistent top header with a mobile-friendly dropdown, without removing or relocating a single existing page.
