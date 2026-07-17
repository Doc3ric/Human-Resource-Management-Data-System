@extends('layouts.app')
@section('title', 'Add Qualification Standard')

@section('content')
<div class="container-fluid py-4" style="max-width: 800px;">
    <div class="mb-4">
        <h4 class="mb-0 fw-bold">Add Qualification Standard</h4>
        <p class="text-muted">Enter the CSC Qualification Standards for a specific position title.</p>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form action="{{ route('system.qualification-standards.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="form-label fw-bold">Position Title <span class="text-danger">*</span></label>
                    <input type="text" name="position_title" class="form-control @error('position_title') is-invalid @enderror" value="{{ old('position_title') }}" required placeholder="e.g. Administrative Aide I">
                    @error('position_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Education</label>
                        <textarea name="education" rows="3" class="form-control">{{ old('education') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Training</label>
                        <textarea name="training" rows="3" class="form-control">{{ old('training') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Experience</label>
                        <textarea name="experience" rows="3" class="form-control">{{ old('experience') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Eligibility</label>
                        <textarea name="eligibility" rows="3" class="form-control">{{ old('eligibility') }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('system.qualification-standards.index') }}" class="btn btn-light border">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Standard</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
