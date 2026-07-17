<div class="card shadow-sm border-0 h-100">
    <div class="card-header bg-white border-bottom fw-bold d-flex align-items-center">
        <i class="bi bi-card-checklist me-2 text-primary"></i> Pre-Evaluation (Screening)
    </div>
    <div class="card-body">
        <h6 class="fw-bold">Qualification Standards Checklist</h6>
        <p class="text-muted small">Verify the applicant's documents against the minimum requirements for the position.</p>
        
        <div class="alert alert-info py-2" style="font-size:13px;">
            <ul class="mb-0 ps-3">
                <li><strong>Education:</strong> Bachelor's Degree</li>
                <li><strong>Experience:</strong> 1 year relevant experience</li>
                <li><strong>Training:</strong> 4 hours relevant training</li>
                <li><strong>Eligibility:</strong> CS Professional</li>
            </ul>
        </div>
        
        <form method="POST" action="{{ route('recruitment.bulk-evaluate') }}">
            @csrf
            
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold" style="font-size:13px;">QS Requirement</label>
                    <select name="evaluations[{{ $applicant->id }}][qs_requirement]" class="form-select form-select-sm {{ ($applicant->evaluation->qs_requirement ?? '') === 'Met' ? 'border-success bg-light-success text-success fw-bold' : (($applicant->evaluation->qs_requirement ?? '') === 'Unmet' ? 'border-danger bg-light-danger text-danger fw-bold' : '') }}">
                        <option value=""></option>
                        <option value="Met" {{ ($applicant->evaluation->qs_requirement ?? '') === 'Met' ? 'selected' : '' }}>Met</option>
                        <option value="Unmet" {{ ($applicant->evaluation->qs_requirement ?? '') === 'Unmet' ? 'selected' : '' }}>Unmet</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold" style="font-size:13px;">Exam Status</label>
                    <select name="evaluations[{{ $applicant->id }}][exam_status]" class="form-select form-select-sm {{ ($applicant->evaluation->exam_status ?? '') === 'Passed' ? 'border-success text-success fw-bold' : (($applicant->evaluation->exam_status ?? '') === 'Failed' ? 'border-danger text-danger fw-bold' : '') }}">
                        <option value=""></option>
                        <option value="Passed" {{ ($applicant->evaluation->exam_status ?? '') === 'Passed' ? 'selected' : '' }}>Passed</option>
                        <option value="Failed" {{ ($applicant->evaluation->exam_status ?? '') === 'Failed' ? 'selected' : '' }}>Failed</option>
                        <option value="Absent" {{ ($applicant->evaluation->exam_status ?? '') === 'Absent' ? 'selected' : '' }}>Absent</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold" style="font-size:13px;">Docs Complete</label>
                    <select name="evaluations[{{ $applicant->id }}][docs_complete]" class="form-select form-select-sm {{ ($applicant->evaluation->docs_complete ?? '') === 'Yes' ? 'border-success text-success fw-bold' : (($applicant->evaluation->docs_complete ?? '') === 'No' ? 'border-danger text-danger fw-bold' : '') }}">
                        <option value=""></option>
                        <option value="Yes" {{ ($applicant->evaluation->docs_complete ?? '') === 'Yes' ? 'selected' : '' }}>Yes</option>
                        <option value="No" {{ ($applicant->evaluation->docs_complete ?? '') === 'No' ? 'selected' : '' }}>No</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold" style="font-size:13px;">Final Rating</label>
                    <select name="evaluations[{{ $applicant->id }}][final_rating]" class="form-select form-select-sm shadow-sm border-secondary {{ ($applicant->evaluation->final_rating ?? '') === 'Qualified' ? 'bg-success text-white fw-bold' : (($applicant->evaluation->final_rating ?? '') === 'Disqualified' ? 'bg-danger text-white fw-bold' : '') }}">
                        <option value=""></option>
                        <option value="Qualified" {{ ($applicant->evaluation->final_rating ?? '') === 'Qualified' ? 'selected' : '' }}>Qualified</option>
                        <option value="Disqualified" {{ ($applicant->evaluation->final_rating ?? '') === 'Disqualified' ? 'selected' : '' }}>Disqualified</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold" style="font-size:14px;">Remarks / Notes</label>
                <textarea name="evaluations[{{ $applicant->id }}][remarks]" class="form-control form-control-sm" rows="3" placeholder="Enter reason for screening out or additional notes...">{{ $applicant->evaluation->remarks ?? '' }}</textarea>
            </div>
            
            <div class="d-flex justify-content-between align-items-center mt-4">
                <button type="submit" class="btn btn-primary btn-sm fw-bold px-4"><i class="bi bi-save-fill"></i> Save Result</button>
                
                <a href="javascript:void(0)" onclick="document.getElementById('move-twg-form').submit();" class="btn btn-outline-success btn-sm fw-bold">Move to TWG Evaluation <i class="bi bi-arrow-right"></i></a>
            </div>
        </form>

        <form id="move-twg-form" method="POST" action="{{ route('recruitment.deliberation.phase', $applicant) }}" class="d-none">
            @csrf
            <input type="hidden" name="deliberation_phase" value="twg_evaluation">
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selects = document.querySelectorAll('select[name^="evaluations"]');
        selects.forEach(select => {
            select.addEventListener('change', function() {
                let val = this.value;
                this.className = 'form-select form-select-sm'; // reset classes
                
                if (val === 'Met' || val === 'Passed' || val === 'Yes') {
                    this.classList.add('border-success', 'text-success', 'fw-bold', 'bg-light-success');
                } else if (val === 'Unmet' || val === 'Failed' || val === 'No') {
                    this.classList.add('border-danger', 'text-danger', 'fw-bold', 'bg-light-danger');
                } else if (val === 'Qualified') {
                    this.classList.add('bg-success', 'text-white', 'fw-bold', 'border-secondary', 'shadow-sm');
                } else if (val === 'Disqualified') {
                    this.classList.add('bg-danger', 'text-white', 'fw-bold', 'border-secondary', 'shadow-sm');
                }
            });
        });
    });
</script>
