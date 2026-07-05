<x-dashboard-app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Deliberation Workspace (Module 5)
        </h2>
    </x-slot>

    @php
        $phaseLabels = [
            'screening' => 'Screening',
            'twg_evaluation' => 'TWG Evaluation',
            'hrmpsb_deliberation' => 'HRMPSB Deliberation',
            'completed' => 'Completed',
        ];
        $currentPhase = $applicant->deliberation_phase ?: 'screening';
    @endphp

    <style>
        .deliberation-header {
            position: sticky; top: 0; z-index: 5;
            background: linear-gradient(135deg,#082977 0%,#0f172a 100%);
            color: #fff; padding: 14px 20px; border-radius: 10px;
            display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;
        }
        .applicant-chip-track {
            display: flex; gap: 8px; overflow-x: auto; padding: 12px 4px; margin: 10px 0;
        }
        .applicant-chip {
            display: flex; align-items: center; gap: 6px; padding: 6px 12px 6px 6px;
            border-radius: 20px; border: 1px solid #e5e7eb; background: #fff;
            text-decoration: none; color: #374151; font-size: 12.5px; white-space: nowrap; flex-shrink: 0;
        }
        .applicant-chip.active { border-color: var(--color-accent, #2563eb); background: #eff6ff; color: var(--color-accent, #2563eb); font-weight: 700; }
        .applicant-chip img, .applicant-chip .chip-silhouette {
            width: 24px; height: 24px; border-radius: 50%; object-fit: cover;
        }
        .applicant-chip .chip-silhouette { background: #f3f4f6; border: 1.5px solid var(--color-danger, #dc2626); display: flex; align-items: center; justify-content: center; }

        .deliberation-grid {
            display: grid; grid-template-columns: 45% 55%; gap: 16px;
            height: calc(100vh - 230px); min-height: 500px;
        }
        .deliberation-panel { overflow-y: auto; padding-right: 6px; }

        @media (max-width: 1024px) {
            .deliberation-grid { display: block; height: auto; }
            .deliberation-panel { display: none; height: auto; overflow-y: visible; }
            .deliberation-panel.active-tab { display: block; }
        }
    </style>

    <div class="content-wrapper p-4">
        <div class="deliberation-header">
            <div>
                <div style="font-size:18px;font-weight:800;">{{ $applicant->position_applied ?: 'N/A' }}</div>
                <div style="font-size:12px;opacity:.8;">
                    Item No: {{ $applicant->item_no ?: '—' }}
                    &middot; SG: {{ $position?->salary_grade ?? '—' }}
                    &middot; Office: {{ $applicant->office ?: '—' }}
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <form method="POST" action="{{ route('recruitment.deliberation.phase', $applicant) }}" class="d-flex align-items-center gap-2">
                    @csrf
                    <select name="deliberation_phase" class="form-select form-select-sm" onchange="this.form.submit()" style="width:auto;">
                        @foreach($phaseLabels as $key => $label)
                            <option value="{{ $key }}" {{ $currentPhase === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

        <div class="applicant-chip-track">
            @foreach($applicants as $app)
                <a href="{{ route('recruitment.deliberation.show', $app) }}" class="applicant-chip {{ $app->id === $applicant->id ? 'active' : '' }}">
                    @if($app->photo_url)
                        <img src="{{ $app->photo_url }}" alt="">
                    @else
                        <span class="chip-silhouette"><i class="bi bi-person-fill" style="font-size:11px;color:#dc2626;"></i></span>
                    @endif
                    {{ $app->last_name }}, {{ $app->first_name }}
                </a>
            @endforeach
        </div>

        <div class="d-md-none mb-2" style="display:none;" id="mobileTabSwitcher">
            <div class="btn-group w-100">
                <button type="button" class="btn btn-outline-primary btn-sm active" onclick="showDeliberationTab('profile')" id="tabBtnProfile">Profile</button>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="showDeliberationTab('scoring')" id="tabBtnScoring">Scoring Sheet</button>
            </div>
        </div>

        <div class="deliberation-grid">
            <div class="deliberation-panel active-tab" id="profilePanel">
                @include('recruitment.partials.deliberation-profile')
            </div>
            <div class="deliberation-panel" id="scoringPanel">
                @include('hrmpsb.twg-dynamic._sheet')
            </div>
        </div>
    </div>

    <script>
        function showDeliberationTab(which) {
            document.getElementById('profilePanel').classList.toggle('active-tab', which === 'profile');
            document.getElementById('scoringPanel').classList.toggle('active-tab', which === 'scoring');
            document.getElementById('tabBtnProfile').classList.toggle('active', which === 'profile');
            document.getElementById('tabBtnScoring').classList.toggle('active', which === 'scoring');
        }
        function applyResponsiveDeliberation() {
            const isMobile = window.innerWidth <= 1024;
            document.getElementById('mobileTabSwitcher').style.display = isMobile ? 'block' : 'none';
        }
        window.addEventListener('resize', applyResponsiveDeliberation);
        document.addEventListener('DOMContentLoaded', applyResponsiveDeliberation);
    </script>
</x-dashboard-app>
