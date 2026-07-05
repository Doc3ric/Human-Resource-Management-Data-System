<x-dashboard-app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            HRMPSB Signatories
        </h2>
    </x-slot>

    <div class="content-wrapper p-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-1 text-primary"><i class="fas fa-signature me-2"></i>HRMPSB Signatories</h2>
            <p class="text-muted">Configure the panel signatories for the Management and Legislative teams.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-1"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <ul class="nav nav-tabs mb-4" id="signatoryTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="management-tab" data-bs-toggle="tab" data-bs-target="#management" type="button" role="tab" aria-controls="management" aria-selected="true">Management Team</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="legislative-tab" data-bs-toggle="tab" data-bs-target="#legislative" type="button" role="tab" aria-controls="legislative" aria-selected="false">Legislative Team</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="weightings-tab" data-bs-toggle="tab" data-bs-target="#weightings" type="button" role="tab" aria-controls="weightings" aria-selected="false">Score Weightings</button>
        </li>
    </ul>
    
    <div class="tab-content" id="signatoryTabsContent">
        <!-- Management Team -->
        <div class="tab-pane fade show active" id="management" role="tabpanel" aria-labelledby="management-tab">
            <form action="{{ route('recruitment.hrmpsb.signatories.store') }}" method="POST">
                @csrf
                <input type="hidden" name="team" value="management">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div id="management_list">
                            @php
                                // Ensure default roles exist if empty
                                $m_sigs = $management->count() ? $management : collect([
                                    (object)['id'=>'','role'=>'secretary','name'=>'','title'=>''],
                                    (object)['id'=>'','role'=>'member','name'=>'','title'=>''],
                                    (object)['id'=>'','role'=>'chairman','name'=>'','title'=>''],
                                    (object)['id'=>'','role'=>'appointing_authority','name'=>'','title'=>'']
                                ]);
                            @endphp
                            @foreach($m_sigs as $index => $sig)
                                <div class="row g-3 mb-3 align-items-end sig-row">
                                    <input type="hidden" name="signatories[{{$index}}][id]" value="{{ $sig->id ?? '' }}">
                                    <div class="col-md-3">
                                        <label class="form-label">Role</label>
                                        <select name="signatories[{{$index}}][role]" class="form-select" required>
                                            <option value="secretary" {{ $sig->role == 'secretary' ? 'selected' : '' }}>Secretary</option>
                                            <option value="member" {{ $sig->role == 'member' ? 'selected' : '' }}>Member</option>
                                            <option value="chairman" {{ $sig->role == 'chairman' ? 'selected' : '' }}>Chairman</option>
                                            <option value="appointing_authority" {{ $sig->role == 'appointing_authority' ? 'selected' : '' }}>Appointing Authority</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Name</label>
                                        <input type="text" name="signatories[{{$index}}][name]" class="form-control" value="{{ $sig->name }}" placeholder="e.g. HON. ROGELIO NEIL P. ROQUE">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Title / Position</label>
                                        <input type="text" name="signatories[{{$index}}][title]" class="form-control" value="{{ $sig->title }}" placeholder="e.g. Governor">
                                    </div>
                                    <div class="col-md-1 text-center">
                                        <button type="button" class="btn btn-danger btn-sm rounded-circle remove-btn"><i class="fas fa-times"></i></button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mb-4" onclick="addSigRow('management')"><i class="fas fa-plus me-1"></i>Add Signatory</button>
                        
                        <div class="text-end border-top pt-3">
                            <button type="submit" class="btn btn-success px-4"><i class="fas fa-save me-1"></i> Save Management Team</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Legislative Team -->
        <div class="tab-pane fade" id="legislative" role="tabpanel" aria-labelledby="legislative-tab">
            <form action="{{ route('recruitment.hrmpsb.signatories.store') }}" method="POST">
                @csrf
                <input type="hidden" name="team" value="legislative">
                <div class="card shadow-sm border-0">
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
                                <div class="row g-3 mb-3 align-items-end sig-row">
                                    <input type="hidden" name="signatories[{{$index}}][id]" value="{{ $sig->id ?? '' }}">
                                    <div class="col-md-3">
                                        <label class="form-label">Role</label>
                                        <select name="signatories[{{$index}}][role]" class="form-select" required>
                                            <option value="secretary" {{ $sig->role == 'secretary' ? 'selected' : '' }}>Secretary</option>
                                            <option value="member" {{ $sig->role == 'member' ? 'selected' : '' }}>Member</option>
                                            <option value="chairman" {{ $sig->role == 'chairman' ? 'selected' : '' }}>Chairman</option>
                                            <option value="appointing_authority" {{ $sig->role == 'appointing_authority' ? 'selected' : '' }}>Appointing Authority</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Name</label>
                                        <input type="text" name="signatories[{{$index}}][name]" class="form-control" value="{{ $sig->name }}" placeholder="e.g. HON. VICE GOVERNOR">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Title / Position</label>
                                        <input type="text" name="signatories[{{$index}}][title]" class="form-control" value="{{ $sig->title }}" placeholder="e.g. Vice Governor">
                                    </div>
                                    <div class="col-md-1 text-center">
                                        <button type="button" class="btn btn-danger btn-sm rounded-circle remove-btn"><i class="fas fa-times"></i></button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mb-4" onclick="addSigRow('legislative')"><i class="fas fa-plus me-1"></i>Add Signatory</button>
                        
                        <div class="text-end border-top pt-3">
                            <button type="submit" class="btn btn-success px-4"><i class="fas fa-save me-1"></i> Save Legislative Team</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Score Weightings -->
        <div class="tab-pane fade" id="weightings" role="tabpanel" aria-labelledby="weightings-tab">
            <form action="{{ route('recruitment.hrmpsb.weights.store') }}" method="POST">
                @csrf
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="mb-4">Interview Category Weightings</h5>
                        <p class="text-muted small mb-4">Adjust the percentage weight of each category. Ensure the total is exactly 100.</p>
                        
                        <div class="row mb-3 align-items-center">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Personal Appearance</label>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group">
                                    <input type="number" step="0.1" name="appearance" class="form-control" value="{{ $weights['appearance'] }}" required>
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3 align-items-center">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Knowledge on the Job & Organization</label>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group">
                                    <input type="number" step="0.1" name="knowledge" class="form-control" value="{{ $weights['knowledge'] }}" required>
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3 align-items-center">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Communication / Interpersonal</label>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group">
                                    <input type="number" step="0.1" name="communication" class="form-control" value="{{ $weights['communication'] }}" required>
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4 align-items-center">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Other Evaluation Criteria</label>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group">
                                    <input type="number" step="0.1" name="other" class="form-control" value="{{ $weights['other'] }}" required>
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>

                        <div class="text-end border-top pt-3">
                            <button type="submit" class="btn btn-success px-4"><i class="fas fa-save me-1"></i> Save Weightings</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
    <script>
    let counter = 100;
    function addSigRow(team) {
        counter++;
        const container = document.getElementById(team + '_list');
        const row = document.createElement('div');
        row.className = 'row g-3 mb-3 align-items-end sig-row';
        row.innerHTML = `
            <input type="hidden" name="signatories[${counter}][id]" value="">
            <div class="col-md-3">
                <label class="form-label">Role</label>
                <select name="signatories[${counter}][role]" class="form-select" required>
                    <option value="member">Member</option>
                    <option value="secretary">Secretary</option>
                    <option value="chairman">Chairman</option>
                    <option value="appointing_authority">Appointing Authority</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Name</label>
                <input type="text" name="signatories[${counter}][name]" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Title / Position</label>
                <input type="text" name="signatories[${counter}][title]" class="form-control">
            </div>
            <div class="col-md-1 text-center">
                <button type="button" class="btn btn-danger btn-sm rounded-circle remove-btn"><i class="fas fa-times"></i></button>
            </div>
        `;
        container.appendChild(row);
    }

    document.addEventListener('click', function(e) {
        if(e.target.closest('.remove-btn')) {
            e.target.closest('.sig-row').remove();
        }
    });
</script>
</div>
</x-dashboard-app>
