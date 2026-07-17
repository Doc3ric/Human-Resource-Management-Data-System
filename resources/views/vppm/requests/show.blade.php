@php
    $r = $publicationRequest;
    $v = $r->vacantPosition;
@endphp
<x-dashboard-app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            CS Form No. 9 — {{ $v->position_title }} <small class="text-muted">(v{{ $r->version_no }})</small>
        </h2>
    </x-slot>

    <div class="content-wrapper p-4">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <span class="badge bg-dark fs-6">{{ $r->status }}</span>
                @if($r->supersedes)
                    <span class="text-muted small ms-2">Republication of <a href="{{ route('vppm.requests.show', $r->supersedes) }}">v{{ $r->supersedes->version_no }}</a> — {{ $r->edit_reason }}</span>
                @endif
            </div>
            <div>
                <a href="{{ route('vppm.vacancies.show', $v) }}" class="btn btn-sm btn-outline-secondary">Back to Vacancy</a>
                <a href="{{ route('vppm.requests.cs-form-9', $r) }}" target="_blank" class="btn btn-sm btn-outline-dark"><i class="bi bi-file-pdf"></i> CS Form No. 9 PDF</a>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-body" style="font-size:13px;">
                <div class="row">
                    <div class="col-md-4"><b>Agency Contact:</b> {{ $r->agency_contact_person }} ({{ $r->agency_contact_number }}, {{ $r->agency_contact_email }})</div>
                    <div class="col-md-4"><b>Submission Mode:</b> {{ $r->submission_mode }}</div>
                    <div class="col-md-4"><b>Posting Period:</b> {{ $r->posting_min_required_days }} days min.</div>
                    <div class="col-md-4"><b>Posting Start:</b> {{ $r->posting_start_date?->format('M d, Y') ?? '—' }}</div>
                    <div class="col-md-4"><b>Validity End:</b> {{ $r->validity_end_date?->format('M d, Y') ?? '—' }}</div>
                    @if($r->days_to_expiry !== null)<div class="col-md-4"><b>Days to Expiry:</b> {{ $r->days_to_expiry }}</div>@endif
                </div>
            </div>
        </div>

        {{-- Step 2/3: Draft -> Pending Signature -> Signed -> Submit to CSC FO --}}
        @if($r->status === 'DRAFT')
            <form method="POST" action="{{ route('vppm.requests.pending-signature', $r) }}">
                @csrf
                <button class="btn btn-primary btn-sm">Route for Department Head Signature</button>
            </form>
        @endif

        @if($r->status === 'PENDING_SIGNATURE')
            <div class="card shadow-sm mb-3" x-data="{}">
                <div class="card-body">
                    <h6 class="fw-bold">Department Head Signature (CS Form No. 11 s.2025)</h6>
                    @if($r->is_signed)
                        <div class="alert alert-success py-1 px-2 small">Signed by {{ $r->signatures->last()->signatory_name }} on {{ $r->signatures->last()->signed_at->format('M d, Y H:i') }}</div>
                    @else
                        <form method="POST" action="{{ route('vppm.requests.sign', $r) }}">
                            @csrf
                            <input name="signatory_name" required placeholder="Signatory full name" class="form-control form-control-sm mb-2">
                            <input name="signatory_position" placeholder="Signatory position" class="form-control form-control-sm mb-2">
                            <x-signature-pad input-name="signature_image" />
                            <button class="btn btn-sm btn-primary mt-2">Capture Signature</button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <h6 class="fw-bold">Submit to CSC Field Office</h6>
                    @unless($r->is_signed)
                        <p class="text-muted small">Blocked — CS Form No. 9 must be signed first.</p>
                    @endunless
                    <form method="POST" action="{{ route('vppm.requests.submit-csc-fo', $r) }}" enctype="multipart/form-data" class="row g-2">
                        @csrf
                        <div class="col-md-4"><input type="date" name="submitted_to_csc_fo_date" required class="form-control form-control-sm"></div>
                        <div class="col-md-6"><input type="file" name="csc_fo_receiving_copy" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png"></div>
                        <div class="col-md-2"><button class="btn btn-sm btn-primary w-100" @disabled(!$r->is_signed)>Submit</button></div>
                    </form>
                </div>
            </div>
        @endif

        {{-- Step 5: 3-site posting checklist --}}
        @if(in_array($r->status, ['SUBMITTED_TO_CSC_FO', 'POSTED', 'PUBLICATION_ACTIVE', 'PUBLICATION_DEFICIENT']))
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <h6 class="fw-bold">Conspicuous-Place Posting Checklist ({{ $r->postingSiteLogs->count() }}/3)</h6>
                    <table class="table table-sm">
                        <thead><tr><th>Site</th><th>Posted</th><th>Proof</th><th>By</th></tr></thead>
                        <tbody>
                            @foreach($r->postingSiteLogs as $log)
                                <tr>
                                    <td>{{ $log->site_label }}</td>
                                    <td>{{ $log->posted_date->format('M d, Y') }}</td>
                                    <td>@if($log->proof_photo_path)<a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($log->proof_photo_path) }}" target="_blank">View</a>@else — @endif</td>
                                    <td>{{ $log->postedBy->name ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if($r->postingSiteLogs->count() < 3)
                        <form method="POST" action="{{ route('vppm.requests.posting-sites.store', $r) }}" enctype="multipart/form-data" class="row g-2">
                            @csrf
                            <div class="col-md-4">
                                <input name="site_label" required list="siteOptions" placeholder="Site label" class="form-control form-control-sm">
                                <datalist id="siteOptions">
                                    <option value="PHRMO Bulletin Board"><option value="Provincial Capitol Lobby"><option value="Agency Website">
                                </datalist>
                            </div>
                            <div class="col-md-3"><input type="date" name="posted_date" required class="form-control form-control-sm"></div>
                            <div class="col-md-4"><input type="file" name="proof_photo" accept="image/*" class="form-control form-control-sm"></div>
                            <div class="col-md-1"><button class="btn btn-sm btn-primary w-100">Log</button></div>
                        </form>
                    @endif
                </div>
            </div>
        @endif

        @if($r->status === 'PUBLICATION_DEFICIENT')
            <div class="alert alert-danger">Minimum posting period elapsed without all 3 sites confirmed — this blocks downstream HRMPSB deliberation start. Log the remaining site(s) above; status will self-correct on the next daily check.</div>
        @endif

        @if(in_array($r->status, ['VALID', 'NEAR_EXPIRY']))
            <form method="POST" action="{{ route('vppm.requests.mark-filled', $r) }}" class="mb-3" onsubmit="return confirm('Mark this vacancy as filled?');">
                @csrf
                <button class="btn btn-sm btn-success">Mark Filled (Appointment Issued)</button>
            </form>
        @endif

        {{-- Sec.26 edit rules — only meaningful once submitted --}}
        @unless($r->can_edit_in_place)
            <div class="row">
                <div class="col-md-6">
                    <div class="card shadow-sm mb-3">
                        <div class="card-body">
                            <h6 class="fw-bold">Sec.26 Exempt Correction <small class="text-muted">(spelling / missing parenthetical title only)</small></h6>
                            <form method="POST" action="{{ route('vppm.requests.exempt-edit', $r) }}">
                                @csrf
                                <input name="position_title" placeholder="Corrected Position Title" class="form-control form-control-sm mb-2" value="{{ $v->position_title }}">
                                <input name="parenthetical_title" placeholder="Corrected Parenthetical Title" class="form-control form-control-sm mb-2" value="{{ $v->parenthetical_title }}">
                                <input name="note" required placeholder="Correction note *" class="form-control form-control-sm mb-2">
                                <button class="btn btn-sm btn-outline-primary">Apply (No Republication)</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm mb-3">
                        <div class="card-body">
                            <h6 class="fw-bold">Republish <small class="text-muted">(any other field correction — resets posting clock)</small></h6>
                            <form method="POST" action="{{ route('vppm.requests.republish', $r) }}">
                                @csrf
                                <input name="agency_contact_person" required placeholder="Agency Contact Person *" class="form-control form-control-sm mb-2" value="{{ $r->agency_contact_person }}">
                                <input name="agency_contact_number" required placeholder="Contact Number *" class="form-control form-control-sm mb-2" value="{{ $r->agency_contact_number }}">
                                <input type="email" name="agency_contact_email" required placeholder="Contact Email *" class="form-control form-control-sm mb-2" value="{{ $r->agency_contact_email }}">
                                <input name="edit_reason" required placeholder="Reason for edit *" class="form-control form-control-sm mb-2">
                                <button class="btn btn-sm btn-outline-warning">Republish as New Version</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endunless

        @unless(in_array($r->status, ['FILLED', 'CANCELLED']))
            <form method="POST" action="{{ route('vppm.requests.cancel', $r) }}" onsubmit="return confirm('Cancel this publication request?');">
                @csrf
                <input name="reason" required placeholder="Cancellation reason *" class="form-control form-control-sm d-inline-block w-auto me-2" style="min-width:260px;">
                <button class="btn btn-sm btn-outline-danger">Cancel Request</button>
            </form>
        @endunless
    </div>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</x-dashboard-app>
