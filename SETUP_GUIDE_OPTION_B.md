# HRDMS SETUP GUIDE — Two AI Coders, Option B (Build with Claude Code, Review with Gemini)

A plain, step-by-step procedure. No prior dev experience assumed. Do the parts in order.
Option B = Claude Code WRITES the code; Gemini READS and REVIEWS it. Only one tool ever writes.

═══════════════════════════════════════════════════════════
PART 1 — ONE-TIME SETUP (do this once, ~30–60 min)
═══════════════════════════════════════════════════════════

STEP 1 — Find your project folder (your "project root")
  - Locate the folder on your computer that holds your existing HRDMS files
    (your index.html, assets, scripts, etc.).
  - In File Explorer (Windows) / Finder (Mac), click into that folder and note its
    full path (Windows: click the address bar; Mac: View → Show Path Bar).
  - If the system isn't on this computer yet, make a new folder named HRDMS and put
    your existing files in it. That folder is now your project root.

STEP 2 — Install the tools you need (once)
  a) Git (version control — your safety net):
     - Download from git-scm.com, install with defaults.
     - Verify: open a terminal and type:  git --version   (should print a version)
  b) Node.js (Claude Code needs it):
     - Download the LTS version from nodejs.org, install with defaults.
     - Verify:  node --version
  c) Claude Code:
     - Install per Anthropic's instructions (docs.claude.com → Claude Code).
     - Verify:  claude --version
  d) Google Gemini access:
     - Use Gemini in your browser (gemini.google.com) OR the Gemini CLI/code tool if you
       have it. For Option B (review only) the browser is enough — you'll paste code in
       and read findings back.

STEP 3 — Put the spec into your project
  - Unzip HRDMS_Spec_Package.zip INTO your project root.
  - After unzipping, confirm these sit at the TOP of your project folder:
      CLAUDE.md , 00_SHARED_CONTEXT.md , BUILD_ORDER.md , MULTI_CODER.md , SETUP_GUIDE_OPTION_B.md
      and a /modules folder with M0.md … M12.md
  - Copy CLAUDE.md to GEMINI.md as well (so Gemini gets the same rules):
      Windows:  copy CLAUDE.md GEMINI.md
      Mac/Linux:  cp CLAUDE.md GEMINI.md

STEP 4 — Turn the folder into a Git repository (your undo button)
  Open a terminal, go into your project root, and run these one at a time:
      cd "PATH-TO-YOUR-PROJECT"        (drag the folder onto the terminal to paste the path)
      git init
      git add .
      git commit -m "Initial commit: existing HRDMS + spec package"
  Now every change is tracked and reversible.

STEP 5 — (Recommended) Create a private GitHub backup
  - Make a free private repo at github.com (keep it PRIVATE — government data context).
  - Follow GitHub's "push an existing repository" instructions to upload your project.
  - This is your offsite backup; push after each module is done.

═══════════════════════════════════════════════════════════
PART 2 — SESSION 0: DISCOVERY (do once, no code written)
═══════════════════════════════════════════════════════════

STEP 6 — Start Claude Code in your project root
      cd "PATH-TO-YOUR-PROJECT"
      claude
  (Claude Code auto-reads CLAUDE.md, so it already knows the rules.)

STEP 7 — Run discovery only
  Paste this to Claude Code:
    "Read 00_SHARED_CONTEXT.md. Run ONLY Phase A discovery across this existing codebase.
     Produce a System Discovery Report describing the current structure, files, data, and
     existing features. Save it as DISCOVERY_REPORT.md. Do NOT write or change any code."

STEP 8 — Review and commit the report
  - Read DISCOVERY_REPORT.md. It tells you what exists and what the build must preserve.
      git add DISCOVERY_REPORT.md
      git commit -m "Phase A discovery report"

═══════════════════════════════════════════════════════════
PART 3 — THE PER-MODULE LOOP (repeat for each module)
═══════════════════════════════════════════════════════════
Follow the order in BUILD_ORDER.md (foundation first). For EACH module, do steps 9–15.
Example below uses M11 (schema) as the first module; substitute the next module each loop.

STEP 9 — Make a branch for this module (in the terminal)
      git checkout main
      git pull            (only if using GitHub)
      git checkout -b feature/M11-schema-skeleton

STEP 10 — Start a FRESH Claude Code session
  - If Claude Code is still open from the last module, type  /clear  to reset context
    (this keeps token use low). Or close and reopen it in the project root.

STEP 11 — Ask Claude Code to PLAN the module (it stops before coding)
  Paste (swap M11 for the current module):
    "Read 00_SHARED_CONTEXT.md and modules/M11.md. Run Phase A discovery for this module
     against the existing code, then Phase B assessment, then present a Phase C implementation
     plan and STOP for my approval before writing any code. Build ONLY this module."

STEP 12 — Read the plan, then approve or adjust
  - If it looks right:  "Approved, proceed with implementation."
  - If not:  tell it what to change; it re-plans. Only approve when you're satisfied.

STEP 13 — Let it build, one step at a time
  - Claude Code implements the approved plan. After it finishes, ask:
    "Confirm nothing existing was broken, and summarize what changed."

STEP 14 — Commit the built module (terminal)
      git add .
      git commit -m "M11: MySQL schema + Flask app factory + Blueprints"

STEP 15 — REVIEW with Gemini (the Option B step)
  - Open Gemini (gemini.google.com).
  - Give it the same rules + the module spec + the code to check. Paste:
    "You are reviewing code for a government HR system. First, here are the project rules:
     [paste the contents of 00_SHARED_CONTEXT.md]
     Here is the module spec being reviewed:
     [paste the contents of modules/M11.md]
     Here is the code that was written:
     [paste the new/changed code files]
     Review WITHOUT rewriting. Check: correctness vs the spec, SQL/schema integrity, RBAC
     enforcement, SPI AES-256 + audit logging, NO unguarded DELETE/DROP, NO hardcoded hex
     (theme.css only), legal-citation accuracy, and single-source-of-truth (no duplicate
     metrics/services). Give findings as a prioritized list: BLOCKING / SHOULD-FIX / NICE-TO-HAVE."
  - (If your files are large, paste them in parts, or share the GitHub link if your repo
     is connected to Gemini.)

STEP 16 — Apply review fixes via Claude Code
  - Take Gemini's BLOCKING and SHOULD-FIX findings back to Claude Code:
    "Gemini's review found these issues: [paste the findings]. Assess each, propose fixes,
     and STOP for my approval before applying."
  - Approve the fixes, let it apply them, then commit:
      git add .
      git commit -m "M11: apply review fixes from Gemini"

STEP 17 — Merge the module to main
      git checkout main
      git merge feature/M11-schema-skeleton
      git push        (if using GitHub)

STEP 18 — Next module
  - Go back to STEP 9 with the next module from BUILD_ORDER.md.
  - Foundation modules (M11 → M4A → M0 → M9 → M9A) MUST be done before feature modules.

═══════════════════════════════════════════════════════════
GOLDEN RULES (keep these in mind every loop)
═══════════════════════════════════════════════════════════
- Only Claude Code writes. Gemini only reads/reviews. (That's what makes Option B safe.)
- One module per session. Use /clear or a fresh session each time → keeps tokens low.
- Always let Claude Code STOP at the plan and wait for your approval before it codes.
- Commit after every module AND after every fix. Commits are your undo button.
- Build foundation first (BUILD_ORDER.md). Don't jump to a feature whose base isn't built.
- Never let either tool delete existing files/columns or hardcode colors — the rules in
  CLAUDE.md / 00_SHARED_CONTEXT.md forbid it; the review step catches it if it slips.
- After the first module or two you'll know your real token pace — plan the rest from that.

═══════════════════════════════════════════════════════════
QUICK REFERENCE — the loop in one line each
═══════════════════════════════════════════════════════════
1. git checkout -b feature/<MODULE>
2. fresh Claude Code session (/clear)
3. "Read 00_SHARED_CONTEXT.md and modules/<MODULE>.md … plan and STOP for approval."
4. approve → it builds
5. git commit
6. Gemini reviews (read-only) → findings
7. Claude Code applies fixes → git commit
8. git merge to main → push
9. next module (foundation first)
