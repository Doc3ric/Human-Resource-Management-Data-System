<x-dashboard-app>
<style>
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

.section-row td { background:#f1f5f9;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#475569;padding:8px 16px; }
.perm-table { width:100%;border-collapse:separate;border-spacing:0;font-size:13px; }
.perm-table thead th {
    background:#fff;padding:12px 10px;text-align:center;font-size:11px;
    font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;
    border-bottom:2px solid #e5e7eb;
}
.perm-table thead th:first-child { text-align:left;padding-left:16px;min-width:220px; }
.perm-table tbody tr:hover td { background:#f9fafb; }
.perm-table tbody td { padding:9px 8px;border-bottom:1px solid #f3f4f6;text-align:center;vertical-align:middle; }
.perm-table tbody td:first-child { text-align:left;padding-left:16px;font-weight:600;color:#111827; }
.card-wrap { background:#fff;border-radius:14px;border:1px solid #e5e7eb;box-shadow:0 1px 3px rgba(0,0,0,.05);overflow:hidden; }
.perm-checkbox { width: 20px; height: 20px; cursor: pointer; }
.role-badge {
    display:inline-flex;align-items:center;gap:6px;padding:4px 12px;border-radius:99px;
    font-size:11px;font-weight:800;letter-spacing:.04em;white-space:nowrap;background:#eff6ff;color:#1e40af;
}
</style>

<div style="max-width:1100px;margin:0 auto;">
    <div class="matrix-hero">
        <div class="matrix-hero-inner">
            <div>
                <h1><i class="bi bi-person-gear me-2"></i>Per-User Permission Overrides</h1>
                <p>Grant a specific user extra access beyond their role &nbsp;·&nbsp; Only System &amp; Administration can save changes</p>
            </div>
            <div class="no-print">
                <a href="{{ route('users.role-matrix') }}"
                   style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3);padding:9px 16px;border-radius:10px;font-size:12px;font-weight:700;text-decoration:none;display:flex;align-items:center;gap:6px;">
                    <i class="bi bi-table"></i> Role Matrix
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

    <form method="GET" action="{{ route('users.role-matrix.user-overrides') }}" style="margin-bottom:16px;display:flex;gap:10px;align-items:center;">
        <label style="font-size:13px;font-weight:600;">User:</label>
        <select name="user_id" onchange="this.form.submit()" style="border:1px solid #d1d5db;border-radius:8px;padding:8px 12px;font-size:13px;">
            @foreach($users as $u)
                <option value="{{ $u->id }}" {{ $selectedUser && $selectedUser->id === $u->id ? 'selected' : '' }}>
                    {{ $u->name }} ({{ $u->role_label }})
                </option>
            @endforeach
        </select>
        @if($selectedUser)
            <span class="role-badge"><i class="bi bi-person-fill"></i> Role: {{ $selectedUser->role_label }}</span>
        @endif
    </form>

    @if($selectedUser)
        <div class="alert" style="background:#fffbeb;color:#92400e;border:1px solid #fde68a;padding:10px 14px;border-radius:8px;font-size:12.5px;margin-bottom:14px;">
            <i class="bi bi-info-circle-fill"></i> These checkboxes are <strong>direct grants to this user only</strong>,
            on top of whatever their <strong>{{ $selectedUser->role_label }}</strong> role already gives them from the
            <a href="{{ route('users.role-matrix') }}">Role Matrix</a>. Unchecking a box here does not remove access
            that comes from their role — go to the Role Matrix to change role-wide defaults.
        </div>

        <form action="{{ route('users.role-matrix.user-overrides.store', $selectedUser) }}" method="POST">
            @csrf
            <div class="card-wrap">
                <div style="overflow-x:auto;">
                    <table class="perm-table">
                        <thead>
                            <tr>
                                <th style="text-align:left;padding-left:16px;">Module / Feature</th>
                                @foreach($actions as $action)
                                    <th>{{ ucfirst($action) }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($modules as $group => $moduleList)
                                <tr class="section-row">
                                    <td colspan="{{ count($actions) + 1 }}"><i class="bi bi-folder-fill me-2"></i>{{ strtoupper($group) }}</td>
                                </tr>
                                @foreach($moduleList as $module)
                                    <tr>
                                        <td><i class="bi bi-dot"></i> {{ $module }}</td>
                                        @foreach($actions as $action)
                                            @php $permName = "{$action} {$module}"; @endphp
                                            <td>
                                                <input type="checkbox" name="permissions[{{ $permName }}]" value="1"
                                                    class="perm-checkbox" data-module="{{ $module }}" data-action="{{ $action }}"
                                                    {{ isset($directPerms[$permName]) ? 'checked' : '' }}
                                                    {{ !auth()->user()->isSuperAdmin() ? 'disabled' : '' }}>
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
                    <i class="bi bi-save me-2"></i> Save Overrides for {{ $selectedUser->name }}
                </button>
            </div>
            @endif
        </form>
    @else
        <p>No other users found.</p>
    @endif
</div>

<script>
document.querySelectorAll('.perm-checkbox').forEach(function (cb) {
    cb.addEventListener('change', function () {
        if (['add', 'edit', 'delete'].includes(cb.dataset.action) && cb.checked) {
            var viewCb = document.querySelector(
                '.perm-checkbox[data-module="' + CSS.escape(cb.dataset.module) + '"][data-action="view"]'
            );
            if (viewCb) { viewCb.checked = true; }
        }
    });
});
</script>
</x-dashboard-app>
