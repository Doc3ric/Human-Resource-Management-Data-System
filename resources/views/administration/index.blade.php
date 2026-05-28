<x-dashboard-app>
    <style>
        .admin-hero {
            background: linear-gradient(135deg, #1e3a5f 0%, #0f4c75 100%);
            border-radius: 14px;
            padding: 28px 32px;
            position: relative;
            overflow: hidden;
            margin-bottom: 24px;
        }

        .admin-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle, rgba(255, 255, 255, .08) 1px, transparent 1px);
            background-size: 22px 22px;
        }

        .admin-hero-inner {
            position: relative;
            z-index: 1;
        }

        .admin-hero h1 {
            color: #fff;
            font-size: 24px;
            font-weight: 800;
            margin: 0;
        }

        .admin-hero p {
            color: rgba(255, 255, 255, .6);
            font-size: 13px;
            margin: 6px 0 0;
        }

        .admin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
        }

        .admin-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 24px 20px;
            text-align: center;
            text-decoration: none;
            color: inherit;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
            transition: transform .15s, box-shadow .15s;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }

        .admin-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, .1);
            color: inherit;
        }

        .admin-card-icon {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .admin-card-title {
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
        }

        .admin-card-desc {
            font-size: 12px;
            color: #9ca3af;
            line-height: 1.4;
        }

        .btn-run-codes {
            width: 100%;
            padding: 8px 16px;
            background: #ecfdf5;
            color: #059669;
            border: 1.5px solid #10b981;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .btn-run-codes:hover {
            background: #d1fae5;
            color: #047857;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);
        }

        .btn-run-codes:active {
            transform: translateY(0);
        }

        /* ── Premium Modal Styles ── */
        .code-modal-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(15,23,42,0.55);
            backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);
            z-index: 9999; justify-content: center; align-items: center;
            animation: fadeInBg 0.2s ease;
        }
        .code-modal-overlay.active { display: flex; }
        .code-modal-card {
            background: #fff; border-radius: 20px; padding: 0;
            max-width: 420px; width: calc(100% - 32px);
            box-shadow: 0 25px 60px rgba(0,0,0,.18), 0 8px 20px rgba(0,0,0,.08);
            position: relative; overflow: hidden;
            animation: slideUp 0.3s cubic-bezier(0.34,1.56,0.64,1);
        }
        .code-modal-top {
            background: linear-gradient(135deg, #ecfdf5, #d1fae5);
            padding: 36px 32px 24px; text-align: center;
            border-bottom: 1px solid #a7f3d0;
        }
        .code-modal-icon-wrap {
            width: 72px; height: 72px;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            margin: 0 auto 18px;
            box-shadow: 0 8px 24px rgba(16,185,129,.3);
            animation: pulse-green 2s infinite;
        }
        @keyframes pulse-green {
            0%, 100% { box-shadow: 0 8px 24px rgba(16,185,129,.30); }
            50%      { box-shadow: 0 8px 32px rgba(16,185,129,.55); }
        }
        .code-modal-icon-wrap i { font-size: 30px; color: #fff; }
        .code-modal-title  { font-size: 22px; font-weight: 800; color: #0f172a; margin: 0 0 6px; letter-spacing: -.3px; }
        .code-modal-subtitle { font-size: 13px; color: #64748b; margin: 0; line-height: 1.6; }
        .code-modal-body { padding: 22px 28px 28px; }
        .code-modal-info {
            display: flex; align-items: flex-start; gap: 10px;
            background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px;
            padding: 12px 14px; margin-bottom: 22px;
            font-size: 12.5px; color: #166534; line-height: 1.5;
        }
        .code-modal-info i { font-size: 15px; color: #15803d; flex-shrink: 0; margin-top: 1px; }
        .code-modal-actions { display: flex; gap: 10px; }
        .code-btn-cancel {
            flex: 1; padding: 12px 16px;
            border: 1.5px solid #e2e8f0; border-radius: 12px;
            font-size: 13.5px; font-weight: 600; color: #475569; background: #f8fafc;
            cursor: pointer; transition: all .2s;
            display: flex; align-items: center; justify-content: center; gap: 6px;
        }
        .code-btn-cancel:hover { background: #f1f5f9; border-color: #cbd5e1; color: #0f172a; }
        .code-btn-confirm {
            flex: 1; padding: 12px 16px;
            border: none; border-radius: 12px;
            font-size: 13.5px; font-weight: 700; color: #fff;
            background: linear-gradient(135deg, #10b981, #059669);
            cursor: pointer; transition: all .2s;
            display: flex; align-items: center; justify-content: center; gap: 6px;
            box-shadow: 0 4px 14px rgba(16,185,129,.3);
        }
        .code-btn-confirm:hover { background: linear-gradient(135deg, #059669, #047857); box-shadow: 0 6px 20px rgba(16,185,129,.45); transform: translateY(-1px); }
        .code-btn-confirm:active { transform: translateY(0); }
        .code-modal-close {
            position: absolute; top: 12px; right: 14px;
            width: 28px; height: 28px;
            background: rgba(0,0,0,.06); border: none; border-radius: 50%;
            color: #64748b; font-size: 14px; cursor: pointer;
            display: flex; align-items: center; justify-content: center; transition: all .2s;
        }
        .code-modal-close:hover { background: rgba(0,0,0,.12); color: #0f172a; }
    </style>

    {{-- Hero --}}
    <div class="admin-hero">
        <div class="admin-hero-inner">
            <h1><i class="bi bi-shield-fill-gear me-2"></i>Administration</h1>
            <p>System settings and management tools</p>
        </div>
    </div>

    {{-- Admin shortcuts --}}
    <div class="admin-grid">

        {{-- User Management --}}
        @if(Route::has('users.index'))
            <a href="{{ route('users.index') }}" class="admin-card">
                <div class="admin-card-icon" style="background:#dbeafe;color:#1e40af;">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <div class="admin-card-title">User Management</div>
                    <div class="admin-card-desc">Create, edit, and assign roles to system users</div>
                </div>
            </a>
        @endif

        {{-- Salary Table --}}
        @if(Route::has('step-increment.salary-table'))
            <a href="{{ route('step-increment.salary-table') }}" class="admin-card">
                <div class="admin-card-icon" style="background:#d1fae5;color:#065f46;">
                    <i class="bi bi-table"></i>
                </div>
                <div>
                    <div class="admin-card-title">Salary Grade Table</div>
                    <div class="admin-card-desc">View the official CSC salary schedule</div>
                </div>
            </a>
        @endif

        {{-- Import Data (super_admin only) --}}
        @if(auth()->user()->isSuperAdmin() && Route::has('imports.index'))
            <a href="{{ route('imports.index') }}" class="admin-card">
                <div class="admin-card-icon" style="background:#fef3c7;color:#92400e;">
                    <i class="bi bi-cloud-upload-fill"></i>
                </div>
                <div>
                    <div class="admin-card-title">Import Data</div>
                    <div class="admin-card-desc">Bulk import plantilla records via spreadsheet</div>
                </div>
            </a>
        @endif

        {{-- My Profile --}}
        <a href="{{ route('profile.edit') }}" class="admin-card">
            <div class="admin-card-icon" style="background:#f3e8ff;color:#7c3aed;">
                <i class="bi bi-person-circle"></i>
            </div>
            <div>
                <div class="admin-card-title">My Profile</div>
                <div class="admin-card-desc">Update your account details and password</div>
            </div>
        </a>
        {{-- Generate Employee Codes (super_admin only) --}}
        @if(auth()->user()->isSuperAdmin() && Route::has('employee-codes.generate-all'))
            <div class="admin-card" style="cursor:default;">
                <div class="admin-card-icon" style="background:#ecfdf5;color:#065f46;">
                    <i class="bi bi-qr-code"></i>
                </div>
                <div>
                    <div class="admin-card-title">Generate Employee Codes</div>
                    <div class="admin-card-desc">Bulk-generate unique codes (DDMMYYYY + initial) for all existing employees
                        without a code</div>
                </div>
                <form id="generate-codes-form" method="POST" action="{{ route('employee-codes.generate-all') }}" style="width:100%; margin-top:auto;">
                    @csrf
                    <input type="hidden" name="table" value="all">
                    <button type="button" class="btn-run-codes" onclick="openCodeModal()">
                        <i class="bi bi-lightning-fill"></i> Run Now
                    </button>
                </form>
            </div>
        @endif

    </div>

    {{-- Premium Confirmation Modal --}}
    <div id="codeModal" class="code-modal-overlay">
        <div class="code-modal-card">
            <button type="button" class="code-modal-close" onclick="closeCodeModal()" title="Close">
                <i class="bi bi-x-lg"></i>
            </button>
            <div class="code-modal-top">
                <div class="code-modal-icon-wrap">
                    <i class="bi bi-qr-code-scan"></i>
                </div>
                <h2 class="code-modal-title">Generate Codes?</h2>
                <p class="code-modal-subtitle">This will generate unique employee codes for ALL existing records missing a code.</p>
            </div>
            <div class="code-modal-body">
                <div class="code-modal-info">
                    <i class="bi bi-info-circle-fill"></i>
                    <span>This process covers Plantilla, Casual, and Job Order tables. Any duplicates will be automatically resolved.</span>
                </div>
                <div class="code-modal-actions">
                    <button type="button" class="code-btn-cancel" onclick="closeCodeModal()">
                        <i class="bi bi-arrow-left"></i> Cancel
                    </button>
                    <button type="button" class="code-btn-confirm" onclick="document.getElementById('generate-codes-form').submit()">
                        <i class="bi bi-check-lg"></i> Proceed
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openCodeModal() {
            document.getElementById('codeModal').classList.add('active');
        }
        function closeCodeModal() {
            document.getElementById('codeModal').classList.remove('active');
        }
        document.addEventListener('DOMContentLoaded', function() {
            var overlay = document.getElementById('codeModal');
            if (overlay) {
                overlay.addEventListener('click', function(e) {
                    if (e.target === overlay) closeCodeModal();
                });
            }
        });
    </script>
</x-dashboard-app>