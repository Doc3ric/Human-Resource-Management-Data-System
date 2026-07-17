# HRDMS Post-Go-Live Enhancement Specifications
**Provincial Human Resource Management Office (PHRMO), Provincial Government of Bukidnon**
**System Owner:** Carmelo L. Cagas, HRMO IV, Division Chief — Personnel Transactions Division (PTD)

This document consolidates the framework-agnostic design specs for six enhancement items to the Human Resource Management Data System (HRDMS). All designs are stack-neutral (apply regardless of whether implemented in Python/Flask, PHP, Node, etc.) so they can be handed to any developer — including Track A (Claude Code) or Track B (Gemini CLI) — for direct implementation once the live codebase structure is available.

---

## 1. Recruitment Module — Collapsible & Sortable Columns (Active-Filter-Safe)

**Applies to tabs:** All Data, Regular/Permanent, Casual, Job Order

**Problem:** Column collapse/sort currently resets or ignores active filters.

**Fix — Decoupled State Model:**
```
view_state = {
    active_tab: "Casual",
    active_filters: { office: "PTD", status: "Occupied" },
    column_visibility: { middle_name: false, item_no: true, ... },
    sort_column: "last_name",
    sort_direction: "asc"
}
```
`active_filters`, `column_visibility`, and `sort_column/direction` are three independent state objects. No user action on one resets another.

**Render Logic:**
```
function render_matrix(view_state):
    dataset = query_employees(tab = view_state.active_tab, filters = view_state.active_filters)
    dataset = sort_dataset(dataset, view_state.sort_column, view_state.sort_direction)
    visible_columns = get_columns(view_state.active_tab) minus columns_where(column_visibility == false)
    return build_table(dataset, visible_columns)

function on_column_collapse_toggle(column_name):
    view_state.column_visibility[column_name] = NOT view_state.column_visibility[column_name]
    render_matrix(view_state)

function on_sort_click(column_name):
    if view_state.sort_column == column_name:
        view_state.sort_direction = toggle(view_state.sort_direction)
    else:
        view_state.sort_column = column_name
        view_state.sort_direction = "asc"
    render_matrix(view_state)

function on_filter_apply(new_filters):
    view_state.active_filters = new_filters
    render_matrix(view_state)
```

**Persistence:** Store `column_visibility` and `sort_column/direction` per user, per tab (`user_prefs` table or session store keyed by `user_id + tab_name`).

**UI Notes:**
- Sort click and collapse icon must be separate controls on the header, not the same click target.
- Collapsed columns move to a "Show Columns" dropdown — recoverable, not lost.
- Filter bar stays sticky regardless of column state changes.

---

## 2. Performance Management (IPCR) — Rating Cursor Flow

**Problem:** After Enter/Tab on a rating input, focus jumps to a date picker instead of the next employee's rating cell.

**Fix — Explicit Focus Management:**
```
rating_column_cells = get_ordered_cells(column = current_criterion, order_by = "row_position")

function on_rating_input_keydown(event, current_cell):
    if event.key == "Enter" or event.key == "Tab":
        event.prevent_default()

        current_index = rating_column_cells.index_of(current_cell)
        next_index = current_index + 1

        if next_index < rating_column_cells.length:
            next_cell = rating_column_cells[next_index]
            focus(next_cell)
            select_all_text(next_cell)
        else:
            focus(nearest_action_button("Save" or "Next Criterion"))
```

**Implementation Notes:**
- Set `tabindex="-1"` on date pickers and non-rating fields within matrix rows so native browser tab order can never leak into them.
- Apply this handler to every rating column/criterion tab (Overall Rating, Job Output, Behavioral Competency, etc.) — not just the first.

---

## 3. Detailed Unit — 1-Year Tracking & Auto-Drafted Recall Letter

**Legal basis:** CSC rules limit a detail to one (1) year absent proper extension authority.

**Data Model:**

`detail_orders`
| Field | Type | Notes |
|---|---|---|
| detail_id | PK | |
| employee_id | FK → employees | |
| home_unit_id | FK → offices | Permanent station |
| detailed_unit_id | FK → offices | Temporary station |
| detail_order_no | string | |
| date_issued | date | |
| date_effective_start | date | |
| date_effective_end | date, nullable | Computed as start + 365 days if open-ended |
| attached_documents | linked → document_filing | |
| status | enum | Active / Nearing Expiry / Overdue / Recalled / Extended |
| recall_letter_id | FK → generated_letters, nullable | |
| remarks | text | |

**Flagging Logic (daily scheduled job):**
```
function check_detail_status(detail_order):
    today = current_date()
    one_year_mark = detail_order.date_effective_start + 365 days
    days_remaining = one_year_mark - today

    if days_remaining <= 30 and days_remaining > 0:
        detail_order.status = "Nearing Expiry"
        send_notification(
            to = [detail_order.employee_id, detail_order.detailed_unit_head, HRMO_Division_Head],
            message = "Detail order " + detail_order.detail_order_no + " reaches 1-year limit in " + days_remaining + " days."
        )
    else if days_remaining <= 0 and detail_order.status != "Extended":
        detail_order.status = "Overdue"
        if detail_order.recall_letter_id is null:
            recall_letter_id = generate_recall_letter(detail_order)
            detail_order.recall_letter_id = recall_letter_id
            send_notification(
                to = [HRMO_Division_Head],
                message = "Recall letter auto-drafted for " + detail_order.employee_id + ". Awaiting review/signature."
            )
    save(detail_order)
```

**Auto-Drafted Recall Letter Logic:**
```
function generate_recall_letter(detail_order):
    letter = new_document(template = "recall_letter_template")
    letter.addressee = detail_order.detailed_unit_head_name
    letter.addressee_office = detail_order.detailed_unit_id.office_name
    letter.body_fields = {
        employee_name: detail_order.employee_id.full_name,
        detail_order_no: detail_order.detail_order_no,
        date_effective_start: detail_order.date_effective_start,
        one_year_mark: detail_order.date_effective_start + 365 days,
        home_unit: detail_order.home_unit_id.office_name
    }
    letter.status = "Draft — Pending HRMO Division Head Review"
    save_to_document_filing(letter, linked_employee = detail_order.employee_id)
    return letter.id
```

**Recall Letter Template Skeleton:**
```
[LETTERHEAD — Provincial Government of Bukidnon / PHRMO]

[Date]

[Detailed Unit Head Name]
[Detailed Unit Office Name]

Subject: Recall of Detailed Employee — [Employee Name]

Sir/Madam:

This is to formally recall [Employee Name], currently detailed to your
office per Detail Order No. [detail_order_no] dated [date_issued], the
one (1) year maximum period of detail having lapsed on [one_year_mark].

[Employee Name] is directed to report back to [Home Unit] effective
immediately upon receipt of this letter.

Thank you for your cooperation.

Very truly yours,

[HRMO Division Head Name]
[Position]
```

**Approval Rule:** The letter is generated as a draft only, tagged "Pending Review" in Document Filing. It is never auto-sent — preserves the human signature/approval gate.

**Notification Routing:**
- 30-day warning → employee, detailed unit head, HRMO Division Head.
- "Recall letter ready for review" → HRMO Division Head only (centralizes signature authority).

---

## 4. Violation → 201-File / Administrative Case Linkage

**Rule:** Violations from the Leave or Incident Report modules must append immutably into the employee's 201-file, and cross-reference into Admin Case tracking where severity warrants.

**Data Model:**

`violations` (immutable — insert only, never update/delete)
| Field | Type | Notes |
|---|---|---|
| violation_id | PK | |
| employee_id | FK → employees | |
| source_module | enum | Leave / Incident Report |
| source_record_id | FK (polymorphic) | Originating record |
| violation_type | string | e.g. AWOL, Habitual Tardiness |
| date_created | datetime | System timestamp |
| legal_basis | string | e.g. "2025 RACCS Rule 10, Sec. 63" |
| severity_level | enum | Minor / Grave |

`violation_status_log` (append-only)
| Field | Type | Notes |
|---|---|---|
| log_id | PK | |
| violation_id | FK → violations | |
| status | enum | Recorded / Under Investigation / Escalated / Resolved / Dismissed |
| date_logged | datetime | |
| remarks | text | |
| logged_by | FK → users | |

`admin_cases` (spawned only if severity threshold met)
| Field | Type | Notes |
|---|---|---|
| case_id | PK | |
| employee_id | FK → employees | |
| originating_violation_id | FK → violations | Reference only, not a merge |
| case_status | enum | Filed / Formal Charge / Preliminary Investigation / Decided / Closed |
| date_filed | date | |

**Sync Logic:**
```
function record_violation(source_module, source_record, employee_id, violation_type, legal_basis, severity_level):
    violation = create(violations, {
        employee_id: employee_id,
        source_module: source_module,
        source_record_id: source_record.id,
        violation_type: violation_type,
        date_created: now(),
        legal_basis: legal_basis,
        severity_level: severity_level
    })
    log_status(violation.violation_id, "Recorded", logged_by = current_user())
    append_to_201file(employee_id, reference = violation.violation_id, read_only = true)

    if severity_level == "Grave" or is_repeat_offense(employee_id, violation_type):
        spawn_admin_case(violation)

    return violation

function spawn_admin_case(violation):
    case = create(admin_cases, {
        employee_id: violation.employee_id,
        originating_violation_id: violation.violation_id,
        case_status: "Filed",
        date_filed: now()
    })
    log_status(violation.violation_id, "Escalated", remarks = "Admin Case #" + case.case_id + " opened")
    notify(HRMO_Division_Head, "New admin case opened for employee " + violation.employee_id)
```

**Audit Integrity Rule:** `violations` rows are never UPDATE'd or DELETE'd at the application layer. The 201-file view joins `violations` + `violation_status_log` at read-time, so the record always reflects current state without any row ever being altered.

---

## 5. Report: Detailed Employees

**Fields:** Name, Detailed Unit, Date of Movement

```
function generate_detailed_employees_report(as_of_date = today()):
    records = query(detail_orders, where = status IN ("Active", "Nearing Expiry", "Overdue", "Extended"))

    report_rows = []
    for order in records:
        report_rows.append({
            name: order.employee_id.full_name,
            detailed_unit: order.detailed_unit_id.office_name,
            date_of_movement: order.date_effective_start
        })

    sort(report_rows, by = "detailed_unit", then = "name")
    return report_rows
```

**Notes:** Default filter excludes `Recalled`; expose a toggle for "Include Recalled/Historical" for COA audit trail purposes. Export in the same .docx/.xlsx pattern already used for other reports.

---

## 6. Report: Separation

**Data Model Addition:**

`separation_records`
| Field | Type | Notes |
|---|---|---|
| separation_id | PK | |
| employee_id | FK → employees | |
| separation_type | enum | Retirement / Resignation / Termination / Death / Dropped from Rolls / End of Term / Other |
| effective_date | date | |
| basis_reference | string | e.g. R.A. 8291 for retirement, RACCS for termination |
| remarks | text | |
| attached_documents | linked → document_filing | |

**Query Logic:**
```
function generate_separation_report(date_from, date_to, separation_type = "All"):
    records = query(separation_records, where = effective_date BETWEEN date_from AND date_to)

    if separation_type != "All":
        records = filter(records, where = separation_type == separation_type)

    report_rows = []
    for rec in records:
        report_rows.append({
            name: rec.employee_id.full_name,
            plantilla_item_or_position: rec.employee_id.position_title,
            separation_type: rec.separation_type,
            effective_date: rec.effective_date,
            basis_reference: rec.basis_reference
        })

    sort(report_rows, by = "effective_date", direction = "desc")
    return report_rows
```

**Compliance Notes:**
- On `Termination`, auto-link back to `admin_cases` if the separation stemmed from an administrative case — same immutable-append pattern as Section 4.
- On `Retirement` or `Resignation`, trigger a clearance/terminal-leave checklist reminder (COA-audited items with disallowance risk if processed out of sequence).

---

## RBAC Baseline (Reference)
- Dashboard module and Document Filing module are granted to **every** user role by default, regardless of other permission restrictions.

---

# Step-by-Step Implementation Procedure (Beginner-Friendly)

This procedure assumes no prior experience implementing these specs. Do each section **one at a time**, in this order, testing after each step before moving to the next.

### Step 0 — Before You Start
1. Make a full backup of your current HRDMS database and codebase (copy the whole project folder somewhere safe, and export a MySQL dump). Do this before every major step below, not just once.
2. Work in a separate git branch (e.g. `feature/enhancements-2026`) so you can always roll back if something breaks.
3. Keep this document open side-by-side with your code editor — you'll be copying table structures and pseudocode logic section by section.

### Step 1 — Database Changes First
1. Open your MySQL client (phpMyAdmin via Laragon, or MySQL Workbench).
2. For each new table in this document (`detail_orders`, `violations`, `violation_status_log`, `admin_cases`, `separation_records`), create the table using the field list as your column definitions. Match data types to your existing schema conventions (e.g. if `employee_id` is `INT` elsewhere, keep it `INT` here too — consistency matters more than the "ideal" type).
3. Add foreign key constraints linking `employee_id` back to your existing `employees` table, and any `office_id`/`document_id` links to your existing tables.
4. Run a small test insert into each new table manually (via phpMyAdmin's Insert tab) to confirm the table works before writing any application code against it.

### Step 2 — Implement One Backend Function at a Time
1. Start with the simplest one: **Report: Detailed Employees** (Section 5). Translate the pseudocode into your actual backend language (Python/Flask route, PHP script, etc.), reusing your existing query/connection pattern.
2. Test it by hitting the route directly in your browser or Postman before wiring it into any UI.
3. Repeat for **Report: Separation** (Section 6) the same way.
4. Then implement the **Violation → 201-File linkage** (Section 4) — this is the most important one for compliance, so take your time. Test by manually inserting a violation and confirming it appears read-only in the 201-file view, and that a "Grave" severity violation correctly spawns an `admin_cases` row.
5. Then implement the **Detailed Unit flagging job** (Section 3) as a standalone script first (run it manually from the command line), confirming it correctly flags "Nearing Expiry" and generates a draft recall letter. Only after that works, wire it into your task scheduler (Windows Task Scheduler, matching your existing Laragon/PHP setup) to run daily.

### Step 3 — Frontend/UI Changes
1. Implement the **IPCR cursor fix** (Section 2) next — it's UI-only, no new database tables, so it's a good "quick win" once the backend items above are stable.
2. Test it by entering ratings for at least 5 employees in a row without touching the mouse — confirm focus always lands on the next rating cell, never the date picker.
3. Implement the **Recruitment Module column collapse/sort** (Section 1) last, since it touches four tabs (All Data, Regular/Permanent, Casual, Job Order) and needs the most manual click-testing.
4. Test each tab separately: apply a filter, then sort a column, then collapse a column, then re-apply a different filter — confirm none of the three states resets the others.

### Step 4 — RBAC Update
1. Locate your existing roles/permissions table or config.
2. Add Dashboard and Document Filing to the default permission set for every existing role (Admin, HR Staff, Division Head, etc.) — do this as a one-time data migration script, not a manual per-user edit, so it's consistent and repeatable if you add new roles later.

### Step 5 — Full Regression Test
1. Log in as at least two different role types and confirm Dashboard + Document Filing are visible to both.
2. Walk through a full cycle: create a detail order → manually backdate it in test data to simulate "Nearing Expiry" → confirm notification fires → backdate further to "Overdue" → confirm recall letter draft appears in Document Filing.
3. Create a test violation → confirm it appears in 201-file → mark it "Grave" → confirm an admin case is spawned.
4. Run both reports (Detailed Employees, Separation) against your test data and manually verify the output matches what you'd expect by checking the raw data.

### Step 6 — Deploy
1. Merge your feature branch back into your main branch only after all six items pass their individual tests above.
2. Take a fresh database backup immediately before deploying to your live/production Laragon instance.
3. Deploy during low-usage hours, and keep the previous version's backup accessible for at least one week in case a rollback is needed.

---

*Prepared for: Carmelo L. Cagas, HRMO IV — Division Chief, Personnel Transactions Division, PHRMO, Provincial Government of Bukidnon.*
