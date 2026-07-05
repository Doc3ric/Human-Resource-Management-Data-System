<x-dashboard-app>
    <div style="padding: 24px; max-width: 800px; margin: 0 auto;">
        
        <div style="display:flex; align-items:center; gap: 12px; margin-bottom: 24px;">
            <a href="{{ route('plantilla.show', $plantilla->id) }}" style="color: #64748b; text-decoration: none; font-size: 20px;">
                <i class="bi bi-arrow-left-circle-fill"></i>
            </a>
            <h2 style="margin:0; font-size: 24px; font-weight: 800; color: #1e293b;">Promote / Transfer Employee</h2>
        </div>

        <div style="background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 24px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); margin-bottom: 24px;">
            <h4 style="margin-top:0; font-weight: 700; color: #0f172a; margin-bottom: 16px;">Current Details</h4>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; background: #f8fafc; padding: 16px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <div>
                    <div style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase;">Employee Name</div>
                    <div style="font-size: 16px; font-weight: 700; color: #1e293b;">{{ $plantilla->full_name }}</div>
                </div>
                <div>
                    <div style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase;">Current Item No.</div>
                    <div style="font-size: 16px; font-weight: 700; color: #1e293b;">{{ $plantilla->item_no_new }}</div>
                </div>
                <div>
                    <div style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase;">Current Position</div>
                    <div style="font-size: 14px; font-weight: 600; color: #334155;">{{ $plantilla->position_title }} (SG {{ $plantilla->salary_grade }})</div>
                </div>
                <div>
                    <div style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase;">Current Office</div>
                    <div style="font-size: 14px; font-weight: 600; color: #334155;">{{ $plantilla->office_department }}</div>
                </div>
            </div>
        </div>

        <form action="{{ route('plantilla.promote.submit', $plantilla->id) }}" method="POST">
            @csrf

            <div style="background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 24px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
                <h4 style="margin-top:0; font-weight: 700; color: #0f172a; margin-bottom: 20px;">Target Promotion / Transfer Position</h4>

                <div style="margin-bottom: 20px;">
                    <label for="target_position_id" style="display:block; font-weight: 600; color: #334155; margin-bottom: 8px;">Select Vacant Position <span style="color:#ef4444;">*</span></label>
                    <select name="target_position_id" id="target_position_id" class="form-select" required style="width: 100%; border-radius: 8px; padding: 10px; border: 1px solid #cbd5e1;">
                        <option value="">-- Select Vacant Position --</option>
                        @foreach($vacantPositions as $office => $positions)
                            <optgroup label="{{ $office ?: 'Unassigned Office' }}">
                                @foreach($positions as $vp)
                                    <option value="{{ $vp->id }}">
                                        Item {{ $vp->item_no_new }} - {{ $vp->position_title }} (SG {{ $vp->salary_grade }})
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    @error('target_position_id')
                        <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div style="margin-bottom: 24px;">
                    <label for="effective_date" style="display:block; font-weight: 600; color: #334155; margin-bottom: 8px;">Effective Date of Promotion/Transfer <span style="color:#ef4444;">*</span></label>
                    <input type="date" name="effective_date" id="effective_date" class="form-control" required value="{{ date('Y-m-d') }}" style="width: 100%; border-radius: 8px; padding: 10px; border: 1px solid #cbd5e1; max-width: 250px;">
                    @error('effective_date')
                        <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div style="background: #fef2f2; border: 1px solid #fca5a5; padding: 16px; border-radius: 8px; margin-bottom: 24px;">
                    <div style="display:flex; gap: 12px; align-items: flex-start;">
                        <i class="bi bi-exclamation-triangle-fill" style="color: #ef4444; font-size: 20px;"></i>
                        <div>
                            <strong style="color: #991b1b; display: block; margin-bottom: 4px;">Warning</strong>
                            <p style="margin:0; font-size: 13px; color: #b91c1c;">This action will permanently move all employee data (Name, DOB, UMID, etc.) to the new position. The current position (Item {{ $plantilla->item_no_new }}) will immediately be marked as <strong>VACANT</strong>.</p>
                        </div>
                    </div>
                </div>

                <div style="display:flex; justify-content: flex-end; gap: 12px;">
                    <a href="{{ route('plantilla.show', $plantilla->id) }}" style="background: #f1f5f9; color: #475569; padding: 10px 20px; border-radius: 8px; font-weight: 600; text-decoration: none;">Cancel</a>
                    <button type="submit" style="background: #2563eb; color: #fff; border: none; padding: 10px 24px; border-radius: 8px; font-weight: 600; cursor: pointer;">Confirm Promotion</button>
                </div>
            </div>

        </form>
    </div>

    <!-- Initialize Select2 for better searching if available -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
                $('#target_position_id').select2({
                    placeholder: "-- Select Vacant Position --",
                    allowClear: true,
                    width: '100%'
                });
            }
        });
    </script>
</x-dashboard-app>
