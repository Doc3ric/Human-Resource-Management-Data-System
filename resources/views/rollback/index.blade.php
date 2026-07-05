<x-dashboard-app>
<style>
.rb-card        { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:24px; margin-bottom:20px; }
.rb-title       { font-size:20px; font-weight:800; color:#0f172a; display:flex; align-items:center; gap:10px; }
.rb-subtitle    { font-size:13px; color:#64748b; margin-top:4px; }
.rb-section     { font-size:11px; font-weight:700; color:#64748b; letter-spacing:.5px; text-transform:uppercase; margin-bottom:8px; }
.rb-grid        { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
.rb-group       { display:flex; flex-direction:column; gap:6px; }
.rb-label       { font-size:12px; font-weight:600; color:#374151; }
.rb-input       { border:1px solid #d1d5db; border-radius:8px; padding:8px 12px; font-size:13px; width:100%; }
.rb-input:focus { border-color:#3b82f6; outline:none; box-shadow:0 0 0 3px rgba(59,130,246,.1); }
.rb-check-group { display:flex; gap:16px; flex-wrap:wrap; }
.rb-check       { display:flex; align-items:center; gap:7px; font-size:13px; font-weight:500; cursor:pointer; }
.rb-btn         { display:inline-flex; align-items:center; gap:7px; padding:10px 20px; border-radius:8px;
                  font-size:13px; font-weight:700; border:none; cursor:pointer; transition:all .15s; }
.rb-btn-blue    { background:#1d4ed8; color:#fff; }
.rb-btn-blue:hover { background:#1e40af; }
.rb-btn-red     { background:#dc2626; color:#fff; }
.rb-btn-red:hover { background:#b91c1c; }
.rb-btn-gray    { background:#f1f5f9; color:#374151; border:1px solid #e2e8f0; }
.rb-btn-gray:hover { background:#e2e8f0; }
.rb-badge       { display:inline-block; padding:2px 9px; border-radius:20px; font-size:11px; font-weight:700; }
.rb-updated     { background:#dbeafe; color:#1e40af; }
.rb-created     { background:#dcfce7; color:#15803d; }
.rb-deleted     { background:#fee2e2; color:#dc2626; }
.rb-table       { width:100%; border-collapse:collapse; font-size:12px; }
.rb-table th    { background:#f8fafc; color:#64748b; font-size:11px; font-weight:700; padding:8px 12px;
                  text-align:left; border-bottom:2px solid #e2e8f0; }
.rb-table td    { padding:8px 12px; border-bottom:1px solid #f1f5f9; vertical-align:top; }
.rb-table tr:hover td { background:#f8fafc; }
.rb-warn        { background:#fffbeb; border:1px solid #fcd34d; border-radius:8px; padding:14px 18px;
                  font-size:13px; color:#92400e; display:flex; gap:10px; align-items:flex-start; }
.rb-danger-box  { background:#fef2f2; border:2px solid #fca5a5; border-radius:10px; padding:18px 20px; }
.rb-summary-pills { display:flex; gap:10px; flex-wrap:wrap; margin:12px 0; }
.rb-pill        { padding:6px 14px; border-radius:20px; font-size:12px; font-weight:700; }
.rb-spinner     { display:none; width:18px; height:18px; border:2px solid #fff; border-top-color:transparent;
                  border-radius:50%; animation:spin .6s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>

<div style="max-width:900px; margin:0 auto; padding:24px 16px;">

    {{-- Page header --}}
    <div class="rb-card" style="border-left:4px solid #dc2626;">
        <div class="rb-title">
            <i class="bi bi-arrow-counterclockwise" style="color:#dc2626;font-size:22px;"></i>
            Database Rollback by Date Range
        </div>
        <div class="rb-subtitle">
            Restore PlantillaRecord fields to their state before the selected date range.
            Only affects <strong>PlantillaRecord</strong> data. Super Admin only.
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="rb-card" style="border-left:4px solid #16a34a; background:#f0fdf4; color:#15803d;">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('warning'))
        <div class="rb-card" style="border-left:4px solid #f59e0b; background:#fffbeb; color:#92400e;">
            <i class="bi bi-exclamation-triangle-fill"></i> {{ session('warning') }}
            @if(session('rollback_errors'))
                <ul style="margin-top:8px; padding-left:20px; font-size:12px;">
                    @foreach(session('rollback_errors') as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif
    @if(session('error'))
        <div class="rb-card" style="border-left:4px solid #dc2626; background:#fef2f2; color:#dc2626;">
            <i class="bi bi-x-circle-fill"></i> {{ session('error') }}
        </div>
    @endif

    {{-- Step 1: Date range + event type --}}
    <div class="rb-card" id="step1-card">
        <div class="rb-section">Step 1 — Select Date Range &amp; Event Types</div>
        <div class="rb-grid" style="margin-bottom:16px;">
            <div class="rb-group">
                <label class="rb-label">From Date</label>
                <input type="date" id="date_from" class="rb-input" value="{{ date('Y-m-d') }}">
            </div>
            <div class="rb-group">
                <label class="rb-label">To Date</label>
                <input type="date" id="date_to" class="rb-input" value="{{ date('Y-m-d') }}">
            </div>
        </div>
        <div class="rb-section">Which events to roll back?</div>
        <div class="rb-check-group" style="margin-bottom:20px;">
            <label class="rb-check">
                <input type="checkbox" id="ev-updated" checked style="width:15px;height:15px;">
                <span class="rb-badge rb-updated">Updated</span>
                Restore fields to their previous values
            </label>
            <label class="rb-check">
                <input type="checkbox" id="ev-created" style="width:15px;height:15px;">
                <span class="rb-badge rb-created">Created</span>
                Soft-delete records added in this period
            </label>
            <label class="rb-check">
                <input type="checkbox" id="ev-deleted" style="width:15px;height:15px;">
                <span class="rb-badge rb-deleted">Deleted</span>
                Restore records deleted in this period
            </label>
        </div>
        <button class="rb-btn rb-btn-blue" id="btn-preview" onclick="runPreview()">
            <span class="rb-spinner" id="spin-preview"></span>
            <i class="bi bi-search" id="icon-preview"></i>
            Preview Changes
        </button>
    </div>

    {{-- Step 2: Preview results --}}
    <div id="preview-section" style="display:none;">
        <div class="rb-card" id="preview-card">
            <div class="rb-section">Step 2 — Review What Will Be Rolled Back</div>

            <div class="rb-summary-pills" id="summary-pills"></div>

            <div id="no-changes" style="display:none; color:#64748b; font-size:13px; padding:12px 0;">
                <i class="bi bi-info-circle"></i> No changes found in the selected date range for the chosen event types.
            </div>

            <div id="preview-table-wrap" style="overflow-x:auto; margin-bottom:16px;">
                <table class="rb-table" id="preview-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Employee</th>
                            <th>Item No.</th>
                            <th>Event</th>
                            <th>Changed By</th>
                            <th>Changed At</th>
                            <th>Fields Affected</th>
                            <th>Will Restore To</th>
                        </tr>
                    </thead>
                    <tbody id="preview-tbody"></tbody>
                </table>
            </div>

            <div id="confirm-section" style="display:none;">
                <div class="rb-warn" style="margin-bottom:16px;">
                    <i class="bi bi-exclamation-triangle-fill" style="font-size:18px; flex-shrink:0;"></i>
                    <div>
                        <strong>This action cannot be undone automatically.</strong>
                        Export a Full Database Backup first if you are unsure.
                        The rollback runs inside a transaction — if any record fails, the entire batch is rolled back.
                    </div>
                </div>
                <form method="POST" action="{{ route('rollback.execute') }}" id="rollback-form">
                    @csrf
                    <input type="hidden" name="date_from" id="h-date-from">
                    <input type="hidden" name="date_to" id="h-date-to">
                    <div id="h-events-container"></div>
                    <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                        <button type="submit" class="rb-btn rb-btn-red" onclick="return confirmExecute()">
                            <i class="bi bi-arrow-counterclockwise"></i>
                            Execute Rollback
                        </button>
                        <button type="button" class="rb-btn rb-btn-gray" onclick="resetPreview()">
                            <i class="bi bi-x"></i> Cancel
                        </button>
                        <a href="{{ route('all-data.export.full') }}" class="rb-btn rb-btn-gray">
                            <i class="bi bi-database-down"></i> Export Backup First
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Info box --}}
    <div class="rb-card" style="background:#f8fafc;">
        <div class="rb-section">How This Works</div>
        <ul style="font-size:13px; color:#475569; line-height:1.8; padding-left:20px;">
            <li><strong>Updated</strong> — For each record changed in the range, the system restores only the fields that were modified, using the snapshot captured before the change. If a record was changed multiple times, it restores to the state <em>before the first change in the range</em>.</li>
            <li><strong>Created</strong> — Records added in the range are soft-deleted (moved to the archive, restorable from the Recycle Bin).</li>
            <li><strong>Deleted</strong> — Records deleted in the range are restored.</li>
            <li>Changes are applied per-record inside a single database transaction. All succeed or all fail.</li>
            <li>The rollback itself is logged in the Activity Log.</li>
        </ul>
    </div>
</div>

<script>
function getSelectedEvents() {
    const events = [];
    if (document.getElementById('ev-updated').checked) events.push('updated');
    if (document.getElementById('ev-created').checked) events.push('created');
    if (document.getElementById('ev-deleted').checked) events.push('deleted');
    return events;
}

function runPreview() {
    const from   = document.getElementById('date_from').value;
    const to     = document.getElementById('date_to').value;
    const events = getSelectedEvents();
    if (!from || !to) { alert('Please select both dates.'); return; }
    if (events.length === 0) { alert('Select at least one event type.'); return; }

    const btn  = document.getElementById('btn-preview');
    const spin = document.getElementById('spin-preview');
    const icon = document.getElementById('icon-preview');
    btn.disabled = true; spin.style.display = 'inline-block'; icon.style.display = 'none';

    fetch('{{ route("rollback.preview") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ date_from: from, date_to: to, events }),
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false; spin.style.display = 'none'; icon.style.display = 'inline-block';
        renderPreview(data, from, to, events);
    })
    .catch(() => {
        btn.disabled = false; spin.style.display = 'none'; icon.style.display = 'inline-block';
        alert('Preview failed. Please try again.');
    });
}

function renderPreview(data, from, to, events) {
    document.getElementById('preview-section').style.display = 'block';
    document.getElementById('preview-section').scrollIntoView({ behavior: 'smooth', block: 'start' });

    // Summary pills
    const pills = document.getElementById('summary-pills');
    pills.innerHTML = '';
    if (data.count === 0) {
        document.getElementById('no-changes').style.display = 'block';
        document.getElementById('preview-table-wrap').style.display = 'none';
        document.getElementById('confirm-section').style.display = 'none';
        return;
    }
    document.getElementById('no-changes').style.display = 'none';
    document.getElementById('preview-table-wrap').style.display = 'block';

    const colors = { updated: '#dbeafe|#1e40af', created: '#dcfce7|#15803d', deleted: '#fee2e2|#dc2626' };
    Object.entries(data.summary).forEach(([ev, cnt]) => {
        if (cnt <= 0) return;
        const [bg, color] = colors[ev].split('|');
        pills.innerHTML += `<span class="rb-pill" style="background:${bg};color:${color};">${cnt} ${ev}</span>`;
    });
    pills.innerHTML += `<span class="rb-pill" style="background:#f1f5f9;color:#334155;">${data.count} total records</span>`;

    // Table rows
    const tbody = document.getElementById('preview-tbody');
    tbody.innerHTML = '';
    const eventClass = { updated: 'rb-updated', created: 'rb-created', deleted: 'rb-deleted' };
    data.records.forEach((r, i) => {
        const fieldsHtml = r.fields.map(f => `<code style="background:#f1f5f9;padding:1px 5px;border-radius:4px;font-size:11px;">${f}</code>`).join(' ');
        tbody.innerHTML += `
        <tr>
            <td style="color:#94a3b8;">${i+1}</td>
            <td style="font-weight:600;">${r.name}</td>
            <td style="font-family:monospace;font-size:11px;">${r.item_no}</td>
            <td><span class="rb-badge ${eventClass[r.event]}">${r.event}</span></td>
            <td style="color:#475569;">${r.changed_by}</td>
            <td style="color:#475569;font-size:11px;white-space:nowrap;">${r.changed_at}</td>
            <td style="max-width:180px;">${fieldsHtml}</td>
            <td style="font-size:11px;color:#64748b;max-width:200px;">${r.old_snippet}</td>
        </tr>`;
    });

    // Wire up hidden form
    document.getElementById('h-date-from').value = from;
    document.getElementById('h-date-to').value = to;
    const ec = document.getElementById('h-events-container');
    ec.innerHTML = '';
    events.forEach(ev => {
        const inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = 'events[]'; inp.value = ev;
        ec.appendChild(inp);
    });
    document.getElementById('confirm-section').style.display = 'block';
}

function confirmExecute() {
    return confirm('⚠️ Execute Rollback?\n\nThis will revert the displayed records to their previous state. This cannot be undone automatically.\n\nClick OK to proceed.');
}

function resetPreview() {
    document.getElementById('preview-section').style.display = 'none';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>
</x-dashboard-app>
