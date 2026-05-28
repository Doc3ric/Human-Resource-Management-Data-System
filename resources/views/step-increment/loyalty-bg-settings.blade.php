<x-dashboard-app>
<style>
    /* ── Hero ─────────────────────────────────── */
    .li-hero {
        background: linear-gradient(135deg, #78350f 0%, #92400e 45%, #b45309 100%);
        border-radius: 14px; padding: 22px 28px;
        position: relative; overflow: hidden; margin-bottom: 20px;
    }
    .li-hero::before {
        content:''; position:absolute; inset:0;
        background-image: radial-gradient(circle, rgba(255,255,255,.07) 1px, transparent 1px);
        background-size: 20px 20px;
    }
    .li-hero-inner { position:relative;z-index:1;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap; }
    .li-hero h1 { color:#fff; font-size:20px; font-weight:800; margin:0; }
    .li-hero p  { color:rgba(255,255,255,.65); font-size:12px; margin:4px 0 0; }

    /* ── Upload card ─────────────────────────── */
    .li-upload-card {
        background:#fff; border:2px dashed #d97706;
        border-radius:14px; padding:28px 24px; margin-bottom:22px;
    }
    .li-upload-card h2 { font-size:14px; font-weight:700; color:#92400e; margin-bottom:16px; display:flex; align-items:center; gap:8px; }

    /* ── Backgrounds grid ────────────────────── */
    .li-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(240px, 1fr)); gap:18px; }

    .li-bg-card {
        background:#fff; border:2px solid #e5e7eb; border-radius:12px;
        overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.06);
        transition:transform .2s, box-shadow .2s;
    }
    .li-bg-card:hover { transform:translateY(-3px); box-shadow:0 8px 24px rgba(0,0,0,.12); }
    .li-bg-card.active-bg { border-color:#f59e0b; box-shadow:0 0 0 3px rgba(245,158,11,.25); }

    .li-bg-thumb {
        width:100%; height:160px; object-fit:cover; display:block;
        background:#f3f4f6;
    }
    .li-bg-thumb-placeholder {
        width:100%; height:160px;
        display:flex; align-items:center; justify-content:center;
        background: linear-gradient(135deg,#fef3c7,#fde68a);
        font-size:40px; color:#d97706;
    }

    .li-bg-body { padding:14px 14px 10px; }
    .li-bg-name { font-size:13px; font-weight:700; color:#1f2937; margin-bottom:4px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .li-bg-file { font-size:10px; color:#9ca3af; font-family:monospace; }
    .li-bg-badge {
        display:inline-flex; align-items:center; gap:4px;
        background:#fef3c7; color:#92400e; border:1px solid #fde68a;
        padding:2px 9px; border-radius:99px; font-size:10px; font-weight:700;
        margin-top:6px;
    }
    .li-bg-actions { padding:10px 14px 14px; display:flex; gap:8px; }

    .li-btn {
        display:inline-flex; align-items:center; gap:5px;
        padding:7px 14px; border-radius:8px; font-size:11px; font-weight:700;
        border:none; cursor:pointer; transition:all .15s; text-decoration:none;
    }
    .li-btn.set-active { background:#fef3c7; color:#92400e; border:1px solid #fde68a; }
    .li-btn.set-active:hover { background:#f59e0b; color:#fff; border-color:#f59e0b; }
    .li-btn.is-active  { background:#f59e0b; color:#fff; cursor:default; }
    .li-btn.del { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; }
    .li-btn.del:hover { background:#dc2626; color:#fff; border-color:#dc2626; }

    /* ── Preview overlay ─────────────────────── */
    .li-preview-overlay {
        position:fixed; inset:0; background:rgba(0,0,0,.82);
        z-index:9999; display:none; align-items:center; justify-content:center;
        padding:16px;
    }
    .li-preview-overlay.open { display:flex; }
    .li-preview-box {
        background:#111827; border-radius:16px; overflow:hidden;
        width:min(95vw, 1100px); max-height:92vh;
        box-shadow:0 32px 80px rgba(0,0,0,.7);
        display:flex; flex-direction:column;
    }
    .li-preview-header {
        padding:14px 20px; background:#1f2937;
        border-bottom:1px solid #374151;
        font-size:13px; font-weight:700; color:#f9fafb;
        display:flex; justify-content:space-between; align-items:center;
        flex-shrink:0;
    }
    .li-preview-close {
        background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.15);
        color:#f9fafb; font-size:18px; cursor:pointer; line-height:1;
        width:30px; height:30px; border-radius:6px;
        display:flex; align-items:center; justify-content:center;
        transition:background .15s;
    }
    .li-preview-close:hover { background:rgba(220,38,38,.7); border-color:#dc2626; }

    /* Tabs */
    .prev-tabs {
        display:flex; background:#1f2937; border-bottom:2px solid #374151;
        flex-shrink:0;
    }
    .prev-tab {
        padding:10px 22px; font-size:12px; font-weight:700; color:#9ca3af;
        cursor:pointer; border-bottom:3px solid transparent; margin-bottom:-2px;
        transition:all .15s; display:flex; align-items:center; gap:7px;
        border:none; background:none;
    }
    .prev-tab:hover { color:#e5e7eb; }
    .prev-tab.active { color:#fbbf24; border-bottom-color:#f59e0b; }

    /* Tab panels */
    .prev-panels { overflow-y:auto; flex:1; min-height:0; }
    .prev-panel  { display:none; padding:20px 24px 24px; }
    .prev-panel.active { display:block; }

    /* Raw image panel */
    .prev-img-frame {
        border-radius:10px; overflow:hidden;
        box-shadow:0 8px 30px rgba(0,0,0,.5);
        border:1px solid #374151;
        background:repeating-conic-gradient(#1f2937 0% 25%, #111827 0% 50%) 0 0 / 20px 20px;
    }
    .prev-img-frame img { width:100%; display:block; }

    /* Certificate mockup */
    .cert-mockup {
        width:100%;
        background:#fff; position:relative; overflow:hidden;
        aspect-ratio:297/210; /* A4 landscape exact */
    }
    .cert-mockup-bg {
        position:absolute; inset:0; width:100%; height:100%;
        object-fit:cover; opacity:1.0;
    }
    /* Certificate content overlay — mimics real PDF layout */
    .cert-ov {
        position:absolute; inset:0; z-index:2;
        display:flex; flex-direction:column;
        padding:2.8% 6% 3% 6%;
        font-family:sans-serif;
    }
    .cert-ov-header {
        display:flex; align-items:center; justify-content:space-between;
        border-bottom:2px solid #555; padding-bottom:1.2%;
        margin-bottom:1%;
    }
    .cert-ov-logo {
        width:7%; aspect-ratio:1; background:#e5e7eb;
        border-radius:50%; display:flex; align-items:center; justify-content:center;
        font-size:1.4vw; color:#9ca3af;
    }
    .cert-ov-title-block { text-align:center; flex:1; padding:0 2%; }
    .cert-ov-republic { font-size:0.75vw; color:#555; letter-spacing:0.5px; margin-bottom:1px; }
    .cert-ov-province { font-size:0.9vw; font-weight:800; color:#1a1a1a; margin-bottom:1px; }
    .cert-ov-office { font-size:0.65vw; color:#444; letter-spacing:0.5px; }
    .cert-ov-body { flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; }
    .cert-ov-dept { font-size:0.7vw; letter-spacing:1.5px; color:#333; text-transform:uppercase; margin-bottom:0.8%; font-weight:700; }
    .cert-ov-main-title {
        font-size:3.2vw; font-weight:900; letter-spacing:4px;
        font-family:Georgia,serif; color:#111; line-height:1.1; margin-bottom:0.4%;
    }
    .cert-ov-awarded { font-size:0.75vw; letter-spacing:3px; color:#555; text-transform:uppercase; margin-bottom:0.5%; }
    .cert-ov-stars { color:#b8912a; font-size:1.1vw; margin-bottom:0.4%; }
    .cert-ov-name { font-size:2vw; font-style:italic; font-weight:800; color:#111; margin-bottom:0.3%; }
    .cert-ov-pos  { font-size:0.75vw; color:#333; font-weight:600; margin-bottom:0.3%; }
    .cert-ov-unit { font-size:0.65vw; color:#555; }
    .cert-ov-para {
        font-size:0.72vw; color:#222; line-height:1.65;
        margin:1.5% auto 0; max-width:75%; text-align:center;
    }
    .cert-ov-footer {
        display:flex; justify-content:center; margin-top:auto; padding-top:1.5%;
    }
    .cert-ov-signatory { text-align:center; }
    .cert-ov-sign-name { font-size:0.8vw; font-weight:800; color:#111; letter-spacing:0.5px; }
    .cert-ov-sign-pos  { font-size:0.65vw; color:#444; margin-top:1px; }
    /* No-bg card */
    .li-nobg-card {
        background:#f8fafc; border:2px dashed #cbd5e1; border-radius:12px;
        padding:24px 20px; display:flex; align-items:center; gap:16px;
        margin-bottom:22px;
    }
    .li-nobg-icon { font-size:36px; color:#94a3b8; }
    .li-nobg-info h3 { font-size:13px; font-weight:700; color:#374151; margin:0 0 4px; }
    .li-nobg-info p  { font-size:12px; color:#6b7280; margin:0; }

    /* Flash messages */
    .flash-success { background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; border-radius:10px; padding:11px 16px; margin-bottom:16px; font-size:13px; font-weight:600; display:flex; align-items:center; gap:8px; }
    .flash-error   { background:#fef2f2; border:1px solid #fecaca; color:#991b1b; border-radius:10px; padding:11px 16px; margin-bottom:16px; font-size:13px; font-weight:600; display:flex; align-items:center; gap:8px; }

    /* Drop zone */
    .drop-zone {
        border:2px dashed #f59e0b; border-radius:10px;
        padding:24px; text-align:center; cursor:pointer;
        background:#fffbeb; transition:background .15s;
        position:relative;
    }
    .drop-zone:hover, .drop-zone.drag-over { background:#fef3c7; }
    .drop-zone input[type=file] { position:absolute; inset:0; opacity:0; cursor:pointer; }
    .drop-zone .dz-icon { font-size:32px; color:#f59e0b; margin-bottom:8px; }
    .drop-zone .dz-text { font-size:12px; color:#92400e; font-weight:600; }
    .drop-zone .dz-sub  { font-size:10px; color:#b45309; margin-top:3px; }
    #dz-filename { font-size:11px; color:#16a34a; font-weight:700; margin-top:6px; }
    #preview-thumb-wrap { margin-top:10px; display:none; }
    #preview-thumb { max-height:120px; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,.15); }
</style>

{{-- Hero --}}
<div class="li-hero">
    <div class="li-hero-inner">
        <div>
            <h1><i class="bi bi-image-fill me-2"></i>Loyalty Incentive — Background Templates</h1>
            <p>Upload, preview and choose the background image for the Loyalty Incentive Certificate PDF</p>
        </div>
        <a href="{{ route('step-increment.index') }}" class="li-btn set-active">
            <i class="bi bi-arrow-left"></i> Back to Step Increment
        </a>
    </div>
</div>

{{-- Flash messages --}}
@if(session('success'))
    <div class="flash-success"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="flash-error"><i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="flash-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $errors->first() }}</div>
@endif

{{-- Upload card --}}
<div class="li-upload-card">
    <h2><i class="bi bi-cloud-arrow-up-fill"></i> Upload New Background</h2>
    <form method="POST" action="{{ route('step-increment.loyalty-incentive-settings.upload') }}" enctype="multipart/form-data">
        @csrf
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;align-items:start;">
            {{-- Drop zone --}}
            <div>
                <div class="drop-zone" id="dz-area">
                    <input type="file" name="background_image" id="bg-file-input" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                    <div class="dz-icon"><i class="bi bi-image"></i></div>
                    <div class="dz-text">Click or drag &amp; drop an image here</div>
                    <div class="dz-sub">JPG, PNG, GIF, WEBP — max 5 MB</div>
                    <div id="dz-filename"></div>
                </div>
                <div id="preview-thumb-wrap">
                    <img id="preview-thumb" src="" alt="Preview">
                </div>
            </div>

            {{-- Name + submit --}}
            <div style="display:flex;flex-direction:column;gap:14px;padding-top:4px;">
                <div>
                    <label style="font-size:12px;font-weight:700;color:#374151;display:block;margin-bottom:6px;">
                        Background Name <span style="color:#ef4444">*</span>
                    </label>
                    <input type="text" name="background_name" id="bg-name-input"
                        value="{{ old('background_name') }}"
                        placeholder="e.g. Gold Floral, Plain Cream, Anniversary…"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:border-amber-400 outline-none"
                        maxlength="80" required>
                    <p style="font-size:10px;color:#9ca3af;margin-top:4px;">Give it a descriptive name so you can identify it later.</p>
                </div>

                <button type="submit" class="li-btn" style="background:#f59e0b;color:#fff;align-self:flex-start;padding:10px 22px;font-size:13px;">
                    <i class="bi bi-upload"></i> Upload Background
                </button>

                {{-- No background option --}}
                <div style="padding-top:4px;border-top:1px solid #e5e7eb;">
                    <p style="font-size:11px;color:#6b7280;margin-bottom:8px;">Or generate without any background:</p>
                    <form method="POST" action="{{ route('step-increment.loyalty-incentive-settings.set-none') }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="li-btn" style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;">
                            <i class="bi bi-file-earmark-x"></i>
                            {{ is_null($active) ? '✓ Currently: No Background' : 'Use No Background' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- Backgrounds grid --}}
<div style="margin-bottom:14px;">
    <h2 style="font-size:15px;font-weight:800;color:#1f2937;margin-bottom:4px;">
        <i class="bi bi-collection-fill me-2" style="color:#f59e0b;"></i>
        Saved Backgrounds
        <span style="font-size:11px;font-weight:500;color:#9ca3af;margin-left:8px;">({{ count($backgrounds) }} templates)</span>
    </h2>
    <p style="font-size:12px;color:#6b7280;margin-bottom:14px;">
        The <span style="background:#fef3c7;color:#92400e;font-weight:700;padding:1px 7px;border-radius:4px;">✓ ACTIVE</span> background will be used when generating Loyalty Incentive PDFs.
    </p>
</div>

@if(count($backgrounds) === 0)
    <div class="li-nobg-card">
        <div class="li-nobg-icon"><i class="bi bi-images"></i></div>
        <div class="li-nobg-info">
            <h3>No backgrounds uploaded yet</h3>
            <p>Upload your first background image above. It will automatically be set as the active template.</p>
        </div>
    </div>
@else
    <div class="li-grid">
        @foreach($backgrounds as $bg)
            @php $isActive = ($active === $bg['id']); @endphp
            <div class="li-bg-card {{ $isActive ? 'active-bg' : '' }}" id="card-{{ $bg['id'] }}">

                {{-- Thumbnail --}}
                <div style="position:relative;cursor:pointer;" onclick="openPreview('{{ asset('storage/loyalty-backgrounds/' . $bg['file']) }}', '{{ addslashes($bg['name']) }}')">
                    <img
                        src="{{ asset('storage/loyalty-backgrounds/' . $bg['file']) }}"
                        alt="{{ $bg['name'] }}"
                        class="li-bg-thumb"
                        onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                    <div class="li-bg-thumb-placeholder" style="display:none;"><i class="bi bi-image-alt"></i></div>
                    <div style="position:absolute;inset:0;background:rgba(0,0,0,0);transition:background .2s;display:flex;align-items:center;justify-content:center;" class="thumb-hover-overlay">
                        <span style="color:#fff;font-size:11px;font-weight:700;background:rgba(0,0,0,.5);padding:4px 12px;border-radius:99px;opacity:0;transition:opacity .2s;" class="preview-hint">
                            <i class="bi bi-eye-fill me-1"></i>Preview
                        </span>
                    </div>
                </div>

                <div class="li-bg-body">
                    <div class="li-bg-name" title="{{ $bg['name'] }}">{{ $bg['name'] }}</div>
                    <div class="li-bg-file">{{ $bg['file'] }}</div>
                    @if($isActive)
                        <div class="li-bg-badge"><i class="bi bi-check-circle-fill"></i> ACTIVE</div>
                    @endif
                </div>

                <div class="li-bg-actions">
                    @if(!$isActive)
                        <form method="POST" action="{{ route('step-increment.loyalty-incentive-settings.set-active', $bg['id']) }}" style="flex:1;">
                            @csrf
                            <button type="submit" class="li-btn set-active" style="width:100%;">
                                <i class="bi bi-check-circle"></i> Set as Active
                            </button>
                        </form>
                    @else
                        <button class="li-btn is-active" style="flex:1;cursor:default;">
                            <i class="bi bi-check-circle-fill"></i> Active
                        </button>
                    @endif

                    <form method="POST" action="{{ route('step-increment.loyalty-incentive-settings.destroy', $bg['id']) }}"
                        onsubmit="return confirmDelete(event, '{{ addslashes($bg['name']) }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="li-btn del" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
@endif

{{-- Preview Modal --}}
<div class="li-preview-overlay" id="preview-overlay" onclick="closePreview(event)">
    <div class="li-preview-box">

        {{-- Header --}}
        <div class="li-preview-header">
            <span id="preview-title" style="display:flex;align-items:center;gap:8px;">
                <i class="bi bi-eye-fill" style="color:#f59e0b;"></i>
                <span>Certificate Preview</span>
            </span>
            <button class="li-preview-close" onclick="closePreviewBtn()">&times;</button>
        </div>

        {{-- Tabs --}}
        <div class="prev-tabs">
            <button class="prev-tab active" id="tab-btn-img" onclick="switchTab('img')">
                <i class="bi bi-image"></i> Background Image
            </button>
            <button class="prev-tab" id="tab-btn-pdf" onclick="switchTab('pdf')">
                <i class="bi bi-file-earmark-pdf-fill" style="color:#f87171;"></i> PDF Certificate Preview
                <span style="background:#f59e0b;color:#000;font-size:9px;font-weight:900;padding:1px 6px;border-radius:4px;margin-left:2px;">REAL</span>
            </button>
        </div>

        {{-- Panels --}}
        <div class="prev-panels">

            {{-- Tab 1: Raw image --}}
            <div class="prev-panel active" id="tab-panel-img">
                <p style="font-size:11px;color:#6b7280;margin-bottom:12px;display:flex;align-items:center;gap:6px;">
                    <i class="bi bi-info-circle" style="color:#60a5fa;"></i>
                    This is the full background image as you uploaded it.
                </p>
                <div class="prev-img-frame">
                    <img id="preview-raw-img" src="" alt="Background Image">
                </div>
            </div>

            {{-- Tab 2: PDF Certificate simulation --}}
            <div class="prev-panel" id="tab-panel-pdf">
                <p style="font-size:11px;color:#6b7280;margin-bottom:12px;display:flex;align-items:center;gap:6px;">
                    <i class="bi bi-check-circle-fill" style="color:#34d399;"></i>
                    This is exactly how the certificate will look when generated as PDF. The background fully replaces the CSS corner strips.
                </p>
                <div style="border-radius:10px;overflow:hidden;box-shadow:0 8px 40px rgba(0,0,0,.6);border:1px solid #374151;">
                    <div class="cert-mockup">
                        {{-- Your background at full opacity --}}
                        <img src="" alt="" class="cert-mockup-bg" id="preview-mockup-bg">

                        {{-- Overlay that mimics the real certificate layout --}}
                        <div class="cert-ov">

                            {{-- Header row: logo | agency | logo --}}
                            <div class="cert-ov-header">
                                <div class="cert-ov-logo"><i class="bi bi-award"></i></div>
                                <div class="cert-ov-title-block">
                                    <div class="cert-ov-republic">Republic of the Philippines</div>
                                    <div class="cert-ov-province">PROVINCE OF BUKIDNON</div>
                                    <div class="cert-ov-office">Provincial Capitol, Malaybalay City</div>
                                </div>
                                <div class="cert-ov-logo"><i class="bi bi-shield"></i></div>
                            </div>

                            {{-- Body --}}
                            <div class="cert-ov-body">
                                <div class="cert-ov-dept">Provincial Human Resource Management Office</div>

                                <div class="cert-ov-main-title">LOYALTY INCENTIVE</div>
                                <div class="cert-ov-awarded">is awarded to</div>
                                <div class="cert-ov-stars">✦ &nbsp; ✦ &nbsp; ✦</div>

                                <div class="cert-ov-name">Juan Dela Cruz</div>
                                <div class="cert-ov-pos">Administrative Officer IV</div>
                                <div class="cert-ov-unit">BPH-MANOLO FORTICH</div>

                                <div class="cert-ov-para">
                                    in grateful appreciation for your <u><strong>10</strong></u> years of continuous
                                    and satisfactory service to the Provincial Government of Bukidnon (PGB)
                                    from&nbsp;<strong>March 16, 2016</strong>&nbsp;to&nbsp;<strong>March 16, 2026</strong>,
                                    with corresponding loyalty incentive in the amount of
                                    <strong>Ten Thousand Pesos (₱10,000.00)</strong>,
                                    as per CSC Memorandum Circular No. 6, s. 2002.
                                </div>
                            </div>

                            {{-- Signatory --}}
                            <div class="cert-ov-footer">
                                <div class="cert-ov-signatory">
                                    <div class="cert-ov-sign-name">AIDA B. LOVERES</div>
                                    <div class="cert-ov-sign-pos">P.G. Department Head &mdash; PHRMO</div>
                                </div>
                            </div>

                        </div>{{-- /cert-ov --}}
                    </div>{{-- /cert-mockup --}}
                </div>
            </div>

        </div>{{-- /prev-panels --}}
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Drop zone file handling
    const dzArea      = document.getElementById('dz-area');
    const bgFileInput = document.getElementById('bg-file-input');
    const dzFilename  = document.getElementById('dz-filename');
    const previewWrap = document.getElementById('preview-thumb-wrap');
    const previewImg  = document.getElementById('preview-thumb');
    const bgNameInput = document.getElementById('bg-name-input');

    function handleFile(file) {
        if (!file) return;
        dzFilename.textContent = '📎 ' + file.name;
        const reader = new FileReader();
        reader.onload = e => {
            previewImg.src = e.target.result;
            previewWrap.style.display = 'block';
        };
        reader.readAsDataURL(file);
        // Auto-suggest name from filename
        if (!bgNameInput.value.trim()) {
            const rawName = file.name.replace(/\.[^.]+$/, '').replace(/[-_]/g, ' ');
            bgNameInput.value = rawName.charAt(0).toUpperCase() + rawName.slice(1);
        }
    }

    bgFileInput.addEventListener('change', function() {
        handleFile(this.files[0]);
    });

    dzArea.addEventListener('dragover', e => { e.preventDefault(); dzArea.classList.add('drag-over'); });
    dzArea.addEventListener('dragleave', () => dzArea.classList.remove('drag-over'));
    dzArea.addEventListener('drop', e => {
        e.preventDefault(); dzArea.classList.remove('drag-over');
        if (e.dataTransfer.files.length) {
            bgFileInput.files = e.dataTransfer.files;
            handleFile(e.dataTransfer.files[0]);
        }
    });

    // Thumbnail hover effect
    document.querySelectorAll('.li-bg-card').forEach(card => {
        const overlay = card.querySelector('.thumb-hover-overlay');
        const hint    = card.querySelector('.preview-hint');
        if (!overlay || !hint) return;
        card.addEventListener('mouseenter', () => {
            overlay.style.background = 'rgba(0,0,0,.25)';
            hint.style.opacity = '1';
        });
        card.addEventListener('mouseleave', () => {
            overlay.style.background = 'rgba(0,0,0,0)';
            hint.style.opacity = '0';
        });
    });

    // Preview modal
    function openPreview(url, name) {
        document.getElementById('preview-raw-img').src   = url;
        document.getElementById('preview-mockup-bg').src = url;
        document.getElementById('preview-title').querySelector('span').textContent = 'Preview — ' + name;
        // Always open on Image tab first
        switchTab('img');
        document.getElementById('preview-overlay').classList.add('open');
    }
    function switchTab(tab) {
        ['img','pdf'].forEach(t => {
            document.getElementById('tab-btn-' + t).classList.toggle('active', t === tab);
            document.getElementById('tab-panel-' + t).classList.toggle('active', t === tab);
        });
    }
    function closePreview(e) {
        if (e.target === document.getElementById('preview-overlay')) closePreviewBtn();
    }
    function closePreviewBtn() {
        document.getElementById('preview-overlay').classList.remove('open');
    }

    // Delete confirmation
    function confirmDelete(e, name) {
        e.preventDefault();
        const form = e.target;
        Swal.fire({
            title: 'Delete Background?',
            html: `"<b>${name}</b>" will be permanently deleted.`,
            icon: 'warning',
            iconColor: '#dc2626',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it'
        }).then(r => { if (r.isConfirmed) form.submit(); });
        return false;
    }
</script>
</x-dashboard-app>
