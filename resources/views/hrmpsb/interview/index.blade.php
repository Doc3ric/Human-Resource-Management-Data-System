<x-dashboard-app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            HRMPSB Panel Interviews
        </h2>
    </x-slot>

    <div class="content-wrapper p-4">

        {{-- Page header --}}
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h2 class="mb-1 text-primary"><i class="bi bi-person-video3 me-2"></i>HRMPSB Panel Interview Evaluations</h2>
                <p class="text-muted mb-0">All submitted panel interview evaluations. Click an applicant's matrix to review scores.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('recruitment.hrmpsb.interview.create') }}" class="btn btn-primary fw-bold">
                    <i class="bi bi-plus-circle me-1"></i> New Evaluation
                </a>
                <a href="{{ route('recruitment.hrmpsb.comparative_report') }}" class="btn btn-success">
                    <i class="bi bi-file-earmark-bar-graph me-1"></i> Comparative Report
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-1"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Stats --}}
        @php
            $totalEvals      = \App\Models\InterviewEvaluation::count();
            $uniqueApplicants = \App\Models\InterviewEvaluation::distinct('applicant_id')->count('applicant_id');
            $uniqueRaters    = \App\Models\InterviewEvaluation::whereNotNull('rater_id')->orWhereNotNull('panel_member_id')->distinct()->count('rater_id');
        @endphp
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm" style="border-radius:12px;border-left:4px solid #0dcaf0 !important;">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="bg-info bg-opacity-10 rounded p-3 me-3 text-info"><i class="bi bi-clipboard2-check-fill fs-4"></i></div>
                        <div>
                            <p class="text-muted mb-0" style="font-size:12px;font-weight:700;text-transform:uppercase;">Total Evaluations</p>
                            <h4 class="mb-0 fw-bold">{{ number_format($totalEvals) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm" style="border-radius:12px;border-left:4px solid #198754 !important;">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 rounded p-3 me-3 text-success"><i class="bi bi-people-fill fs-4"></i></div>
                        <div>
                            <p class="text-muted mb-0" style="font-size:12px;font-weight:700;text-transform:uppercase;">Applicants Evaluated</p>
                            <h4 class="mb-0 fw-bold">{{ number_format($uniqueApplicants) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm" style="border-radius:12px;border-left:4px solid #6366f1 !important;">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 rounded p-3 me-3 text-primary"><i class="bi bi-person-badge-fill fs-4"></i></div>
                        <div>
                            <p class="text-muted mb-0" style="font-size:12px;font-weight:700;text-transform:uppercase;">Panel Raters</p>
                            <h4 class="mb-0 fw-bold">{{ number_format($uniqueRaters) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Evaluations table --}}
        <div class="card border-0 shadow-sm" style="border-radius:14px;overflow:hidden;">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3 px-4">
                <h6 class="mb-0 fw-bold"><i class="bi bi-table me-2 text-primary"></i>Evaluation Records</h6>
                <span class="text-muted small">{{ $evaluations->total() }} record(s)</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:13px;">
                    <thead style="background:#f8fafc;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:#64748b;">
                        <tr>
                            <th class="px-4 py-3">Applicant</th>
                            <th>Position Applied</th>
                            <th>Office</th>
                            <th>Evaluator / Panel Member</th>
                            <th class="text-center">Score</th>
                            <th class="text-center">Submitted</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($evaluations as $eval)
                            <tr>
                                <td class="px-4">
                                    <div class="fw-semibold text-dark">{{ $eval->applicant ? \App\Support\BlindScoringId::forApplicant($eval->applicant) : '—' }}</div>
                                </td>
                                <td>{{ $eval->applicant?->position_applied ?? '—' }}</td>
                                <td>{{ $eval->applicant?->office ?? '—' }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $eval->rater_display_name }}</div>
                                    @if($eval->rater_id)
                                        <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size:10px;">System User</span>
                                    @elseif($eval->panel_member_id)
                                        <span class="badge bg-info bg-opacity-10 text-info" style="font-size:10px;">Panel Portal</span>
                                    @elseif($eval->rater_name)
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary" style="font-size:10px;">Guest</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($eval->total_score)
                                        @php $s = (float)$eval->total_score; @endphp
                                        <span class="fw-bold {{ $s >= 80 ? 'text-success' : ($s >= 60 ? 'text-primary' : 'text-warning') }}">
                                            {{ number_format($s, 2) }}%
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center text-muted" style="font-size:11px;">
                                    {{ $eval->created_at?->format('M d, Y') }}<br>
                                    <span style="font-size:10px;">{{ $eval->created_at?->format('g:i A') }}</span>
                                </td>
                                <td class="text-center">
                                    @if($eval->applicant_id)
                                        <a href="{{ route('recruitment.hrmpsb.interview.matrix', $eval->applicant_id) }}"
                                           class="btn btn-sm btn-outline-primary py-0 px-2" title="View Scoring Matrix">
                                            <i class="bi bi-grid-3x3-gap-fill me-1"></i>Matrix
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-clipboard2-x fs-2 d-block mb-2"></i>
                                    No evaluations submitted yet.
                                    <a href="{{ route('recruitment.hrmpsb.interview.create') }}" class="d-block mt-2">Start evaluating</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($evaluations->hasPages())
                <div class="card-footer bg-white border-top px-4 py-3">
                    {{ $evaluations->links() }}
                </div>
            @endif
        </div>

    </div>
</x-dashboard-app>
