<x-dashboard-app>
<style>
.nosa-hero {
    background: linear-gradient(135deg, #881337 0%, #be123c 55%, #e11d48 100%);
    border-radius: 14px; padding: 28px 32px;
    position: relative; overflow: hidden; margin-bottom: 24px;
}
.nosa-hero::before {
    content: ''; position: absolute; inset: 0;
    background-image: radial-gradient(circle, rgba(255,255,255,.07) 1px, transparent 1px);
    background-size: 22px 22px;
}
.nosa-hero-inner { position: relative; z-index: 1; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
.nosa-hero h1 { color: #fff; font-size: 24px; font-weight: 800; margin: 0; line-height: 1.2; }
.nosa-hero p  { color: rgba(255,255,255,.65); font-size: 13px; margin: 5px 0 0; }
.nosa-back-btn {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(255,255,255,.18); border: 1px solid rgba(255,255,255,.3);
    color: #fff; padding: 9px 18px; border-radius: 10px;
    font-size: 13px; font-weight: 600; text-decoration: none;
    transition: background .2s;
}
.nosa-back-btn:hover { background: rgba(255,255,255,.28); color: #fff; }

.info-card {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 14px;
    padding: 28px 32px; margin-bottom: 18px;
    box-shadow: 0 2px 8px rgba(0,0,0,.04);
}
.info-card h2 { font-size: 16px; font-weight: 800; color: #0f172a; margin: 0 0 10px; display: flex; align-items: center; gap: 10px; }
.info-card p  { font-size: 13.5px; color: #475569; line-height: 1.75; margin: 0 0 10px; }
.info-card p:last-child { margin: 0; }

.info-tag {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 12px; border-radius: 99px;
    font-size: 11px; font-weight: 700; margin: 3px;
}

.nosa-steps {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 14px; margin-top: 16px;
}
.nosa-step-card {
    background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; padding: 18px;
}
.nosa-step-num  { font-size: 10px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: #be123c; margin-bottom: 6px; }
.nosa-step-title{ font-size: 14px; font-weight: 800; color: #0f172a; margin-bottom: 6px; }
.nosa-step-desc { font-size: 12px; color: #64748b; line-height: 1.6; }

.coming-soon-banner {
    background: linear-gradient(135deg, #fef2f2, #fff1f2);
    border: 2px dashed #fca5a5; border-radius: 14px;
    padding: 36px; text-align: center; margin-bottom: 20px;
}
</style>

{{-- HERO --}}
<div class="nosa-hero">
    <div class="nosa-hero-inner">
        <div>
            <h1><i class="bi bi-file-earmark-medical-fill me-2"></i>Notice of Salary Adjustment (NOSA)</h1>
            <p>Issued when salary grade changes occur outside of the regular step increment cycle.</p>
        </div>
        <a href="{{ route('step-increment.hub') }}" class="nosa-back-btn">
            <i class="bi bi-arrow-left"></i> Back to Hub
        </a>
    </div>
</div>

{{-- Info Cards --}}
<div class="info-card">
    <h2><i class="bi bi-info-circle-fill" style="color:#e11d48;"></i> What is a NOSA?</h2>
    <p>A <strong>Notice of Salary Adjustment (NOSA)</strong> is an official document issued to a government employee whenever their <strong>monthly salary rate is adjusted</strong> — whether due to position reclassification, SSL (Salary Standardization Law) tranche implementations, or Magna Carta-related salary grade increases for health workers.</p>
    <p>Unlike the NOSI (which is tied to the 3-year step increment cycle) or NOLP (5-year longevity pay for hospital personnel), a NOSA can be issued <em>at any time</em> when a salary adjustment is warranted by law or policy.</p>
</div>

<div class="info-card">
    <h2><i class="bi bi-list-check" style="color:#e11d48;"></i> Common Scenarios for NOSA Issuance</h2>
    <div class="nosa-steps">
        <div class="nosa-step-card">
            <div class="nosa-step-num">Scenario 1</div>
            <div class="nosa-step-title">SSL Tranche Adjustment</div>
            <div class="nosa-step-desc">When a new Salary Standardization Law tranche takes effect and the approved salary schedule is updated, all affected employees receive a NOSA.</div>
        </div>
        <div class="nosa-step-card">
            <div class="nosa-step-num">Scenario 2</div>
            <div class="nosa-step-title">Magna Carta NOSA (Health Workers)</div>
            <div class="nosa-step-desc">Health workers (RA 7305) who are within 3 months of their 65th birthday receive a pre-retirement NOSA, increasing their Salary Grade by 1 while retaining their step.</div>
        </div>
        <div class="nosa-step-card">
            <div class="nosa-step-num">Scenario 3</div>
            <div class="nosa-step-title">Position Reclassification</div>
            <div class="nosa-step-desc">When a position is reclassified to a higher or different salary grade, the incumbent receives a NOSA to reflect the updated salary.</div>
        </div>
        <div class="nosa-step-card">
            <div class="nosa-step-num">Scenario 4</div>
            <div class="nosa-step-title">Promotion / Appointment Change</div>
            <div class="nosa-step-desc">When an employee is promoted to a higher position, a NOSA documents the transition from the old salary grade/step to the new one.</div>
        </div>
    </div>
</div>

<div class="info-card">
    <h2><i class="bi bi-file-earmark-text-fill" style="color:#e11d48;"></i> How to Generate a NOSA</h2>
    <p>NOSA generation is currently handled through two pathways in this system:</p>
    <ul style="font-size:13.5px;color:#475569;line-height:2;padding-left:20px;">
        <li><strong>Magna Carta NOSA</strong> — Go to <a href="{{ route('step-increment.index', ['tab' => 'magna_carta']) }}" style="color:#e11d48;font-weight:700;">NOSI/NOLP → Magna Carta NOSA tab</a> to process and print NOSA certificates for eligible health workers.</li>
        <li><strong>General NOSA (SSL/Reclassification)</strong> — Access individual employee records and generate a NOSA PDF directly from the employee's plantilla record.</li>
    </ul>
    <div style="margin-top:14px;padding:12px 16px;background:#fff1f2;border:1px solid #fecdd3;border-radius:10px;font-size:12.5px;color:#be123c;display:flex;align-items:flex-start;gap:10px;">
        <i class="bi bi-lightbulb-fill" style="flex-shrink:0;margin-top:2px;"></i>
        <span>To process <strong>Magna Carta NOSA</strong> for health workers near retirement, use the dedicated tab inside the <a href="{{ route('step-increment.index', ['tab' => 'magna_carta']) }}" style="color:#be123c;font-weight:700;">NOSI/NOLP Processing View</a>.</span>
    </div>
</div>

</x-dashboard-app>
