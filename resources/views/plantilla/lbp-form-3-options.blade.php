<x-dashboard-app>
<style>
/* ── Options Page Styles ───────────────────────────────────────────────── */
.inv-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #1a5276 55%, #117a65 100%);
    border-radius: 14px;
    padding: 28px 32px;
    position: relative;
    overflow: hidden;
    margin-bottom: 20px;
}
.inv-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: radial-gradient(circle, rgba(255,255,255,.08) 1px, transparent 1px);
    background-size: 22px 22px;
}
.inv-hero-inner { position: relative; z-index: 1; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
.inv-hero h1 { color: #fff; font-size: 26px; font-weight: 800; margin: 0; line-height: 1.2; }
.inv-hero p  { color: rgba(255,255,255,.65); font-size: 13px; margin: 6px 0 0; }

.filter-bar {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 12px;
    padding: 24px 32px; margin-bottom: 20px;
}
.filter-field { margin-bottom: 20px; }
.filter-field label { display: block; font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: .6px; margin-bottom: 8px; }
.filter-field input, .filter-field select {
    width: 100%; border: 1px solid #cbd5e1; border-radius: 8px;
    padding: 10px 14px; font-size: 14px; outline: none;
    transition: border .15s;
}
.filter-field input:focus, .filter-field select:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.12); }

.submit-btn {
    background: #2563eb; color: #fff; padding: 12px 24px; border-radius: 8px;
    font-size: 14px; font-weight: 600; border: none; cursor: pointer; transition: background .15s;
}
.submit-btn:hover { background: #1d4ed8; }
</style>

<div class="px-6 py-6 max-w-4xl mx-auto">
    <div class="inv-hero shadow-lg">
        <div class="inv-hero-inner">
            <div>
                <h1>LBP Form 3: Plantilla of Personnel</h1>
                <p>Generate LBP Form 3 Report (Excel) for Budget Year proposals</p>
            </div>
            <div>
                <a href="{{ route('plantilla.reports') }}" class="text-white opacity-80 hover:opacity-100 text-sm font-semibold flex items-center gap-2">
                    <i class="fa-solid fa-arrow-left"></i> Back to Reports
                </a>
            </div>
        </div>
    </div>

    <div class="filter-bar shadow-sm">
        <form action="{{ route('plantilla.lbp-form-3.export.excel') }}" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
                <div class="filter-field">
                    <label>Budget Year</label>
                    <input type="number" name="year" value="{{ date('Y') + 1 }}" required>
                </div>
                
                <div class="filter-field">
                    <label>Employment Type</label>
                    <select name="employment_type" required>
                        <option value="Permanent" {{ request('employment_type') == 'Permanent' ? 'selected' : '' }}>Regular/Permanent</option>
                        <option value="Casual" {{ request('employment_type') == 'Casual' ? 'selected' : '' }}>Casual</option>
                    </select>
                </div>

                <div class="filter-field md:col-span-2">
                    <label>Office / Department</label>
                    <select name="office">
                        <option value="">All Offices</option>
                        @foreach($offices as $office)
                            <option value="{{ $office }}" {{ request('office') == $office ? 'selected' : '' }}>{{ $office }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-field">
                    <label>Prepared by:</label>
                    <input type="text" name="prepared_by" placeholder="Name of Preparer" value="AIDA B. LOVERES">
                </div>

                <div class="filter-field">
                    <label>Reviewed by:</label>
                    <input type="text" name="reviewed_by" placeholder="Name of Reviewer" value="MAYFE P. ALERTA">
                </div>

                <div class="filter-field">
                    <label>Approved by:</label>
                    <input type="text" name="approved_by" placeholder="Name of Approver" value="ROGELIO NEIL P. ROQUE">
                </div>
            </div>

            <hr class="my-4" style="border-color: #eef2f6;">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="filter-field">
                    <label>Current Year Tranche Label:</label>
                    <input type="text" name="current_tranche" placeholder="e.g. LBC #165 2nd Tranche" value="LBC #165<br>2nd Tranche<br>Amount">
                    <div style="font-size: 10px; color: #9ca3af; margin-top: 4px; text-transform: none;">Use &lt;br&gt; for line breaks.</div>
                </div>
                <div class="filter-field">
                    <label>Proposed Year Tranche Label:</label>
                    <input type="text" name="proposed_tranche" placeholder="e.g. EO #64 3rd Tranche" value="EO #64<br>3rd Tranche<br>Amount">
                    <div style="font-size: 10px; color: #9ca3af; margin-top: 4px; text-transform: none;">Use &lt;br&gt; for line breaks.</div>
                </div>
            </div>

            <div class="mt-6 border-t pt-6 border-gray-100 flex items-center justify-end gap-3">
                <button type="submit" formaction="{{ route('plantilla.lbp-form-3.export.pdf') }}" class="submit-btn flex items-center justify-center gap-2" style="background: #dc2626;">
                    <i class="fa-solid fa-file-pdf"></i> Generate PDF
                </button>
                <button type="submit" formaction="{{ route('plantilla.lbp-form-3.export.excel') }}" class="submit-btn flex items-center justify-center gap-2">
                    <i class="fa-solid fa-file-excel"></i> Generate Excel
                </button>
            </div>
        </form>
    </div>
</div>
</x-dashboard-app>
