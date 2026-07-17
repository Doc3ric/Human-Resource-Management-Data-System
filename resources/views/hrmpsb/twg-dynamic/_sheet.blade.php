{{-- Module 6.1-6.4 scoring sheet — shared by the standalone picker page
     (hrmpsb/twg-dynamic/create.blade.php) and the Module 5 split-screen
     deliberation workspace right panel, so the two never drift apart.
     Expects: $applicant, $scores, $bracketOptions, $submission, $history, $totals. --}}
@php
    $blindId = \App\Support\BlindScoringId::forApplicant($applicant);
    $hasPhoto = app(\App\Support\PhotoEnforcementService::class)->hasValidPhoto($applicant);
    $isLocked = $submission?->isLocked() ?? false;
@endphp

<div class="card border-0 shadow-sm mb-4">
    <div style="background:linear-gradient(135deg,#082977 0%,#0f172a 100%);padding:18px 24px;border-radius:8px 8px 0 0;color:#fff;display:flex;justify-content:space-between;align-items:center;">
        <div>
            <div style="font-size:20px;font-weight:800;">TWG Rating — {{ $blindId }}</div>
            <div style="font-size:12px;opacity:.75;">Item No: {{ $applicant->item_no ?: '—' }} &middot; {{ $applicant->position_applied ?: 'N/A' }}</div>
            <div style="font-size:10px;opacity:.6;margin-top:2px;">Ref: 2025 ORAOHRA Rule IX &amp; CSC MC No. 03 s.2001</div>
        </div>
        @if($isLocked)
            <span class="badge bg-danger" style="font-size:11px;padding:6px 10px;">SUBMITTED &amp; LOCKED</span>
        @else
            <span class="badge bg-warning text-dark" style="font-size:11px;padding:6px 10px;">DRAFT</span>
        @endif
    </div>
    <div class="card-body">
        @if(!$hasPhoto)
            <div style="background:#fef2f2;color:#991b1b;padding:10px 14px;border-radius:8px;margin-bottom:14px;font-size:13px;">
                {{ \App\Support\PhotoEnforcementService::BLOCK_MESSAGE }}
            </div>
        @endif

        @if($isLocked)
            <div style="background:#fffbeb;color:#92400e;padding:10px 14px;border-radius:8px;margin-bottom:14px;font-size:13px;display:flex;justify-content:space-between;align-items:center;">
                <span>This evaluation was submitted on {{ $submission->submitted_at?->format('M d, Y h:i A') }} and is locked. Only an HRMPSB Chairperson may unlock it.</span>
                <form method="POST" action="{{ route('recruitment.hrmpsb.twg_dynamic.unlock', $applicant) }}" onsubmit="return confirm('Unlock this evaluation for editing? This will be logged.');">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-dark">Unlock</button>
                </form>
            </div>
        @endif

        <form method="POST" action="{{ route('recruitment.hrmpsb.twg_dynamic.store', $applicant) }}" id="dynamicScoreForm">
            @csrf
            <div class="table-responsive">
                <table class="table table-bordered align-middle" style="font-size:13px;">
                    <thead style="background:#f8fafc;">
                        <tr>
                            <th>Criterion</th>
                            <th style="width:90px;">Max Points</th>
                            <th>Basis / Description</th>
                            <th style="width:130px;">Auto-Populated</th>
                            <th style="width:140px;">Assessor Score</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($scores as $score)
                            @php $options = $bracketOptions[$score->id] ?? collect(); @endphp
                            <tr style="{{ $score->is_edited ? 'border-left:3px solid var(--color-accent,#2563eb);' : '' }}">
                                <td>
                                    {{ $score->criterion->criterion_name }}
                                    @if($score->is_edited)
                                        <i class="bi bi-pencil-fill" title="Manually adjusted from the auto-populated value" style="color:#2563eb;"></i>
                                    @endif
                                </td>
                                <td>{{ number_format($score->criterion->point_value, 2) }}</td>
                                <td style="font-size:12px;color:#6b7280;">{{ $score->criterion->score_basis_description }}</td>
                                <td>
                                    @if($options->isNotEmpty())
                                        <select name="scores[{{ $score->id }}][bracket_points]" class="form-select form-select-sm" {{ $isLocked ? 'disabled' : '' }}>
                                            <option value="">— Select —</option>
                                            @foreach($options as $opt)
                                                <option value="{{ $opt->points }}" {{ (float) $score->auto_populated_value === (float) $opt->points ? 'selected' : '' }}>
                                                    {{ $opt->condition_name ?? $opt->points }} ({{ $opt->points }})
                                                </option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input type="text" class="form-control form-control-sm" style="background:#f8fafc;color:#1e293b;font-weight:600;" readonly
                                               value="{{ $score->auto_populated_value !== null ? number_format($score->auto_populated_value, 2) : '—' }}">
                                    @endif
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" max="{{ $score->criterion->point_value }}"
                                           name="scores[{{ $score->id }}][assessor_value]"
                                           class="form-control form-control-sm"
                                           value="{{ old("scores.{$score->id}.assessor_value", $score->assessor_value ?? $score->auto_populated_value ?? '') }}"
                                           {{ $isLocked ? 'disabled' : '' }}>
                                </td>
                                <td>
                                    <input type="text" name="scores[{{ $score->id }}][notes]" class="form-control form-control-sm"
                                           value="{{ old("scores.{$score->id}.notes", $score->assessor_notes) }}" {{ $isLocked ? 'disabled' : '' }}>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot style="background:#f8fafc;font-weight:700;">
                        <tr>
                            <td colspan="3" class="text-end">Totals</td>
                            <td>{{ number_format($totals['total_auto'], 2) }}</td>
                            <td>
                                {{ number_format($totals['total_assessor'], 2) }}
                                @if($totals['edited_count'] > 0)
                                    <span class="badge bg-primary" style="font-size:10px;">{{ $totals['edited_count'] }} edited</span>
                                @endif
                            </td>
                            <td>Diff: {{ number_format($totals['total_assessor'] - $totals['total_auto'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @unless($isLocked)
                <button type="submit" class="btn btn-outline-primary btn-sm mb-4"><i class="bi bi-save"></i> Save as Draft</button>
            @endunless
        </form>

        <div class="card border-0 shadow-sm mb-4" style="background:#f8fafc;">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4"><strong>Cumulative Score:</strong> {{ number_format($totals['total_assessor'], 2) }} / {{ number_format($totals['max_possible'], 2) }}</div>
                    <div class="col-md-4"><strong>Percentage:</strong> {{ number_format($totals['percentage'], 2) }}%</div>
                    <div class="col-md-4"><strong>Classification:</strong> {{ $totals['adjectival_classification'] }}</div>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('recruitment.hrmpsb.twg_dynamic.submit', $applicant) }}" id="submitForm">
            @csrf
            <div class="mb-3">
                <label class="form-label">Overall Assessment Notes</label>
                <textarea name="overall_notes" class="form-control" rows="2" {{ $isLocked ? 'disabled' : '' }}>{{ old('overall_notes', $submission->overall_notes ?? '') }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Assessor Recommendation</label>
                <select name="recommendation" class="form-select" {{ $isLocked ? 'disabled' : '' }} required>
                    <option value="">-- Select --</option>
                    @foreach(\App\Support\TwgDynamicScoringService::RECOMMENDATIONS as $rec)
                        <option value="{{ $rec }}" {{ old('recommendation', $submission->recommendation ?? '') === $rec ? 'selected' : '' }}>{{ $rec }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="certCheck" name="certification_accepted" value="1"
                       {{ $isLocked ? 'disabled checked' : '' }} onchange="document.getElementById('submitBtn').disabled = !this.checked || {{ $hasPhoto ? 'false' : 'true' }};">
                <label class="form-check-label" for="certCheck" style="font-size:12.5px;">
                    I certify that this evaluation was conducted in accordance with the approved TWG Rating Criteria and the 2025 ORAOHRA Rule IX, and that all scores are based on submitted and verified documents. (Subject to R.A. 6713 Code of Conduct)
                </label>
            </div>
            <button type="submit" id="submitBtn" class="btn btn-primary" disabled>
                <i class="bi bi-lock-fill"></i> Submit &amp; Lock Evaluation
            </button>
        </form>

        <div class="mt-4">
            <h6 class="fw-bold">Evaluation History</h6>
            @if($history->isEmpty())
                <p class="text-muted" style="font-size:12.5px;">No history yet.</p>
            @else
                <ul class="list-group">
                    @foreach($history as $entry)
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="font-size:12.5px;">
                            <span>{{ ucfirst($entry->action) }} by {{ $entry->performedBy?->name ?? 'Unknown' }}</span>
                            <span>Total: {{ number_format($entry->total_score ?? 0, 2) }} &middot; {{ $entry->created_at->format('M d, Y h:i A') }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
