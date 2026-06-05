<x-dashboard-app>
    <div class="max-w-4xl mx-auto space-y-6">
        {{-- Header --}}
        <div class="flex items-center gap-4">
            <a href="{{ route('all-data.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Add New Record</h1>
                <p class="text-gray-500 text-sm">Fill in the details to create a new plantilla record</p>
            </div>
        </div>

        {{-- Wizard CSS --}}
        <style>
            .wz-step {
                text-align: center;
                flex: 1;
            }

            .wz-num {
                width: 34px;
                height: 34px;
                border-radius: 50%;
                background: #e2e8f0;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-weight: 700;
                font-size: 14px;
                margin-bottom: 4px;
                transition: all .3s;
            }

            .wz-step.wz-active .wz-num {
                background: #2563eb;
                color: #fff;
                box-shadow: 0 0 0 4px rgba(37, 99, 235, .15);
            }

            .wz-step.wz-done .wz-num {
                background: #22c55e;
                color: #fff;
            }

            .wz-line {
                flex: 1;
                height: 2px;
                background: #e2e8f0;
                margin-top: 17px;
                transition: background .3s;
            }

            .wz-line.wz-done {
                background: #22c55e;
            }

            .wz-lbl {
                font-size: 11px;
                font-weight: 600;
                color: #94a3b8;
            }

            .wz-step.wz-active .wz-lbl {
                color: #2563eb;
            }

            .wz-step.wz-done .wz-lbl {
                color: #16a34a;
            }

            .wizard-step {
                display: none;
            }

            .wizard-step.wz-visible {
                display: block;
            }
        </style>

        {{-- Wizard Progress Bar --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 mb-2">
            <div style="display:flex;align-items:center;">
                <div class="wz-step wz-active" id="wz-s1">
                    <div class="wz-num">1</div>
                    <div class="wz-lbl">Position Details</div>
                </div>
                <div class="wz-line" id="wz-l1"></div>
                <div class="wz-step" id="wz-s2">
                    <div class="wz-num">2</div>
                    <div class="wz-lbl">Employee Info</div>
                </div>
                <div class="wz-line" id="wz-l2"></div>
                <div class="wz-step" id="wz-s3">
                    <div class="wz-num">3</div>
                    <div class="wz-lbl">Review &amp; Submit</div>
                </div>
            </div>
        </div>

        {{-- Errors --}}
        @if($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <p class="text-red-700 font-medium text-sm mb-1">Please fix the following errors:</p>
                <ul class="list-disc list-inside text-red-600 text-sm space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="create-record-form" method="POST" action="{{ route('all-data.store') }}" class="space-y-6">
            @csrf

            <div class="wizard-step wz-visible" id="wizard-step-1">
                {{-- Step 1: Position Information --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-base font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">Position
                        Information</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

                        <div class="lg:col-span-2">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Organizational Unit <span
                                    class="text-red-500">*</span></label>
                            <div class="flex flex-col gap-2">
                                @php $oldOrg = old('organizational_unit'); @endphp
                                <select id="org_unit_select"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 bg-white outline-none">
                                    <option value="">Select an office...</option>
                                    @foreach($offices as $office)
                                        <option value="{{ $office }}" {{ $oldOrg === $office ? 'selected' : '' }}>
                                            {{ $office }}</option>
                                    @endforeach
                                    <option value="Others" {{ $oldOrg && !$offices->contains($oldOrg) ? 'selected' : '' }}>Others (Please specify)</option>
                                </select>
                                <input type="text" name="organizational_unit" id="org_unit_input" value="{{ $oldOrg }}"
                                    placeholder="Type organizational unit manually..."
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none {{ $oldOrg && !$offices->contains($oldOrg) ? '' : 'hidden' }}">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Item (Position Code) <span
                                    class="text-red-500">*</span></label>

                            <div class="flex flex-col gap-2">
                                {{-- Shown when an office is selected: dropdown of vacant items --}}
                                <div id="item_dropdown_wrap" class="hidden relative">
                                    <select id="item_code_select"
                                        class="w-full border border-indigo-400 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none font-mono bg-indigo-50 text-indigo-900 font-semibold">
                                        <option value="">⬇ Select a vacant item…</option>
                                    </select>
                                    <p class="text-[10px] text-indigo-500 mt-1 flex items-center gap-1">
                                        <i class="bi bi-info-circle"></i>
                                        Selecting one will auto-fill the position &amp; salary grade.
                                    </p>
                                </div>

                                <input type="text" name="item" id="item_code" value="{{ old('item') }}"
                                    placeholder="Type Item (Position Code)..."
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none font-mono">
                            </div>
                        </div>

                        <div class="lg:col-span-2">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Position Title <span
                                    class="text-red-500">*</span></label>
                            @php
                                $oldPosTitle = old('position_title');
                                $knownTitles = \App\Models\PlantillaRecord::distinct()->pluck('position_title')
                                    ->filter()
                                    ->map(fn($v) => trim($v))
                                    ->unique()
                                    ->sortBy(fn($v) => strtolower($v))
                                    ->values();
                                $isPosOther = $oldPosTitle && !$knownTitles->contains($oldPosTitle);
                            @endphp
                            <div class="flex flex-col gap-2">
                                <select id="pos_title_select"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 bg-white outline-none"
                                    onchange="handlePosTitleChange(this)">
                                    <option value="">Select position title...</option>
                                    @foreach($knownTitles as $pt)
                                        <option value="{{ $pt }}" {{ $oldPosTitle === $pt ? 'selected' : '' }}>{{ $pt }}
                                        </option>
                                    @endforeach
                                    <option value="Others" {{ $isPosOther ? 'selected' : '' }}>Others (type manually)
                                    </option>
                                </select>
                                <input type="text" name="position_title" id="pos_title_input" value="{{ $oldPosTitle }}"
                                    placeholder="Type position title manually..."
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none {{ $isPosOther ? '' : 'hidden' }}">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Position Classification</label>
                            <select name="position_classification"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white">
                                <option value="">Select…</option>
                                <option value="EXECUTIVE/MANAGERIAL" {{ old('position_classification') === 'EXECUTIVE/MANAGERIAL' ? 'selected' : '' }}>
                                    Executive/Managerial</option>
                                <option value="2nd Level" {{ old('position_classification') === '2nd Level' ? 'selected' : '' }}>2nd Level</option>
                                <option value="1st Level" {{ old('position_classification') === '1st Level' ? 'selected' : '' }}>1st Level</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Salary Grade <span
                                    class="text-red-500">*</span></label>
                            <input type="number" name="salary_grade" id="salary_grade" min="1" max="33"
                                value="{{ old('salary_grade') }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Step <span
                                    class="text-red-500">*</span></label>
                            <select name="step" id="step"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white">
                                <option value="">Select step...</option>
                                @for($s = 1; $s <= 8; $s++)
                                    <option value="{{ $s }}" {{ (int) old('step', 1) === $s ? 'selected' : '' }}>Step {{ $s }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Authorized Annual Salary <span
                                    class="text-gray-400 text-[10px] ml-1">(Calculates from SG if blank)</span></label>
                            <input type="number" step="0.01" name="authorized_annual_salary"
                                id="authorized_annual_salary" value="{{ old('authorized_annual_salary') }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Actual Annual Salary <span
                                    class="text-gray-400 text-[10px] ml-1">(Calculates from SG if blank)</span></label>
                            <input type="number" step="0.01" name="actual_annual_salary" id="actual_annual_salary"
                                value="{{ old('actual_annual_salary') }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Employment Status</label>
                            <select name="employment_status"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white">
                                <option value="">Select…</option>
                                <option value="P" {{ old('employment_status') === 'P' ? 'selected' : '' }}>Permanent</option>
                                <option value="CT" {{ old('employment_status') === 'CT' ? 'selected' : '' }}>Co-Terminous
                                </option>
                                <option value="E" {{ old('employment_status') === 'E' ? 'selected' : '' }}>Elected</option>
                                <option value="Casual" {{ old('employment_status') === 'Casual' ? 'selected' : '' }}>Casual
                                </option>
                                <option value="JO" {{ old('employment_status') === 'JO' ? 'selected' : '' }}>Job Order
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Area Code</label>
                            <input type="text" name="area_code" value="{{ old('area_code') }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Area Type</label>
                            <input type="text" name="area_type" value="{{ old('area_type') }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Level</label>
                            <input type="text" name="level" value="{{ old('level') }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>
                    </div>
                </div>

            </div>{{-- /wizard-step-1 --}}

            <div class="wizard-step" id="wizard-step-2">
                {{-- Step 2: Employee Information --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex justify-between items-center mb-1 pb-2 border-b border-gray-100">
                        <h2 class="text-base font-semibold text-gray-800">Employee Information</h2>
                        <label
                            class="flex items-center gap-2 cursor-pointer bg-gray-50 px-3 py-1.5 rounded-md border border-gray-200">
                            <input type="checkbox" id="explicit_vacant_toggle"
                                class="rounded w-4 h-4 text-blue-600 focus:ring-blue-500">
                            <span class="text-xs font-medium text-gray-700">Mark as Vacant Position</span>
                        </label>
                    </div>

                    <div id="employee_fields_container" class="transition-opacity duration-200">
                        <p class="text-xs text-gray-400 mb-4">Leave blank or check the box above if this is a vacant
                            position.</p>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Last Name</label>
                                <input type="text" name="last_name" value="{{ old('last_name') }}"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">First Name</label>
                                <input type="text" name="first_name" value="{{ old('first_name') }}"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Middle Name</label>
                                <input type="text" name="middle_name" value="{{ old('middle_name') }}"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Sex</label>
                                <select name="sex"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white">
                                    <option value="">Not specified</option>
                                    <option value="M" {{ old('sex') === 'M' ? 'selected' : '' }}>Male (M)</option>
                                    <option value="F" {{ old('sex') === 'F' ? 'selected' : '' }}>Female (F)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Religion</label>
                                <input type="text" name="religion" value="{{ old('religion') }}"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Date of Birth</label>
                                <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">TIN</label>
                                <input type="text" name="tin" value="{{ old('tin') }}"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none font-mono">
                            </div>
                            <div>
                                <label class="flex justify-between items-center mb-1">
                                    <span class="text-xs font-medium text-gray-600">Employee Code</span>
                                    <button type="button" id="btn-generate-code"
                                        class="text-[10px] font-bold text-blue-600 hover:text-blue-800 bg-blue-50 px-2 py-0.5 rounded border border-blue-200 uppercase transition"
                                        title="Auto-fill using Date of Birth & Last Name">Auto-Fill</button>
                                </label>
                                <input type="text" name="employee_code" id="employee_code"
                                    value="{{ old('employee_code') }}" placeholder="DDMMYYYY + Initial"
                                    class="w-full border border-emerald-300 bg-emerald-50 text-emerald-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none font-mono font-bold placeholder-emerald-300 shadow-inner">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Date of Original
                                    Appointment</label>
                                <input type="date" name="date_original_appointment"
                                    value="{{ old('date_original_appointment') }}"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Date of Last
                                    Promotion</label>
                                <input type="date" name="date_last_promotion" value="{{ old('date_last_promotion') }}"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Civil Service
                                    Eligibility</label>
                                <div class="flex flex-col gap-2">
                                    @php $oldCse = old('civil_service_eligibility'); @endphp
                                    <select id="cse_select"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 bg-white outline-none">
                                        <option value="" {{ empty($oldCse) ? 'selected' : '' }}>None / Not Applicable
                                        </option>
                                        <option value="Professional" {{ $oldCse === 'Professional' ? 'selected' : '' }}>
                                            Professional</option>
                                        <option value="Sub-Professional" {{ $oldCse === 'Sub-Professional' ? 'selected' : '' }}>Sub-Professional</option>
                                        <option value="Others" {{ $oldCse && !in_array($oldCse, ['Professional', 'Sub-Professional']) ? 'selected' : '' }}>Others (Please specify)</option>
                                    </select>
                                    <input type="text" name="civil_service_eligibility" id="cse_input"
                                        value="{{ $oldCse }}" placeholder="Type eligibility manually..."
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none {{ $oldCse && !in_array($oldCse, ['Professional', 'Sub-Professional']) ? '' : 'hidden' }}">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">GSIS BP Number</label>
                                <input type="text" name="gsis_bp_number" value="{{ old('gsis_bp_number') }}"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none font-mono">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">UMID</label>
                                <input type="text" name="umid" value="{{ old('umid') }}"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none font-mono">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Solo Parent ID</label>
                                <input type="text" name="solo_parent" value="{{ old('solo_parent') }}"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Leave Without Pay
                                    (Days)</label>
                                <input type="number" min="0" name="lwop" value="{{ old('lwop') }}"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-6 mt-4 items-center">
                            <div class="flex items-center gap-2">
                                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                    <input type="checkbox" name="is_pwd" id="is_pwd" value="1" {{ old('is_pwd') ? 'checked' : '' }} class="rounded"> PWD
                                </label>
                                <input type="text" name="type_of_disability" id="type_of_disability"
                                    value="{{ old('type_of_disability') }}"
                                    placeholder="Type of Disability (e.g. Physical, Visual)"
                                    class="border border-gray-300 rounded-lg px-2 py-1 text-xs focus:ring-2 focus:ring-blue-500 outline-none bg-white {{ old('is_pwd') ? '' : 'hidden' }}">
                            </div>
                            <div class="flex items-center gap-2" style="flex-wrap: wrap;">
                                @php $oldIp = old('indigenous_people'); @endphp
                                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                    <input type="checkbox" id="is_ip_checkbox" {{ $oldIp ? 'checked' : '' }}
                                        class="rounded"> IP
                                </label>

                                <div id="ip_container" class="flex gap-2 {{ $oldIp ? '' : 'hidden' }}">
                                    <select id="ip_select"
                                        class="border border-gray-300 rounded-lg px-2 py-1 text-xs focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                                        <option value="">Select IP Group</option>
                                        @foreach(['Aeta', 'Ati', 'Badjao', 'Batak', 'Blaan', 'Bontoc', 'Bukidnon', 'Higaonon', 'Ibaloi', 'Ifugao', 'Ilongot', 'Itneg', 'Kalinga', 'Kankanaey', 'Lumad', 'Mangyan', 'Manobo', 'Mansaka', 'Palawano', 'Pala\'wan', 'Subanen', 'T\'boli', 'Tiruray', 'Yakan'] as $ipGroup)
                                            <option value="{{ $ipGroup }}" {{ $oldIp === $ipGroup ? 'selected' : '' }}>
                                                {{ $ipGroup }}</option>
                                        @endforeach
                                        <option value="Others" {{ $oldIp && !in_array($oldIp, ['Aeta', 'Ati', 'Badjao', 'Batak', 'Blaan', 'Bontoc', 'Bukidnon', 'Higaonon', 'Ibaloi', 'Ifugao', 'Ilongot', 'Itneg', 'Kalinga', 'Kankanaey', 'Lumad', 'Mangyan', 'Manobo', 'Mansaka', 'Palawano', 'Pala\'wan', 'Subanen', 'T\'boli', 'Tiruray', 'Yakan', 'Y']) ? 'selected' : '' }}>Others</option>
                                    </select>
                                    <input type="text" id="ip_others" placeholder="Specify..."
                                        value="{{ $oldIp && !in_array($oldIp, ['Aeta', 'Ati', 'Badjao', 'Batak', 'Blaan', 'Bontoc', 'Bukidnon', 'Higaonon', 'Ibaloi', 'Ifugao', 'Ilongot', 'Itneg', 'Kalinga', 'Kankanaey', 'Lumad', 'Mangyan', 'Manobo', 'Mansaka', 'Palawano', 'Pala\'wan', 'Subanen', 'T\'boli', 'Tiruray', 'Yakan', 'Y']) ? $oldIp : '' }}"
                                        class="border border-gray-300 rounded-lg px-2 py-1 text-xs focus:ring-2 focus:ring-blue-500 outline-none bg-white {{ $oldIp && !in_array($oldIp, ['Aeta', 'Ati', 'Badjao', 'Batak', 'Blaan', 'Bontoc', 'Bukidnon', 'Higaonon', 'Ibaloi', 'Ifugao', 'Ilongot', 'Itneg', 'Kalinga', 'Kankanaey', 'Lumad', 'Mangyan', 'Manobo', 'Mansaka', 'Palawano', 'Pala\'wan', 'Subanen', 'T\'boli', 'Tiruray', 'Yakan', 'Y']) ? '' : 'hidden' }}">
                                </div>
                                <input type="hidden" name="indigenous_people" id="ip_hidden" value="{{ $oldIp }}">
                            </div>
                            <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer"
                                title="Eligible for Magna Carta benefits (e.g. Pre-Retirement NOSA)">
                                <input type="checkbox" name="is_health_worker" value="1" {{ old('is_health_worker') ? 'checked' : '' }}
                                    class="rounded text-pink-600 focus:ring-pink-500">
                                <span class="text-pink-700 font-semibold"><i class="bi bi-heart-pulse-fill"></i> Health
                                    Worker</span>
                            </label>
                            {{-- Vacant status is auto-detected: if no employee name is entered, the position is
                            automatically marked as Vacant --}}
                            <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                <input type="checkbox" name="abolished" value="1" {{ old('abolished') ? 'checked' : '' }}
                                    class="rounded"> Abolished
                            </label>
                            <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                <input type="checkbox" name="dissolved" value="1" {{ old('dissolved') ? 'checked' : '' }}
                                    class="rounded"> Dissolved
                            </label>
                            <div class="flex flex-col gap-2 p-3 bg-orange-50 rounded-lg border border-orange-200">
                                <label
                                    class="flex items-center gap-2 text-sm font-semibold text-orange-800 cursor-pointer"
                                    title="Excludes employee from NOSI/NOLP step increment processing">
                                    <input type="checkbox" name="is_apprehended" id="is_apprehended" value="1" {{ old('is_apprehended') ? 'checked' : '' }}
                                        class="rounded text-orange-600 focus:ring-orange-500"> ⚠ Apprehended
                                </label>
                                <div id="apprehended_dates"
                                    class="{{ old('is_apprehended') ? '' : 'hidden' }} flex items-center gap-2 pl-6">
                                    <span class="text-xs text-orange-700 font-medium">From:</span>
                                    <input type="date" name="apprehended_from" value="{{ old('apprehended_from') }}"
                                        class="border border-orange-300 rounded-lg px-2 py-1 text-xs focus:ring-2 focus:ring-orange-500 outline-none bg-white">
                                </div>
                            </div>

                            <div class="flex flex-col gap-2 p-3 bg-red-50 rounded-lg border border-red-200">
                                <label class="flex items-center gap-2 text-sm font-semibold text-red-800 cursor-pointer"
                                    title="Excludes employee from NOSI/NOLP step increment processing">
                                    <input type="checkbox" name="is_admin_charge" id="is_admin_charge" value="1" {{ old('is_admin_charge') ? 'checked' : '' }}
                                        class="rounded text-red-600 focus:ring-red-500"> ⚠ Admin Charge
                                </label>
                                <div id="admin_charge_details"
                                    class="{{ old('is_admin_charge') ? '' : 'hidden' }} flex flex-col gap-2 pl-6 mt-2">
                                    @php
                                        $oldCharges = old('admin_charges', []);
                                        if (empty($oldCharges)) {
                                            $oldCharges[] = ['from' => '', 'to' => '', 'type' => ''];
                                        }
                                    @endphp
                                    <div id="admin_charges_list" class="flex flex-col gap-3">
                                        @foreach($oldCharges as $index => $charge)
                                            <div
                                                class="admin-charge-row flex flex-wrap items-start gap-2 bg-white p-2 rounded-lg border border-red-100 relative">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-[10px] text-red-700 font-bold uppercase">From:</span>
                                                    <input type="date" name="admin_charges[{{ $index }}][from]"
                                                        value="{{ $charge['from'] ?? '' }}"
                                                        class="border border-red-300 rounded-lg px-2 py-1 text-xs focus:ring-2 focus:ring-red-500 outline-none w-[110px]">
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-[10px] text-red-700 font-bold uppercase">To:</span>
                                                    <input type="date" name="admin_charges[{{ $index }}][to]"
                                                        value="{{ $charge['to'] ?? '' }}"
                                                        class="border border-red-300 rounded-lg px-2 py-1 text-xs focus:ring-2 focus:ring-red-500 outline-none w-[110px]">
                                                </div>
                                                <div class="flex flex-col gap-1 flex-1 min-w-[200px]">
                                                    <div class="flex items-center gap-2">
                                                        <span
                                                            class="text-[10px] text-red-700 font-bold uppercase">Type:</span>
                                                        @php
                                                            $cType = $charge['type'] ?? '';
                                                            $isOther = $cType && !in_array($cType, ['Stern warning', 'Reprimand', 'Suspension']);
                                                        @endphp
                                                        <select
                                                            class="admin-charge-type-select border border-red-300 rounded-lg px-2 py-1 text-xs focus:ring-2 focus:ring-red-500 outline-none bg-white flex-1">
                                                            <option value="" {{ empty($cType) ? 'selected' : '' }}>Select...
                                                            </option>
                                                            <option value="Stern warning" {{ $cType === 'Stern warning' ? 'selected' : '' }}>Stern warning</option>
                                                            <option value="Reprimand" {{ $cType === 'Reprimand' ? 'selected' : '' }}>Reprimand</option>
                                                            <option value="Suspension" {{ $cType === 'Suspension' ? 'selected' : '' }}>Suspension</option>
                                                            <option value="Others" {{ $isOther ? 'selected' : '' }}>Others
                                                                (Please Specify)</option>
                                                        </select>
                                                    </div>
                                                    <input type="text" name="admin_charges[{{ $index }}][type]"
                                                        value="{{ $cType }}" placeholder="Specify type..."
                                                        class="admin-charge-type-input border border-red-300 rounded-lg px-2 py-1 text-xs focus:ring-2 focus:ring-red-500 outline-none w-full {{ $isOther ? '' : 'hidden' }}">
                                                </div>
                                                <button type="button"
                                                    class="btn-remove-charge flex items-center justify-center w-7 h-7 mt-0.5 text-red-500 bg-white border border-red-200 hover:bg-red-50 hover:border-red-300 hover:text-red-600 rounded-lg transition-all shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500"
                                                    title="Remove Charge">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                    <button type="button" id="btn_add_charge"
                                        class="self-start mt-1 text-xs font-semibold text-red-700 bg-white border border-red-300 hover:bg-red-50 px-3 py-1.5 rounded-lg transition shadow-sm">
                                        + Add Another Charge
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Comment / Annotation</label>
                            <textarea name="comment_annotation" rows="2"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">{{ old('comment_annotation') }}</textarea>
                        </div>
                    </div>
                </div>

            </div>{{-- /wizard-step-2 --}}

            {{-- Step 3: Review & Submit --}}
            <div class="wizard-step" id="wizard-step-3">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-base font-semibold text-gray-800 mb-1 pb-2 border-b border-gray-100">Review &amp;
                        Submit</h2>
                    <p class="text-sm text-gray-500 mb-4">Please review the key details below before creating the
                        record.</p>
                    <div id="wz-review-body" class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4"></div>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-sm text-blue-800">
                        <i class="bi bi-info-circle-fill me-1"></i> Scroll up if you need to edit anything using the
                        <strong>Back</strong> button. When ready, click <strong>Create Record</strong>.
                    </div>
                </div>
            </div>{{-- /wizard-step-3 --}}

            {{-- Wizard Navigation --}}
            <div class="flex justify-between items-center pt-2">
                <a href="{{ route('all-data.index') }}"
                    class="px-4 py-2 border border-gray-300 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-50 transition">Cancel</a>
                <div class="flex gap-3">
                    <button type="button" id="wz-prev-btn" onclick="wizPrev()"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition"
                        style="display:none;">← Back</button>
                    <button type="button" id="wz-next-btn" onclick="wizNext()"
                        class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition">Next
                        →</button>
                    <button type="submit" id="wz-submit-btn"
                        class="px-5 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition"
                        style="display:none;">✓ Create Record</button>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sgInput = document.getElementById('salary_grade');
            const stepSelect = document.getElementById('step');
            const authSalaryInput = document.getElementById('authorized_annual_salary');
            const actualSalaryInput = document.getElementById('actual_annual_salary');

            const vacantToggle = document.getElementById('explicit_vacant_toggle');
            const empContainer = document.getElementById('employee_fields_container');

            // Dynamic Salary Fetching
            function fetchSalary() {
                const grade = sgInput.value;
                const step = stepSelect.value;

                if (grade && step) {
                    fetch(`/all-data/salary/${grade}/${step}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.annual_salary) {
                                // Auto-fill if empty or update to give hint
                                authSalaryInput.value = data.annual_salary;
                                actualSalaryInput.value = data.annual_salary;
                            }
                        })
                        .catch(err => console.error('Error fetching salary:', err));
                }
            }

            if (sgInput && stepSelect) {
                sgInput.addEventListener('change', fetchSalary);
                stepSelect.addEventListener('change', fetchSalary);

                // Fetch on initial load if we don't have numbers yet but have old values for SG
                if (sgInput.value && (!authSalaryInput.value || !actualSalaryInput.value)) {
                    fetchSalary();
                }
            }

            // Organizational Unit Toggle
            const orgSelect = document.getElementById('org_unit_select');
            const orgInput = document.getElementById('org_unit_input');
            if (orgSelect && orgInput) {
                orgSelect.addEventListener('change', function () {
                    if (this.value === 'Others') {
                        orgInput.classList.remove('hidden');
                        let isFromList = false;
                        Array.from(orgSelect.options).forEach(opt => {
                            if (opt.value && opt.value !== 'Others' && opt.value === orgInput.value) {
                                isFromList = true;
                            }
                        });
                        if (isFromList) orgInput.value = '';
                        orgInput.focus();
                    } else {
                        orgInput.classList.add('hidden');
                        orgInput.value = this.value;
                        orgInput.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                });
            }

            // ── Dynamic Item Dropdown ─────────────────────────────────────────
            const ouInput = document.getElementById('org_unit_input');
            const itemDropWrap = document.getElementById('item_dropdown_wrap');
            const itemCodeSelect = document.getElementById('item_code_select');
            const itemCodeInput = document.getElementById('item_code');
            const posTitleSelect = document.getElementById('pos_title_select');
            const posTitleInput = document.getElementById('pos_title_input');
            const sgInput2 = document.getElementById('salary_grade');

            // Stores the full item data fetched from the server
            let vacantItemsData = [];

            function fetchItemsByOffice() {
                const office = ouInput ? ouInput.value : '';
                if (!office) {
                    itemDropWrap.classList.add('hidden');
                    itemCodeSelect.innerHTML = '<option value="">⬇ Select a vacant item…</option>';
                    vacantItemsData = [];
                    return;
                }

                fetch(`/all-data/items-by-office?office=${encodeURIComponent(office)}`)
                    .then(r => r.json())
                    .then(items => {
                        vacantItemsData = items;
                        itemCodeSelect.innerHTML = '';

                        if (items.length > 0) {
                            // Heading option
                            const blank = document.createElement('option');
                            blank.value = '';
                            blank.textContent = `— ${items.length} vacant item(s) available —`;
                            itemCodeSelect.appendChild(blank);

                            items.forEach(row => {
                                const opt = document.createElement('option');
                                opt.value = row.item;
                                const sg = row.salary_grade ? ` (SG-${row.salary_grade})` : '';
                                const pos = row.position_title ? ` — ${row.position_title}` : '';
                                opt.textContent = `${row.item}${pos}${sg}`;
                                itemCodeSelect.appendChild(opt);
                            });

                            // Show dropdown
                            itemDropWrap.classList.remove('hidden');
                        } else {
                            // No vacants — let user type manually
                            const blank = document.createElement('option');
                            blank.value = '';
                            blank.textContent = 'No vacant items for this office';
                            itemCodeSelect.appendChild(blank);
                            itemDropWrap.classList.remove('hidden');
                        }
                    })
                    .catch(err => console.error('Error fetching items:', err));
            }

            // When user picks an item from the dropdown → auto-fill position title & SG
            if (itemCodeSelect) {
                itemCodeSelect.addEventListener('change', function () {
                    const selectedItem = this.value;
                    if (itemCodeInput && selectedItem) {
                        itemCodeInput.value = selectedItem;
                        itemCodeInput.dispatchEvent(new Event('input', { bubbles: true }));
                    }

                    if (!selectedItem) return;

                    // Fetch details for the selected item and auto-fill position fields
                    fetch(`/plantilla/item-details?item=${encodeURIComponent(selectedItem)}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data && Object.keys(data).length > 0) {
                                // Auto-fill fields if they exist in the response
                                const fieldsToFill = {
                                    'pos_title_select': data.position_title,
                                    'position_classification': data.position_classification,
                                    'salary_grade': data.salary_grade,
                                    'step': data.step,
                                    'authorized_annual_salary': data.authorized_annual_salary,
                                    'actual_annual_salary': data.actual_annual_salary,
                                    'emp_status_select': data.employment_status,
                                    'area_code': data.area_code,
                                    'area_type': data.area_type,
                                    'level': data.level
                                };

                                for (const [idOrName, value] of Object.entries(fieldsToFill)) {
                                    if (value !== null && value !== undefined) {
                                        let input = document.getElementById(idOrName) || document.querySelector(`[name="${idOrName}"]`);
                                        if (input) {
                                            // Handle specific cases for "Others" dropdowns
                                            if (idOrName === 'pos_title_select') {
                                                let optionExists = Array.from(input.options).some(opt => opt.value === value);
                                                if (optionExists) {
                                                    input.value = value;
                                                    if (window.handlePosTitleChange) window.handlePosTitleChange(input);
                                                } else {
                                                    input.value = 'Others';
                                                    if (window.handlePosTitleChange) window.handlePosTitleChange(input);
                                                    let posInput = document.getElementById('pos_title_input');
                                                    if (posInput) posInput.value = value;
                                                }
                                                // Flash effect
                                                input.style.transition = 'background .4s';
                                                input.style.background = '#e0e7ff';
                                                setTimeout(() => input.style.background = '', 900);
                                            } else if (idOrName === 'emp_status_select') {
                                                let optionExists = Array.from(input.options).some(opt => opt.value === value);
                                                if (optionExists) {
                                                    input.value = value;
                                                    if (window.handleEmpStatusChange) window.handleEmpStatusChange(input);
                                                } else {
                                                    input.value = '__others__';
                                                    if (window.handleEmpStatusChange) window.handleEmpStatusChange(input);
                                                    let empInput = document.getElementById('emp_status_input');
                                                    if (empInput) empInput.value = value;
                                                }
                                            } else {
                                                input.value = value;
                                                if (idOrName === 'salary_grade') {
                                                    input.dispatchEvent(new Event('change', { bubbles: true })); // trigger salary fetch
                                                    // Flash effect
                                                    input.style.transition = 'background .4s';
                                                    input.style.background = '#e0e7ff';
                                                    setTimeout(() => input.style.background = '', 900);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        })
                        .catch(error => console.error('Error fetching item details:', error));
                });
            }

            let ouTimeout = null;
            if (ouInput) {
                ouInput.addEventListener('input', () => {
                    clearTimeout(ouTimeout);
                    ouTimeout = setTimeout(fetchItemsByOffice, 300);
                });
                ouInput.addEventListener('change', fetchItemsByOffice);
                // Also trigger on load in case there's an old value
                if (ouInput.value) fetchItemsByOffice();
            }

            // Explicit Vacant Toggle
            function handleVacantToggle() {
                const isVacant = vacantToggle.checked;
                const inputs = empContainer.querySelectorAll('input, select, textarea');

                if (isVacant) {
                    empContainer.classList.add('opacity-50');
                    inputs.forEach(input => {
                        // Don't disable hidden inputs
                        if (input.type !== 'hidden') {
                            input.disabled = true;
                            // Clear values
                            if (input.type === 'checkbox' || input.type === 'radio') {
                                input.checked = false;
                            } else {
                                input.value = '';
                            }
                        }
                    });
                } else {
                    empContainer.classList.remove('opacity-50');
                    inputs.forEach(input => {
                        input.disabled = false;
                    });
                }
            }

            if (vacantToggle) {
                vacantToggle.addEventListener('change', handleVacantToggle);
                // Also check on load in case browser restores checkbox state or if already vacant
                handleVacantToggle();
            }

            // PWD Toggle
            const isPwdCheckbox = document.getElementById('is_pwd');
            const typeOfDisabilitySelect = document.getElementById('type_of_disability');

            if (isPwdCheckbox && typeOfDisabilitySelect) {
                isPwdCheckbox.addEventListener('change', function () {
                    if (this.checked) {
                        typeOfDisabilitySelect.classList.remove('hidden');
                        typeOfDisabilitySelect.required = true;
                    } else {
                        typeOfDisabilitySelect.classList.add('hidden');
                        typeOfDisabilitySelect.value = '';
                        typeOfDisabilitySelect.required = false;
                    }
                });
                // Initial state on load
                if (isPwdCheckbox.checked) {
                    typeOfDisabilitySelect.classList.remove('hidden');
                    typeOfDisabilitySelect.required = true;
                } else {
                    typeOfDisabilitySelect.classList.add('hidden');
                    typeOfDisabilitySelect.required = false;
                }
            }

            // Apprehended and Admin Charge Toggles
            const isApprehendedCheck = document.getElementById('is_apprehended');
            const apprehendedDates = document.getElementById('apprehended_dates');
            if (isApprehendedCheck && apprehendedDates) {
                isApprehendedCheck.addEventListener('change', function () {
                    if (this.checked) apprehendedDates.classList.remove('hidden');
                    else apprehendedDates.classList.add('hidden');
                });
            }

            const isAdminChargeCheck = document.getElementById('is_admin_charge');
            const adminChargeDetails = document.getElementById('admin_charge_details');
            if (isAdminChargeCheck && adminChargeDetails) {
                isAdminChargeCheck.addEventListener('change', function () {
                    if (this.checked) adminChargeDetails.classList.remove('hidden');
                    else adminChargeDetails.classList.add('hidden');
                });
            }

            // Multiple Admin Charges Logic
            const chargesList = document.getElementById('admin_charges_list');
            const btnAddCharge = document.getElementById('btn_add_charge');
            let chargeIndex = document.querySelectorAll('.admin-charge-row').length || 1;

            if (btnAddCharge && chargesList) {
                btnAddCharge.addEventListener('click', function () {
                    const row = document.createElement('div');
                    row.className = 'admin-charge-row flex flex-wrap items-start gap-2 bg-white p-2 rounded-lg border border-red-100 relative';
                    row.innerHTML = `
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] text-red-700 font-bold uppercase">From:</span>
                            <input type="date" name="admin_charges[${chargeIndex}][from]" class="border border-red-300 rounded-lg px-2 py-1 text-xs focus:ring-2 focus:ring-red-500 outline-none w-[110px]">
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] text-red-700 font-bold uppercase">To:</span>
                            <input type="date" name="admin_charges[${chargeIndex}][to]" class="border border-red-300 rounded-lg px-2 py-1 text-xs focus:ring-2 focus:ring-red-500 outline-none w-[110px]">
                        </div>
                        <div class="flex flex-col gap-1 flex-1 min-w-[200px]">
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] text-red-700 font-bold uppercase">Type:</span>
                                <select class="admin-charge-type-select border border-red-300 rounded-lg px-2 py-1 text-xs focus:ring-2 focus:ring-red-500 outline-none bg-white flex-1">
                                    <option value="">Select...</option>
                                    <option value="Stern warning">Stern warning</option>
                                    <option value="Reprimand">Reprimand</option>
                                    <option value="Suspension">Suspension</option>
                                    <option value="Others">Others (Please Specify)</option>
                                </select>
                            </div>
                            <input type="text" name="admin_charges[${chargeIndex}][type]" placeholder="Specify type..." class="admin-charge-type-input border border-red-300 rounded-lg px-2 py-1 text-xs focus:ring-2 focus:ring-red-500 outline-none w-full hidden">
                        </div>
                        <button type="button" class="btn-remove-charge flex items-center justify-center w-7 h-7 mt-0.5 text-red-500 bg-white border border-red-200 hover:bg-red-50 hover:border-red-300 hover:text-red-600 rounded-lg transition-all shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500" title="Remove Charge">
                            <i class="bi bi-trash"></i>
                        </button>
                    `;
                    chargesList.appendChild(row);
                    chargeIndex++;
                });

                chargesList.addEventListener('click', function (e) {
                    const btn = e.target.closest('.btn-remove-charge');
                    if (btn) {
                        const rows = chargesList.querySelectorAll('.admin-charge-row');
                        if (rows.length > 1) {
                            btn.closest('.admin-charge-row').remove();
                        } else {
                            // If it's the last row, just clear the inputs
                            const row = btn.closest('.admin-charge-row');
                            row.querySelectorAll('input').forEach(inp => inp.value = '');
                            row.querySelectorAll('select').forEach(sel => sel.value = '');
                        }
                    }
                });

                chargesList.addEventListener('change', function (e) {
                    if (e.target.classList.contains('admin-charge-type-select')) {
                        const select = e.target;
                        const container = select.closest('.flex-col');
                        const input = container.querySelector('.admin-charge-type-input');
                        if (select.value === 'Others') {
                            input.classList.remove('hidden');
                            input.value = '';
                            input.focus();
                        } else {
                            input.classList.add('hidden');
                            input.value = select.value;
                        }
                    }
                });
            }

            // Civil Service Eligibility Toggle
            const cseSelect = document.getElementById('cse_select');
            const cseInput = document.getElementById('cse_input');
            if (cseSelect && cseInput) {
                cseSelect.addEventListener('change', function () {
                    if (this.value === 'Others') {
                        cseInput.classList.remove('hidden');
                        if (['Professional', 'Sub-Professional'].includes(cseInput.value)) {
                            cseInput.value = '';
                        }
                        cseInput.focus();
                    } else {
                        cseInput.classList.add('hidden');
                        cseInput.value = this.value;
                    }
                });
            }

            // IP Toggle
            const ipCheckbox = document.getElementById('is_ip_checkbox');
            const ipContainer = document.getElementById('ip_container');
            const ipSelect = document.getElementById('ip_select');
            const ipOthers = document.getElementById('ip_others');
            const ipHidden = document.getElementById('ip_hidden');

            if (ipCheckbox) {
                function updateIp() {
                    if (ipCheckbox.checked) {
                        ipContainer.classList.remove('hidden');
                        if (ipSelect.value === 'Others') {
                            ipOthers.classList.remove('hidden');
                            ipHidden.value = ipOthers.value;
                        } else {
                            ipOthers.classList.add('hidden');
                            ipHidden.value = ipSelect.value || 'Y'; // default to 'Y' if checked but nothing selected
                        }
                    } else {
                        ipContainer.classList.add('hidden');
                        ipHidden.value = '';
                    }
                }

                ipCheckbox.addEventListener('change', updateIp);
                ipSelect.addEventListener('change', updateIp);
                ipOthers.addEventListener('input', updateIp);
            }

            // Create record SweetAlert confirmation
            const createForm = document.getElementById('create-record-form');
            if (createForm) {
                createForm.addEventListener('submit', function (e) {
                    e.preventDefault();
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Create Record?',
                            text: "Are you sure you want to add this new record? This action will permanently modify the database.",
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, create it!',
                            cancelButtonText: 'Cancel',
                            customClass: {
                                popup: 'custom-swal',
                                icon: 'custom-swal-icon',
                                title: 'custom-swal-title',
                                htmlContainer: 'custom-swal-text',
                                actions: 'custom-swal-actions',
                                confirmButton: 'custom-swal-confirm',
                                cancelButton: 'custom-swal-cancel',
                                footer: 'custom-swal-footer'
                            },
                            buttonsStyling: false,
                            footer: 'HDMS- Human Resource Data Management System'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                createForm.submit();
                            }
                        });
                    } else {
                        createForm.submit();
                    }
                });
            }
        });

        // ── Wizard Controller ────────────────────────────────────────
        let wizStep = 1;
        const wizSteps = ['wizard-step-1', 'wizard-step-2', 'wizard-step-3'];

        function wizShow(n) {
            wizSteps.forEach((id, i) => {
                const el = document.getElementById(id);
                if (el) el.classList.toggle('wz-visible', i + 1 === n);
            });
            ['wz-s1', 'wz-s2', 'wz-s3'].forEach((id, i) => {
                const el = document.getElementById(id);
                if (!el) return;
                el.classList.toggle('wz-active', i + 1 === n);
                el.classList.toggle('wz-done', i + 1 < n);
            });
            ['wz-l1', 'wz-l2'].forEach((id, i) => {
                const el = document.getElementById(id);
                if (el) el.classList.toggle('wz-done', i + 1 < n);
            });
            const prev = document.getElementById('wz-prev-btn');
            const next = document.getElementById('wz-next-btn');
            const sub = document.getElementById('wz-submit-btn');
            if (prev) prev.style.display = n === 1 ? 'none' : '';
            if (next) next.style.display = n === 3 ? 'none' : '';
            if (sub) sub.style.display = n === 3 ? '' : 'none';
            if (n === 3) wizBuildReview();
            wizStep = n;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function wizValidateStep(n) {
            const step = document.getElementById(wizSteps[n - 1]);
            if (!step) return true;
            let ok = true;
            step.querySelectorAll('[required]').forEach(el => {
                if (!el.value.trim()) {
                    el.style.outline = '2px solid #ef4444';
                    ok = false;
                } else {
                    el.style.outline = '';
                }
            });
            // Step 1 manual checks
            if (n === 1) {
                const orgVal = document.getElementById('org_unit_input');
                const sgVal = document.getElementById('salary_grade');
                const itemCodeInput = document.getElementById('item_code');
                const itemSelEl = document.getElementById('item_code_select');
                const dropVisible = itemSelEl && !document.getElementById('item_dropdown_wrap')?.classList.contains('hidden');

                if (orgVal && !orgVal.value.trim()) { orgVal.style.outline = '2px solid #ef4444'; ok = false; }
                else if (orgVal) orgVal.style.outline = '';

                if (!itemCodeInput || !itemCodeInput.value.trim()) {
                    if (itemCodeInput) itemCodeInput.style.outline = '2px solid #ef4444';
                    ok = false;
                } else if (itemCodeInput) {
                    itemCodeInput.style.outline = '';
                }

                if (sgVal && !sgVal.value) { sgVal.style.outline = '2px solid #ef4444'; ok = false; }
                else if (sgVal) sgVal.style.outline = '';
            }
            if (!ok) {
                const first = step.querySelector('[style*="2px solid #ef4444"]');
                if (first) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            return ok;
        }

        function wizNext() {
            if (wizValidateStep(wizStep) && wizStep < 3) wizShow(wizStep + 1);
        }
        function wizPrev() { if (wizStep > 1) wizShow(wizStep - 1); }

        function wizBuildReview() {
            const body = document.getElementById('wz-review-body');
            if (!body) return;
            const fields = [
                ['Org. Unit', document.getElementById('org_unit_input')?.value],
                ['Position', document.getElementById('pos_title_input')?.value],
                ['Item Code', document.getElementById('item_code')?.value],
                ['Salary Grade', document.getElementById('salary_grade')?.value],
                ['Step', document.getElementById('step')?.value],
                ['Last Name', document.querySelector('[name="last_name"]')?.value],
                ['First Name', document.querySelector('[name="first_name"]')?.value],
                ['Sex', document.querySelector('[name="sex"]')?.value],
                ['TIN', document.querySelector('[name="tin"]')?.value],
            ];
            body.innerHTML = fields.filter(f => f[1]).map(f =>
                `<div class="flex flex-col gap-0.5 bg-gray-50 rounded-lg p-3 border border-gray-100">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">${f[0]}</span>
                    <span class="text-sm font-semibold text-gray-800">${f[1] || '—'}</span>
                </div>`
            ).join('');
        }

        document.addEventListener('DOMContentLoaded', () => wizShow(1));

        // Position Title dropdown handler
        window.handlePosTitleChange = function (sel) {
            const inp = document.getElementById('pos_title_input');
            if (!inp) return;
            if (sel.value === 'Others') {
                inp.classList.remove('hidden');
                inp.value = '';
                inp.focus();
            } else {
                inp.classList.add('hidden');
                inp.value = sel.value;
            }
        };

        // ── Employee Code Auto-fill Logic ──────────────────────────────────────────
        const lastNameInput = document.querySelector('input[name="last_name"]');
        const dobInput = document.querySelector('input[name="date_of_birth"]');
        const empCodeInput = document.getElementById('employee_code');
        const btnGenerateCode = document.getElementById('btn-generate-code');

        function generateCode() {
            if (!lastNameInput || !dobInput || !empCodeInput) return;
            const ln = lastNameInput.value.trim().toUpperCase();
            const dob = dobInput.value; // YYYY-MM-DD
            if (ln && dob) {
                const initial = ln.charAt(0);
                const parts = dob.split('-');
                if (parts.length === 3) {
                    const formattedDob = `${parts[2]}${parts[1]}${parts[0]}`; // DDMMYYYY
                    const newCode = `${formattedDob}${initial}`;
                    empCodeInput.value = newCode;

                    // Flash effect
                    empCodeInput.style.transition = 'background-color 0.3s, transform 0.1s';
                    empCodeInput.style.backgroundColor = '#a7f3d0';
                    empCodeInput.style.transform = 'scale(1.02)';
                    setTimeout(() => {
                        empCodeInput.style.backgroundColor = '';
                        empCodeInput.style.transform = '';
                    }, 300);
                }
            }
        }

        if (btnGenerateCode) {
            btnGenerateCode.addEventListener('click', generateCode);
        }

        // Auto-update as they type, only if empty or matches previous generation
        let lastGeneratedCode = '';
        function autoGenerateCode() {
            if (empCodeInput.value === '' || empCodeInput.value === lastGeneratedCode) {
                generateCode();
                lastGeneratedCode = empCodeInput.value;
            }
        }

        if (lastNameInput) lastNameInput.addEventListener('input', autoGenerateCode);
        if (dobInput) dobInput.addEventListener('change', autoGenerateCode);

    </script>
</x-dashboard-app>