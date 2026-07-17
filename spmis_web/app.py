"""
=========================================================================
 PGB-SPMIS  (Web Edition)
 Strategic Performance Management Information System
 Provincial Government of Bukidnon - PHRMO

 Stack : Python + Flask + SQLite  (standalone single-file database)
 Use   : multi-user over a LAN / Intranet (browser-based)
 Rules : mirrors the corrected SPMS manual -
         70% Part I + 30% Part II, CSC band 4.500-5.000,
         cross-rating 20/30/50, intervening task (max 0.5),
         teamwork cap, OPCR<-DPCR<-IPCR roll-up.

 Style : plain functions, raw SQL, linear and commented (no ORM/OOP).
=========================================================================
"""
import os
import sqlite3
from flask import (Flask, g, render_template, request, redirect, url_for,
                   session, flash, Response)
from werkzeug.security import generate_password_hash, check_password_hash

# --------------------------------------------------------------------- config
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
DB_PATH = os.path.join(BASE_DIR, "spmis.db")

app = Flask(__name__)
# CHANGE THIS before deployment (any long random string):
app.secret_key = "CHANGE-ME-pgb-spmis-secret-key-2026"


# --------------------------------------------------------------------- database
def get_db():
    """One SQLite connection per request."""
    if "db" not in g:
        g.db = sqlite3.connect(DB_PATH, timeout=15)
        g.db.row_factory = sqlite3.Row
        g.db.execute("PRAGMA foreign_keys=ON")
        g.db.execute("PRAGMA journal_mode=WAL")   # LAN concurrency
    return g.db


@app.teardown_appcontext
def close_db(exc):
    db = g.pop("db", None)
    if db is not None:
        db.close()


def init_db():
    """Create tables (from schema.sql) and seed reference + admin once."""
    first_time = not os.path.exists(DB_PATH)
    con = sqlite3.connect(DB_PATH)
    with open(os.path.join(BASE_DIR, "schema.sql"), "r", encoding="utf-8") as f:
        con.executescript(f.read())
    cur = con.cursor()

    # -- adjectival band (CSC MC 6 s.2012): largest lower-bound <= rating --
    if cur.execute("SELECT COUNT(*) FROM band").fetchone()[0] == 0:
        cur.executemany("INSERT INTO band(lower_bound,adjectival) VALUES (?,?)", [
            (0, "Poor"), (1.5, "Unsatisfactory"), (2.5, "Satisfactory"),
            (3.5, "Very Satisfactory"), (4.5, "Outstanding")])

    # -- weights / caps --
    if cur.execute("SELECT COUNT(*) FROM param").fetchone()[0] == 0:
        cur.executemany("INSERT INTO param(name,value) VALUES (?,?)", [
            ("WeightP1", 0.70), ("WeightP2", 0.30),
            ("ITSmax", 0.5), ("ITShourBase", 176), ("RatingCap", 5.0)])

    # -- cross-rating weights (both roles use 20/30/50) --
    if cur.execute("SELECT COUNT(*) FROM rater_weight").fetchone()[0] == 0:
        rows = []
        for role in ("Rank & File", "Head"):
            rows += [(role, "Supervisor/NHA", 0.2),
                     (role, "Peer/Subordinate", 0.3),
                     (role, "Client", 0.5)]
        cur.executemany(
            "INSERT INTO rater_weight(role_type,rater_group,weight) VALUES (?,?,?)", rows)

    # -- 7 Pillars --
    if cur.execute("SELECT COUNT(*) FROM mfo").fetchone()[0] == 0:
        cur.executemany("INSERT INTO mfo(no,name) VALUES (?,?)", [
            (1, "Health & Social Services"), (2, "Peace & Order"),
            (3, "Education for All"), (4, "Economic Services / Livelihood"),
            (5, "Infrastructure Program"), (6, "Environmental Protection & NRM"),
            (7, "General Administration")])

    # -- default admin + sample office/period/employees --
    if cur.execute("SELECT COUNT(*) FROM users").fetchone()[0] == 0:
        cur.execute("INSERT INTO users(username,pw_hash,fullname,role) VALUES (?,?,?,?)",
                    ("admin", generate_password_hash("admin123"), "System Administrator", "admin"))
    if cur.execute("SELECT COUNT(*) FROM office").fetchone()[0] == 0:
        cur.executemany("INSERT INTO office(code,name,parent_id) VALUES (?,?,?)", [
            ("PHRMO", "Provincial Human Resource Management Office", None),
            ("PHRMO-AD", "PHRMO - Administrative Division", 1),
            ("PHRMO-PT", "PHRMO - Personnel Transaction Division", 1),
            ("PHRMO-CEDD", "PHRMO - Career & Employees Development Division", 1)])
    if cur.execute("SELECT COUNT(*) FROM period").fetchone()[0] == 0:
        cur.execute("INSERT INTO period(name,sem_no,ryear,start_date,end_date) VALUES (?,?,?,?,?)",
                    ("1st Semester 2026", 1, 2026, "2026-01-01", "2026-06-30"))
    if cur.execute("SELECT COUNT(*) FROM employee").fetchone()[0] == 0:
        cur.executemany(
            "INSERT INTO employee(emp_no,last_name,first_name,position,office_id,role_type,status) "
            "VALUES (?,?,?,?,?,?,?)", [
                ("2024-001", "Dela Cruz", "Maria", "Admin Officer III", 2, "Rank & File", "Permanent"),
                ("2024-002", "Reyes", "Juan", "HRMO IV (Division Chief)", 3, "Head", "Permanent"),
                ("2024-003", "Santos", "Ana", "Admin Aide VI", 4, "Rank & File", "Permanent")])

    # -- guarded migration: add new columns for DBs created before Settings --
    cols = [r[1] for r in cur.execute("PRAGMA table_info(success_indicator)").fetchall()]
    if "measure" not in cols:
        cur.execute("ALTER TABLE success_indicator ADD COLUMN measure TEXT")
    if "template_id" not in cols:
        cur.execute("ALTER TABLE success_indicator ADD COLUMN template_id INTEGER")

    # -- users.active flag (deactivate without deleting) --
    ucols = [r[1] for r in cur.execute("PRAGMA table_info(users)").fetchall()]
    if "active" not in ucols:
        cur.execute("ALTER TABLE users ADD COLUMN active INTEGER DEFAULT 1")
        cur.execute("UPDATE users SET active=1 WHERE active IS NULL")

    # -- OPCR/DPCR finalize workflow: track when a rating was locked in --
    ocols = [r[1] for r in cur.execute("PRAGMA table_info(opcr)").fetchall()]
    if "finalized_at" not in ocols:
        cur.execute("ALTER TABLE opcr ADD COLUMN finalized_at TEXT")
    dcols = [r[1] for r in cur.execute("PRAGMA table_info(dpcr)").fetchall()]
    if "finalized_at" not in dcols:
        cur.execute("ALTER TABLE dpcr ADD COLUMN finalized_at TEXT")
    if "final_rating_by" not in dcols:
        cur.execute("ALTER TABLE dpcr ADD COLUMN final_rating_by TEXT")

    # -- seed the GENERIC Q/E/T rubric (operational definitions per score) --
    if cur.execute("SELECT COUNT(*) FROM rating_rubric").fetchone()[0] == 0:
        generic = {
            "Q": {5: "Output consistently exceeds standards; no errors or revisions.",
                  4: "Output exceeds standards; minimal (1) error or revision.",
                  3: "Output meets standards; acceptable (2) revisions.",
                  2: "Output below standards; multiple (3) revisions.",
                  1: "Output far below standards; 4 or more revisions / unacceptable."},
            "E": {5: "96-100% optimal use of allotted resources / budget.",
                  4: "90-95% of resources / budget used efficiently.",
                  3: "80-89% of resources / budget used efficiently.",
                  2: "70-79% of resources / budget used efficiently.",
                  1: "Below 70%, or over 100% of allotted budget."},
            "T": {5: "Completed at least 3 days ahead of deadline.",
                  4: "Completed ahead of or exactly on the deadline.",
                  3: "Completed within an acceptable, minor delay.",
                  2: "Completed late beyond the acceptable delay.",
                  1: "Significantly delayed or not completed."},
        }
        for dim, scores in generic.items():
            for sc, desc in scores.items():
                cur.execute("INSERT INTO rating_rubric(template_id,dimension,score,descriptor) "
                            "VALUES (NULL,?,?,?)", (dim, sc, desc))

    # -- seed a few sample Targets (library) --
    if cur.execute("SELECT COUNT(*) FROM si_template").fetchone()[0] == 0:
        cur.executemany(
            "INSERT INTO si_template(level,mfo_id,indicator_text,measure) VALUES (?,?,?,?)", [
                ("IPCR", 7, "Personnel transactions processed",
                 "% processed within the prescribed period (e.g., within 10 working days)"),
                ("IPCR", 7, "Reports submitted to oversight agencies",
                 "% submitted on or before the deadline set by CSC/COA/DBM"),
                ("DPCR", 7, "Division accomplishment vs. committed targets",
                 "% of committed division targets accomplished for the period")])

    con.commit()
    con.close()
    if first_time:
        print(">> Database created at", DB_PATH, "(login admin / admin123)")


# --------------------------------------------------------------------- engine
# All four functions reproduce the corrected manual exactly.

def param(db, name):
    return db.execute("SELECT value FROM param WHERE name=?", (name,)).fetchone()["value"]


def adj_from_rating(db, r):
    """Adjectival for a numeric rating (largest band lower-bound <= r)."""
    if r is None:
        return ""
    row = db.execute(
        "SELECT adjectival FROM band WHERE lower_bound<=? ORDER BY lower_bound DESC LIMIT 1",
        (r,)).fetchone()
    return row["adjectival"] if row else "Poor"


def compute_p1(db, ipcr_id):
    """Part I = average over indicators of (average of the Q/E/T entered)."""
    rows = db.execute("SELECT q,e,t FROM success_indicator WHERE ipcr_id=?", (ipcr_id,)).fetchall()
    target_aves = []
    for r in rows:
        vals = [v for v in (r["q"], r["e"], r["t"]) if v is not None]
        if vals:
            target_aves.append(sum(vals) / len(vals))
    if not target_aves:
        return None
    return round(sum(target_aves) / len(target_aves), 3)


def compute_p2(db, ipcr_id, role_type):
    """Part II = sum over 3 groups of (group average FactorAve * weight)."""
    p2, used = 0.0, 0
    for grp in ("Supervisor/NHA", "Peer/Subordinate", "Client"):
        ga = db.execute("SELECT AVG(factor_ave) a FROM cross_rating "
                        "WHERE ipcr_id=? AND rater_group=?", (ipcr_id, grp)).fetchone()["a"]
        w = db.execute("SELECT weight FROM rater_weight WHERE role_type=? AND rater_group=?",
                       (role_type, grp)).fetchone()
        if ga is not None and w is not None:
            p2 += ga * w["weight"]
            used += 1
    return round(p2, 3) if used else None


def compute_ipcr(db, ipcr_id):
    """Full 70/30 computation; writes results back to the ipcr row."""
    ip = db.execute("SELECT i.*, e.role_type, e.office_id FROM ipcr i "
                    "JOIN employee e ON e.id=i.emp_id WHERE i.id=?", (ipcr_id,)).fetchone()
    role = ip["role_type"] or "Rank & File"

    p1 = compute_p1(db, ipcr_id)
    p2 = compute_p2(db, ipcr_id, role)
    p1v = p1 if p1 is not None else 0.0
    p2v = p2 if p2 is not None else 0.0

    eps1 = round(p1v * param(db, "WeightP1"), 3)
    eps2 = round(p2v * param(db, "WeightP2"), 3)

    hrs = ip["its_hours"] or 0
    its = round(min(param(db, "ITSmax"),
                    param(db, "ITSmax") * hrs / param(db, "ITShourBase")), 3)

    fnr = min(param(db, "RatingCap"), eps1 + eps2 + its)
    fnr = round(fnr, 2)
    adj = adj_from_rating(db, fnr)

    # link to the employee's division DPCR (if finalized) so the teamwork cap can be checked
    dpcr_row = db.execute("SELECT id FROM dpcr WHERE office_id=? AND period_id=?",
                          (ip["office_id"], ip["period_id"])).fetchone()
    dpcr_id = dpcr_row["id"] if dpcr_row else None

    db.execute("UPDATE ipcr SET p1=?,p2=?,eps1=?,eps2=?,its=?,fnr=?,adjectival=?,dpcr_id=?,status='Rated' WHERE id=?",
               (p1, p2, eps1, eps2, its, fnr, adj, dpcr_id, ipcr_id))
    db.commit()
    return fnr, adj


# --------------------------------------------------------------------- auth
def login_required(view):
    from functools import wraps
    @wraps(view)
    def wrapped(*a, **kw):
        if "uid" not in session:
            return redirect(url_for("login"))
        return view(*a, **kw)
    return wrapped


def admin_required(view):
    """Only users with role 'admin' may open Settings."""
    from functools import wraps
    @wraps(view)
    def wrapped(*a, **kw):
        if "uid" not in session:
            return redirect(url_for("login"))
        if session.get("role") != "admin":
            flash("Settings is restricted to administrators.")
            return redirect(url_for("dashboard"))
        return view(*a, **kw)
    return wrapped


def write_required(view):
    """Blocks the 'viewer' role from data-entry / mutating routes (read-only per README)."""
    from functools import wraps
    @wraps(view)
    def wrapped(*a, **kw):
        if "uid" not in session:
            return redirect(url_for("login"))
        if session.get("role") == "viewer":
            flash("Your account has read-only (viewer) access.")
            return redirect(request.referrer or url_for("dashboard"))
        return view(*a, **kw)
    return wrapped


@app.route("/login", methods=["GET", "POST"])
def login():
    if request.method == "POST":
        db = get_db()
        u = db.execute("SELECT * FROM users WHERE username=?",
                       (request.form["username"].strip(),)).fetchone()
        if u and (u["active"] is None or u["active"] == 1) and \
                check_password_hash(u["pw_hash"], request.form["password"]):
            session["uid"] = u["id"]
            session["uname"] = u["username"]
            session["role"] = u["role"]
            session["fullname"] = u["fullname"]
            return redirect(url_for("dashboard"))
        if u and u["active"] == 0:
            flash("That account is deactivated. Contact an administrator.")
        else:
            flash("Invalid username or password.")
    return render_template("login.html")


@app.route("/logout")
def logout():
    session.clear()
    return redirect(url_for("login"))


# --------------------------------------------------------------------- pages
@app.route("/")
@login_required
def dashboard():
    db = get_db()
    stats = {
        "employees": db.execute("SELECT COUNT(*) c FROM employee WHERE active=1").fetchone()["c"],
        "ipcr": db.execute("SELECT COUNT(*) c FROM ipcr").fetchone()["c"],
        "rated": db.execute("SELECT COUNT(*) c FROM ipcr WHERE fnr IS NOT NULL").fetchone()["c"],
        "late": db.execute("SELECT COUNT(*) c FROM submission "
                           "WHERE date_submitted IS NULL AND due_date < date('now')").fetchone()["c"],
    }
    return render_template("dashboard.html", stats=stats)


# ---- employees ----
@app.route("/employees")
@login_required
def employees():
    db = get_db()
    rows = db.execute(
        "SELECT e.*, o.name office FROM employee e LEFT JOIN office o ON o.id=e.office_id "
        "ORDER BY e.last_name").fetchall()
    return render_template("employees.html", rows=rows,
                           offices=db.execute("SELECT * FROM office ORDER BY name").fetchall())


@app.route("/employees/add", methods=["POST"])
@write_required
def employee_add():
    db = get_db()
    f = request.form
    db.execute("INSERT INTO employee(emp_no,last_name,first_name,position,office_id,role_type,status) "
               "VALUES (?,?,?,?,?,?,?)",
               (f["emp_no"], f["last_name"], f["first_name"], f["position"],
                f["office_id"], f["role_type"], f["status"]))
    db.commit()
    flash("Employee added.")
    return redirect(url_for("employees"))


@app.route("/employees/<int:emp_id>/edit", methods=["POST"])
@write_required
def employee_edit(emp_id):
    db = get_db()
    f = request.form
    db.execute("UPDATE employee SET last_name=?, first_name=?, position=?, office_id=?, "
               "role_type=?, status=? WHERE id=?",
               (f["last_name"], f["first_name"], f["position"], f["office_id"],
                f["role_type"], f["status"], emp_id))
    db.commit()
    flash("Employee updated.")
    return redirect(url_for("employees"))


@app.route("/employees/<int:emp_id>/toggle", methods=["POST"])
@write_required
def employee_toggle(emp_id):
    db = get_db()
    e = db.execute("SELECT active FROM employee WHERE id=?", (emp_id,)).fetchone()
    if e:
        going_off = (e["active"] == 1)
        db.execute("UPDATE employee SET active=? WHERE id=?", (0 if going_off else 1, emp_id))
        db.commit()
        flash("Employee %s." % ("deactivated" if going_off else "reactivated"))
    return redirect(url_for("employees"))


# ---- periods ----
@app.route("/periods", methods=["GET", "POST"])
@login_required
def periods():
    db = get_db()
    if request.method == "POST":
        if session.get("role") == "viewer":
            flash("Your account has read-only (viewer) access.")
            return redirect(url_for("periods"))
        f = request.form
        db.execute("INSERT INTO period(name,sem_no,ryear,start_date,end_date) VALUES (?,?,?,?,?)",
                   (f["name"], f["sem_no"], f["ryear"], f["start_date"], f["end_date"]))
        db.commit()
        flash("Period added.")
        return redirect(url_for("periods"))
    rows = db.execute("SELECT * FROM period ORDER BY ryear DESC, sem_no DESC").fetchall()
    return render_template("periods.html", rows=rows)


@app.route("/periods/<int:period_id>/edit", methods=["POST"])
@write_required
def period_edit(period_id):
    db = get_db()
    f = request.form
    db.execute("UPDATE period SET name=?, sem_no=?, ryear=?, start_date=?, end_date=? WHERE id=?",
               (f["name"], f["sem_no"], f["ryear"], f["start_date"], f["end_date"], period_id))
    db.commit()
    flash("Period updated.")
    return redirect(url_for("periods"))


@app.route("/periods/<int:period_id>/delete", methods=["POST"])
@write_required
def period_delete(period_id):
    db = get_db()
    used = db.execute(
        "SELECT (SELECT COUNT(*) FROM ipcr WHERE period_id=?) + "
        "(SELECT COUNT(*) FROM opcr WHERE period_id=?) + "
        "(SELECT COUNT(*) FROM dpcr WHERE period_id=?) + "
        "(SELECT COUNT(*) FROM submission WHERE period_id=?) c",
        (period_id, period_id, period_id, period_id)).fetchone()["c"]
    if used:
        flash("Cannot delete: this period has IPCR/OPCR/DPCR or monitoring records tied to it.")
    else:
        db.execute("DELETE FROM period WHERE id=?", (period_id,))
        db.commit()
        flash("Period deleted.")
    return redirect(url_for("periods"))


# ---- IPCR ----
@app.route("/ipcr")
@login_required
def ipcr_list():
    db = get_db()
    rows = db.execute(
        "SELECT i.*, (e.last_name||', '||e.first_name) emp, p.name period "
        "FROM ipcr i JOIN employee e ON e.id=i.emp_id JOIN period p ON p.id=i.period_id "
        "ORDER BY i.id DESC").fetchall()
    return render_template("ipcr_list.html", rows=rows)


@app.route("/ipcr/new", methods=["GET", "POST"])
@login_required
def ipcr_new():
    db = get_db()
    if request.method == "POST":
        if session.get("role") == "viewer":
            flash("Your account has read-only (viewer) access.")
            return redirect(url_for("ipcr_list"))
        f = request.form
        db.execute("INSERT INTO ipcr(emp_id,period_id,its_hours,status) VALUES (?,?,?,?)",
                   (f["emp_id"], f["period_id"], f.get("its_hours", 0) or 0, "Draft"))
        db.commit()
        nid = db.execute("SELECT last_insert_rowid() id").fetchone()["id"]
        return redirect(url_for("ipcr_detail", ipcr_id=nid))
    return render_template("ipcr_new.html",
                           employees=db.execute("SELECT * FROM employee WHERE active=1 ORDER BY last_name").fetchall(),
                           periods=db.execute("SELECT * FROM period ORDER BY id DESC").fetchall())


@app.route("/ipcr/<int:ipcr_id>")
@login_required
def ipcr_detail(ipcr_id):
    db = get_db()
    ip = db.execute(
        "SELECT i.*, (e.last_name||', '||e.first_name) emp, e.position, e.role_type, "
        "o.name office, p.name period "
        "FROM ipcr i JOIN employee e ON e.id=i.emp_id "
        "LEFT JOIN office o ON o.id=e.office_id JOIN period p ON p.id=i.period_id "
        "WHERE i.id=?", (ipcr_id,)).fetchone()
    si = db.execute("SELECT s.*, m.name mfo FROM success_indicator s "
                    "LEFT JOIN mfo m ON m.id=s.mfo_id WHERE s.ipcr_id=? ORDER BY s.id", (ipcr_id,)).fetchall()
    cr = db.execute("SELECT * FROM cross_rating WHERE ipcr_id=? ORDER BY rater_group", (ipcr_id,)).fetchall()
    templates = db.execute("SELECT * FROM si_template WHERE active=1 ORDER BY level, id").fetchall()
    dpcr_cap = db.execute("SELECT * FROM dpcr WHERE id=?", (ip["dpcr_id"],)).fetchone() if ip["dpcr_id"] else None
    return render_template("ipcr_detail.html", ip=ip, si=si, cr=cr,
                           mfos=db.execute("SELECT * FROM mfo ORDER BY no").fetchall(),
                           templates=templates, dpcr_cap=dpcr_cap)


@app.route("/ipcr/<int:ipcr_id>/add_si", methods=["POST"])
@write_required
def add_si(ipcr_id):
    db = get_db()
    f = request.form
    def num(x):
        return float(x) if x.strip() != "" else None
    db.execute("INSERT INTO success_indicator(ipcr_id,mfo_id,indicator_text,measure,template_id,q,e,t,remarks) "
               "VALUES (?,?,?,?,?,?,?,?,?)",
               (ipcr_id, f["mfo_id"] or None, f["indicator_text"], f.get("measure", ""),
                f.get("template_id") or None,
                num(f["q"]), num(f["e"]), num(f["t"]), f.get("remarks", "")))
    db.commit()
    return redirect(url_for("ipcr_detail", ipcr_id=ipcr_id))


@app.route("/ipcr/<int:ipcr_id>/si/<int:si_id>/delete", methods=["POST"])
@write_required
def delete_si(ipcr_id, si_id):
    db = get_db()
    db.execute("DELETE FROM success_indicator WHERE id=? AND ipcr_id=?", (si_id, ipcr_id))
    db.commit()
    return redirect(url_for("ipcr_detail", ipcr_id=ipcr_id))


@app.route("/ipcr/<int:ipcr_id>/add_cr", methods=["POST"])
@write_required
def add_cr(ipcr_id):
    db = get_db()
    f = request.form
    db.execute("INSERT INTO cross_rating(ipcr_id,rater_group,rater_name,factor_ave) VALUES (?,?,?,?)",
               (ipcr_id, f["rater_group"], f["rater_name"], float(f["factor_ave"])))
    db.commit()
    return redirect(url_for("ipcr_detail", ipcr_id=ipcr_id))


@app.route("/ipcr/<int:ipcr_id>/cr/<int:cr_id>/delete", methods=["POST"])
@write_required
def delete_cr(ipcr_id, cr_id):
    db = get_db()
    db.execute("DELETE FROM cross_rating WHERE id=? AND ipcr_id=?", (cr_id, ipcr_id))
    db.commit()
    return redirect(url_for("ipcr_detail", ipcr_id=ipcr_id))


@app.route("/ipcr/<int:ipcr_id>/hours", methods=["POST"])
@write_required
def set_hours(ipcr_id):
    db = get_db()
    db.execute("UPDATE ipcr SET its_hours=? WHERE id=?",
               (float(request.form.get("its_hours", 0) or 0), ipcr_id))
    db.commit()
    return redirect(url_for("ipcr_detail", ipcr_id=ipcr_id))


@app.route("/ipcr/<int:ipcr_id>/compute", methods=["POST"])
@write_required
def ipcr_compute(ipcr_id):
    db = get_db()
    fnr, adj = compute_ipcr(db, ipcr_id)
    flash("Computed: FNR %.2f  (%s)" % (fnr, adj))
    return redirect(url_for("ipcr_detail", ipcr_id=ipcr_id))


@app.route("/ipcr/<int:ipcr_id>/delete", methods=["POST"])
@write_required
def ipcr_delete(ipcr_id):
    db = get_db()
    db.execute("DELETE FROM success_indicator WHERE ipcr_id=?", (ipcr_id,))
    db.execute("DELETE FROM cross_rating WHERE ipcr_id=?", (ipcr_id,))
    db.execute("DELETE FROM ipcr WHERE id=?", (ipcr_id,))
    db.commit()
    flash("IPCR deleted.")
    return redirect(url_for("ipcr_list"))


# ---- Summary List (division) + teamwork cap ----
@app.route("/summary")
@login_required
def summary():
    db = get_db()
    offices = db.execute("SELECT * FROM office ORDER BY name").fetchall()
    periods = db.execute("SELECT * FROM period ORDER BY id DESC").fetchall()
    office_id = request.args.get("office_id", type=int)
    period_id = request.args.get("period_id", type=int)
    office_rating = request.args.get("office_rating", type=float)
    rows, avg, avg_adj, cap = [], None, "", None
    if office_id and period_id:
        rows = db.execute(
            "SELECT (e.last_name||', '||e.first_name) emp, i.fnr, i.adjectival "
            "FROM ipcr i JOIN employee e ON e.id=i.emp_id "
            "WHERE e.office_id=? AND i.period_id=? AND i.fnr IS NOT NULL "
            "ORDER BY i.fnr DESC", (office_id, period_id)).fetchall()
        vals = [r["fnr"] for r in rows]
        if vals:
            avg = round(sum(vals) / len(vals), 2)
            avg_adj = adj_from_rating(db, avg)
            if office_rating is not None:
                cap = "EXCEEDED - calibrate down" if avg > office_rating else "OK - within cap"
    return render_template("summary.html", offices=offices, periods=periods, rows=rows,
                           avg=avg, avg_adj=avg_adj, cap=cap,
                           sel_office=office_id, sel_period=period_id, office_rating=office_rating)


# ---- OPCR / DPCR roll-up + finalize workflow ----
# OPCR = the office's own official rating for the period (entered by the rating official).
# DPCR = each division's rating, finalized against (and flagged if it exceeds) the OPCR.
@app.route("/opcr")
@login_required
def opcr():
    db = get_db()
    # parent offices that have children
    parents = db.execute(
        "SELECT * FROM office WHERE id IN (SELECT DISTINCT parent_id FROM office WHERE parent_id IS NOT NULL)"
    ).fetchall()
    periods = db.execute("SELECT * FROM period ORDER BY id DESC").fetchall()
    parent_id = request.args.get("parent_id", type=int)
    period_id = request.args.get("period_id", type=int)
    rows, avg, avg_adj = [], None, ""
    opcr_row = None
    if parent_id and period_id:
        opcr_row = db.execute("SELECT * FROM opcr WHERE office_id=? AND period_id=?",
                              (parent_id, period_id)).fetchone()
        # each child division's live average of its individual FNRs, plus any finalized DPCR
        divs = db.execute("SELECT * FROM office WHERE parent_id=? ORDER BY name", (parent_id,)).fetchall()
        for d in divs:
            r = db.execute("SELECT AVG(i.fnr) a FROM ipcr i JOIN employee e ON e.id=i.emp_id "
                           "WHERE e.office_id=? AND i.period_id=? AND i.fnr IS NOT NULL",
                           (d["id"], period_id)).fetchone()["a"]
            live_fnr = round(r, 2) if r is not None else None
            dpcr_row = db.execute("SELECT * FROM dpcr WHERE office_id=? AND period_id=?",
                                  (d["id"], period_id)).fetchone()
            rows.append({"id": d["id"], "name": d["name"], "live_fnr": live_fnr,
                         "live_adj": adj_from_rating(db, live_fnr) if live_fnr is not None else "",
                         "dpcr": dpcr_row})
        vals = [x["live_fnr"] for x in rows if x["live_fnr"] is not None]
        if vals:
            avg = round(sum(vals) / len(vals), 2)
            avg_adj = adj_from_rating(db, avg)
    over_cap = False
    if opcr_row:
        if avg is not None and avg > opcr_row["office_fnr"]:
            over_cap = True
        if any(r["dpcr"] and r["dpcr"]["div_fnr"] > opcr_row["office_fnr"] for r in rows):
            over_cap = True
    return render_template("opcr.html", parents=parents, periods=periods, rows=rows,
                           avg=avg, avg_adj=avg_adj, over_cap=over_cap,
                           sel_parent=parent_id, sel_period=period_id, opcr_row=opcr_row)


@app.route("/opcr/finalize", methods=["POST"])
@write_required
def opcr_finalize():
    """Lock in the office's official OPCR rating for a period (re-submitting updates it)."""
    db = get_db()
    f = request.form
    office_id = int(f["office_id"])
    period_id = int(f["period_id"])
    office_fnr = float(f["office_fnr"])
    adj = adj_from_rating(db, office_fnr)
    by = f.get("final_rating_by", "").strip() or session.get("fullname") or session.get("uname")
    db.execute(
        "INSERT INTO opcr(office_id,period_id,office_fnr,office_adjectival,final_rating_by,status,finalized_at) "
        "VALUES (?,?,?,?,?,'Finalized',datetime('now','localtime')) "
        "ON CONFLICT(office_id,period_id) DO UPDATE SET "
        "office_fnr=excluded.office_fnr, office_adjectival=excluded.office_adjectival, "
        "final_rating_by=excluded.final_rating_by, status='Finalized', finalized_at=excluded.finalized_at",
        (office_id, period_id, office_fnr, adj, by))
    db.commit()
    flash("OPCR finalized: %.2f (%s)" % (office_fnr, adj))
    return redirect(url_for("opcr", parent_id=office_id, period_id=period_id))


@app.route("/opcr/finalize_dpcr", methods=["POST"])
@write_required
def opcr_finalize_dpcr():
    """Lock in one division's DPCR rating, linked to its parent office's finalized OPCR."""
    db = get_db()
    f = request.form
    office_id = int(f["office_id"])    # the division
    period_id = int(f["period_id"])
    parent_id = int(f["parent_id"])    # the division's parent office
    div_fnr = float(f["div_fnr"])
    opcr_row = db.execute("SELECT * FROM opcr WHERE office_id=? AND period_id=?",
                          (parent_id, period_id)).fetchone()
    if not opcr_row:
        flash("Finalize the OPCR for the parent office first.")
        return redirect(url_for("opcr", parent_id=parent_id, period_id=period_id))
    adj = adj_from_rating(db, div_fnr)
    by = f.get("final_rating_by", "").strip() or session.get("fullname") or session.get("uname")
    db.execute(
        "INSERT INTO dpcr(office_id,period_id,opcr_id,div_fnr,div_adjectival,final_rating_by,status,finalized_at) "
        "VALUES (?,?,?,?,?,?,'Finalized',datetime('now','localtime')) "
        "ON CONFLICT(office_id,period_id) DO UPDATE SET "
        "opcr_id=excluded.opcr_id, div_fnr=excluded.div_fnr, div_adjectival=excluded.div_adjectival, "
        "final_rating_by=excluded.final_rating_by, status='Finalized', finalized_at=excluded.finalized_at",
        (office_id, period_id, opcr_row["id"], div_fnr, adj, by))
    db.commit()
    flash("DPCR finalized: %.2f (%s)" % (div_fnr, adj))
    return redirect(url_for("opcr", parent_id=parent_id, period_id=period_id))


# ---- Monitoring & sanctions ----
@app.route("/monitoring", methods=["GET", "POST"])
@login_required
def monitoring():
    db = get_db()
    if request.method == "POST":
        if session.get("role") == "viewer":
            flash("Your account has read-only (viewer) access.")
            return redirect(url_for("monitoring"))
        f = request.form
        db.execute("INSERT INTO submission(office_id,emp_id,form_type,period_id,due_date,date_submitted) "
                   "VALUES (?,?,?,?,?,?)",
                   (f["office_id"] or None, f["emp_id"] or None, f["form_type"],
                    f["period_id"] or None, f["due_date"], f.get("date_submitted") or None))
        db.commit()
        return redirect(url_for("monitoring"))
    rows = db.execute(
        "SELECT s.*, o.name office, (e.last_name||', '||e.first_name) owner, "
        "CASE WHEN s.date_submitted IS NULL AND s.due_date < date('now') THEN 'NOT SUBMITTED - sanction (6.2.22)' "
        "     WHEN s.date_submitted IS NULL THEN 'pending' "
        "     WHEN s.date_submitted > s.due_date THEN 'LATE - explain / possible sanction' "
        "     ELSE 'On time' END AS flag "
        "FROM submission s LEFT JOIN office o ON o.id=s.office_id "
        "LEFT JOIN employee e ON e.id=s.emp_id ORDER BY s.due_date").fetchall()
    return render_template("monitoring.html", rows=rows,
                           offices=db.execute("SELECT * FROM office ORDER BY name").fetchall(),
                           employees=db.execute("SELECT * FROM employee ORDER BY last_name").fetchall(),
                           periods=db.execute("SELECT * FROM period ORDER BY id DESC").fetchall())


@app.route("/monitoring/<int:sub_id>/edit", methods=["POST"])
@write_required
def monitoring_edit(sub_id):
    db = get_db()
    f = request.form
    db.execute("UPDATE submission SET office_id=?, emp_id=?, form_type=?, period_id=?, "
               "due_date=?, date_submitted=? WHERE id=?",
               (f["office_id"] or None, f["emp_id"] or None, f["form_type"],
                f["period_id"] or None, f["due_date"], f.get("date_submitted") or None, sub_id))
    db.commit()
    flash("Submission updated.")
    return redirect(url_for("monitoring"))


@app.route("/monitoring/<int:sub_id>/delete", methods=["POST"])
@write_required
def monitoring_delete(sub_id):
    db = get_db()
    db.execute("DELETE FROM submission WHERE id=?", (sub_id,))
    db.commit()
    flash("Submission deleted.")
    return redirect(url_for("monitoring"))


# ---- CSV export: Consolidated Individual Performance Review Report ----
@app.route("/export/iprr")
@login_required
def export_iprr():
    db = get_db()
    rows = db.execute(
        "SELECT p.name period, o.name office, (e.last_name||', '||e.first_name) emp, "
        "e.position, i.fnr, i.adjectival "
        "FROM ipcr i JOIN employee e ON e.id=i.emp_id LEFT JOIN office o ON o.id=e.office_id "
        "JOIN period p ON p.id=i.period_id WHERE i.fnr IS NOT NULL "
        "ORDER BY o.name, e.last_name").fetchall()
    out = ["Period,Office,Employee,Position,FNR,Adjectival"]
    for r in rows:
        out.append('"%s","%s","%s","%s",%s,"%s"' %
                   (r["period"], r["office"] or "", r["emp"], r["position"] or "",
                    r["fnr"], r["adjectival"]))
    return Response("\n".join(out), mimetype="text/csv",
                    headers={"Content-Disposition": "attachment;filename=Consolidated_IPRR.csv"})


# =====================================================================
#  SETTINGS MODULE  (admin only)  — editable, takes effect immediately
# =====================================================================
@app.route("/settings")
@admin_required
def settings():
    return render_template("settings.html")


# ---- 1) Rating scale (adjectival band) ----
@app.route("/settings/band", methods=["GET", "POST"])
@admin_required
def settings_band():
    db = get_db()
    if request.method == "POST":
        act = request.form.get("action")
        if act == "add":
            db.execute("INSERT INTO band(lower_bound,adjectival) VALUES (?,?)",
                       (float(request.form["lower_bound"]), request.form["adjectival"]))
        elif act == "update":
            db.execute("UPDATE band SET lower_bound=?, adjectival=? WHERE id=?",
                       (float(request.form["lower_bound"]), request.form["adjectival"],
                        request.form["id"]))
        elif act == "delete":
            db.execute("DELETE FROM band WHERE id=?", (request.form["id"],))
        db.commit()
        flash("Rating scale updated. New ratings use this band immediately.")
        return redirect(url_for("settings_band"))
    rows = db.execute("SELECT * FROM band ORDER BY lower_bound DESC").fetchall()
    return render_template("settings_band.html", rows=rows)


# ---- 2) Weights & caps + cross-rating weights ----
@app.route("/settings/params", methods=["GET", "POST"])
@admin_required
def settings_params():
    db = get_db()
    if request.method == "POST":
        for name in ("WeightP1", "WeightP2", "ITSmax", "ITShourBase", "RatingCap"):
            if name in request.form:
                db.execute("UPDATE param SET value=? WHERE name=?",
                           (float(request.form[name]), name))
        # cross-rating weights: fields named rw_<id>
        for key, val in request.form.items():
            if key.startswith("rw_"):
                db.execute("UPDATE rater_weight SET weight=? WHERE id=?",
                           (float(val), key[3:]))
        db.commit()
        flash("Weights and caps saved. The computation engine uses them immediately.")
        return redirect(url_for("settings_params"))
    params = {r["name"]: r["value"] for r in db.execute("SELECT * FROM param").fetchall()}
    weights = db.execute("SELECT * FROM rater_weight ORDER BY role_type, "
                         "CASE rater_group WHEN 'Supervisor/NHA' THEN 1 "
                         "WHEN 'Peer/Subordinate' THEN 2 ELSE 3 END").fetchall()
    return render_template("settings_params.html", params=params, weights=weights)


# ---- 3) MFOs ----
@app.route("/settings/mfo", methods=["GET", "POST"])
@admin_required
def settings_mfo():
    db = get_db()
    if request.method == "POST":
        act = request.form.get("action")
        if act == "add":
            db.execute("INSERT INTO mfo(no,name) VALUES (?,?)",
                       (request.form["no"] or None, request.form["name"]))
        elif act == "update":
            db.execute("UPDATE mfo SET no=?, name=? WHERE id=?",
                       (request.form["no"] or None, request.form["name"], request.form["id"]))
        elif act == "delete":
            db.execute("DELETE FROM mfo WHERE id=?", (request.form["id"],))
        db.commit()
        flash("MFOs updated.")
        return redirect(url_for("settings_mfo"))
    rows = db.execute("SELECT * FROM mfo ORDER BY no").fetchall()
    return render_template("settings_mfo.html", rows=rows)


# ---- 4) Targets library (OPCR/DPCR/IPCR success indicators) ----
@app.route("/settings/targets", methods=["GET", "POST"])
@admin_required
def settings_targets():
    db = get_db()
    if request.method == "POST":
        act = request.form.get("action")
        if act == "add":
            db.execute("INSERT INTO si_template(level,mfo_id,indicator_text,measure) VALUES (?,?,?,?)",
                       (request.form["level"], request.form["mfo_id"] or None,
                        request.form["indicator_text"], request.form["measure"]))
        elif act == "update":
            db.execute("UPDATE si_template SET level=?, mfo_id=?, indicator_text=?, measure=? WHERE id=?",
                       (request.form["level"], request.form["mfo_id"] or None,
                        request.form["indicator_text"], request.form["measure"], request.form["id"]))
        elif act == "delete":
            db.execute("DELETE FROM si_template WHERE id=?", (request.form["id"],))
            db.execute("DELETE FROM rating_rubric WHERE template_id=?", (request.form["id"],))
        db.commit()
        flash("Targets library updated.")
        return redirect(url_for("settings_targets"))
    rows = db.execute("SELECT s.*, m.name mfo FROM si_template s "
                      "LEFT JOIN mfo m ON m.id=s.mfo_id ORDER BY s.level, s.id").fetchall()
    return render_template("settings_targets.html", rows=rows,
                           mfos=db.execute("SELECT * FROM mfo ORDER BY no").fetchall())


# ---- 5) Q/E/T rating descriptions (generic + per-target rubric) ----
@app.route("/settings/rubric", methods=["GET", "POST"])
@admin_required
def settings_rubric():
    db = get_db()
    tid = request.values.get("template_id", type=int)   # None = generic default
    if request.method == "POST":
        # replace the rubric rows for this scope, then re-insert the grid
        if tid:
            db.execute("DELETE FROM rating_rubric WHERE template_id=?", (tid,))
        else:
            db.execute("DELETE FROM rating_rubric WHERE template_id IS NULL")
        for dim in ("Q", "E", "T"):
            for sc in (5, 4, 3, 2, 1):
                desc = request.form.get("d_%s_%d" % (dim, sc), "").strip()
                if desc:
                    db.execute("INSERT INTO rating_rubric(template_id,dimension,score,descriptor) "
                               "VALUES (?,?,?,?)", (tid, dim, sc, desc))
        db.commit()
        flash("Rating descriptions saved.")
        return redirect(url_for("settings_rubric", template_id=tid or ""))

    # build grid[dim][score] for the chosen scope
    if tid:
        rs = db.execute("SELECT dimension,score,descriptor FROM rating_rubric WHERE template_id=?", (tid,)).fetchall()
    else:
        rs = db.execute("SELECT dimension,score,descriptor FROM rating_rubric WHERE template_id IS NULL").fetchall()
    grid = {d: {s: "" for s in (5, 4, 3, 2, 1)} for d in ("Q", "E", "T")}
    for r in rs:
        grid[r["dimension"]][r["score"]] = r["descriptor"]
    templates = db.execute("SELECT id, level, indicator_text FROM si_template ORDER BY level, id").fetchall()
    sel = db.execute("SELECT * FROM si_template WHERE id=?", (tid,)).fetchone() if tid else None
    return render_template("settings_rubric.html", grid=grid, templates=templates, tid=tid, sel=sel)


# ---- interactive API: effective rubric + text for a target template ----
@app.route("/api/template/<int:tid>")
@login_required
def api_template(tid):
    from flask import jsonify
    db = get_db()
    t = db.execute("SELECT * FROM si_template WHERE id=?", (tid,)).fetchone()
    if not t:
        return jsonify({})
    # effective rubric: template's own rows where present, else the generic default
    grid = {d: {} for d in ("Q", "E", "T")}
    for d in ("Q", "E", "T"):
        for sc in (5, 4, 3, 2, 1):
            row = db.execute("SELECT descriptor FROM rating_rubric "
                             "WHERE template_id=? AND dimension=? AND score=?", (tid, d, sc)).fetchone()
            if not row or not row["descriptor"]:
                row = db.execute("SELECT descriptor FROM rating_rubric "
                                 "WHERE template_id IS NULL AND dimension=? AND score=?", (d, sc)).fetchone()
            grid[d][sc] = (row["descriptor"] if row else "")
    return jsonify({"indicator_text": t["indicator_text"], "measure": t["measure"], "rubric": grid})


# ---- 6) User management (admin only) ----
def _active_admins(db, exclude_id=None):
    sql = "SELECT COUNT(*) c FROM users WHERE role='admin' AND active=1"
    args = ()
    if exclude_id:
        sql += " AND id<>?"
        args = (exclude_id,)
    return db.execute(sql, args).fetchone()["c"]


@app.route("/settings/users", methods=["GET", "POST"])
@admin_required
def settings_users():
    db = get_db()
    if request.method == "POST":
        act = request.form.get("action")
        uid = request.form.get("id", type=int)

        if act == "add":
            uname = request.form["username"].strip()
            if not uname or not request.form.get("password"):
                flash("Username and initial password are required.")
            elif db.execute("SELECT 1 FROM users WHERE username=?", (uname,)).fetchone():
                flash("That username already exists.")
            else:
                db.execute("INSERT INTO users(username,pw_hash,fullname,role,active) VALUES (?,?,?,?,1)",
                           (uname, generate_password_hash(request.form["password"]),
                            request.form.get("fullname", ""), request.form.get("role", "encoder")))
                flash("User '%s' created." % uname)

        elif act == "update":          # change fullname / role
            new_role = request.form.get("role", "encoder")
            cur = db.execute("SELECT role FROM users WHERE id=?", (uid,)).fetchone()
            # do not let the last admin be demoted
            if cur and cur["role"] == "admin" and new_role != "admin" and _active_admins(db, uid) == 0:
                flash("Cannot change role: at least one active administrator must remain.")
            else:
                db.execute("UPDATE users SET fullname=?, role=? WHERE id=?",
                           (request.form.get("fullname", ""), new_role, uid))
                flash("User updated.")

        elif act == "resetpw":
            if not request.form.get("password"):
                flash("Enter a new password.")
            else:
                db.execute("UPDATE users SET pw_hash=? WHERE id=?",
                           (generate_password_hash(request.form["password"]), uid))
                flash("Password reset.")

        elif act == "toggle":          # activate / deactivate
            u = db.execute("SELECT * FROM users WHERE id=?", (uid,)).fetchone()
            if u:
                going_off = (u["active"] == 1)
                if going_off and uid == session["uid"]:
                    flash("You cannot deactivate your own account.")
                elif going_off and u["role"] == "admin" and _active_admins(db, uid) == 0:
                    flash("Cannot deactivate the last active administrator.")
                else:
                    db.execute("UPDATE users SET active=? WHERE id=?", (0 if going_off else 1, uid))
                    flash("Account %s." % ("deactivated" if going_off else "reactivated"))

        elif act == "delete":
            u = db.execute("SELECT * FROM users WHERE id=?", (uid,)).fetchone()
            if u and uid == session["uid"]:
                flash("You cannot delete your own account.")
            elif u and u["role"] == "admin" and _active_admins(db, uid) == 0:
                flash("Cannot delete the last active administrator.")
            else:
                db.execute("DELETE FROM users WHERE id=?", (uid,))
                flash("User deleted.")

        db.commit()
        return redirect(url_for("settings_users"))

    rows = db.execute("SELECT id,username,fullname,role,active FROM users ORDER BY role, username").fetchall()
    return render_template("settings_users.html", rows=rows, me=session["uid"])


# ---- self-service: change my own password (any logged-in user) ----
@app.route("/account/password", methods=["GET", "POST"])
@login_required
def account_password():
    db = get_db()
    if request.method == "POST":
        u = db.execute("SELECT * FROM users WHERE id=?", (session["uid"],)).fetchone()
        if not check_password_hash(u["pw_hash"], request.form["current"]):
            flash("Current password is incorrect.")
        elif len(request.form.get("new1", "")) < 4:
            flash("New password must be at least 4 characters.")
        elif request.form["new1"] != request.form["new2"]:
            flash("New passwords do not match.")
        else:
            db.execute("UPDATE users SET pw_hash=? WHERE id=?",
                       (generate_password_hash(request.form["new1"]), session["uid"]))
            db.commit()
            flash("Your password has been changed.")
            return redirect(url_for("dashboard"))
    return render_template("account_password.html")


# --------------------------------------------------------------------- run
# Runs at import time (not just "python app.py") so the DB/schema/seed data
# exist before the first request whether started directly or via a WSGI
# server that imports "app:app" (e.g. run_server.bat's waitress command).
init_db()

if __name__ == "__main__":
    # host 0.0.0.0 -> reachable by other PCs on the LAN at http://<server-ip>:5000
    app.run(host="0.0.0.0", port=5000, debug=False)
