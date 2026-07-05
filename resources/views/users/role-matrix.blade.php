<x-dashboard-app>
<style>
/* ── Page shell ──────────────────────────────────────────────────────── */
.matrix-hero {
    background: linear-gradient(135deg,#1e3a5f 0%,#1a5276 55%,#0f4c75 100%);
    border-radius:14px; padding:28px 32px; position:relative; overflow:hidden; margin-bottom:24px;
}
.matrix-hero::before {
    content:''; position:absolute; inset:0;
    background-image:radial-gradient(circle,rgba(255,255,255,.08) 1px,transparent 1px);
    background-size:22px 22px;
}
.matrix-hero-inner { position:relative;z-index:1;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap; }
.matrix-hero h1  { color:#fff;font-size:22px;font-weight:800;margin:0 0 4px; }
.matrix-hero p   { color:rgba(255,255,255,.75);font-size:13px;margin:0; }

/* ── Section header rows ─────────────────────────────────────────────── */
.section-row td { background:#f1f5f9;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#475569;padding:8px 16px; }

/* ── Main table ───────────────────────────────────────────────────────── */
.perm-table { width:100%;border-collapse:separate;border-spacing:0;font-size:13px; }
.perm-table thead th {
    background:#fff;padding:12px 10px;text-align:center;font-size:11px;
    font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;
    border-bottom:2px solid #e5e7eb;position:sticky;top:0;z-index:2;
}
.perm-table thead th:first-child { text-align:left;padding-left:16px;min-width:200px; }
.perm-table thead th.role-col     { min-width:52px; }

.perm-table tbody tr:hover td { background:#f9fafb; }
.perm-table tbody td { padding:9px 8px;border-bottom:1px solid #f3f4f6;text-align:center;vertical-align:middle; }
.perm-table tbody td:first-child { text-align:left;padding-left:16px;font-weight:600;color:#111827; }

/* Action cell: each cell is a mini-grid of 5 checkboxes */
.action-grid { display:inline-flex;gap:3px;align-items:center; }
.dot {
    width:22px;height:22px;border-radius:6px;display:inline-flex;align-items:center;
    justify-content:center;font-size:11px;font-weight:700;
}
.dot-yes  { background:#d1fae5;color:#065f46; }
.dot-no   { background:#f3f4f6;color:#d1d5db; }
.dot-sa   { background:#eff6ff;color:#1e40af; }

/* Action label row inside thead */
.action-labels { font-size:9px;color:#9ca3af;letter-spacing:.04em;display:flex;gap:3px;justify-content:center;margin-top:2px; }
.action-label  { width:22px;text-align:center; }

.card-wrap { background:#fff;border-radius:14px;border:1px solid #e5e7eb;box-shadow:0 1px 3px rgba(0,0,0,.05);overflow:hidden; }

/* Filter tabs */
.filter-tabs { display:flex;gap:6px;margin-bottom:16px;flex-wrap:wrap; }
.filter-tab  { padding:6px 14px;border-radius:8px;font-size:12px;font-weight:700;border:1px solid #e5e7eb;background:#fff;cursor:pointer;color:#6b7280;transition:.15s; }
.filter-tab:hover { background:#f3f4f6; }
.filter-tab.active { background:var(--color-primary,#113659);color:#fff;border-color:transparent; }

.role-pill { display:inline-flex;align-items:center;gap:6px;padding:4px 12px;border-radius:99px;font-size:11px;font-weight:800;letter-spacing:.04em;white-space:nowrap; background:#f0fdf4;color:#166534; }
.pill-sa { background:#fef2f2;color:#991b1b; }

/* Custom Checkbox */
.perm-checkbox {
    width: 20px;
    height: 20px;
    cursor: pointer;
}
</style>

<div style="max-width:1200px;margin:0 auto;">

    {{-- Hero --}}
    <div class="matrix-hero">
        <div class="matrix-hero-inner">
            <div>
                <h1><i class="bi bi-table me-2"></i>Role-Permission Matrix</h1>
                <p>Define dynamic access for each role &nbsp;·&nbsp; Only System & Administration can save changes</p>
            </div>
            <div class="no-print" style="display:flex;gap:8px;">
                <a href="{{ route('users.role-matrix.user-overrides') }}"
                   style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3);padding:9px 16px;border-radius:10px;font-size:12px;font-weight:700;text-decoration:none;display:flex;align-items:center;gap:6px;">
                    <i class="bi bi-person-gear"></i> Per-User Overrides
                </a>
                <a href="{{ route('users.index') }}"
                   style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3);padding:9px 16px;border-radius:10px;font-size:12px;font-weight:700;text-decoration:none;display:flex;align-items:center;gap:6px;">
                    <i class="bi bi-people-fill"></i> Manage Users
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div style="background:#d1fae5;color:#065f46;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-weight:600;font-size:14px;">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="background:#fee2e2;color:#991b1b;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-weight:600;font-size:14px;">
            <i class="bi bi-exclamation-circle-fill me-2"></i>{{ session('error') }}
        </div>
    @endif

    <div class="filter-tabs no-print">
        <button class="filter-tab active" onclick="filterGroup('all',this)">All Modules</button>
        @foreach($modules as $group => $moduleList)
            <button class="filter-tab" onclick="filterGroup('{{ \Illuminate\Support\Str::slug($group) }}',this)">{{ ucwords(str_replace('_', ' ', $group)) }}</button>
        @endforeach
    </div>

    <form action="{{ route('users.role-matrix.store') }}" method="POST">
        @csrf
        <div class="card-wrap">
            <div style="overflow-x:auto;">
                <table class="perm-table" id="matrixTable">
                    <thead>
                        <tr>
                            <th style="text-align:left;padding-left:16px;">Module / Feature</th>
                            
                            {{-- Super Admin (Read-only representation) --}}
                            <th class="role-col">
                                <div><span class="role-pill pill-sa"><i class="bi bi-shield-fill-check"></i> {{ $superAdmin->name ?? 'System & Administration' }}</span></div>
                                <div class="action-labels">
                                    @foreach($actions as $action)
                                        <span class="action-label">{{ strtoupper(substr($action,0,1)) }}</span>
                                    @endforeach
                                </div>
                            </th>

                            {{-- Dynamic Roles --}}
                            @foreach($roles as $role)
                                <th class="role-col">
                                    <div><span class="role-pill"><i class="bi bi-person-fill"></i> {{ $role->name }}</span></div>
                                    <div class="action-labels">
                                        @foreach($actions as $action)
                                            <span class="action-label" title="{{ ucfirst($action) }}">{{ strtoupper(substr($action,0,1)) }}</span>
                                        @endforeach
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>

                        @foreach($modules as $group => $moduleList)
                            <tr class="section-row group-{{ \Illuminate\Support\Str::slug($group) }}">
                                <td colspan="{{ count($roles) + 2 }}"><i class="bi bi-folder-fill me-2"></i> {{ strtoupper(str_replace('_', ' ', $group)) }}</td>
                            </tr>
                            @foreach($moduleList as $module)
                                <tr class="perm-row group-{{ \Illuminate\Support\Str::slug($group) }}">
                                    <td><i class="bi bi-dot"></i> {{ $module }}</td>
                                    
                                    {{-- Super Admin --}}
                                    <td>
                                        <div class="action-grid">
                                            @foreach($actions as $action)
                                                <div class="dot dot-sa" title="Super Admin automatically has all permissions"><i class="bi bi-check-lg"></i></div>
                                            @endforeach
                                        </div>
                                    </td>

                                    {{-- Other Roles --}}
                                    @foreach($roles as $role)
                                        <td>
                                            <div class="action-grid">
                                                @foreach($actions as $action)
                                                    @php 
                                                        $permName = "{$action} {$module}";
                                                        $hasPerm = $role->hasPermissionTo($permName);
                                                    @endphp
                                                    <input type="checkbox" name="permissions[{{ $role->id }}][{{ $permName }}]" value="1"
                                                        class="perm-checkbox" data-role="{{ $role->id }}" data-module="{{ $module }}" data-action="{{ $action }}"
                                                        {{ $hasPerm ? 'checked' : '' }} {{ !auth()->user()->isSuperAdmin() ? 'disabled' : '' }}>
                                                @endforeach
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @endforeach

                    </tbody>
                </table>
            </div>
        </div>

        @if(auth()->user()->isSuperAdmin())
        <div style="margin-top: 20px; text-align: right;">
            <button type="submit" style="background:#113659;color:#fff;border:none;padding:12px 24px;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;">
                <i class="bi bi-save me-2"></i> Save Matrix Configurations
            </button>
        </div>
        @endif
    </form>
</div>

<script>
// Module 4A.1: checking Add/Edit/Delete auto-enables View for that sub-module.
// (Server-side applyViewLock() in RolePermissionController is the source of
// truth on save; this is just immediate UI feedback.)
document.querySelectorAll('.perm-checkbox').forEach(function (cb) {
    cb.addEventListener('change', function () {
        if (['add', 'edit', 'delete'].includes(cb.dataset.action) && cb.checked) {
            var viewCb = document.querySelector(
                '.perm-checkbox[data-role="' + cb.dataset.role + '"][data-module="' + CSS.escape(cb.dataset.module) + '"][data-action="view"]'
            );
            if (viewCb) { viewCb.checked = true; }
        }
    });
});

function filterGroup(group, btn) {
    document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');

    document.querySelectorAll('.section-row, .perm-row').forEach(row => {
        if (group === 'all') {
            row.style.display = '';
        } else {
            row.style.display = row.classList.contains('group-' + group) ? '' : 'none';
        }
    });
}
</script>
</x-dashboard-app>
