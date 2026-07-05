@props(['applicant'])

<style>
    .ai-section { margin-bottom: 28px; }
    .ai-section-title {
        font-size: 11px; font-weight: 800; text-transform: uppercase;
        letter-spacing: .7px; color: #6b7280;
        border-bottom: 1px solid #e5e7eb; padding-bottom: 6px; margin-bottom: 14px;
    }
    .ai-field { margin-bottom: 14px; }
    .ai-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #9ca3af; margin-bottom: 3px; }
    .ai-value { font-size: 13px; color: #1f2937; font-weight: 500; }
    .ai-value.empty { color: #d1d5db; font-style: italic; font-weight: 400; }
    .doc-link { font-size: 12px; display: inline-flex; align-items: center; gap: 4px; }
    .badge-yes { background: #d1fae5; color: #065f46; padding: 2px 8px; border-radius: 20px; font-size: 11px; font-weight: 700; }
    .badge-no  { background: #f3f4f6; color: #9ca3af; padding: 2px 8px; border-radius: 20px; font-size: 11px; font-weight: 700; }
</style>

{{-- Header card --}}
<div class="card border-0 shadow-sm mb-4" style="border-radius: 14px; overflow: hidden;">
    <div style="background: linear-gradient(135deg, #082977 0%, #0f172a 100%); padding: 24px 28px;">
        <div class="d-flex align-items-center gap-4">
            @if($applicant->photo_url)
                <img src="{{ $applicant->photo_url }}" alt="Photo"
                     style="width:72px;height:72px;border-radius:50%;object-fit:cover;border:3px solid rgba(255,255,255,.3);">
            @else
                <div style="width:72px;height:72px;border-radius:50%;background:rgba(255,255,255,.15);
                            display:flex;align-items:center;justify-content:center;
                            font-size:28px;font-weight:800;color:#fff;border:3px solid rgba(255,255,255,.2);">
                    {{ strtoupper(substr($applicant->first_name, 0, 1)) }}
                </div>
            @endif
            <div>
                <div style="color:#fff;font-size:22px;font-weight:800;">{{ $applicant->full_name }}</div>
                <div style="color:rgba(255,255,255,.65);font-size:13px;margin-top:4px;">
                    AIN: <strong style="color:#fff;">{{ $applicant->ain }}</strong>
                    &nbsp;·&nbsp; Ref: <strong style="color:#fff;">{{ $applicant->reference_no }}</strong>
                    @if($applicant->applied_at)
                        &nbsp;·&nbsp; Applied: <strong style="color:#fff;">{{ $applicant->applied_at->format('M d, Y') }}</strong>
                    @endif
                </div>
            </div>
            <div class="ms-auto d-flex gap-2">
                {{ $actions ?? '' }}
            </div>
        </div>
    </div>
</div>

@include('recruitment.partials.profile-details')
