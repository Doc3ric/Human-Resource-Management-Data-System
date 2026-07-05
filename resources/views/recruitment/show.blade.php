<x-dashboard-app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Applicant Profile
        </h2>
    </x-slot>

    <div class="content-wrapper p-4">
        <x-applicant-profile :applicant="$applicant">
            <x-slot name="actions">
                @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin() || auth()->user()->isAppointmentAdmin() || auth()->user()->isAppointmentEncoder())
                <button type="button" onclick="document.getElementById('floatingScorePanel').style.display='flex'"
                   class="btn btn-sm btn-warning fw-bold text-dark">
                    <i class="bi bi-star-fill me-1"></i> Score HRMPSB (TWG)
                </button>
                @endif
                @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin() || auth()->user()->isAppointmentAdmin() || auth()->user()->isAppointmentEncoder())
                <a href="{{ route('recruitment.hrmpsb.interview.create', ['applicant_id' => $applicant->id]) }}"
                   class="btn btn-sm btn-info fw-bold text-dark">
                    <i class="bi bi-person-video3 me-1"></i> Interview Evaluation
                </a>
                @endif
                <a href="{{ route('recruitment.receipt', $applicant->id) }}" target="_blank"
                   class="btn btn-sm btn-outline-light fw-bold">
                    <i class="bi bi-file-earmark-pdf-fill me-1"></i> Receipt
                </a>
                <a href="{{ route('recruitment.index') }}" class="btn btn-sm btn-light fw-bold">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            </x-slot>
        </x-applicant-profile>
    </div>

    @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin() || auth()->user()->isAppointmentAdmin() || auth()->user()->isAppointmentEncoder())
        <x-twg-score-panel :applicant="$applicant" :score="$score" :psb-from-panel="$psbFromPanel" :panel-count="$panelCount" actionUrl="{{ route('recruitment.hrmpsb.save_score', $applicant->id) }}" />
    @endif
</x-dashboard-app>
