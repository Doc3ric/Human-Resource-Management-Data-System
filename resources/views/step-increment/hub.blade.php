<x-dashboard-app>
    <style>
        /* ── Hub Hero ──────────────────────────────────────────────────── */
        .hub-hero {
            background: linear-gradient(135deg, #1e3a5f 0%, #1a5276 50%, #154360 100%);
            border-radius: 16px;
            padding: 32px 36px;
            position: relative;
            overflow: hidden;
            margin-bottom: 28px;
        }

        .hub-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle, rgba(255, 255, 255, .06) 1px, transparent 1px);
            background-size: 24px 24px;
        }

        .hub-hero::after {
            content: '';
            position: absolute;
            right: -60px;
            top: -60px;
            width: 280px;
            height: 280px;
            background: rgba(255, 255, 255, .04);
            border-radius: 50%;
        }

        .hub-hero-inner {
            position: relative;
            z-index: 1;
        }

        .hub-hero h1 {
            color: #fff;
            font-size: 28px;
            font-weight: 900;
            margin: 0 0 6px;
            letter-spacing: -.5px;
        }

        .hub-hero p {
            color: rgba(255, 255, 255, .65);
            font-size: 14px;
            margin: 0;
        }

        .hub-breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: rgba(255, 255, 255, .55);
            margin-bottom: 12px;
        }

        .hub-breadcrumb i {
            font-size: 11px;
        }

        /* ── Quick Stat Bar ─────────────────────────────────────────────── */
        .hub-stat-bar {
            display: flex;
            gap: 12px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .hub-stat-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, .13);
            border: 1px solid rgba(255, 255, 255, .2);
            border-radius: 10px;
            padding: 8px 16px;
            color: #fff;
            font-size: 12px;
            font-weight: 600;
        }

        .hub-stat-chip strong {
            font-size: 18px;
            font-weight: 900;
        }

        /* ── Section Title ──────────────────────────────────────────────── */
        .hub-section-title {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .hub-section-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e5e7eb;
        }

        /* ── Main Option Cards ──────────────────────────────────────────── */
        .hub-cards-main {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin-bottom: 28px;
        }

        .hub-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            overflow: hidden;
            text-decoration: none;
            color: inherit;
            transition: transform .2s, box-shadow .2s;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .04);
            display: flex;
            flex-direction: column;
        }

        .hub-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, .1);
            color: inherit;
            text-decoration: none;
        }

        .hub-card-top {
            padding: 24px 24px 20px;
            display: flex;
            flex-direction: column;
            gap: 14px;
            flex: 1;
        }

        .hub-card-icon {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .hub-card-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 4px;
        }

        .hub-card-title {
            font-size: 20px;
            font-weight: 900;
            color: #0f172a;
            margin: 0 0 6px;
        }

        .hub-card-desc {
            font-size: 12.5px;
            color: #64748b;
            line-height: 1.6;
            margin: 0;
        }

        .hub-card-stats {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 6px;
        }

        .hub-card-stat-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 99px;
            font-size: 11px;
            font-weight: 700;
        }

        .hub-card-footer {
            border-top: 1px solid #f1f5f9;
            padding: 14px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12px;
            font-weight: 700;
        }

        .hub-card-footer-label {
            color: #64748b;
        }

        .hub-card-footer i {
            font-size: 16px;
            transition: transform .2s;
        }

        .hub-card:hover .hub-card-footer i {
            transform: translateX(4px);
        }

        /* ── Loyalty Section Cards ──────────────────────────────────────── */
        .hub-cards-loyalty {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        .hub-loyalty-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 20px 22px;
            text-decoration: none;
            color: inherit;
            display: flex;
            align-items: center;
            gap: 16px;
            transition: transform .2s, box-shadow .2s;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
        }

        .hub-loyalty-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, .08);
            color: inherit;
            text-decoration: none;
        }

        .hub-loyalty-card-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }

        .hub-loyalty-card-title {
            font-size: 15px;
            font-weight: 800;
            color: #1f2937;
            margin: 0 0 3px;
        }

        .hub-loyalty-card-desc {
            font-size: 11.5px;
            color: #6b7280;
            margin: 0;
            line-height: 1.5;
        }

        .hub-loyalty-arrow {
            margin-left: auto;
            color: #d1d5db;
            font-size: 18px;
            transition: transform .2s;
            flex-shrink: 0;
        }

        .hub-loyalty-card:hover .hub-loyalty-arrow {
            transform: translateX(4px);
            color: #94a3b8;
        }
    </style>

    {{-- ── HERO ── --}}
    <div class="hub-hero">
        <div class="hub-hero-inner">
            <div class="hub-breadcrumb">
                <i class="bi bi-house-door-fill"></i> Dashboard
                <i class="bi bi-chevron-right"></i> Plantilla of Personnel
            </div>
            <h1><i class="bi bi-building me-2"></i>Plantilla of Personnel</h1>
            <p>Manage annual salary plantillas, step increments, longevity pay, loyalty incentives, and Magna Carta
                NOSA.</p>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div
            style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:12px 18px;border-radius:10px;margin-bottom:18px;font-size:13px;font-weight:600;">
            <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Statistics with compliance analysis --}}
    <div style="display:grid;grid-template-columns:repeat(6,1fr);gap:10px;margin-bottom:20px;">
        <x-stat-card icon="bi-people-fill" color="blue" label="Total Positions" :value="$totalPositions"
            compliance="DBM Plantilla"
            analysis="Total authorized positions in the approved plantilla. Any increase requires DBM authority per EO 366 and RA 10149 (GOCC Governance Act for GOCCs)." />

        <x-stat-card icon="bi-person-fill-check" color="green" label="Filled Positions" :value="$filledCount"
            :pct="$totalPositions > 0 ? round($filledCount/$totalPositions*100,0) : 0"
            sub="of authorized positions"
            compliance="DBM / CSC"
            analysis="Filled plantilla items with incumbents. Salary and benefits are charged to the office's MOOE/PS budget per GAA." />

        <x-stat-card icon="bi-person-fill-slash" color="slate" label="Vacant Positions" :value="$vacantCount"
            compliance="RA 7041"
            analysis="Vacant plantilla positions must be published per RA 7041 before filling. Prolonged vacancies must be reported to DBM within 30 days." />

        <x-stat-card icon="bi-alarm" color="orange" label="Due for Step Increment" :value="$dueCount"
            :alert="$dueCount > 0"
            compliance="CSC / SSL"
            analysis="{{ $dueCount > 0 ? $dueCount.' employee(s) due for step increment this period. Process NOSI/NOLP immediately to avoid delayed salary adjustments per CSC Step Increment Rules.' : 'No step increments due this period.' }}" />

        <x-stat-card icon="bi-award-fill" color="yellow" label="Loyalty Incentive" :value="$loyaltyCount"
            compliance="EO 77 / CSC"
            analysis="Employees eligible for Loyalty Cash Award (₱5,000 to ₱20,000) based on years of service per EO 77 and DBM BC 2004-1." />

        <x-stat-card icon="bi-heart-pulse-fill" color="red" label="NOSA Due (Magna Carta)" :value="$magnaCartaCount"
            :alert="$magnaCartaCount > 0"
            compliance="RA 7305"
            analysis="{{ $magnaCartaCount > 0 ? $magnaCartaCount.' health worker(s) due for NOSA under RA 7305 (Magna Carta for Public Health Workers). SG+1 entitlement must be processed.' : 'No Magna Carta NOSA due at this time.' }}" />
    </div>

    {{-- ── MAIN 3 CARDS ── --}}
    <div class="hub-section-title"><i class="bi bi-grid-3x3-gap-fill"></i> Choose a Module</div>
    <div class="hub-cards-main">

        {{-- ANNUAL --}}
        <div class="hub-card">
            <div class="hub-card-top">
                <div class="hub-card-icon" style="background:linear-gradient(135deg,#dbeafe,#bfdbfe);">
                    <i class="bi bi-file-earmark-spreadsheet-fill" style="color:#1d4ed8;"></i>
                </div>
                <div>
                    <div class="hub-card-label">Annual Report</div>
                    <div class="hub-card-title">Annual</div>
                    <p class="hub-card-desc">
                        View and export the formal <strong>Plantilla of Personnel</strong> — office-by-office salary
                        plantilla in the standard government format.
                    </p>
                    <div class="hub-card-stats">
                        <span class="hub-card-stat-badge" style="background:#dbeafe;color:#1e40af;">
                            <i class="bi bi-people-fill"></i> {{ number_format($filledCount) }} Personnel
                        </span>
                        <span class="hub-card-stat-badge" style="background:#f0fdf4;color:#166534;">
                            <i class="bi bi-file-earmark-excel"></i> Excel &amp; PDF
                        </span>
                    </div>
                </div>
            </div>
            <div class="hub-card-footer" style="padding:0; display:flex;">
                <a href="{{ route('step-increment.office-report', ['type' => 'permanent']) }}" style="flex:1; padding: 14px 10px; text-align:center; color:#1d4ed8; text-decoration:none; border-right: 1px solid #f1f5f9; transition: background .2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                    Permanent <i class="bi bi-arrow-right"></i>
                </a>
                <a href="{{ route('step-increment.office-report', ['type' => 'casual']) }}" style="flex:1; padding: 14px 10px; text-align:center; color:#1d4ed8; text-decoration:none; transition: background .2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                    Casual <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        {{-- NOSI / NOLP --}}
        <a href="{{ route('step-increment.index') }}" class="hub-card"
            style="border-color:{{ $overdueCount > 0 ? '#fca5a5' : '#e5e7eb' }};">
            <div class="hub-card-top">
                <div class="hub-card-icon" style="background:linear-gradient(135deg,#e0e7ff,#c7d2fe);">
                    <i class="bi bi-arrow-up-circle-fill" style="color:#4338ca;"></i>
                </div>
                <div>
                    <div class="hub-card-label">Step Increment &amp; Longevity</div>
                    <div class="hub-card-title">NOSI / NOLP</div>
                    <p class="hub-card-desc">
                        Process <strong>Notice of Step Increment (NOSI)</strong> for REGULAR employees every 3 years,
                        and <strong>Notice of Longevity Pay (NOLP)</strong> for hospital personnel every 5 years.
                        Includes Magna Carta NOSA and Increment History.
                    </p>
                    <div class="hub-card-stats">
                        <!-- Overdue removed -->
                        @if($dueCount > 0)
                            <span class="hub-card-stat-badge" style="background:#fff7ed;color:#ea580c;">
                                <i class="bi bi-alarm"></i> {{ $dueCount }} Due This Month
                            </span>
                        @endif
                        @if($upcomingCount > 0)
                            <span class="hub-card-stat-badge" style="background:#fffbeb;color:#d97706;">
                                <i class="bi bi-clock"></i> {{ $upcomingCount }} Upcoming
                            </span>
                        @endif
                        @if($magnaCartaCount > 0)
                            <span class="hub-card-stat-badge" style="background:#fff1f2;color:#e11d48;">
                                <i class="bi bi-heart-pulse-fill"></i> {{ $magnaCartaCount }} NOSA
                            </span>
                        @endif
                        @if($overdueCount === 0 && $dueCount === 0 && $upcomingCount === 0)
                            <span class="hub-card-stat-badge" style="background:#f0fdf4;color:#16a34a;">
                                <i class="bi bi-check-circle-fill"></i> All Caught Up
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="hub-card-footer" style="color:#4338ca;">
                <span class="hub-card-footer-label" style="color:#4338ca;">Open Processing View</span>
                <i class="bi bi-arrow-right" style="color:#4338ca;"></i>
            </div>
        </a>

        {{-- NOSA --}}
        <div class="hub-card">
            <div class="hub-card-top">
                <div class="hub-card-icon" style="background:linear-gradient(135deg,#ffe4e6,#fecdd3);">
                    <i class="bi bi-file-earmark-medical-fill" style="color:#be123c;"></i>
                </div>
                <div>
                    <div class="hub-card-label">Salary Adjustment</div>
                    <div class="hub-card-title">NOSA</div>
                    <p class="hub-card-desc">
                        <strong>Notice of Salary Adjustment (NOSA)</strong> — issued when a new SSL tranche takes effect.
                        Generate NOSA reports and individual NOSA letters for Permanent and Casual employees.
                    </p>
                    <div class="hub-card-stats">
                        <span class="hub-card-stat-badge" style="background:#fff1f2;color:#be123c;">
                            <i class="bi bi-file-earmark-medical-fill"></i> SSL Tranche
                        </span>
                        <span class="hub-card-stat-badge" style="background:#f0fdf4;color:#166534;">
                            <i class="bi bi-printer-fill"></i> Print NOSA
                        </span>
                    </div>
                </div>
            </div>
            <div class="hub-card-footer" style="padding:0; display:flex;">
                <a href="{{ route('step-increment.nosa', ['type' => 'permanent']) }}"
                   style="flex:1; padding:14px 10px; text-align:center; color:#be123c; text-decoration:none; border-right:1px solid #f1f5f9; transition:background .2s;"
                   onmouseover="this.style.background='#fff1f2'" onmouseout="this.style.background='transparent'">
                    Permanent <i class="bi bi-arrow-right"></i>
                </a>
                <a href="{{ route('step-increment.nosa', ['type' => 'casual']) }}"
                   style="flex:1; padding:14px 10px; text-align:center; color:#be123c; text-decoration:none; transition:background .2s;"
                   onmouseover="this.style.background='#fff1f2'" onmouseout="this.style.background='transparent'">
                    Casual <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

    </div>

    {{-- ── LOYALTY INCENTIVE SECTION ── --}}
    <div class="hub-section-title"><i class="bi bi-award-fill" style="color:#b45309;"></i> Loyalty Incentive</div>
    <div class="hub-cards-loyalty">

        <a href="{{ route('step-increment.loyalty') }}" class="hub-loyalty-card">
            <div class="hub-loyalty-card-icon" style="background:linear-gradient(135deg,#fef3c7,#fde68a);">
                <i class="bi bi-award-fill" style="color:#b45309;"></i>
            </div>
            <div>
                <div class="hub-loyalty-card-title">Loyalty Incentive</div>
                <div class="hub-loyalty-card-desc">
                    View employees eligible for Loyalty Incentive and generate Loyalty Incentive Certificate PDFs.
                    @if($loyaltyCount > 0)
                        <span
                            style="display:inline-flex;align-items:center;gap:4px;background:#fef3c7;color:#92400e;font-size:10px;font-weight:800;padding:2px 8px;border-radius:99px;margin-left:4px;border:1px solid #fde68a;">
                            <i class="bi bi-star-fill"></i> {{ $loyaltyCount }} Eligible
                        </span>
                    @endif
                </div>
            </div>
            <i class="bi bi-chevron-right hub-loyalty-arrow"></i>
        </a>

        <a href="{{ route('step-increment.loyalty-incentive-settings.index') }}" class="hub-loyalty-card">
            <div class="hub-loyalty-card-icon" style="background:linear-gradient(135deg,#f3f4f6,#e5e7eb);">
                <i class="bi bi-image-fill" style="color:#6b7280;"></i>
            </div>
            <div>
                <div class="hub-loyalty-card-title">Loyalty Incentive Background</div>
                <div class="hub-loyalty-card-desc">
                    Upload and manage background images for the Loyalty Incentive Certificate PDF. Set the active
                    template.
                </div>
            </div>
            <i class="bi bi-chevron-right hub-loyalty-arrow"></i>
        </a>

    </div>

</x-dashboard-app>