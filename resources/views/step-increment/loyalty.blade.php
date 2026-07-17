<x-dashboard-app>
<style>
.li-hero {
    background: linear-gradient(135deg, #78350f 0%, #92400e 45%, #b45309 100%);
    border-radius: 14px; padding: 28px 32px;
    position: relative; overflow: hidden; margin-bottom: 20px;
}
.li-hero::before {
    content:''; position:absolute; inset:0;
    background-image: radial-gradient(circle, rgba(255,255,255,.07) 1px, transparent 1px);
    background-size: 22px 22px;
}
.li-hero-inner { position:relative;z-index:1;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap; }
.li-hero h1 { color:#fff; font-size:24px; font-weight:800; margin:0; line-height:1.2; }
.li-hero p  { color:rgba(255,255,255,.65); font-size:13px; margin:5px 0 0; }
.li-hero-btn {
    display:inline-flex;align-items:center;gap:6px;
    background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.3);
    color:#fff;padding:9px 18px;border-radius:10px;
    font-size:13px;font-weight:600;text-decoration:none;
    transition:background .2s;
}
.li-hero-btn:hover { background:rgba(255,255,255,.28);color:#fff; }

/* Table */
.si-table-wrap {
    background: var(--color-surface, #fff);border: 1px solid var(--color-border, #e5e7eb);border-radius:14px;
    overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.04);
}
.si-table { width:100%;border-collapse:separate;border-spacing:0; }
.si-table thead tr { background:linear-gradient(135deg,#78350f 0%,#b45309 100%); }
.si-table th {
    text-align:left;font-size:10px;font-weight:700;
    text-transform:uppercase;letter-spacing:.8px;
    color:rgba(255,255,255,.72);padding:12px 16px;white-space:nowrap;
    border-bottom:2px solid rgba(255,255,255,.1);
}
.si-table th.tc { text-align:center; }
.si-table tbody tr { transition:background .12s; }
.si-table tbody tr:nth-child(even) td { background:#fefce8; }
.si-table tbody tr:hover td { background:#fef3c7 !important; }
.si-table td {
    padding:11px 16px;vertical-align:middle;
    border-bottom:1px solid #f1f5f9;font-size:13px;color:#374151;
}
.si-table td.tc { text-align:center; }
.si-table tbody tr:last-child td { border-bottom:0; }
.si-emp-name { font-weight:700;color:#0f172a;font-size:13px;letter-spacing:-.1px; }
.si-emp-unit { font-size:11px;color:#94a3b8;margin-top:2px; }
.si-pos-title { font-weight:600;color:#78350f;font-size:13px; }
.si-pos-item  { font-family:ui-monospace,monospace;font-size:11px;color:#94a3b8;margin-top:2px; }
.si-sg-badge {
    display:inline-flex;align-items:center;justify-content:center;
    background:linear-gradient(135deg,#78350f,#b45309);
    color:#fff;font-size:11px;font-weight:800;
    width:32px;height:32px;border-radius:8px;
    box-shadow:0 2px 6px rgba(120,53,15,.3);
}
.si-empty { text-align:center;padding:60px 20px; }
.si-empty-icon {
    width:64px;height:64px;margin:0 auto 16px;
    background:linear-gradient(135deg,#fef3c7,#fde68a);
    border-radius:50%;display:flex;align-items:center;justify-content:center;
    font-size:28px;color:#b45309;
    box-shadow:0 4px 14px rgba(180,83,9,.18);
}
</style>

{{-- HERO --}}
<div class="li-hero">
    <div class="li-hero-inner">
        <div>
            <h1><i class="bi bi-award-fill me-2"></i>Loyalty Incentive</h1>
            <p>Employees eligible for Loyalty Incentive — per CSC MC No. 6, s. 2002</p>
        </div>
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <a href="{{ route('step-increment.loyalty-incentive-settings.index') }}" class="li-hero-btn"
               style="background:rgba(245,158,11,.35);border-color:rgba(245,158,11,.6);" title="Manage background images">
                <i class="bi bi-image-fill"></i> LI Backgrounds
            </a>
            <a href="{{ route('step-increment.hub') }}" class="li-hero-btn">
                <i class="bi bi-arrow-left"></i> Back to Hub
            </a>
        </div>
    </div>
</div>

{{-- Flash Messages --}}
@if(session('success'))
<div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:12px 18px;border-radius:10px;margin-bottom:16px;font-size:13px;font-weight:600;">
    <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px 18px;border-radius:10px;margin-bottom:16px;font-size:13px;font-weight:600;">
    <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ session('error') }}
</div>
@endif

{{-- Active Background Banner --}}
@php
    $activeBgPath = \App\Http\Controllers\LoyaltyIncentiveSettingController::getActiveBackgroundPath();
    $activeBgMeta = null;
    if (\Illuminate\Support\Facades\Storage::disk('public')->exists('loyalty-backgrounds/meta.json')) {
        $liMeta = json_decode(\Illuminate\Support\Facades\Storage::disk('public')->get('loyalty-backgrounds/meta.json'), true);
        $activeBgMeta = collect($liMeta['backgrounds'] ?? [])->firstWhere('id', $liMeta['active'] ?? null);
    }
@endphp

<div class="si-table-wrap">
    <div style="padding:14px 16px;background: var(--color-surface, #fff);border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
        <div style="font-size:13px;color: var(--color-text-secondary, #475569);">
            <i class="bi bi-award-fill" style="color:#b45309;"></i>
            Employees eligible for <strong>Loyalty Incentive</strong> — every <strong>10 years</strong> first, then every additional <strong>5 years</strong> thereafter.
        </div>
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            @if($activeBgMeta)
                <span style="display:inline-flex;align-items:center;gap:6px;background:#fef3c7;border:1px solid #fde68a;color:#92400e;padding:5px 12px;border-radius:8px;font-size:11px;font-weight:700;">
                    <i class="bi bi-image-fill"></i>
                    BG: {{ Str::limit($activeBgMeta['name'], 20) }}
                </span>
            @else
                <span style="display:inline-flex;align-items:center;gap:6px;background:#f1f5f9;border:1px solid #e2e8f0;color: var(--color-text-muted, #64748b);padding:5px 12px;border-radius:8px;font-size:11px;font-weight:600;">
                    <i class="bi bi-image-alt"></i> No background set
                </span>
            @endif
            <a href="{{ route('step-increment.loyalty-incentive-settings.index') }}"
               style="display:inline-flex;align-items:center;gap:5px;background:linear-gradient(135deg,#b45309,#d97706);color:#fff;padding:7px 14px;border-radius:8px;font-size:12px;font-weight:700;text-decoration:none;box-shadow:0 2px 8px rgba(180,83,9,.3);">
                <i class="bi bi-gear-fill"></i> Background Settings
            </a>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="si-table">
            <thead>
                <tr>
                    <th style="min-width:180px;">Employee</th>
                    <th style="min-width:160px;">Position</th>
                    <th class="tc" style="width:56px;">SG</th>
                    <th class="tc" style="width:130px;">Govt. Service Start</th>
                    <th class="tc" style="width:70px;">Years</th>
                    <th class="tc" style="width:130px;">Milestone</th>
                    <th class="tc" style="width:120px;">Amount</th>
                    <th class="tc" style="width:180px;">Certificate</th>
                </tr>
            </thead>
            <tbody>
                @forelse($loyalty as $record)
                @php
                    $startDt  = $record->date_original_appointment;
                    $yearsNum = $startDt ? (int) $startDt->diffInYears(now()) : 0;
                    if ($yearsNum >= 10 && $yearsNum === 10) {
                        $milestoneYrs = 10;
                        $loyaltyAmt   = 10000;
                    } elseif ($yearsNum > 10) {
                        $afterTen     = $yearsNum - 10;
                        $blocks       = (int) floor($afterTen / 5);
                        $milestoneYrs = 10 + ($blocks * 5);
                        $loyaltyAmt   = 5000;
                    } else {
                        $milestoneYrs = $yearsNum;
                        $loyaltyAmt   = 0;
                    }
                @endphp
                <tr>
                    <td>
                        <div class="si-emp-name">{{ $record->full_name }}</div>
                        <div class="si-emp-unit"><i class="bi bi-building" style="color:#d97706;"></i> {{ $record->office_department }}</div>
                    </td>
                    <td>
                        <div class="si-pos-title">{{ $record->position_title }}</div>
                        <div class="si-pos-item">{{ $record->item_no_new }}</div>
                    </td>
                    <td class="tc"><span class="si-sg-badge">{{ $record->salary_grade }}</span></td>
                    <td class="tc">
                        <span style="font-size:12px;color:#374151;white-space:nowrap;">
                            {{ $startDt ? $startDt->format('M d, Y') : '—' }}
                        </span>
                    </td>
                    <td class="tc">
                        <span style="display:inline-flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#b45309,#d97706);color:#fff;font-size:12px;font-weight:800;width:34px;height:34px;border-radius:8px;box-shadow:0 2px 6px rgba(180,83,9,.3);">
                            {{ $yearsNum }}
                        </span>
                    </td>
                    <td class="tc">
                        @if($milestoneYrs === 10)
                            <span style="display:inline-flex;align-items:center;gap:4px;white-space:nowrap;font-size:11px;font-weight:800;background:#f59e0b;color:#fff;padding:4px 12px;border-radius:99px;">
                                <i class="bi bi-star-fill"></i>10 Years
                            </span>
                        @else
                            <span style="display:inline-flex;align-items:center;gap:4px;white-space:nowrap;font-size:11px;font-weight:800;background:#b45309;color:#fff;padding:4px 12px;border-radius:99px;">
                                <i class="bi bi-award-fill"></i>{{ $milestoneYrs }} Years
                            </span>
                        @endif
                    </td>
                    <td class="tc">
                        <span style="display:inline-block;white-space:nowrap;font-family:ui-monospace,monospace;font-size:13px;font-weight:800;color:{{ $loyaltyAmt >= 10000 ? '#d97706' : '#b45309' }};">
                            ₱{{ number_format($loyaltyAmt, 2) }}
                        </span>
                        @if($loyaltyAmt >= 10000)
                        <div style="font-size:10px;color:#94a3b8;margin-top:2px;white-space:nowrap;">First 10 yrs</div>
                        @else
                        <div style="font-size:10px;color:#94a3b8;margin-top:2px;white-space:nowrap;">+5 yr block</div>
                        @endif
                    </td>
                    <td class="tc" style="white-space:nowrap;">
                        <a href="{{ route('step-increment.pdf.loyalty-incentive', $record) }}" target="_blank"
                           title="Generate Loyalty Incentive Certificate"
                           style="display:inline-flex;align-items:center;gap:5px;white-space:nowrap;background:linear-gradient(135deg,#b45309,#d97706,#f59e0b);color:#fff;padding:7px 14px;border-radius:8px;font-size:12px;font-weight:700;text-decoration:none;box-shadow:0 2px 8px rgba(180,83,9,.3);">
                            <i class="bi bi-printer-fill"></i> Print
                        </a>
                        <button type="button"
                            onclick="openDismissModal({{ $record->id }}, '{{ addslashes($record->full_name) }}')"
                            title="Remove from Loyalty Incentive list"
                            style="display:inline-flex;align-items:center;gap:5px;white-space:nowrap;background:linear-gradient(135deg,#dc2626,#b91c1c);color:#fff;padding:7px 12px;border-radius:8px;font-size:12px;font-weight:700;border:none;cursor:pointer;box-shadow:0 2px 8px rgba(185,28,28,.25);margin-left:5px;">
                            <i class="bi bi-trash3-fill"></i> Remove
                        </button>
                        <form id="dismiss-form-{{ $record->id }}" method="POST"
                              action="{{ route('step-increment.dismiss-loyalty', $record) }}" style="display:none;">
                            @csrf
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="si-empty">
                            <div class="si-empty-icon"><i class="bi bi-award"></i></div>
                            <div style="font-size:16px;font-weight:700;color:#92400e;margin-bottom:6px;">No employees due</div>
                            <div style="font-size:13px;color:#94a3b8;">No employees have reached a loyalty incentive milestone (10, 15, 20... years).</div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($loyalty->hasPages())
    <div style="padding:14px 20px;border-top:1px solid #f1f5f9;">
        {{ $loyalty->links() }}
    </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function openDismissModal(recordId, employeeName) {
    Swal.fire({
        title: 'Remove from Loyalty Incentive List?',
        html: `
            <div style="text-align:center; margin-top:10px;">
                <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:12px; padding:16px 20px; margin-bottom:16px;">
                    <div style="font-size:15px; font-weight:800; color: var(--color-text-primary, #1e293b); margin-bottom:4px;">${employeeName}</div>
                    <div style="font-size:13px; color: var(--color-text-muted, #64748b);">will be removed from the Loyalty Incentive list.</div>
                </div>
                <div style="background:#fff7ed; border:1px solid #fed7aa; border-radius:10px; padding:12px; font-size:13px; color:#9a3412; display:flex; gap:10px; align-items:flex-start; text-align:left;">
                    <i class="bi bi-info-circle-fill" style="flex-shrink:0; margin-top:2px;"></i>
                    <span>This only removes the employee from this view. Their record is <strong>not deleted</strong>. You can restore them later if needed.</span>
                </div>
            </div>`,
        icon: 'warning',
        iconColor: '#dc2626',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="bi bi-trash3-fill me-1"></i> Yes, Remove',
        cancelButtonText: 'Cancel',
        width: 480
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('dismiss-form-' + recordId).submit();
        }
    });
}
</script>
</x-dashboard-app>
