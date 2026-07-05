<x-dashboard-app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            TWG Dynamic Scoring (Module 6.1)
        </h2>
    </x-slot>

    <div class="content-wrapper p-4">

        @if(session('success'))
            <div style="background:#f0fdf4;color:#166534;padding:10px 14px;border-radius:8px;margin-bottom:14px;font-size:13px;">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div style="background:#fef2f2;color:#991b1b;padding:10px 14px;border-radius:8px;margin-bottom:14px;font-size:13px;">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('recruitment.hrmpsb.twg_dynamic.create') }}" class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label">Applicant</label>
                        <select name="applicant_id" class="form-select" required>
                            <option value="">-- Select Applicant --</option>
                            @foreach($applicants as $app)
                                <option value="{{ $app->id }}" {{ $applicant && $applicant->id === $app->id ? 'selected' : '' }}>
                                    {{ $app->last_name }}, {{ $app->first_name }} ({{ $app->item_no ?: 'no item no.' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Load</button>
                    </div>
                </form>
            </div>
        </div>

        @if($applicant)
            @include('hrmpsb.twg-dynamic._sheet')
        @endif
    </div>
</x-dashboard-app>
