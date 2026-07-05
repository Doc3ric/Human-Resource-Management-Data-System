<x-dashboard-app>
    <div style="padding: 24px; max-width: 600px; margin: 0 auto;">
        
        <div style="display:flex; align-items:center; gap: 12px; margin-bottom: 24px;">
            <a href="{{ route('plantilla.index') }}" style="color: #64748b; text-decoration: none; font-size: 20px;">
                <i class="bi bi-arrow-left-circle-fill"></i>
            </a>
            <h2 style="margin:0; font-size: 24px; font-weight: 800; color: #1e293b;">Quick Add Office & Position</h2>
        </div>

        <div style="background: #eff6ff; border: 1px solid #bfdbfe; padding: 16px; border-radius: 8px; margin-bottom: 24px;">
            <div style="display:flex; gap: 12px; align-items: flex-start;">
                <i class="bi bi-info-circle-fill" style="color: #3b82f6; font-size: 20px;"></i>
                <div>
                    <p style="margin:0; font-size: 13px; color: #1e3a8a;">This form quickly creates a new <strong>Vacant, Unfunded Position</strong>. Once created, the Office and Position Title will instantly appear in all dropdowns.</p>
                </div>
            </div>
        </div>

        <form action="{{ route('plantilla.quick-add.submit') }}" method="POST">
            @csrf

            <div style="background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 24px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">

                <div style="margin-bottom: 20px;">
                    <label for="office_department" style="display:block; font-weight: 600; color: #334155; margin-bottom: 8px;">Office / OFFICE <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="office_department" id="office_department" list="office_list" class="form-control" required placeholder="Type or select an Office" style="width: 100%; border-radius: 8px; padding: 10px; border: 1px solid #cbd5e1;">
                    <datalist id="office_list">
                        @foreach($offices as $office)
                            <option value="{{ $office }}"></option>
                        @endforeach
                    </datalist>
                    @error('office_department')
                        <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div style="margin-bottom: 20px;">
                    <label for="position_title" style="display:block; font-weight: 600; color: #334155; margin-bottom: 8px;">Position Title <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="position_title" id="position_title" class="form-control" required placeholder="e.g. Administrative Officer I" style="width: 100%; border-radius: 8px; padding: 10px; border: 1px solid #cbd5e1;">
                    @error('position_title')
                        <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                    <div>
                        <label for="item_no_new" style="display:block; font-weight: 600; color: #334155; margin-bottom: 8px;">Item Number <span style="color:#ef4444;">*</span></label>
                        <input type="text" name="item_no_new" id="item_no_new" class="form-control" required placeholder="e.g. 102-1" style="width: 100%; border-radius: 8px; padding: 10px; border: 1px solid #cbd5e1;">
                        @error('item_no_new')
                            <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div>
                        <label for="salary_grade" style="display:block; font-weight: 600; color: #334155; margin-bottom: 8px;">Salary Grade <span style="color:#ef4444;">*</span></label>
                        <input type="number" name="salary_grade" id="salary_grade" class="form-control" required min="1" max="33" placeholder="1-33" style="width: 100%; border-radius: 8px; padding: 10px; border: 1px solid #cbd5e1;">
                        @error('salary_grade')
                            <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div style="margin-bottom: 24px;">
                    <label for="step" style="display:block; font-weight: 600; color: #334155; margin-bottom: 8px;">Step <span style="color:#ef4444;">*</span></label>
                    <input type="number" name="step" id="step" class="form-control" required min="1" max="8" value="1" style="width: 100%; border-radius: 8px; padding: 10px; border: 1px solid #cbd5e1;">
                    @error('step')
                        <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div style="display:flex; justify-content: flex-end; gap: 12px;">
                    <a href="{{ route('plantilla.index') }}" style="background: #f1f5f9; color: #475569; padding: 10px 20px; border-radius: 8px; font-weight: 600; text-decoration: none;">Cancel</a>
                    <button type="submit" style="background: #10b981; color: #fff; border: none; padding: 10px 24px; border-radius: 8px; font-weight: 600; cursor: pointer;">Save Position</button>
                </div>
            </div>

        </form>
    </div>
</x-dashboard-app>
