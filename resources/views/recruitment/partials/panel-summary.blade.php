<div class="card shadow-sm border-0 h-100 bg-light">
    <div class="card-header bg-white border-bottom fw-bold d-flex align-items-center">
        <i class="bi bi-flag-fill me-2 text-danger"></i> Deliberation Completed
    </div>
    <div class="card-body text-center py-5">
        <div class="mb-4">
            <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
        </div>
        <h5 class="fw-bold">Deliberation Summary</h5>
        <p class="text-muted">The deliberation for this applicant has been successfully concluded and locked.</p>
        
        <div class="d-flex justify-content-center gap-3 mt-4">
            <a href="{{ route('recruitment.deliberation.export.cer', $applicant) }}" target="_blank" class="btn btn-info text-dark fw-bold">
                <i class="bi bi-file-earmark-pdf"></i> View CER
            </a>
            <a href="{{ route('recruitment.deliberation.export.layout-c', $applicant) }}" target="_blank" class="btn btn-secondary text-white fw-bold">
                <i class="bi bi-file-earmark-pdf"></i> View Scores (Layout C)
            </a>
        </div>
    </div>
</div>
