<x-dashboard-app>
<x-slot name="header">
    <h2 class="font-semibold text-xl leading-tight">HRMPSB System Settings</h2>
</x-slot>

<div class="content-wrapper p-4">

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-1"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Tabs --}}
<ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
    <li class="nav-item">
        <button class="nav-link active fw-semibold" id="tab-panel-btn" data-bs-toggle="tab" data-bs-target="#tab-panel" type="button">
            <i class="bi bi-people-fill me-1 text-primary"></i>Panel Interview Weights
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link fw-semibold" id="tab-twg-btn" data-bs-toggle="tab" data-bs-target="#tab-twg" type="button">
            <i class="bi bi-star-fill me-1 text-warning"></i>TWG Criterion Weights
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link fw-semibold" id="tab-scales-btn" data-bs-toggle="tab" data-bs-target="#tab-scales" type="button">
            <i class="bi bi-table me-1 text-success"></i>TWG Rating Scales
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link fw-semibold" id="tab-signatories-btn" data-bs-toggle="tab" data-bs-target="#tab-signatories" type="button">
            <i class="bi bi-pen-fill me-1 text-info"></i>Signatories
        </button>
    </li>
</ul>

<div class="tab-content">

{{-- ════════════════════════════════════════════════════════════
     TAB 1 — HRMPSB Panel Interview Category Weights
     ════════════════════════════════════════════════════════════ --}}
<div class="tab-pane fade show active" id="tab-panel" role="tabpanel">
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white fw-bold">
                    <i class="bi bi-sliders me-1"></i>HRMPSB Panel Interview — 18-Criteria Category Weights
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        These four categories make up the PSB Interview Rating (50 pts). The weights must total <strong>100%</strong>.
                        The category score for each panel member is then averaged to produce the consolidated interview score.
                    </p>
                    <form action="{{ route('recruitment.hrmpsb.weights.store') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            @php
                            $cats = [
                                'appearance'    => ['label'=>'I. Personal Appearance',             'desc'=>'1 criterion', 'color'=>'info'],
                                'knowledge'     => ['label'=>'II. Knowledge on the Job & Org.',   'desc'=>'4 criteria',  'color'=>'primary'],
                                'communication' => ['label'=>'III. Communication / Interpersonal', 'desc'=>'3 criteria',  'color'=>'success'],
                                'other'         => ['label'=>'IV. Other Evaluation Criteria',      'desc'=>'10 criteria', 'color'=>'warning'],
                            ];
                            @endphp
                            @foreach($cats as $key => $cat)
                            <div class="col-sm-6">
                                <label class="form-label fw-bold">
                                    {{ $cat['label'] }}
                                    <span class="badge bg-{{ $cat['color'] }} ms-1">{{ $cat['desc'] }}</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" max="100"
                                           name="{{ $key }}" class="form-control weight-input-panel"
                                           value="{{ $panelWeights[$key] }}"
                                           oninput="updatePanelTotal()">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            @endforeach
                            <div class="col-12">
                                <div class="d-flex align-items-center gap-3 mt-1">
                                    <div>Total: <strong id="panelTotal" class="text-primary fs-5">{{ array_sum($panelWeights) }}</strong>%
                                        <small id="panelTotalWarn" class="text-danger ms-2" style="display:none;">Must equal 100%</small>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm fw-bold ms-auto">
                                        <i class="bi bi-save me-1"></i>Save Panel Weights
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-bold border-bottom">
                    <i class="bi bi-info-circle me-1 text-primary"></i>How Panel Scores Feed Into the TWG
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="fw-bold">PSB Interview (Panel Average × 0.50)</small>
                            <small class="text-muted">50 pts</small>
                        </div>
                        <div class="progress" style="height:10px;">
                            <div class="progress-bar bg-primary" style="width:50%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="fw-bold">TWG Credential Criteria</small>
                            <small class="text-muted">50 pts</small>
                        </div>
                        <div class="progress" style="height:10px;">
                            <div class="progress-bar bg-warning" style="width:50%"></div>
                        </div>
                    </div>
                    <div class="alert alert-light border p-2 mb-0">
                        <small>
                            <strong>Grand Total = 100 pts</strong><br>
                            Panel evaluation average is expressed as a percentage (0–100%) then multiplied by 0.50 to give the 50-pt PSB Interview Rating that the TWG enters in the consolidated score sheet.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════
     TAB 2 — TWG Criterion Max Points
     ════════════════════════════════════════════════════════════ --}}
<div class="tab-pane fade" id="tab-twg" role="tabpanel">
    <form action="{{ route('recruitment.hrmpsb.twg_weights.store') }}" method="POST">
        @csrf
        <div class="row g-4">

            {{-- With Eligibility --}}
            <div class="col-lg-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white fw-bold">
                        <i class="bi bi-award me-1"></i>With Eligibility — Criterion Max Points
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Grand total must equal <strong>100 pts</strong>.</p>
                        @php
                        $eligCriteria = [
                            ['twg_max_psb_interview',         'PSB Interview Rating (Panel Score)', 'primary', '50'],
                            ['twg_max_ipcr',                  'IPCR Rating',                        'info',    '10'],
                            ['twg_max_awards',                'Awards & Recognition',               'success', '5'],
                            ['twg_max_education_eligibility', 'Education (1st & 2nd Level)',        'warning', '15'],
                            ['twg_max_experience',            'Relevant Experience',                'secondary','10'],
                            ['twg_max_training',              'Relevant Training',                  'dark',    '10'],
                        ];
                        @endphp
                        @foreach($eligCriteria as [$key, $label, $color, $default])
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size:13px;">{{ $label }}</label>
                            <div class="input-group input-group-sm">
                                <input type="number" step="0.01" min="0" max="100"
                                       name="{{ $key }}" class="form-control weight-input-elig"
                                       value="{{ $twgWeights[$key] ?? $default }}"
                                       oninput="updateEligTotal()">
                                <span class="input-group-text fw-bold">pts</span>
                            </div>
                        </div>
                        @endforeach
                        <div class="d-flex align-items-center border-top pt-2 mt-2">
                            <span class="fw-bold">Total: <span id="eligTotal" class="text-primary">{{ array_sum([$twgWeights['psb_interview'] ?? 50,$twgWeights['ipcr'] ?? 10,$twgWeights['awards'] ?? 5,$twgWeights['education_eligibility'] ?? 15,$twgWeights['experience'] ?? 10,$twgWeights['training'] ?? 10]) }}</span> pts</span>
                            <small id="eligTotalWarn" class="text-danger ms-2" style="display:none;">Should be 100</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Without Eligibility --}}
            <div class="col-lg-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-warning fw-bold">
                        <i class="bi bi-award-fill me-1"></i>No Eligibility — Criterion Max Points
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Grand total must equal <strong>100 pts</strong>.</p>
                        @php
                        $noeligCriteria = [
                            ['twg_max_psb_interview',              'PSB Interview Rating (Panel Score)', 'primary',   '50'],
                            ['twg_max_ipcr',                       'IPCR Rating',                        'info',      '10'],
                            ['twg_max_awards',                     'Awards & Recognition',               'success',   '5'],
                            ['twg_max_education_no_eligibility',   'Education',                          'warning',   '20'],
                            ['twg_max_length_of_service',          'Length of Service in PGB',           'secondary', '15'],
                        ];
                        @endphp
                        @foreach($noeligCriteria as [$key, $label, $color, $default])
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size:13px;">{{ $label }}</label>
                            <div class="input-group input-group-sm">
                                <input type="number" step="0.01" min="0" max="100"
                                       name="{{ $key }}" class="form-control weight-input-noelig"
                                       value="{{ $twgWeights[$key] ?? $default }}"
                                       oninput="updateNoeligTotal()">
                                <span class="input-group-text fw-bold">pts</span>
                            </div>
                        </div>
                        @endforeach
                        <div class="d-flex align-items-center border-top pt-2 mt-2">
                            <span class="fw-bold">Total: <span id="noeligTotal" class="text-warning">{{ array_sum([$twgWeights['psb_interview'] ?? 50,$twgWeights['ipcr'] ?? 10,$twgWeights['awards'] ?? 5,$twgWeights['education_no_eligibility'] ?? 20,$twgWeights['length_of_service'] ?? 15]) }}</span> pts</span>
                            <small id="noeligTotalWarn" class="text-danger ms-2" style="display:none;">Should be 100</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="alert alert-info small mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    <strong>Note:</strong> PSB Interview and IPCR max points are shared across both categories. Changing them here updates both. The Education max differs by category (15 for Eligibility, 20 for No Eligibility). Demerits are deductions and have no max-points cap (the suspension max is 5.00, but accumulated warnings can add more).
                </div>
                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-primary fw-bold">
                        <i class="bi bi-save me-1"></i>Save TWG Criterion Weights
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- ════════════════════════════════════════════════════════════
     TAB 3 — TWG Rating Scales Reference Table
     ════════════════════════════════════════════════════════════ --}}
<div class="tab-pane fade" id="tab-scales" role="tabpanel">

    {{-- Add new scale --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-bottom fw-bold">
            <i class="bi bi-plus-circle me-1 text-primary"></i>Add / Override a Rating Scale Row
        </div>
        <div class="card-body">
            <form action="{{ route('recruitment.hrmpsb.settings.store') }}" method="POST">
                @csrf
                <div class="row g-2 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Category</label>
                        <select name="position_category" class="form-select form-select-sm" required>
                            <option value="all">All / Both</option>
                            <option value="eligibility">With Eligibility</option>
                            <option value="no_eligibility">No Eligibility</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Criterion</label>
                        <select name="criterion" class="form-select form-select-sm" required>
                            <option value="ipcr">IPCR Rating</option>
                            <option value="awards">Awards & Recognition</option>
                            <option value="education">Education</option>
                            <option value="experience">Relevant Experience</option>
                            <option value="training">Relevant Training</option>
                            <option value="length_of_service">Length of Service</option>
                            <option value="demerits_suspension">Demerits – Suspension</option>
                            <option value="demerits_reprimand">Demerits – Reprimand</option>
                            <option value="demerits_stern_warning">Demerits – Stern Warning</option>
                            <option value="demerits_warning_memo">Demerits – Warning/Memo</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label small fw-bold">Level</label>
                        <select name="level" class="form-select form-select-sm">
                            <option value="">N/A</option>
                            <option value="first_level">1st Level</option>
                            <option value="second_level">2nd Level</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Condition / Descriptor</label>
                        <input type="text" name="condition_name" class="form-control form-control-sm" placeholder="e.g. Doctorate Degree">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label small fw-bold">Min Value</label>
                        <input type="number" step="0.01" name="min_value" class="form-control form-control-sm" placeholder="4.90">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label small fw-bold">Max Value</label>
                        <input type="number" step="0.01" name="max_value" class="form-control form-control-sm" placeholder="4.99">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label small fw-bold">Points</label>
                        <input type="number" step="0.01" name="points" class="form-control form-control-sm" required placeholder="9.50">
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary btn-sm w-100">Add</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Rating scales grouped by criterion --}}
    @php
    $criterionLabels = [
        'ipcr'                  => ['label'=>'IPCR Rating',             'color'=>'primary',   'icon'=>'bi-graph-up'],
        'awards'                => ['label'=>'Awards & Recognition',    'color'=>'success',   'icon'=>'bi-trophy-fill'],
        'education'             => ['label'=>'Education',               'color'=>'warning',   'icon'=>'bi-mortarboard-fill'],
        'experience'            => ['label'=>'Relevant Experience',     'color'=>'info',      'icon'=>'bi-briefcase-fill'],
        'training'              => ['label'=>'Relevant Training',       'color'=>'secondary', 'icon'=>'bi-book-fill'],
        'length_of_service'     => ['label'=>'Length of Service (PGB)', 'color'=>'dark',      'icon'=>'bi-calendar3'],
        'demerits_suspension'   => ['label'=>'Demerits – Suspension',   'color'=>'danger',    'icon'=>'bi-slash-circle-fill'],
        'demerits_reprimand'    => ['label'=>'Demerits – Reprimand',    'color'=>'danger',    'icon'=>'bi-dash-circle'],
        'demerits_stern_warning'=> ['label'=>'Demerits – Stern Warning','color'=>'danger',    'icon'=>'bi-dash-circle'],
        'demerits_warning_memo' => ['label'=>'Demerits – Warning/Memo', 'color'=>'danger',    'icon'=>'bi-dash-circle'],
    ];
    @endphp

    <div class="row g-3">
        @foreach($criterionLabels as $crit => $meta)
        @if(isset($scales[$crit]) && $scales[$crit]->count())
        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-{{ $meta['color'] }} {{ in_array($meta['color'],['primary','danger','secondary','dark','info']) ? 'text-white' : '' }} py-2">
                    <i class="bi {{ $meta['icon'] }} me-1"></i>{{ $meta['label'] }}
                    <span class="badge bg-white text-dark ms-2">{{ $scales[$crit]->count() }} rows</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0" style="font-size:12px;">
                        <thead class="table-light">
                            <tr>
                                <th>Category</th>
                                <th>Level</th>
                                <th>Descriptor</th>
                                <th>Range</th>
                                <th class="text-end">Points</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($scales[$crit] as $row)
                        <tr>
                            <td>
                                <span class="badge bg-secondary" style="font-size:10px;">
                                    {{ $row->position_category === 'all' ? 'BOTH' : strtoupper(str_replace('_',' ',$row->position_category)) }}
                                </span>
                            </td>
                            <td>{{ $row->level ? str_replace('_',' ',ucfirst($row->level)) : '—' }}</td>
                            <td>{{ $row->condition_name ?: '—' }}</td>
                            <td class="text-muted">
                                @if($row->min_value !== null || $row->max_value !== null)
                                    {{ $row->min_value ?? '?' }} – {{ $row->max_value ?? '∞' }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-end fw-bold text-primary">{{ number_format($row->points, 2) }}</td>
                            <td>
                                <form action="{{ route('recruitment.hrmpsb.settings.destroy', $row->id) }}" method="POST" onsubmit="return confirm('Delete this row?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-link btn-sm text-danger p-0"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
        @endforeach
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════
     TAB 4 — Signatories
     ════════════════════════════════════════════════════════════ --}}
<div class="tab-pane fade" id="tab-signatories" role="tabpanel">
    <div class="row g-4">
        <div class="col-lg-6">
            <form action="{{ route('recruitment.hrmpsb.signatories.store') }}" method="POST">
                @csrf
                <input type="hidden" name="team" value="management">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white fw-bold">
                        <i class="bi bi-building me-1"></i>Management Team
                    </div>
                    <div class="card-body">
                        <div id="management_list">
                            @php
                                $m_sigs = $management->count() ? $management : collect([
                                    (object)['id'=>'','role'=>'secretary','name'=>'','title'=>''],
                                    (object)['id'=>'','role'=>'member','name'=>'','title'=>''],
                                    (object)['id'=>'','role'=>'chairman','name'=>'','title'=>''],
                                    (object)['id'=>'','role'=>'appointing_authority','name'=>'','title'=>'']
                                ]);
                            @endphp
                            @foreach($m_sigs as $index => $sig)
                                <div class="row g-2 mb-2 align-items-end sig-row">
                                    <input type="hidden" name="signatories[{{$index}}][id]" value="{{ $sig->id ?? '' }}">
                                    <div class="col-12 col-md-4">
                                        <label class="form-label small text-muted mb-1">Role</label>
                                        <select name="signatories[{{$index}}][role]" class="form-select form-select-sm" required>
                                            <option value="secretary" {{ $sig->role == 'secretary' ? 'selected' : '' }}>Secretary</option>
                                            <option value="member" {{ $sig->role == 'member' ? 'selected' : '' }}>Member</option>
                                            <option value="chairman" {{ $sig->role == 'chairman' ? 'selected' : '' }}>Chairman</option>
                                            <option value="appointing_authority" {{ $sig->role == 'appointing_authority' ? 'selected' : '' }}>Appointing Authority</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-7">
                                        <label class="form-label small text-muted mb-1">Name</label>
                                        <input type="text" name="signatories[{{$index}}][name]" class="form-control form-control-sm mb-1" value="{{ $sig->name }}" placeholder="Name">
                                        <input type="text" name="signatories[{{$index}}][title]" class="form-control form-control-sm" value="{{ $sig->title }}" placeholder="Title / Position">
                                    </div>
                                    <div class="col-12 col-md-1 text-center">
                                        <button type="button" class="btn btn-outline-danger btn-sm rounded remove-btn"><i class="bi bi-trash"></i></button>
                                    </div>
                                </div>
                                <hr class="my-2">
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mb-3" onclick="addSigRow('management')"><i class="bi bi-plus me-1"></i>Add</button>
                        
                        <div class="text-end border-top pt-3">
                            <button type="submit" class="btn btn-success"><i class="bi bi-save me-1"></i> Save Management Team</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-lg-6">
            <form action="{{ route('recruitment.hrmpsb.signatories.store') }}" method="POST">
                @csrf
                <input type="hidden" name="team" value="legislative">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-success text-white fw-bold">
                        <i class="bi bi-bank me-1"></i>Legislative Team
                    </div>
                    <div class="card-body">
                        <div id="legislative_list">
                            @php
                                $l_sigs = $legislative->count() ? $legislative : collect([
                                    (object)['id'=>'','role'=>'secretary','name'=>'','title'=>''],
                                    (object)['id'=>'','role'=>'member','name'=>'','title'=>''],
                                    (object)['id'=>'','role'=>'chairman','name'=>'','title'=>''],
                                    (object)['id'=>'','role'=>'appointing_authority','name'=>'','title'=>'']
                                ]);
                            @endphp
                            @foreach($l_sigs as $index => $sig)
                                <div class="row g-2 mb-2 align-items-end sig-row">
                                    <input type="hidden" name="signatories[{{$index}}][id]" value="{{ $sig->id ?? '' }}">
                                    <div class="col-12 col-md-4">
                                        <label class="form-label small text-muted mb-1">Role</label>
                                        <select name="signatories[{{$index}}][role]" class="form-select form-select-sm" required>
                                            <option value="secretary" {{ $sig->role == 'secretary' ? 'selected' : '' }}>Secretary</option>
                                            <option value="member" {{ $sig->role == 'member' ? 'selected' : '' }}>Member</option>
                                            <option value="chairman" {{ $sig->role == 'chairman' ? 'selected' : '' }}>Chairman</option>
                                            <option value="appointing_authority" {{ $sig->role == 'appointing_authority' ? 'selected' : '' }}>Appointing Authority</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-7">
                                        <label class="form-label small text-muted mb-1">Name</label>
                                        <input type="text" name="signatories[{{$index}}][name]" class="form-control form-control-sm mb-1" value="{{ $sig->name }}" placeholder="Name">
                                        <input type="text" name="signatories[{{$index}}][title]" class="form-control form-control-sm" value="{{ $sig->title }}" placeholder="Title / Position">
                                    </div>
                                    <div class="col-12 col-md-1 text-center">
                                        <button type="button" class="btn btn-outline-danger btn-sm rounded remove-btn"><i class="bi bi-trash"></i></button>
                                    </div>
                                </div>
                                <hr class="my-2">
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mb-3" onclick="addSigRow('legislative')"><i class="bi bi-plus me-1"></i>Add</button>
                        
                        <div class="text-end border-top pt-3">
                            <button type="submit" class="btn btn-success"><i class="bi bi-save me-1"></i> Save Legislative Team</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

</div>{{-- tab-content --}}
</div>{{-- content-wrapper --}}

<script>
function updatePanelTotal() {
    let t = 0;
    document.querySelectorAll('.weight-input-panel').forEach(i => { t += parseFloat(i.value) || 0; });
    document.getElementById('panelTotal').textContent = t.toFixed(2);
    document.getElementById('panelTotalWarn').style.display = Math.abs(t - 100) > 0.01 ? 'inline' : 'none';
}
function updateEligTotal() {
    let t = 0;
    document.querySelectorAll('.weight-input-elig').forEach(i => { t += parseFloat(i.value) || 0; });
    document.getElementById('eligTotal').textContent = t.toFixed(2);
    document.getElementById('eligTotalWarn').style.display = Math.abs(t - 100) > 0.01 ? 'inline' : 'none';
}
function updateNoeligTotal() {
    let t = 0;
    document.querySelectorAll('.weight-input-noelig').forEach(i => { t += parseFloat(i.value) || 0; });
    document.getElementById('noeligTotal').textContent = t.toFixed(2);
    document.getElementById('noeligTotalWarn').style.display = Math.abs(t - 100) > 0.01 ? 'inline' : 'none';
}

function addSigRow(team) {
    let counter = Math.floor(Math.random() * 10000);
    const container = document.getElementById(team + '_list');
    const row = document.createElement('div');
    row.className = 'row g-2 mb-2 align-items-end sig-row';
    row.innerHTML = `
        <input type="hidden" name="signatories[${counter}][id]" value="">
        <div class="col-12 col-md-4">
            <label class="form-label small text-muted mb-1">Role</label>
            <select name="signatories[${counter}][role]" class="form-select form-select-sm" required>
                <option value="member">Member</option>
                <option value="secretary">Secretary</option>
                <option value="chairman">Chairman</option>
                <option value="appointing_authority">Appointing Authority</option>
            </select>
        </div>
        <div class="col-12 col-md-7">
            <label class="form-label small text-muted mb-1">Name</label>
            <input type="text" name="signatories[${counter}][name]" class="form-control form-control-sm mb-1" placeholder="Name">
            <input type="text" name="signatories[${counter}][title]" class="form-control form-control-sm" placeholder="Title / Position">
        </div>
        <div class="col-12 col-md-1 text-center">
            <button type="button" class="btn btn-outline-danger btn-sm rounded remove-btn"><i class="bi bi-trash"></i></button>
        </div>
    `;
    container.appendChild(row);
    const hr = document.createElement('hr');
    hr.className = 'my-2';
    container.appendChild(hr);
}

document.addEventListener('click', function(e) {
    if(e.target.closest('.remove-btn')) {
        let row = e.target.closest('.sig-row');
        if(row.nextElementSibling && row.nextElementSibling.tagName === 'HR') row.nextElementSibling.remove();
        row.remove();
    }
});
</script>
</x-dashboard-app>
