# HRDMS Module Spec: Vacant Position Publication & Monitoring (VPPM)
**Parent Module:** Appointment / Recruitment
**Prepared for:** HRMO Division Head, PTD, PHRMO – Provincial Government of Bukidnon
**Legal anchors:** RA 7041; 2025 ORAOHRA (CSC MC No. 08, s. 2025 / Resolution No. 2500358) Sections 26, 30, 31; CS Form No. 9, Revised 2025

---

## 1. Legal Basis & Compliance Notes (verify before go-live)

| Requirement | Rule | Status |
|---|---|---|
| Prescribed form | CS Form No. 9, Revised 2025 – "Request for Publication of Vacant Positions" | Confirmed (Annex P, 2025 ORAOHRA) |
| Posting locations | Three (3) conspicuous places in the agency, plus submission to CSC Field Office | Confirmed |
| Minimum posting period (LGU) | At least 15 calendar days | Confirmed under prior ORAOHRA baseline; **re-verify this figure was not amended by the 2025 revision** — the search results did not surface the exact current section text |
| Active contact details in request | Now mandatory on the publication request | Confirmed — Sec. 26, 2025 ORAOHRA |
| Errors in published info | Ground for disapproval/invalidation of the resulting appointment, **except** spelling errors or missing parenthetical title | Confirmed — Sec. 26 |
| Alternate publication modes | Agency website, newspaper (local/national), job search sites — reckoning date is the actual publication/republication date certified by the HRMO, **provided** the CS Form No. 9 request was filed at CSC FO on the same day | Confirmed — Sec. 26 |
| Anticipated vacancy publication | May now be published up to 180 calendar days before the incumbent's retirement/resignation/transfer (previously 30 days) | Confirmed — Sec. 31 |
| Validity extension | 9-month standard validity of a published vacancy may be extended during declared calamities/events limiting onsite work | Confirmed — Sec. 30; **base 9-month validity figure needs confirmation against full section text** |
| CSC FO/RO flow | Agency submits list to CSC Field Office → CSC FO posts on its own bulletin board → forwards to CSC Regional Office → CSC RO posts to the online Bulletin of Vacant Positions | Confirmed (CSC procedural guidance) |

**Action item before deployment:** Pull the full verbatim text of Sections 24–29 of the 2025 ORAOHRA from csc.gov.ph to lock the exact base posting-period figure and the 9-month validity clause before hardcoding any deadline logic. Do not treat the figures above as final without that check.

---

## 2. Functional Scope

1. Capture vacant positions eligible for publication (sourced from Plantilla/Personnel Inventory or entered manually).
2. Auto-generate CS Form No. 9 (Revised 2025) in the prescribed layout, pre-filled from position/QS data.
3. Track the three-conspicuous-places posting requirement as a checklist with proof-of-posting evidence (photo/date/location).
4. Track submission to, and acknowledgment by, the CSC Field Office.
5. Allow controlled editing of a submitted report, with mandatory version history and re-publication triggers per Sec. 26.
6. Monitor posting-period compliance, validity expiry, and anticipated-vacancy publication windows, with automated alerts.
7. Produce compliance reports for COA/CSC audit trail purposes.

---

## 3. Status Lifecycle

```
DRAFT
  → PENDING_SIGNATURE (Division Chief prepares, Dept. Head signs CS Form 9)
  → SUBMITTED_TO_CSC_FO (date-stamped, receiving copy attached)
  → POSTED (posting_start_date captured; 3-site checklist active)
  → PUBLICATION_ACTIVE (countdown running against minimum posting period)
  → PUBLICATION_COMPLIANT (minimum period satisfied) or PUBLICATION_DEFICIENT (flagged, blocks downstream appointment action)
  → VALID (within 9-month/extended validity window)
  → NEAR_EXPIRY (auto-alert, configurable lead time e.g. 30 days out)
  → EXPIRED → requires REPUBLICATION (new version, resets posting clock)
  → FILLED (linked to an appointment record) or CANCELLED (with reason)
```

Any edit made **after** `SUBMITTED_TO_CSC_FO` does not overwrite the record — it spawns a new version and forces the status back to `DRAFT`/`PENDING_SIGNATURE`, because Sec. 26 makes incorrect published information a disapproval ground for the eventual appointment. The system should never silently allow a "quiet" correction on an already-submitted request.

---

## 4. Data Model (MySQL, simple/linear per your coding standard — no ORM abstraction beyond basic SQLAlchemy models if using Flask)

```sql
-- Core vacancy record, one row per plantilla item being published
CREATE TABLE vacant_positions (
    vacancy_id          INT AUTO_INCREMENT PRIMARY KEY,
    plantilla_item_no   VARCHAR(50) NOT NULL,
    position_title      VARCHAR(150) NOT NULL,
    parenthetical_title VARCHAR(150) NULL,          -- Sec 26 carve-out: missing this is NOT a disapproval ground
    salary_grade        VARCHAR(10) NOT NULL,
    monthly_salary      DECIMAL(12,2) NOT NULL,
    place_of_assignment VARCHAR(150) NOT NULL,
    office_division     VARCHAR(150) NOT NULL,
    appointment_status  ENUM('Permanent','Temporary','Casual','Coterminous') NOT NULL,
    vacancy_type        ENUM('Original','Vice','Reclassified','Created') NOT NULL,
    vice_whom           VARCHAR(150) NULL,
    vacated_date        DATE NULL,
    is_anticipated       BOOLEAN DEFAULT FALSE,       -- Sec 31: up to 180 days before incumbent separates
    anticipated_incumbent_separation_date DATE NULL,
    qs_education        TEXT NOT NULL,
    qs_training          TEXT NOT NULL,
    qs_experience        TEXT NOT NULL,
    qs_eligibility        TEXT NOT NULL,
    created_by           INT NOT NULL,                -- FK to users
    created_at            DATETIME DEFAULT CURRENT_TIMESTAMP,
    status                ENUM('DRAFT','FOR_PUBLICATION','FILLED','CANCELLED') DEFAULT 'DRAFT'
);

-- Each CS Form No. 9 request is versioned; editing after submission creates a new row, never overwrites
CREATE TABLE publication_requests (
    request_id           INT AUTO_INCREMENT PRIMARY KEY,
    vacancy_id            INT NOT NULL,
    version_no             INT NOT NULL DEFAULT 1,
    supersedes_request_id  INT NULL,                  -- links to prior version if this is a correction/republication
    agency_name            VARCHAR(150) NOT NULL DEFAULT 'Provincial Government of Bukidnon',
    agency_contact_person   VARCHAR(150) NOT NULL,     -- mandatory per Sec 26 2025 amendment
    agency_contact_number    VARCHAR(50) NOT NULL,
    agency_contact_email      VARCHAR(150) NOT NULL,
    prepared_by_id             INT NOT NULL,            -- HRMO staff, FK users
    signed_by_id                 INT NULL,               -- Dept Head / appointing authority, FK users
    signed_date                   DATE NULL,
    status                         ENUM('DRAFT','PENDING_SIGNATURE','SUBMITTED_TO_CSC_FO',
                                          'POSTED','PUBLICATION_ACTIVE','PUBLICATION_COMPLIANT',
                                          'PUBLICATION_DEFICIENT','VALID','NEAR_EXPIRY',
                                          'EXPIRED','FILLED','CANCELLED') DEFAULT 'DRAFT',
    submission_mode              ENUM('CSC_FO','Agency_Website','Newspaper','Job_Site','Multiple') NOT NULL,
    submitted_to_csc_fo_date      DATE NULL,
    csc_fo_receiving_copy_path     VARCHAR(255) NULL,     -- scanned proof of receipt
    posting_start_date              DATE NULL,             -- reckoning date per Sec 26
    posting_min_required_days        INT NOT NULL DEFAULT 15,  -- confirm against final section text
    posting_actual_end_date           DATE NULL,
    validity_start_date                DATE NULL,
    validity_months                     INT NOT NULL DEFAULT 9,   -- confirm against final section text
    validity_extended_reason            VARCHAR(255) NULL,        -- Sec 30 calamity extension
    validity_end_date                    DATE NULL,                -- computed: validity_start + validity_months (+ extension)
    edit_reason                           VARCHAR(255) NULL,        -- required if version_no > 1
    created_at                              DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vacancy_id) REFERENCES vacant_positions(vacancy_id)
);

-- Proof of posting in the 3 conspicuous places, required before status can move to PUBLICATION_ACTIVE
CREATE TABLE posting_site_log (
    log_id           INT AUTO_INCREMENT PRIMARY KEY,
    request_id        INT NOT NULL,
    site_label          VARCHAR(100) NOT NULL,     -- e.g. "PHRMO Bulletin Board", "Provincial Capitol Lobby", "Agency Website"
    posted_date           DATE NOT NULL,
    removed_date            DATE NULL,
    proof_photo_path          VARCHAR(255) NULL,
    posted_by_id                INT NOT NULL,
    FOREIGN KEY (request_id) REFERENCES publication_requests(request_id)
);

-- Full audit trail — every field change, every status transition, every signature
CREATE TABLE publication_audit_log (
    audit_id          INT AUTO_INCREMENT PRIMARY KEY,
    request_id          INT NOT NULL,
    action_type            VARCHAR(50) NOT NULL,     -- CREATED, EDITED, SIGNED, SUBMITTED, REPUBLISHED, EXPIRED, FILLED, CANCELLED
    field_changed             VARCHAR(100) NULL,
    old_value                    TEXT NULL,
    new_value                       TEXT NULL,
    performed_by_id                   INT NOT NULL,
    performed_at                        DATETIME DEFAULT CURRENT_TIMESTAMP,
    remarks                               VARCHAR(255) NULL,
    FOREIGN KEY (request_id) REFERENCES publication_requests(request_id)
);
```

---

## 5. Workflow & Signature Hierarchy

1. **PTD staff** (e.g., Marilou Cadelina / Mai Ann Estaniel) drafts the vacancy entry and CS Form No. 9 pre-fill in `DRAFT`.
2. **Carmelo Cagas (Division Chief, HRMO IV)** reviews for QS accuracy and completeness → moves to `PENDING_SIGNATURE`.
3. **Aida Loveres (Department Head)**, as the routing appointing/HRMO authority, digitally or wet-signs CS Form No. 9 → status `SUBMITTED_TO_CSC_FO` is only reachable after `signed_by_id` and `signed_date` are populated — the system should hard-block submission without a captured signature.
4. System logs `submitted_to_csc_fo_date` and requires upload of the CSC FO receiving copy (scanned stamp/date) as proof.
5. PTD staff logs the three posting sites in `posting_site_log` with photo evidence and `posted_date`. Once all three sites are logged, status auto-advances to `POSTED` → `PUBLICATION_ACTIVE`.
6. System runs a daily job comparing `posting_start_date + posting_min_required_days` against today's date:
   - Before threshold → `PUBLICATION_ACTIVE`
   - At/after threshold with all 3 sites confirmed → `PUBLICATION_COMPLIANT`
   - At/after threshold with missing site confirmations → `PUBLICATION_DEFICIENT` (dashboard red flag; blocks HRMPSB deliberation start in the Recruitment module)
7. On reaching `PUBLICATION_COMPLIANT`, `validity_start_date` is set and `validity_end_date` auto-computes (`validity_months`, adjusted by any Sec. 30 extension).
8. Alerts fire at a configurable lead time before `validity_end_date` (mirrors your Detail Order 1-year alert pattern) → `NEAR_EXPIRY`.
9. If unfilled past `validity_end_date` → `EXPIRED`; the only path forward is a new `publication_requests` version (`supersedes_request_id` set), which resets the posting clock — this is the system's built-in "republication" workflow.
10. Once an appointment is issued against the plantilla item, the Recruitment module marks the vacancy `FILLED` and closes out any active publication request.

---

## 6. Editing Rules (per Sec. 26)

- **Before submission (`DRAFT`/`PENDING_SIGNATURE`):** freely editable, no versioning needed.
- **After submission:** editing any published field (title, SG, QS, salary, place of assignment) requires:
  - Mandatory `edit_reason` text
  - New row in `publication_requests` with incremented `version_no` and `supersedes_request_id`
  - Automatic status reset to `DRAFT`
  - Full diff written to `publication_audit_log`
- **Exempt from triggering republication:** correcting spelling errors or adding a missing parenthetical title — per Sec. 26's express carve-out. The UI should let the Division Chief flag an edit as "Sec. 26 exempt (spelling/parenthetical only)" so it does not force a full republication cycle, but the audit log still records it.

---

## 7. Monitoring Dashboard (Recruitment Module → Publication tab)

Columns: Vacancy, Position Title, SG, Status, Posting Start, Days Posted / Required, Validity End, Days to Expiry, CSC FO Receiving Copy (Y/N), 3-Site Posting (✔/✔/✘), Flag.

- Sortable and collapsible per your existing HRDMS Recruitment Module display-matrix requirement, filterable by office/division/status, exportable to CS Form No. 9 batch report.
- Red flag row highlighting for `PUBLICATION_DEFICIENT`, `NEAR_EXPIRY`, and `EXPIRED`.
- Separate sub-view for `is_anticipated = TRUE` records showing the 180-day publication eligibility countdown (Sec. 31).

---

## 8. Reports

1. **CS Form No. 9 Batch Report** — all currently `PUBLICATION_ACTIVE`/`PUBLICATION_COMPLIANT` requests, formatted for CSC FO submission.
2. **Publication Compliance Report** — for COA/CSC audit: shows posting dates, all three site proofs, receiving copy, and signature chain per vacancy — this is your primary disallowance-risk mitigation artifact, since a missing or late posting can be raised against the resulting appointment.
3. **Expiry Forecast Report** — vacancies approaching `validity_end_date`.
4. **Anticipated Vacancy Pipeline Report** — Sec. 31 items still within their 180-day publication window.

---

## 9. Open Items for You to Confirm Before Build

1. Exact current base minimum posting period for LGUs and the 9-month validity figure — verbatim from the final 2025 ORAOHRA text (Sections 24–29), not just the amendment summaries cited above.
2. Whether Bukidnon PHRMO wants `submission_mode` to default to `CSC_FO` only, or to actively support the Sec. 26 alternate-mode reckoning logic (agency website/newspaper same-day filing).
3. Confirm which HRDMS role(s) beyond Division Chief/Department Head should have `signed_by_id` authority (e.g., is there a delegated OIC scenario during Ms. Loveres's absence).
