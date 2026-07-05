<x-dashboard-app>
<style>
    .map-hero {
        background: linear-gradient(135deg, #10327c 0%, #1a5276 100%);
        border-radius: 14px;
        padding: 28px 32px;
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
    }
    .map-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image: radial-gradient(circle, rgba(255,255,255,.07) 1px, transparent 1px);
        background-size: 20px 20px;
    }
    .map-hero-inner { position: relative; z-index: 1; }
    .step-badge {
        display: inline-flex; align-items: center; gap: 6px;
        background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.25);
        color: #fff; font-size: 12px; font-weight: 700; padding: 4px 12px;
        border-radius: 99px; letter-spacing: .4px; margin-bottom: 10px;
    }
    .step-bar {
        display: flex; gap: 6px; margin-top: 18px;
    }
    .step-bar-item {
        flex: 1; height: 4px; border-radius: 99px;
        background: rgba(255,255,255,.2);
    }
    .step-bar-item.done  { background: rgba(255,255,255,.7); }
    .step-bar-item.active { background: #38bdf8; }

    .map-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 1px 6px rgba(0,0,0,.04);
        margin-bottom: 20px;
    }
    .map-card-header {
        padding: 16px 24px;
        border-bottom: 1px solid #f1f5f9;
        display: flex; align-items: center; justify-content: space-between;
    }
    .map-table { width: 100%; border-collapse: collapse; }
    .map-table th {
        font-size: 11px; font-weight: 700; text-transform: uppercase;
        letter-spacing: .7px; color: #64748b; padding: 12px 20px;
        background: #f8fafc; border-bottom: 1px solid #e5e7eb; text-align: left;
    }
    .map-table td {
        padding: 10px 20px; border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .map-table tr:last-child td { border-bottom: 0; }
    .map-table tr:hover td { background: #fafafa; }

    .field-label {
        font-size: 14px; font-weight: 600; color: #0f172a;
    }
    .field-tag {
        font-size: 11px; font-family: ui-monospace, monospace;
        background: #f1f5f9; color: #475569; padding: 2px 7px;
        border-radius: 5px; margin-top: 3px; display: inline-block;
    }
    .required-dot {
        display: inline-block; width: 7px; height: 7px;
        border-radius: 50%; background: #ef4444; margin-right: 5px;
        vertical-align: middle;
    }

    .map-select {
        width: 100%; border: 1.5px solid #e2e8f0; border-radius: 8px;
        padding: 8px 32px 8px 12px; font-size: 13px; color: #1e293b;
        background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' fill='none'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%2394a3b8' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E") no-repeat right 10px center;
        appearance: none; -webkit-appearance: none;
        outline: none; cursor: pointer; transition: border .15s;
    }
    .map-select:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.12); }
    .map-select.auto-matched { border-color: #10b981; background-color: #f0fdf4; }
    .map-select.required-empty { border-color: #ef4444; background-color: #fef2f2; }

    .match-badge {
        display: inline-flex; align-items: center; gap: 4px;
        font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 99px;
        margin-top: 5px;
    }
    .match-badge.auto { background: #dcfce7; color: #15803d; }
    .match-badge.manual { background: #dbeafe; color: #1d4ed8; }

    .summary-bar {
        position: sticky; bottom: 0; z-index: 10;
        background: #fff; border-top: 2px solid #e5e7eb;
        padding: 16px 24px;
        display: flex; align-items: center; justify-content: space-between;
        gap: 16px;
    }

    .btn-proceed {
        background: #10327c; color: #fff; font-size: 15px; font-weight: 700;
        padding: 11px 28px; border-radius: 10px; border: none; cursor: pointer;
        display: inline-flex; align-items: center; gap: 8px;
        transition: background .2s; text-decoration: none;
    }
    .btn-proceed:hover { background: #0c2461; color: #fff; }
    .btn-proceed:disabled { background: #94a3b8; cursor: not-allowed; }

    .btn-back {
        background: #f1f5f9; color: #475569; font-size: 14px; font-weight: 600;
        padding: 10px 20px; border-radius: 10px; border: 1px solid #e2e8f0;
        cursor: pointer; text-decoration: none; transition: background .15s;
    }
    .btn-back:hover { background: #e2e8f0; color: #334155; }

    #progressRing { transition: stroke-dashoffset .3s; }
</style>

{{-- ▌ HERO ▌ --}}
<div class="map-hero">
    <div class="map-hero-inner">
        <div class="step-badge"><i class="bi bi-arrows-angle-contract"></i> Step 2 of 3</div>
        <h1 style="color:#fff; font-size:24px; font-weight:800; margin:0 0 4px;">Map Your Columns</h1>
        <p style="color:rgba(255,255,255,.65); font-size:14px; margin:0;">
            Match each column from your uploaded file to the correct system field.
            <strong style="color:#bfdbfe;">{{ $totalRows }} rows</strong> detected.
        </p>
        <div class="step-bar">
            <div class="step-bar-item done"></div>
            <div class="step-bar-item active"></div>
            <div class="step-bar-item"></div>
        </div>
    </div>
</div>

{{-- ▌ FILE HEADERS DETECTED ▌ --}}
<div class="map-card" style="margin-bottom:20px;">
    <div class="map-card-header">
        <div>
            <div style="font-size:14px; font-weight:700; color:#0f172a;">
                <i class="bi bi-file-earmark-spreadsheet me-1 text-green-600"></i>
                Columns detected in your file ({{ count($headers) }})
            </div>
            <div style="font-size:12px; color:#94a3b8; margin-top:2px;">These are the header names from row 1 of your uploaded file.</div>
        </div>
        <div id="mappedCount" style="font-size:13px; font-weight:700; color:#0f172a;">
            <span id="mappedNum">0</span> / <span id="reqNum">3</span> required fields mapped
        </div>
    </div>
    <div style="padding:16px 20px; display:flex; flex-wrap:wrap; gap:8px;">
        @foreach($headers as $h)
            <span style="background:#f1f5f9; color:#334155; font-size:12px; font-family:ui-monospace,monospace; padding:4px 12px; border-radius:8px; border:1px solid #e2e8f0;">{{ $h }}</span>
        @endforeach
    </div>
</div>

{{-- ▌ MAPPING TABLE ▌ --}}
<form method="POST" action="{{ route('imports.map') }}" id="mappingForm">
    @csrf
    <input type="hidden" name="tmp_key"           value="{{ $tmpKey }}">
    <input type="hidden" name="tmp_path"          value="{{ $tmpPath }}">
    <input type="hidden" name="replace_all"       value="{{ $replaceAll ? '1' : '0' }}">
    <input type="hidden" name="dry_run"           value="{{ $dryRun     ? '1' : '0' }}">
    <input type="hidden" name="original_filename" value="{{ $originalFileName ?? '' }}">
    <input type="hidden" name="routing_mode"      value="{{ $routingMode ?? 'global' }}">
    <input type="hidden" name="granular_status"   value="{{ $granularStatus ?? '' }}">

    <div class="map-card">
        <div class="map-card-header">
            <div style="font-size:14px; font-weight:700; color:#0f172a;">
                <i class="bi bi-table me-1" style="color:#10327c;"></i>
                Field Mapping
            </div>
            <div style="display:flex; align-items:center; gap:10px;">
                <span style="font-size:12px; color:#94a3b8;"><span class="required-dot"></span> = Required field</span>
                <button type="button" onclick="autoMatchAll()" style="font-size:12px; font-weight:600; color:#10327c; background:#eff6ff; border:1px solid #bfdbfe; padding:5px 12px; border-radius:7px; cursor:pointer;">
                    <i class="bi bi-magic me-1"></i>Auto-Match All
                </button>
                <button type="button" onclick="clearAll()" style="font-size:12px; font-weight:600; color:#6b7280; background:#f9fafb; border:1px solid #e5e7eb; padding:5px 12px; border-radius:7px; cursor:pointer;">
                    <i class="bi bi-x-circle me-1"></i>Clear All
                </button>
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table class="map-table">
                <thead>
                    <tr>
                        <th style="width:38%;">System Field</th>
                        <th style="width:40%;">Your File's Column</th>
                        <th style="width:22%;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $fileHeaders     = $headers;
                        $autoMappingData = $autoMapping; // pre-computed in controller
                    @endphp
                    @foreach($systemFields as $dbCol => $info)
                        @php
                            $isRequired  = $info['required'];
                            $preSelected = $autoMappingData[$dbCol] ?? null;
                        @endphp
                        <tr data-required="{{ $isRequired ? '1' : '0' }}" data-dbcol="{{ $dbCol }}">
                            <td>
                                @if($isRequired) <span class="required-dot"></span> @endif
                                <span class="field-label">{{ $info['label'] }}</span><br>
                                <span class="field-tag">{{ $dbCol }}</span>
                            </td>
                            <td>
                                <select
                                    name="mapping[{{ $dbCol }}]"
                                    class="map-select {{ $preSelected ? 'auto-matched' : '' }}"
                                    data-dbcol="{{ $dbCol }}"
                                    data-required="{{ $isRequired ? '1' : '0' }}"
                                    onchange="onSelectChange(this)"
                                >
                                    <option value="">— Skip this field —</option>
                                    @foreach($fileHeaders as $fh)
                                        <option value="{{ $fh }}" {{ $preSelected === $fh ? 'selected' : '' }}>
                                            {{ $fh }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="match-badge {{ $preSelected ? 'auto' : '' }}" id="badge-{{ $dbCol }}" style="{{ $preSelected ? '' : 'display:none;' }}">
                                    @if($preSelected)
                                        <i class="bi bi-check2-circle"></i> Auto-matched
                                    @endif
                                </div>
                            </td>
                            <td id="status-{{ $dbCol }}">
                                @if($preSelected)
                                    <span style="color:#15803d; font-size:12px; font-weight:700;"><i class="bi bi-check-circle-fill me-1"></i>Mapped</span>
                                @elseif($isRequired)
                                    <span style="color:#dc2626; font-size:12px; font-weight:700;"><i class="bi bi-exclamation-circle-fill me-1"></i>Required</span>
                                @else
                                    <span style="color:#9ca3af; font-size:12px;">Optional</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ▌ STICKY BOTTOM BAR ▌ --}}
    <div class="summary-bar">
        <div style="display:flex; align-items:center; gap:16px;">
            <a href="{{ route('imports.index') }}" class="btn-back">
                <i class="bi bi-chevron-left me-1"></i> Back
            </a>
            <div>
                <div style="font-size:13px; font-weight:700; color:#0f172a;" id="summaryText">
                    Checking mapping…
                </div>
                <div style="font-size:12px; color:#64748b; margin-top:1px;">
                    Optional fields can be left as "— Skip this field —"
                </div>
            </div>
        </div>
        <button type="submit" class="btn-proceed" id="proceedBtn">
            Preview Import <i class="bi bi-arrow-right ms-1"></i>
        </button>
    </div>
</form>

<script>
    // ──────────────────────────────────────────────────────────
    // All file headers from PHP (for JS auto-matching)
    const FILE_HEADERS = @json($headers);

    // System fields requiring mapping
    const SYSTEM_FIELDS = @json($systemFields);

    // Original auto-mapping from controller
    const AUTO_MAPPING = @json($autoMapping);

    // Fuzzy-ish auto-match: try to find a file header that closely
    // matches the given system field label or db column name
    function findBestMatch(dbCol, label) {
        const normalize = s => s.toLowerCase().replace(/[^a-z0-9]/g, '');
        const needle1 = normalize(dbCol);
        const needle2 = normalize(label);

        // Exact match on normalized value
        for (const h of FILE_HEADERS) {
            if (normalize(h) === needle1 || normalize(h) === needle2) return h;
        }

        // Partial contains
        for (const h of FILE_HEADERS) {
            const hn = normalize(h);
            if (hn.includes(needle1) || needle1.includes(hn)) return h;
            if (hn.includes(needle2) || needle2.includes(hn)) return h;
        }
        return null;
    }

    function onSelectChange(sel) {
        const dbCol = sel.dataset.dbcol;
        const isRequired = sel.dataset.required === '1';
        const val = sel.value;

        // Update badge
        const badge = document.getElementById('badge-' + dbCol);
        const status = document.getElementById('status-' + dbCol);

        sel.classList.remove('auto-matched', 'required-empty');

        if (val) {
            sel.classList.add('auto-matched');
            badge.style.display = 'inline-flex';
            badge.className = 'match-badge manual';
            badge.innerHTML = '<i class="bi bi-hand-index-thumb me-1"></i> Manually mapped';
            status.innerHTML = '<span style="color:#1d4ed8; font-size:12px; font-weight:700;"><i class="bi bi-check-circle-fill me-1"></i>Mapped</span>';
        } else {
            badge.style.display = 'none';
            sel.classList.remove('auto-matched');
            if (isRequired) {
                sel.classList.add('required-empty');
                status.innerHTML = '<span style="color:#dc2626; font-size:12px; font-weight:700;"><i class="bi bi-exclamation-circle-fill me-1"></i>Required</span>';
            } else {
                status.innerHTML = '<span style="color:#9ca3af; font-size:12px;">Optional — Skipped</span>';
            }
        }

        updateSummary();
    }

    function updateSummary() {
        let requiredTotal  = 0;
        let requiredMapped = 0;
        let anyDuplicate   = false;
        const seen = {};

        document.querySelectorAll('[data-required="1"] select').forEach(sel => {
            requiredTotal++;
            if (sel.value) requiredMapped++;
        });

        // Check for duplicate assignments (two system fields mapped to same Excel column)
        document.querySelectorAll('.map-select').forEach(sel => {
            if (sel.value) {
                if (seen[sel.value]) { anyDuplicate = true; }
                seen[sel.value] = true;
            }
        });

        document.getElementById('mappedNum').textContent = requiredMapped;
        document.getElementById('reqNum').textContent = requiredTotal;

        const btn = document.getElementById('proceedBtn');
        const summary = document.getElementById('summaryText');
        const allOpt = document.querySelectorAll('.map-select').length;
        let optMapped = 0;
        document.querySelectorAll('.map-select').forEach(s => { if (s.value) optMapped++; });

        if (anyDuplicate) {
            summary.innerHTML = '⚠ <span style="color:#dc2626;">Two system fields are mapped to the same column — please fix.</span>';
            btn.disabled = true;
            return;
        }

        if (requiredMapped < requiredTotal) {
            summary.innerHTML = `<span style="color:#dc2626; font-weight:700;">⚠ ${requiredTotal - requiredMapped} required field(s) still need to be mapped.</span>`;
            btn.disabled = true;
        } else {
            summary.innerHTML = `<span style="color:#15803d; font-weight:700;">✔ All required fields mapped!</span> <span style="color:#64748b;">(${optMapped} of ${allOpt} total fields)</span>`;
            btn.disabled = false;
        }
    }

    function autoMatchAll() {
        document.querySelectorAll('.map-select').forEach(sel => {
            if (sel.value) return; // skip already-mapped
            const dbCol = sel.dataset.dbcol;
            const fieldDef = SYSTEM_FIELDS[dbCol];
            if (!fieldDef) return;
            const best = findBestMatch(dbCol, fieldDef.label);
            if (best) {
                sel.value = best;
                onSelectChange(sel);
                const badge = document.getElementById('badge-' + dbCol);
                if (badge) {
                    badge.className = 'match-badge auto';
                    badge.innerHTML = '<i class="bi bi-magic me-1"></i>Auto-matched';
                    badge.style.display = 'inline-flex';
                }
            }
        });
        updateSummary();
    }

    function clearAll() {
        document.querySelectorAll('.map-select').forEach(sel => {
            sel.value = '';
            sel.classList.remove('auto-matched');
            const badge = document.getElementById('badge-' + sel.dataset.dbcol);
            if (badge) badge.style.display = 'none';
        });
        document.querySelectorAll('[data-required="1"] .map-select').forEach(sel => {
            sel.classList.add('required-empty');
            const status = document.getElementById('status-' + sel.dataset.dbcol);
            if (status) status.innerHTML = '<span style="color:#dc2626; font-size:12px; font-weight:700;"><i class="bi bi-exclamation-circle-fill me-1"></i>Required</span>';
        });
        updateSummary();
    }

    // Run on page load
    document.addEventListener('DOMContentLoaded', () => {
        updateSummary();
    });
</script>
</x-dashboard-app>
