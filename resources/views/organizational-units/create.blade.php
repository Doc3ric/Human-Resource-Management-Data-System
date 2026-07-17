<x-dashboard-app>
    <style>
        .form-card {
            background: var(--color-surface, #ffffff);
            border-radius: 14px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            border: 1px solid var(--color-border, #e2e8f0);
            padding: 32px;
            max-width: 600px;
            margin: 0 auto;
        }

        .form-header {
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--color-border, #e2e8f0);
        }

        .form-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: #475569;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .form-control, .form-select {
            width: 100%;
            padding: 10px 14px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            font-size: 0.95rem;
            color: #334155;
            transition: all 0.2s;
        }

        .form-control:focus, .form-select:focus {
            border-color: #0f4c75;
            box-shadow: 0 0 0 3px rgba(15, 76, 117, 0.1);
            outline: none;
        }

        .form-text {
            display: block;
            margin-top: 6px;
            font-size: 0.8rem;
            color: #64748b;
        }

        .invalid-feedback {
            display: block;
            color: #ef4444;
            font-size: 0.85rem;
            margin-top: 6px;
        }

        .btn-actions {
            display: flex;
            gap: 12px;
            margin-top: 32px;
        }

        .btn-submit {
            background: #0f4c75;
            color: #ffffff;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-submit:hover {
            background: #1a5276;
        }

        .btn-cancel {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
        }

        .btn-cancel:hover {
            background: #e2e8f0;
            color: #334155;
        }
    </style>

    <div class="px-4 py-5">
        <div class="form-card">
            <div class="form-header">
                <h2 class="form-title">
                    <i class="bi bi-building-add text-primary"></i> Add New Office
                </h2>
            </div>

            <form action="{{ route('organizational-units.store') }}" method="POST">
                @csrf
                
                <div class="form-group">
                    <label for="name" class="form-label">Office Name <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. Provincial Human Resource Management Office" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="sub_office" class="form-label">Sub-Office <span class="text-muted fw-normal">(Optional)</span></label>
                    <input type="text" id="sub_office" name="sub_office" class="form-control @error('sub_office') is-invalid @enderror" value="{{ old('sub_office') }}" placeholder="e.g. Administrative Division">
                    <small class="form-text">Specify a division or section within the office if applicable.</small>
                    @error('sub_office')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="code" class="form-label">Office Code <span class="text-danger">*</span></label>
                    <input type="text" id="code" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" placeholder="e.g. PHRMO" required>
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                    <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="Active" {{ old('status') == 'Active' ? 'selected' : '' }}>Active</option>
                        <option value="Inactive" {{ old('status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="btn-actions">
                    <button type="submit" class="btn-submit">
                        <i class="bi bi-check2-circle"></i> Save Office
                    </button>
                    <a href="{{ route('organizational-units.index') }}" class="btn-cancel">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-dashboard-app>
