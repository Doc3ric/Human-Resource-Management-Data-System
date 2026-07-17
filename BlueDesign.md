# HRDMS Design Tokens — "Civic Blue" Theme
**Provincial Government of Bukidnon — Human Resource Management Data System**
Prepared for: HRMO Division Head, PHRMO
For implementation by: Eric Alenton (Track A/B, Claude Code + Gemini CLI)

---

## 1. Color Tokens

### 1.1 Primary (Navy) — Headers, Sidebar, Primary Buttons
| Token | Hex | Usage |
|---|---|---|
| `--color-primary-900` | `#101F38` | Sidebar background, active nav item |
| `--color-primary-700` | `#1B3A6B` | Primary buttons, header bar, links |
| `--color-primary-500` | `#2C5388` | Hover state on primary buttons |
| `--color-primary-100` | `#DCE4F0` | Selected row highlight, primary badge background |

### 1.2 Secondary (Institutional Green) — Compliant/Approved/Active States
| Token | Hex | Usage |
|---|---|---|
| `--color-secondary-700` | `#1E5C22` | Success text, "Approved" badge text |
| `--color-secondary-500` | `#2E7D32` | Success buttons, active status dot |
| `--color-secondary-100` | `#E2F0E3` | Success banner background, compliant row background |

### 1.3 Accent (Gold) — Pending/Needs Review/Highlights
| Token | Hex | Usage |
|---|---|---|
| `--color-accent-700` | `#9C7B15` | Pending status text |
| `--color-accent-500` | `#C9A227` | Pending badge, highlight border, official seal accents |
| `--color-accent-100` | `#F7EFD3` | Pending row background |

### 1.4 Alert (Red) — Violations/Disallowance/Flags
| Token | Hex | Usage |
|---|---|---|
| `--color-alert-700` | `#8C1D16` | Violation text, disallowance amount |
| `--color-alert-500` | `#B3261E` | Alert buttons, "Flagged" badge |
| `--color-alert-100` | `#F8E2E0` | Violation row background, error field border |

### 1.5 Neutrals — Background, Text, Borders
| Token | Hex | Usage |
|---|---|---|
| `--color-bg-base` | `#F5F6F8` | Page background |
| `--color-bg-surface` | `#FFFFFF` | Cards, table surfaces, modals |
| `--color-bg-muted` | `#EBEDF0` | Disabled fields, alternating table stripe |
| `--color-border` | `#D3D8DE` | Table borders, input borders |
| `--color-text-primary` | `#1F2933` | Body text |
| `--color-text-secondary` | `#5A6472` | Captions, helper text, timestamps |
| `--color-text-disabled` | `#9AA3AD` | Disabled input text |
| `--color-text-inverse` | `#FFFFFF` | Text on navy/dark backgrounds |

### 1.6 Interactive States (apply to any button/link token above)
| State | Rule |
|---|---|
| Hover | Base color, +1 step lighter (e.g., 700 → 500) |
| Active/Pressed | Base color, +1 step darker (e.g., 700 → 900) |
| Disabled | `--color-bg-muted` background, `--color-text-disabled` text, no shadow |
| Focus (keyboard) | 2px outline in `--color-accent-500`, 2px offset — required for accessibility |

---

## 2. Semantic Status Mapping (for HRDMS-specific workflows)

| Status | Background | Text | Used in |
|---|---|---|---|
| Approved / Active / Regular | `--color-secondary-100` | `--color-secondary-700` | Plantilla status, leave approval, appointment status |
| Pending / Under Review | `--color-accent-100` | `--color-accent-700` | Leave application pending, TWG evaluation stage |
| Flagged / Violation / Disallowed | `--color-alert-100` | `--color-alert-700` | DTR non-compliance, RACCS violation, COA disallowance |
| Neutral / Inactive / Separated | `--color-bg-muted` | `--color-text-secondary` | Separated employees, closed detail orders |

---

## 3. Typography

| Role | Typeface | Notes |
|---|---|---|
| Display / Headers | **Source Serif 4** (or system serif fallback: Georgia) | Used sparingly — module titles, report headers, gives an "official document" weight |
| Body / UI | **Inter** or **Public Sans** (both are US Web Design System–grade, built for dense data UI) | All tables, forms, labels |
| Data / Monospace | **IBM Plex Mono** or **Roboto Mono** | Employee IDs, plantilla item numbers, DTR time values |

Type scale: 12px (caption) / 14px (body/table) / 16px (form labels) / 20px (section header) / 28px (page title)

---

## 4. Spacing & Layout

- Base unit: **8px** grid (4px for tight table cell padding)
- Card/container radius: **6px** (professional, not overly rounded)
- Table row height: **40px** minimum for touch-friendly HR staff use
- Sidebar width: **240px** expanded / **64px** collapsed

---

## 5. CSS Custom Properties (drop-in for Flask/Jinja2 base template)

```css
:root {
  /* Primary */
  --color-primary-900: #101F38;
  --color-primary-700: #1B3A6B;
  --color-primary-500: #2C5388;
  --color-primary-100: #DCE4F0;

  /* Secondary */
  --color-secondary-700: #1E5C22;
  --color-secondary-500: #2E7D32;
  --color-secondary-100: #E2F0E3;

  /* Accent */
  --color-accent-700: #9C7B15;
  --color-accent-500: #C9A227;
  --color-accent-100: #F7EFD3;

  /* Alert */
  --color-alert-700: #8C1D16;
  --color-alert-500: #B3261E;
  --color-alert-100: #F8E2E0;

  /* Neutrals */
  --color-bg-base: #F5F6F8;
  --color-bg-surface: #FFFFFF;
  --color-bg-muted: #EBEDF0;
  --color-border: #D3D8DE;
  --color-text-primary: #1F2933;
  --color-text-secondary: #5A6472;
  --color-text-disabled: #9AA3AD;
  --color-text-inverse: #FFFFFF;

  /* Spacing */
  --space-unit: 8px;
  --radius-base: 6px;
}
```

---

## 6. Implementation Prompt (for Claude Code / Gemini CLI)

Paste this into your Track A (Claude Code) or Track B (Gemini CLI) session at
`C:\laragon\www\Human-Resource-Management-Data-System`:

```
Implement the "Civic Blue" design theme across the HRDMS Flask application.

CONTEXT:
- Stack: Flask + Jinja2 templates + MySQL, Laragon local dev environment.
- This is an internal government HR system (PHRMO, Provincial Government of
  Bukidnon) used daily by non-technical HR staff for plantilla management,
  leave administration, 201-file records, DTR compliance, and disciplinary
  case tracking.
- Coding philosophy: keep CSS simple and heavily commented — no CSS-in-JS,
  no build step required beyond what already exists. Local IT staff must be
  able to read and modify it directly.

TASKS:
1. Create a single `static/css/theme.css` file containing the CSS custom
   properties below as `:root` variables, plus base utility classes for:
   - `.btn-primary`, `.btn-secondary`, `.btn-accent`, `.btn-alert` (with
     hover/active/disabled states per the state rules below)
   - `.badge-approved`, `.badge-pending`, `.badge-flagged`, `.badge-inactive`
     (semantic status badges — background + text color pairs)
   - `.table-row-alert` and `.table-row-pending` (row background tints for
     flagged/pending records in list views)
   - `.card`, `.card-header` (base surface + border-radius per token)

2. Update the base Jinja2 layout template (find it — likely `base.html` or
   `layout.html`) to link `theme.css` and apply:
   - `--color-bg-base` to `<body>`
   - `--color-primary-900` to the sidebar/nav background
   - `--color-primary-700` to the top header bar
   - `--color-text-inverse` for text on the sidebar/header

3. Audit existing templates for hardcoded hex colors or inline styles and
   replace them with the new CSS variables / utility classes. List every
   file you changed.

4. Apply the semantic status mapping to any place that currently renders
   status text/badges for: plantilla status, leave application status,
   appointment status, DTR compliance status, and RACCS case status. Map:
   - Approved/Active/Regular → badge-approved
   - Pending/Under Review → badge-pending
   - Flagged/Violation/Disallowed → badge-flagged
   - Inactive/Separated → badge-inactive

5. Ensure keyboard focus states are visible (2px accent-color outline, 2px
   offset) on all buttons, links, and form inputs — this is a compliance/
   accessibility requirement, not optional.

6. Do NOT change any backend logic, routes, or database queries. This is a
   CSS/template-only pass. Do NOT introduce a frontend build tool (no
   Webpack/Vite/npm build step) — plain CSS only, loaded via <link>.

DELIVERABLE: a short markdown summary of every file changed and a
before/after screenshot description (or actual screenshot if your tooling
supports it) of the Dashboard and one list view (e.g., Plantilla Module).

CSS TOKENS TO USE (paste theme.css verbatim):
[paste Section 5 CSS block from HRDMS_Civic_Blue_Design_Tokens.md here]
```

---

*Reference document — Civic Blue palette derived from PGB official
correspondence color conventions (navy/gold) with institutional green and
alert red added for HRDMS-specific compliance status semantics.*
