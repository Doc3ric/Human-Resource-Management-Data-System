<x-dashboard-app>

{{-- ── ApexCharts CDN ── --}}
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.49.0/dist/apexcharts.min.js"></script>

<style>
.gad-module { display: none; }
.gad-module.active { display: block; animation: fadein .25s ease; }
@keyframes fadein { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:translateY(0); } }

.gad-tab {
    padding: 9px 20px; font-size: 13px; font-weight: 600;
    border: none; background: none; cursor: pointer; color: #6b7280;
    border-bottom: 2.5px solid transparent; transition: .2s; white-space: nowrap;
}
.gad-tab:hover  { color: #111827; }
.gad-tab.active { color: var(--color-accent, #2563eb); border-bottom-color: var(--color-accent, #2563eb); }

.stat-tile {
    background: #fff; border-radius: 12px; padding: 20px 22px;
    border: 1px solid #e5e7eb; box-shadow: 0 1px 2px rgba(0,0,0,.04);
}
.stat-tile .val { font-size: 2rem; font-weight: 800; line-height: 1; }
.stat-tile .lbl { font-size: 11.5px; font-weight: 600; color: #6b7280; margin-top: 4px; text-transform: uppercase; letter-spacing: .04em; }

.insight-panel {
    background: #f0f7ff; border: 1px solid #c3daee; border-radius: 12px; padding: 16px 20px;
    display: flex; flex-wrap: wrap; gap: 16px;
}
.insight-block { flex: 1 1 160px; }
.insight-block .ib-label { font-size: 10px; font-weight: 800; color: #4b6071; text-transform: uppercase; letter-spacing: .06em; }
.insight-block .ib-value { font-size: 1.1rem; font-weight: 800; color: #0f2a45; margin-top: 2px; }
.insight-block .ib-sub   { font-size: 11px; color: #4b6071; margin-top: 2px; }

.gad-alert-red    { background:#fef2f2; border:1.5px solid #fca5a5; color:#991b1b; border-radius:10px; padding:10px 14px; font-size:13px; font-weight:600; display:flex; gap:8px; align-items:flex-start; }
.gad-alert-yellow { background:#fffbeb; border:1.5px solid #fde68a; color:#92400e; border-radius:10px; padding:10px 14px; font-size:13px; font-weight:600; display:flex; gap:8px; align-items:flex-start; }
.gad-alert-green  { background:#f0fdf4; border:1.5px solid #86efac; color:#14532d; border-radius:10px; padding:10px 14px; font-size:13px; font-weight:600; display:flex; gap:8px; align-items:flex-start; }

.module-card { background:#fff; border-radius:14px; padding:24px; border:1px solid #e5e7eb; box-shadow:0 1px 3px rgba(0,0,0,.05); margin-bottom:20px; }
.module-title { font-size:16px; font-weight:800; color:#111827; margin-bottom:4px; }
.module-sub   { font-size:12px; color:#6b7280; margin-bottom:16px; }
.chart-wrap   { min-height: 280px; }

.gad-table th { font-size:11px; text-transform:uppercase; letter-spacing:.04em; color:#6b7280; font-weight:700; padding:8px 12px; }
.gad-table td { font-size:13px; padding:8px 12px; }
.gad-table tr:hover td { background:#f9fafb; }

.filter-bar {
    background:#fff; border-radius:12px; border:1px solid #e5e7eb;
    padding:14px 20px; display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end; margin-bottom:20px;
}
.filter-bar label { font-size:11px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:.05em; display:block; margin-bottom:4px; }
.filter-bar select, .filter-bar input {
    padding:7px 12px; border:1px solid #d1d5db; border-radius:8px; font-size:13px; color:#111827; background:#f9fafb; outline:none;
}
.filter-bar select:focus, .filter-bar input:focus { border-color: var(--color-accent,#2563eb); background:#fff; }

.print-btn {
    background:#f3f4f6; border:1px solid #d1d5db; color:#374151;
    padding:7px 14px; border-radius:8px; font-size:12px; font-weight:600;
    cursor:pointer; display:inline-flex; align-items:center; gap:5px; text-decoration:none;
    transition:.15s;
}
.print-btn:hover { background:#e5e7eb; }
.csv-btn {
    background: var(--color-primary,#113659); color:#fff;
    padding:7px 14px; border-radius:8px; font-size:12px; font-weight:600;
    cursor:pointer; display:inline-flex; align-items:center; gap:5px; text-decoration:none; border:none;
    transition:.15s;
}
.csv-btn:hover { opacity:.88; color:#fff; }

.badge-gap-ok  { background:#d1fae5; color:#065f46; padding:2px 8px; border-radius:99px; font-size:11px; font-weight:700; }
.badge-gap-bad { background:#fee2e2; color:#991b1b; padding:2px 8px; border-radius:99px; font-size:11px; font-weight:700; }
</style>

<div style="max-width:1200px;margin:0 auto;">

    {{-- ── Page Header ── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;flex-wrap:wrap;gap:12px;">
        <div>
            <h1 style="font-size:22px;font-weight:800;color:#111827;margin:0;">GAD Analytics Engine</h1>
            <p style="font-size:12.5px;color:#6b7280;margin:4px 0 0;">
                Sex-disaggregated workforce statistics &nbsp;·&nbsp; RA 9710 (Magna Carta of Women) Compliance
            </p>
        </div>
        <div style="display:flex;gap:8px;">
            <button onclick="window.print()" class="print-btn"><i class="bi bi-printer"></i> Print</button>
        </div>
    </div>

    {{-- ── Global Filter Bar ── --}}
    <form method="GET" action="{{ route('gad.index') }}" class="filter-bar">
        <div>
            <label>Employment Status</label>
            <select name="status">
                <option value="">All Statuses</option>
                @foreach($statusOptions as $s)
                    <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label>Office / Department</label>
            <select name="office" style="max-width:240px;">
                <option value="">All Offices</option>
                @foreach($officeOptions as $o)
                    <option value="{{ $o }}" {{ request('office') == $o ? 'selected' : '' }}>{{ $o }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <button type="submit" class="csv-btn"><i class="bi bi-funnel-fill"></i> Apply Filter</button>
            <a href="{{ route('gad.index') }}" class="print-btn" style="margin-left:6px;"><i class="bi bi-x"></i> Clear</a>
        </div>
    </form>

    {{-- ── Insight Panel (global) ── --}}
    <div class="insight-panel mb-4">
        <div class="insight-block">
            <div class="ib-label">Total Employees</div>
            <div class="ib-value">{{ number_format($total) }}</div>
            <div class="ib-sub">Non-vacant positions</div>
        </div>
        <div class="insight-block">
            <div class="ib-label">Male</div>
            <div class="ib-value" style="color:#1d4ed8;">{{ number_format($male) }}</div>
            <div class="ib-sub">{{ $total > 0 ? round($male/$total*100,1) : 0 }}% of workforce</div>
        </div>
        <div class="insight-block">
            <div class="ib-label">Female</div>
            <div class="ib-value" style="color:#be185d;">{{ number_format($female) }}</div>
            <div class="ib-sub">{{ $femalePct }}% of workforce</div>
        </div>
        <div class="insight-block">
            <div class="ib-label">Sex Gap</div>
            <div class="ib-value" style="color:{{ $gapPct > 20 ? '#dc2626' : '#16a34a' }};">{{ $gapPct }}%</div>
            <div class="ib-sub">{{ $gapPct > 20 ? 'Exceeds 20% threshold' : 'Within acceptable range' }}</div>
        </div>
        <div class="insight-block">
            <div class="ib-label">GAD Compliance (RA 9710)</div>
            <div class="ib-value" style="color:{{ $gadCompliant ? '#16a34a' : '#dc2626' }};">
                {{ $gadCompliant ? 'Compliant' : 'Non-Compliant' }}
            </div>
            <div class="ib-sub">Target: 40–60% female representation</div>
        </div>
        <div class="insight-block">
            <div class="ib-label">Legal Basis</div>
            <div class="ib-value" style="font-size:.85rem;">RA 9710 § 36(b)</div>
            <div class="ib-sub">Magna Carta of Women</div>
        </div>
    </div>

    @if(!$gadCompliant)
    <div class="gad-alert-red mb-3">
        <i class="bi bi-exclamation-octagon-fill" style="font-size:16px;flex-shrink:0;margin-top:2px;"></i>
        <div>
            <strong>GAD Compliance Alert:</strong> Female representation is {{ $femalePct }}%, which is
            outside the 40–60% range required under RA 9710 Section 36(b). The office must submit a
            GAD Plan and Budget to PCW addressing this imbalance.
        </div>
    </div>
    @else
    <div class="gad-alert-green mb-3">
        <i class="bi bi-check-circle-fill" style="font-size:16px;flex-shrink:0;margin-top:2px;"></i>
        <div>
            <strong>GAD Compliant:</strong> Female representation is {{ $femalePct }}%, within the 40–60% range
            per RA 9710 Section 36(b). Maintain this balance in future hiring decisions.
        </div>
    </div>
    @endif

    {{-- ── Module Tabs ── --}}
    <div style="border-bottom:1px solid #e5e7eb;margin-bottom:24px;display:flex;overflow-x:auto;gap:0;">
        @php
            $tabs = [
                ['m1','<i class="bi bi-people-fill"></i> Inventory'],
                ['m2','<i class="bi bi-briefcase-fill"></i> By Status'],
                ['m3','<i class="bi bi-building"></i> By Office'],
                ['m4','<i class="bi bi-bar-chart-steps"></i> Age Groups'],
                ['m5','<i class="bi bi-layers"></i> Salary Grade'],
                ['m6','<i class="bi bi-heart-pulse-fill"></i> PWD & Solo Parent'],
                ['m7','<i class="bi bi-person-plus-fill"></i> Recruitment'],
                ['m8','<i class="bi bi-star-fill"></i> SPMS'],
            ];
        @endphp
        @foreach($tabs as $i => [$id, $label])
            <button class="gad-tab {{ $i === 0 ? 'active' : '' }}" onclick="gadTab('{{ $id }}', this)">
                {!! $label !!}
            </button>
        @endforeach
    </div>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- MODULE 1: Employee Inventory                        --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    <div id="m1" class="gad-module active">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:14px;margin-bottom:20px;">
            <div class="stat-tile">
                <div class="val" style="color:#111827;">{{ number_format($total) }}</div>
                <div class="lbl">Total</div>
            </div>
            <div class="stat-tile">
                <div class="val" style="color:#1d4ed8;">{{ number_format($male) }}</div>
                <div class="lbl">Male</div>
            </div>
            <div class="stat-tile">
                <div class="val" style="color:#be185d;">{{ number_format($female) }}</div>
                <div class="lbl">Female</div>
            </div>
            <div class="stat-tile">
                <div class="val" style="color:{{ $femalePct >= 40 ? '#16a34a' : '#dc2626' }};">{{ $femalePct }}%</div>
                <div class="lbl">Female %</div>
            </div>
            <div class="stat-tile">
                <div class="val" style="color:{{ $gapPct <= 20 ? '#16a34a' : '#dc2626' }};">{{ $gapPct }}%</div>
                <div class="lbl">Sex Gap</div>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 2fr;gap:20px;">
            <div class="module-card">
                <div class="module-title">Sex Distribution</div>
                <div class="module-sub">Proportion of male to female employees</div>
                <div class="chart-wrap" id="chart-donut"></div>
            </div>
            <div class="module-card">
                <div class="module-title">By Employment Status & Sex</div>
                <div class="module-sub">Grouped bar — sex breakdown per status</div>
                <div class="chart-wrap" id="chart-status-sex"></div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- MODULE 2: By Employment Status                      --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    <div id="m2" class="gad-module">
        @if($gapPct > 20)
        <div class="gad-alert-yellow mb-3">
            <i class="bi bi-exclamation-triangle-fill"></i>
            Sex gap exceeds 20% — review staffing patterns by employment status for gender bias.
        </div>
        @endif
        <div class="module-card">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px;">
                <div>
                    <div class="module-title">Workforce by Employment Status</div>
                    <div class="module-sub">Sex-disaggregated count per employment category</div>
                </div>
                <button onclick="window.print()" class="print-btn"><i class="bi bi-printer"></i> Print</button>
            </div>
            <div class="chart-wrap" id="chart-m2"></div>
        </div>
        <div class="module-card">
            <table class="table gad-table table-bordered" style="font-size:13px;">
                <thead class="table-light"><tr><th>Status</th><th>Male</th><th>Female</th><th>Total</th><th>Female %</th><th>Gap Flag</th></tr></thead>
                <tbody>
                @foreach($statusLabels as $i => $s)
                @php $m=$statusMale[$i]??0; $f=$statusFemale[$i]??0; $t=$m+$f; $fp=$t>0?round($f/$t*100,1):0; @endphp
                <tr>
                    <td class="fw-semibold">{{ $s }}</td>
                    <td>{{ $m }}</td>
                    <td>{{ $f }}</td>
                    <td class="fw-bold">{{ $t }}</td>
                    <td>{{ $fp }}%</td>
                    <td>
                        @if($t>0 && ($fp<40||$fp>60))
                            <span class="badge-gap-bad">⚠ {{ abs($m-$f) > 0 ? ($fp<40?'Male-dominated':'Female-dominated') : '' }}</span>
                        @else
                            <span class="badge-gap-ok">✓ Balanced</span>
                        @endif
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- MODULE 3: By Office                                 --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    <div id="m3" class="gad-module">
        <div class="module-card">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px;">
                <div>
                    <div class="module-title">Workforce by Office / Department</div>
                    <div class="module-sub">Top 20 offices — horizontal sex-disaggregated bar</div>
                </div>
                <button onclick="window.print()" class="print-btn"><i class="bi bi-printer"></i> Print</button>
            </div>
            <div id="chart-m3" style="min-height:380px;"></div>
        </div>
        <div class="module-card">
            <div class="module-title">Office Drill-Down Table</div>
            <div class="module-sub">Flag: female representation outside 40–60% range</div>
            <div class="overflow-x-auto">
                <table class="table gad-table table-bordered">
                    <thead class="table-light"><tr><th>#</th><th>Office / Department</th><th>Male</th><th>Female</th><th>Total</th><th>Female %</th><th>GAD Status</th></tr></thead>
                    <tbody>
                    @foreach($officeTable as $i => $row)
                    <tr>
                        <td class="text-muted small">{{ $i+1 }}</td>
                        <td class="fw-semibold">{{ $row['office'] }}</td>
                        <td>{{ $row['male'] }}</td>
                        <td>{{ $row['female'] }}</td>
                        <td class="fw-bold">{{ $row['total'] }}</td>
                        <td>{{ $row['female_pct'] }}%</td>
                        <td>
                            @if($row['gad_flag'])
                                <span class="badge-gap-bad">⚠ Non-Compliant</span>
                            @else
                                <span class="badge-gap-ok">✓ Compliant</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- MODULE 4: Age Groups                               --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    <div id="m4" class="gad-module">
        <div class="module-card">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px;">
                <div>
                    <div class="module-title">Workforce by Age Group & Sex</div>
                    <div class="module-sub">Computed from date_of_birth — age brackets per CSC classification</div>
                </div>
                <button onclick="window.print()" class="print-btn"><i class="bi bi-printer"></i> Print</button>
            </div>
            <div id="chart-m4" class="chart-wrap"></div>
        </div>
        <div class="module-card">
            <table class="table gad-table table-bordered">
                <thead class="table-light"><tr><th>Age Group</th><th>Male</th><th>Female</th><th>Total</th></tr></thead>
                <tbody>
                @foreach($ageGroups as $i => $grp)
                <tr>
                    <td class="fw-semibold">{{ $grp }}</td>
                    <td>{{ $ageMale[$i] ?? 0 }}</td>
                    <td>{{ $ageFemale[$i] ?? 0 }}</td>
                    <td class="fw-bold">{{ ($ageMale[$i]??0) + ($ageFemale[$i]??0) }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- MODULE 5: Salary Grade Bands                       --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    <div id="m5" class="gad-module">
        @if($sgAlert)
        <div class="gad-alert-red mb-3">
            <i class="bi bi-exclamation-octagon-fill"></i>
            <div><strong>Vertical Segregation Alert:</strong> SG 19+ positions are more than 70% male.
            This indicates potential occupational segregation. Address under RA 9710 Section 16.</div>
        </div>
        @endif
        <div class="module-card">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px;">
                <div>
                    <div class="module-title">Workforce by Salary Grade Band & Sex</div>
                    <div class="module-sub">SG 19+ male-dominance triggers vertical segregation alert (RA 9710 § 16)</div>
                </div>
                <button onclick="window.print()" class="print-btn"><i class="bi bi-printer"></i> Print</button>
            </div>
            <div id="chart-m5" class="chart-wrap"></div>
        </div>
        <div class="module-card">
            <table class="table gad-table table-bordered">
                <thead class="table-light"><tr><th>SG Band</th><th>Male</th><th>Female</th><th>Total</th><th>Male %</th><th>Flag</th></tr></thead>
                <tbody>
                @foreach($sgBands as $i => $band)
                @php $m=$sgMale[$i]??0; $f=$sgFemale[$i]??0; $t=$m+$f; $mp=$t>0?round($m/$t*100,1):0; @endphp
                <tr style="{{ $i>=3&&$mp>70?'background:#fef2f2;':'' }}">
                    <td class="fw-semibold">{{ $band }}</td>
                    <td>{{ $m }}</td>
                    <td>{{ $f }}</td>
                    <td class="fw-bold">{{ $t }}</td>
                    <td>{{ $mp }}%</td>
                    <td>
                        @if($i>=3 && $mp>70)
                            <span class="badge-gap-bad">⚠ V-Segregation</span>
                        @elseif($t>0 && $mp>60)
                            <span class="badge-gap-bad">⚠ Male-heavy</span>
                        @else
                            <span class="badge-gap-ok">✓ OK</span>
                        @endif
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- MODULE 6: PWD & Solo Parent                        --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    <div id="m6" class="gad-module">
        @if($pwdQuotaBreach)
        <div class="gad-alert-red mb-3">
            <i class="bi bi-exclamation-octagon-fill"></i>
            <div><strong>PWD Quota Breach:</strong> PWD employees represent only {{ $pwdPct }}% of the workforce.
            RA 7277 (Magna Carta for PWD) mandates at least <strong>1%</strong> quota in government offices.</div>
        </div>
        @endif
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
            <div class="module-card">
                <div class="module-title">PWD Employees</div>
                <div class="module-sub">Persons with Disability (RA 7277)</div>
                <div style="display:flex;gap:16px;margin-bottom:14px;">
                    <div class="stat-tile" style="flex:1;background:{{ $pwdQuotaBreach?'#fef2f2':'#f0fdf4' }};">
                        <div class="val" style="color:{{ $pwdQuotaBreach?'#dc2626':'#16a34a' }};">{{ $pwdPct }}%</div>
                        <div class="lbl">of Workforce</div>
                    </div>
                    <div class="stat-tile" style="flex:1;">
                        <div class="val">{{ $pwdTotal }}</div>
                        <div class="lbl">Total PWD</div>
                    </div>
                </div>
                <div id="chart-pwd" style="min-height:160px;"></div>
            </div>
            <div class="module-card">
                <div class="module-title">Solo Parent Employees</div>
                <div class="module-sub">Solo Parent Leave Act (RA 8972)</div>
                <div style="display:flex;gap:16px;margin-bottom:14px;">
                    <div class="stat-tile" style="flex:1;">
                        <div class="val">{{ $spPct }}%</div>
                        <div class="lbl">of Workforce</div>
                    </div>
                    <div class="stat-tile" style="flex:1;">
                        <div class="val">{{ $spTotal }}</div>
                        <div class="lbl">Total Solo Parents</div>
                    </div>
                </div>
                <div id="chart-sp" style="min-height:160px;"></div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- MODULE 7: Recruitment                              --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    <div id="m7" class="gad-module">
        <div class="module-card">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px;">
                <div>
                    <div class="module-title">Applicant Volume — Last 12 Months</div>
                    <div class="module-sub">Sex-disaggregated monthly recruitment data</div>
                </div>
                <div style="display:flex;gap:8px;">
                    <button onclick="window.print()" class="print-btn"><i class="bi bi-printer"></i> Print</button>
                    <a href="{{ route('recruitment.index') }}" class="csv-btn"><i class="bi bi-person-plus-fill"></i> Go to Recruitment</a>
                </div>
            </div>
            <div id="chart-m7" class="chart-wrap"></div>
        </div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px;">
            <div class="stat-tile text-center">
                <div class="val">{{ number_format($applied) }}</div>
                <div class="lbl">Total Applied</div>
            </div>
            <div class="stat-tile text-center">
                <div class="val" style="color:#16a34a;">{{ number_format($qualified) }}</div>
                <div class="lbl">Qualified</div>
            </div>
            <div class="stat-tile text-center">
                <div class="val" style="color:#7c3aed;">{{ number_format($recommended) }}</div>
                <div class="lbl">Fully Recommended</div>
            </div>
        </div>
        <div class="module-card">
            <div class="module-title">Recruitment Funnel</div>
            <div class="module-sub">Applied → Pre-evaluated Qualified → QS Met + Docs Complete</div>
            <div id="chart-funnel" style="min-height:240px;"></div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- MODULE 8: SPMS                                     --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    <div id="m8" class="gad-module">
        <div class="module-card">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px;">
                <div>
                    <div class="module-title">SPMS Performance Rating Distribution</div>
                    <div class="module-sub">{{ $spmsTotal }} employees with SPMS ratings on record</div>
                </div>
                <a href="{{ route('gad.outstanding-csv') }}" class="csv-btn" target="_blank">
                    <i class="bi bi-file-earmark-spreadsheet"></i> Export Outstanding CSV
                </a>
            </div>
            <div id="chart-spms" class="chart-wrap"></div>
        </div>
        <div class="module-card">
            <div class="module-title">Outstanding Performers (≥ 4.90)</div>
            <div class="module-sub">{{ $outstanding->count() }} employee(s) rated Outstanding</div>
            @if($outstanding->count())
            <div class="overflow-x-auto">
                <table class="table gad-table table-bordered">
                    <thead class="table-light"><tr><th>Name</th><th>Sex</th><th>Office</th><th>Position</th><th>SG</th><th>Rating</th></tr></thead>
                    <tbody>
                    @foreach($outstanding as $o)
                    <tr>
                        <td class="fw-semibold">{{ $o->last_name }}, {{ $o->first_name }} {{ $o->middle_name }}</td>
                        <td><span class="{{ $o->sex=='M'?'text-blue-600':'text-red-600' }} fw-semibold">{{ $o->sex == 'M' ? 'Male' : ($o->sex == 'F' ? 'Female' : $o->sex) }}</span></td>
                        <td class="small">{{ $o->office_department }}</td>
                        <td class="small">{{ $o->position_title }}</td>
                        <td>{{ $o->salary_grade }}</td>
                        <td><span style="background:#d1fae5;color:#065f46;padding:2px 8px;border-radius:6px;font-weight:800;font-size:12px;">{{ number_format($o->spms_rating, 2) }}</span></td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-4 text-muted" style="font-size:13px;">No employees with SPMS rating ≥ 4.90 on record.</div>
            @endif
        </div>
    </div>

</div>{{-- end page container --}}

<script>
// ── Tab switching ──────────────────────────────────────────────────────────
function gadTab(id, btn) {
    document.querySelectorAll('.gad-module').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.gad-tab').forEach(el => el.classList.remove('active'));
    document.getElementById(id).classList.add('active');
    btn.classList.add('active');
}

// ── Colour palette ─────────────────────────────────────────────────────────
const MALE_COLOR   = '#2563eb';
const FEMALE_COLOR = '#ec4899';
const CHART_OPTS   = {
    chart:   { toolbar: { show: false }, fontFamily: 'inherit' },
    legend:  { position: 'bottom', fontSize: '12px' },
    tooltip: { shared: true, intersect: false },
    grid:    { borderColor: '#f0f0f0' },
    dataLabels: { enabled: false },
};

// ── Module 1: Donut ────────────────────────────────────────────────────────
new ApexCharts(document.getElementById('chart-donut'), {
    ...CHART_OPTS,
    chart:  { ...CHART_OPTS.chart, type: 'donut', height: 260 },
    series: [{{ $male }}, {{ $female }}@if($other>0), {{ $other }}@endif],
    labels: ['Male', 'Female'@if($other>0), 'Other/Unknown'@endif],
    colors: [MALE_COLOR, FEMALE_COLOR, '#9ca3af'],
    plotOptions: { pie: { donut: { size: '65%', labels: {
        show: true,
        total: { show: true, label: 'Total', formatter: () => '{{ $total }}' },
    }}}},
}).render();

// ── Module 1 + 2: Status grouped bar ──────────────────────────────────────
var statusChart = new ApexCharts(document.getElementById('chart-status-sex'), {
    ...CHART_OPTS,
    chart:  { ...CHART_OPTS.chart, type: 'bar', height: 280 },
    series: [
        { name: 'Male',   data: {!! json_encode($statusMale->values()) !!} },
        { name: 'Female', data: {!! json_encode($statusFemale->values()) !!} },
    ],
    xaxis:  { categories: {!! json_encode($statusLabels->values()) !!} },
    colors: [MALE_COLOR, FEMALE_COLOR],
    plotOptions: { bar: { columnWidth: '55%', borderRadius: 4 } },
});
statusChart.render();

new ApexCharts(document.getElementById('chart-m2'), {
    ...CHART_OPTS,
    chart:  { ...CHART_OPTS.chart, type: 'bar', height: 300 },
    series: [
        { name: 'Male',   data: {!! json_encode($statusMale->values()) !!} },
        { name: 'Female', data: {!! json_encode($statusFemale->values()) !!} },
    ],
    xaxis:  { categories: {!! json_encode($statusLabels->values()) !!} },
    colors: [MALE_COLOR, FEMALE_COLOR],
    plotOptions: { bar: { columnWidth: '55%', borderRadius: 4 } },
}).render();

// ── Module 3: Office horizontal bar ───────────────────────────────────────
new ApexCharts(document.getElementById('chart-m3'), {
    ...CHART_OPTS,
    chart:  { ...CHART_OPTS.chart, type: 'bar', height: Math.max(350, {{ $officeLabels->count() }} * 28 + 60) },
    series: [
        { name: 'Male',   data: {!! json_encode($officeMale->values()) !!} },
        { name: 'Female', data: {!! json_encode($officeFemale->values()) !!} },
    ],
    xaxis:  { categories: {!! json_encode($officeLabels->values()) !!} },
    colors: [MALE_COLOR, FEMALE_COLOR],
    plotOptions: { bar: { horizontal: true, barHeight: '60%', borderRadius: 3 } },
}).render();

// ── Module 4: Age groups ──────────────────────────────────────────────────
new ApexCharts(document.getElementById('chart-m4'), {
    ...CHART_OPTS,
    chart:  { ...CHART_OPTS.chart, type: 'bar', height: 280 },
    series: [
        { name: 'Male',   data: {!! json_encode($ageMale->values()) !!} },
        { name: 'Female', data: {!! json_encode($ageFemale->values()) !!} },
    ],
    xaxis:  { categories: {!! json_encode($ageGroups) !!} },
    colors: [MALE_COLOR, FEMALE_COLOR],
    plotOptions: { bar: { columnWidth: '55%', borderRadius: 4 } },
}).render();

// ── Module 5: SG bands ────────────────────────────────────────────────────
new ApexCharts(document.getElementById('chart-m5'), {
    ...CHART_OPTS,
    chart:  { ...CHART_OPTS.chart, type: 'bar', height: 280 },
    series: [
        { name: 'Male',   data: {!! json_encode($sgMale->values()) !!} },
        { name: 'Female', data: {!! json_encode($sgFemale->values()) !!} },
    ],
    xaxis:  { categories: {!! json_encode($sgBands) !!} },
    colors: [MALE_COLOR, FEMALE_COLOR],
    plotOptions: { bar: { columnWidth: '55%', borderRadius: 4 } },
}).render();

// ── Module 6: PWD & SP donut ──────────────────────────────────────────────
new ApexCharts(document.getElementById('chart-pwd'), {
    ...CHART_OPTS,
    chart:  { ...CHART_OPTS.chart, type: 'donut', height: 160 },
    series: [{{ $pwdMale }}, {{ $pwdFemale }}],
    labels: ['Male', 'Female'],
    colors: [MALE_COLOR, FEMALE_COLOR],
    plotOptions: { pie: { donut: { size: '60%' }}},
    legend: { position: 'right', fontSize: '11px' },
}).render();

new ApexCharts(document.getElementById('chart-sp'), {
    ...CHART_OPTS,
    chart:  { ...CHART_OPTS.chart, type: 'donut', height: 160 },
    series: [{{ $spMale }}, {{ $spFemale }}],
    labels: ['Male', 'Female'],
    colors: [MALE_COLOR, FEMALE_COLOR],
    plotOptions: { pie: { donut: { size: '60%' }}},
    legend: { position: 'right', fontSize: '11px' },
}).render();

// ── Module 7: Recruitment line ────────────────────────────────────────────
new ApexCharts(document.getElementById('chart-m7'), {
    ...CHART_OPTS,
    chart:  { ...CHART_OPTS.chart, type: 'bar', height: 280 },
    series: [
        { name: 'Male Applicants',   data: {!! json_encode($recruitMale->values()) !!} },
        { name: 'Female Applicants', data: {!! json_encode($recruitFemale->values()) !!} },
    ],
    xaxis:  { categories: {!! json_encode($recruitLabels->values()) !!} },
    colors: [MALE_COLOR, FEMALE_COLOR],
    plotOptions: { bar: { columnWidth: '60%', borderRadius: 3 } },
}).render();

// Funnel
new ApexCharts(document.getElementById('chart-funnel'), {
    ...CHART_OPTS,
    chart:  { ...CHART_OPTS.chart, type: 'bar', height: 240 },
    series: [{ name: 'Count', data: [{{ $applied }}, {{ $qualified }}, {{ $recommended }}] }],
    xaxis:  { categories: ['Applied', 'Qualified', 'Fully Recommended'] },
    colors: ['#6366f1'],
    plotOptions: { bar: { horizontal: true, barHeight: '50%', borderRadius: 4, distributed: true } },
    colors: ['#3b82f6','#10b981','#7c3aed'],
    legend: { show: false },
}).render();

// ── Module 8: SPMS pie ────────────────────────────────────────────────────
new ApexCharts(document.getElementById('chart-spms'), {
    ...CHART_OPTS,
    chart:  { ...CHART_OPTS.chart, type: 'bar', height: 260 },
    series: [{ name: 'Employees', data: {!! json_encode($spmsCounts->values()) !!} }],
    xaxis:  { categories: {!! json_encode(array_keys($spmsBands)) !!} },
    colors: ['#10b981','#3b82f6','#f59e0b','#ef4444'],
    plotOptions: { bar: { columnWidth: '55%', borderRadius: 4, distributed: true } },
    legend: { show: false },
}).render();
</script>

</x-dashboard-app>
