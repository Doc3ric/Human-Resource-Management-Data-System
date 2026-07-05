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
                <a href="{{ route('recruitment.deliberation.export.layout-a', $applicant) }}" target="_blank" class="btn btn-sm btn-outline-light">
                    <i class="bi bi-printer"></i> Layout A (Profile)
                </a>
                <a href="{{ route('recruitment.deliberation.export.cer', $applicant) }}" target="_blank" class="btn btn-sm btn-outline-info text-dark fw-bold" style="background:#e0f2fe;">
                    <i class="bi bi-file-earmark-pdf"></i> Print CER
                </a>
                <a href="{{ route('recruitment.deliberation.export.layout-c', $applicant) }}" target="_blank" class="btn btn-sm btn-outline-light">
                    <i class="bi bi-file-earmark-pdf"></i> Layout C (Scores)
                </a>
                <a href="{{ route('recruitment.deliberation.export.layout-d', ['position' => $applicant->position_applied]) }}" class="btn btn-sm btn-light text-dark fw-bold">
                    <i class="bi bi-file-earmark-spreadsheet"></i> Layout D (Comparative)
                </a>
                <form method="POST" action="{{ route('recruitment.deliberation.phase', $applicant) }}" class="d-flex align-items-center gap-2 ms-2">
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
            @php $photoEnforcer = app(\App\Support\PhotoEnforcementService::class); @endphp
            @foreach($applicants as $app)
                @php $hasValidPhoto = $photoEnforcer->hasValidPhoto($app); @endphp
                <a href="{{ route('recruitment.deliberation.show', $app) }}" class="applicant-chip {{ $app->id === $applicant->id ? 'active' : '' }}" {!! !$hasValidPhoto ? 'style="border-color:#dc2626;" title="Photo missing — scoring blocked."' : '' !!}>
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

        <div class="mb-3">
            <button type="button" class="btn btn-sm btn-dark" onclick="toggleMonitoringBoard()">
                <i class="bi bi-display"></i> HRMPSB Monitoring Board
            </button>
            <div id="monitoringBoard" style="display:none; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:15px; margin-top:10px;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 fw-bold">Deliberation Completion Matrix</h6>
                    <small class="text-muted" id="monitoringLastUpdated">Updating...</small>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered" style="font-size: 13px; text-align: center;">
                        <thead id="monitoringTableHead">
                            <!-- Filled via JS -->
                        </thead>
                        <tbody id="monitoringTableBody">
                            <!-- Filled via JS -->
                        </tbody>
                    </table>
                </div>
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
        
        let monitoringInterval = null;
        function toggleMonitoringBoard() {
            const board = document.getElementById('monitoringBoard');
            if (board.style.display === 'none') {
                board.style.display = 'block';
                fetchMonitoringData();
                monitoringInterval = setInterval(fetchMonitoringData, 10000); // 10s poll
            } else {
                board.style.display = 'none';
                if (monitoringInterval) clearInterval(monitoringInterval);
            }
        }

        async function fetchMonitoringData() {
            try {
                const res = await fetch('{{ route('recruitment.deliberation.monitoring', $applicant) }}');
                if (!res.ok) return;
                const data = await res.json();
                renderMonitoringTable(data);
                document.getElementById('monitoringLastUpdated').innerText = 'Last updated: ' + new Date().toLocaleTimeString();
            } catch (err) {
                console.error("Failed to fetch monitoring data", err);
            }
        }

        function renderMonitoringTable(data) {
            const thead = document.getElementById('monitoringTableHead');
            const tbody = document.getElementById('monitoringTableBody');
            
            // Render Header: Applicants
            let headHtml = '<tr><th style="width: 25%;">Panel Member</th>';
            data.applicants.forEach(app => {
                headHtml += `<th>${app.name} <br><small class="text-muted">${app.masked_id}</small></th>`;
            });
            headHtml += '</tr>';
            thead.innerHTML = headHtml;

            // Render Body: Members and their states
            let bodyHtml = '';
            if (data.members.length === 0) {
                bodyHtml = `<tr><td colspan="${data.applicants.length + 1}" class="text-muted text-center py-3">No active panel members assigned to this position.</td></tr>`;
            } else {
                data.members.forEach(member => {
                    bodyHtml += `<tr>
                        <td class="text-start fw-bold">
                            ${member.name}
                            <div class="text-muted fw-normal" style="font-size:11px;">${member.role} (${member.type})</div>
                        </td>`;
                    
                    data.applicants.forEach(app => {
                        const state = data.matrix[member.id]?.[app.id] || 'Not Started';
                        let badgeClass = 'bg-secondary';
                        if (state === 'Scored') badgeClass = 'bg-success';
                        else if (state === 'In Progress') badgeClass = 'bg-warning text-dark';
                        
                        bodyHtml += `<td><span class="badge ${badgeClass}">${state}</span></td>`;
                    });
                    bodyHtml += '</tr>';
                });
            }
            tbody.innerHTML = bodyHtml;
        }

        window.addEventListener('resize', applyResponsiveDeliberation);
        document.addEventListener('DOMContentLoaded', applyResponsiveDeliberation);
    </script>

    <!-- Cropper Modal -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
    <div class="modal fade" id="cropperModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Crop ID Photo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0" style="background:#000;">
                    <div style="max-height: 400px; overflow: hidden;">
                        <img id="cropperImage" src="" style="max-width: 100%; display: block;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary fw-bold" id="btnSaveCrop">Save Photo</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let cropper = null;
        let cropperModalElement = null;
        let cropperModal = null;

        document.addEventListener('DOMContentLoaded', () => {
            cropperModalElement = document.getElementById('cropperModal');
            if (typeof bootstrap !== 'undefined') {
                cropperModal = new bootstrap.Modal(cropperModalElement);
            }
            
            cropperModalElement.addEventListener('hidden.bs.modal', () => {
                if (cropper) { cropper.destroy(); cropper = null; }
                document.getElementById('photoInput').value = '';
            });

            document.getElementById('btnSaveCrop').addEventListener('click', async () => {
                if (!cropper) return;
                
                const btn = document.getElementById('btnSaveCrop');
                btn.disabled = true;
                btn.innerText = 'Saving...';

                // Get cropped canvas
                const canvas = cropper.getCroppedCanvas({
                    width: 300,
                    height: 300,
                    minWidth: 300,
                    minHeight: 300,
                    imageSmoothingEnabled: true,
                    imageSmoothingQuality: 'high',
                });

                if (!canvas) {
                    alert('Could not crop image. Please try again.');
                    btn.disabled = false;
                    btn.innerText = 'Save Photo';
                    return;
                }

                // Compress as JPEG
                const base64Data = canvas.toDataURL('image/jpeg', 0.85);

                try {
                    const res = await fetch('{{ route('recruitment.deliberation.photo.upload', $applicant) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ photo_data: base64Data })
                    });
                    const json = await res.json();
                    if (res.ok && json.success) {
                        window.location.reload();
                    } else {
                        alert(json.error || 'Failed to upload photo.');
                    }
                } catch (err) {
                    alert('An error occurred during upload.');
                    console.error(err);
                } finally {
                    btn.disabled = false;
                    btn.innerText = 'Save Photo';
                }
            });
        });

        function handlePhotoSelect(event) {
            const files = event.target.files;
            if (!files || files.length === 0) return;
            const file = files[0];
            
            if (file.size > 5 * 1024 * 1024) {
                alert('File size must be under 5MB.');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('cropperImage');
                img.src = e.target.result;
                cropperModal.show();
                
                cropperModalElement.addEventListener('shown.bs.modal', function onShown() {
                    cropperModalElement.removeEventListener('shown.bs.modal', onShown);
                    cropper = new Cropper(img, {
                        aspectRatio: 1,
                        viewMode: 1,
                        dragMode: 'move',
                        autoCropArea: 1,
                        restore: false,
                        guides: true,
                        center: true,
                        highlight: false,
                        cropBoxMovable: true,
                        cropBoxResizable: true,
                        toggleDragModeOnDblclick: false,
                    });
                });
            };
            reader.readAsDataURL(file);
        }
    </script>
</x-dashboard-app>
