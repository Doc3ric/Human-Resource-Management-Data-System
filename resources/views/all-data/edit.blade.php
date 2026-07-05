<x-dashboard-app>
    <div class="max-w-4xl mx-auto space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ session('last_index_url', route('all-data.index')) }}" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Edit Record</h1>
                    <p class="text-gray-500 text-sm">Update the plantilla record — <span
                            class="font-semibold text-gray-700">{{ $plantilla->item_no_new }}</span></p>
                </div>
            </div>
            @if(!$plantilla->is_vacant)
            <div>
                <a href="{{ route('plantilla.promote.form', $plantilla) }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium text-sm transition shadow-sm">
                    <i class="bi bi-person-up"></i> Promote / Transfer
                </a>
            </div>
            @endif
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

        <form id="edit-record-form" method="POST" action="{{ route('all-data.update', $plantilla) }}" class="space-y-6">
            @csrf @method('PUT')
            

            {{-- Position Information --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-base font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">Position
                    Information</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

                    <div class="lg:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">OFFICE<span
                                class="text-red-500">*</span></label>
                        <div class="flex flex-col gap-2">
                            @php $oldOrg = old('office_department', $plantilla->office_department); @endphp
                            <select id="org_unit_select"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 bg-white outline-none">
                                <option value="">Select an office...</option>
                                @foreach($offices as $office)
                                    <option value="{{ $office }}" {{ $oldOrg === $office ? 'selected' : '' }}>{{ $office }}
                                    </option>
                                @endforeach
                                <option value="Others" {{ $oldOrg && !$offices->contains($oldOrg) ? 'selected' : '' }}>
                                    Others (Please specify)</option>
                            </select>
                            <input type="text" name="office_department" id="org_unit_input" value="{{ $oldOrg }}"
                                placeholder="Type OFFICE manually..."
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none {{ $oldOrg && !$offices->contains($oldOrg) ? '' : 'hidden' }}"
                                required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Item (Position Code)</label>
                        <div class="flex flex-col gap-2">
                            <div id="item_dropdown_wrap" class="hidden relative">
                                <select id="item_code_select"
                                    class="w-full border border-indigo-400 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none font-mono bg-indigo-50 text-indigo-900 font-semibold">
                                    <option value="">⬇ Select a vacant item…</option>
                                </select>
                                <p class="text-[10px] text-indigo-500 mt-1 flex items-center gap-1">
                                    <i class="bi bi-info-circle"></i>
                                    Selecting one will replace the item code below.
                                </p>
                            </div>
                            <input type="text" name="item_no_new" id="item_code" value="{{ old('item_no_new', $plantilla->item_no_new) }}"
                                placeholder="Type Item (Position Code)..."
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none font-mono bg-white">
                        </div>
                    </div>

                    <div class="lg:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Position Title <span
                                class="text-red-500">*</span></label>
                        @php
                            $oldPosTitle = old('position_title', $plantilla->position_title);
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
                                    <option value="{{ $pt }}" {{ $oldPosTitle === $pt ? 'selected' : '' }}>{{ $pt }}</option>
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
                            <option value="EXECUTIVE/MANAGERIAL" {{ old('position_classification', $plantilla->position_classification) === 'EXECUTIVE/MANAGERIAL' ? 'selected' : '' }}>
                                Executive/Managerial</option>
                            <option value="2nd Level" {{ old('position_classification', $plantilla->position_classification) === '2nd Level' ? 'selected' : '' }}>2nd Level
                            </option>
                            <option value="1st Level" {{ old('position_classification', $plantilla->position_classification) === '1st Level' ? 'selected' : '' }}>1st Level
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Salary Grade <span
                                class="text-red-500">*</span></label>
                        <input type="number" name="salary_grade" id="salary_grade" min="1" max="33"
                            value="{{ old('salary_grade', $plantilla->salary_grade) }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                            required>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Step <span
                                class="text-red-500">*</span></label>
                        <select name="step" id="step"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white"
                            required>
                            @for($s = 1; $s <= 8; $s++)
                                <option value="{{ $s }}" {{ (int) old('step', $plantilla->step) === $s ? 'selected' : '' }}>
                                    Step
                                    {{ $s }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Authorized Annual Salary <span
                                class="text-gray-400 text-[10px] ml-1">(Calculates from SG if blank)</span></label>
                        <input type="text" inputmode="decimal" name="authorized_annual_salary" id="authorized_annual_salary"
                            value="{{ old('authorized_annual_salary', $plantilla->authorized_annual_salary) }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none peso-input">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Actual Annual Salary <span
                                class="text-gray-400 text-[10px] ml-1">(Calculates from SG if blank)</span></label>
                        <input type="text" inputmode="decimal" name="base_salary_amount" id="base_salary_amount"
                            value="{{ old('base_salary_amount', $plantilla->base_salary_amount) }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none peso-input">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Employment Status</label>
                        @php
                            $empStatus = old('employment_status', $plantilla->employment_status);
                            $knownStatuses = ['P', 'CT', 'E', 'Casual', 'JO'];
                            $isEmpStatusOther = $empStatus && !in_array($empStatus, $knownStatuses);
                        @endphp
                        <div class="flex flex-col gap-2">
                            <select id="emp_status_select"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white"
                                onchange="handleEmpStatusChange(this)">
                                <option value="">Select…</option>
                                <option value="P" {{ $empStatus === 'P' ? 'selected' : '' }}>REGULAR</option>
                                <option value="CT" {{ $empStatus === 'CT' ? 'selected' : '' }}>Co-Terminous</option>
                                <option value="E" {{ $empStatus === 'E' ? 'selected' : '' }}>Elected</option>
                                <option value="Casual" {{ $empStatus === 'Casual' ? 'selected' : '' }}>Casual</option>
                                <option value="JO" {{ $empStatus === 'JO' ? 'selected' : '' }}>Job Order</option>
                                <option value="__others__" {{ $isEmpStatusOther ? 'selected' : '' }}>Others (Please Specify)</option>
                            </select>
                            <input type="text" name="employment_status" id="emp_status_input"
                                value="{{ $empStatus }}"
                                placeholder="Specify employment status..."
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none {{ $isEmpStatusOther ? '' : 'hidden' }}">
                            {{-- Hidden input used when a known status is selected --}}
                            <input type="hidden" id="emp_status_known" name="employment_status" value="{{ $isEmpStatusOther ? '' : $empStatus }}">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Area Code</label>
                        <input type="text" name="area_code" value="{{ old('area_code', $plantilla->area_code) }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Area Type</label>
                        <input type="text" name="area_type" value="{{ old('area_type', $plantilla->area_type) }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Level</label>
                        <input type="text" name="level" value="{{ old('level', $plantilla->level) }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>
                </div>
            </div>

            {{-- Employee Information --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex justify-between items-center mb-1 pb-2 border-b border-gray-100">
                    <h2 class="text-base font-semibold text-gray-800">Employee Information</h2>
                    <label
                        class="flex items-center gap-2 cursor-pointer bg-gray-50 px-3 py-1.5 rounded-md border border-gray-200">
                        <input type="checkbox" id="explicit_vacant_toggle"
                            class="rounded w-4 h-4 text-blue-600 focus:ring-blue-500" {{ $plantilla->is_vacant ? 'checked' : '' }}>
                        <span class="text-xs font-medium text-gray-700">Mark as Vacant Position</span>
                    </label>
                </div>

                <div id="employee_fields_container" class="transition-opacity duration-200">
                    <p class="text-xs text-gray-400 mb-4">Leave blank or check the box above if this is a vacant
                        position.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">LAST NAME</label>
                            <input type="text" name="last_name" value="{{ old('last_name', $plantilla->last_name) }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">First Name</label>
                            <input type="text" name="first_name" value="{{ old('first_name', $plantilla->first_name) }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">MIDDLE NAME</label>
                            <input type="text" name="middle_name"
                                value="{{ old('middle_name', $plantilla->middle_name) }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">SUFFIX</label>
                            <input type="text" name="name_extension"
                                value="{{ old('name_extension', $plantilla->name_extension) }}"
                                placeholder="Jr. / Sr. / III"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">SEX</label>
                            <select name="sex"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white">
                                <option value="">Not specified</option>
                                <option value="M" {{ old('sex', $plantilla->sex) === 'M' ? 'selected' : '' }}>Male (M)</option>
                                <option value="F" {{ old('sex', $plantilla->sex) === 'F' ? 'selected' : '' }}>Female (F)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">CIVIL STATUS</label>
                            <select name="civil_status"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white">
                                <option value="">— Select —</option>
                                @foreach(['SINGLE','MARRIED','WIDOW','WIDOWER','SEPARATED','ANNULLED'] as $cs)
                                    <option value="{{ $cs }}" {{ old('civil_status', $plantilla->civil_status) === $cs ? 'selected' : '' }}>{{ $cs }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Religion</label>
                            <input type="text" name="religion" value="{{ old('religion', $plantilla->religion) }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">DATE OF BIRTH</label>
                            <input type="date" name="date_of_birth"
                                value="{{ old('date_of_birth', $plantilla->date_of_birth?->format('Y-m-d')) }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">TIN</label>
                            <input type="text" name="tin" value="{{ old('tin', $plantilla->tin) }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none font-mono">
                        </div>
                        <div>
                            <label class="flex justify-between items-center mb-1">
                                <span class="text-xs font-medium text-gray-600">Employee Code</span>
                                <button type="button" id="btn-generate-code" class="text-[10px] font-bold text-blue-600 hover:text-blue-800 bg-blue-50 px-2 py-0.5 rounded border border-blue-200 uppercase transition" title="Auto-fill using Birthday & Last Name">Auto-Fill</button>
                            </label>
                            <input type="text" name="employee_code" id="employee_code" value="{{ old('employee_code', $plantilla->employee_code) }}"
                                placeholder="DDMMYYYY + Initial"
                                class="w-full border border-emerald-300 bg-emerald-50 text-emerald-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none font-mono font-bold placeholder-emerald-300 shadow-inner">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Date of Original
                                Appointment</label>
                            <input type="date" name="date_original_appointment"
                                value="{{ old('date_original_appointment', $plantilla->date_original_appointment?->format('Y-m-d')) }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Date of Last Promotion</label>
                            <input type="date" name="date_last_promotion"
                                value="{{ old('date_last_promotion', $plantilla->date_last_promotion?->format('Y-m-d')) }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Civil Service
                                Eligibility</label>
                            <div class="flex flex-col gap-2">
                                @php $oldCse = old('civil_service_eligibility', $plantilla->civil_service_eligibility); @endphp
                                <select id="cse_select"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 bg-white outline-none">
                                    <option value="" {{ empty($oldCse) ? 'selected' : '' }}>None / Not Applicable
                                    </option>
                                    <option value="Professional" {{ $oldCse === 'Professional' ? 'selected' : '' }}>
                                        Professional</option>
                                    <option value="Sub-Professional" {{ $oldCse === 'Sub-Professional' ? 'selected' : '' }}>Sub-Professional</option>
                                    <option value="Others" {{ $oldCse && !in_array($oldCse, ['Professional', 'Sub-Professional']) ? 'selected' : '' }}>Others (Please specify)</option>
                                </select>
                                <input type="text" name="civil_service_eligibility" id="cse_input" value="{{ $oldCse }}"
                                    placeholder="Type eligibility manually..."
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none {{ $oldCse && !in_array($oldCse, ['Professional', 'Sub-Professional']) ? '' : 'hidden' }}">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">GSIS BP Number</label>
                            <input type="text" name="gsis_bp_number"
                                value="{{ old('gsis_bp_number', $plantilla->gsis_bp_number) }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">UMID</label>
                            <input type="text" name="umid" value="{{ old('umid', $plantilla->umid) }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Solo Parent ID</label>
                            <input type="text" name="solo_parent"
                                value="{{ old('solo_parent', $plantilla->solo_parent) }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Leave Without Pay (Days)</label>
                            <input type="number" min="0" name="lwop" value="{{ old('lwop', $plantilla->lwop) }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-6 mt-4 items-center">
                        <div class="flex items-center gap-2">
                            <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                <input type="checkbox" name="is_pwd" id="is_pwd" value="1" {{ old('is_pwd', $plantilla->is_pwd) ? 'checked' : '' }} class="rounded"> PWD
                            </label>
                            <input type="text" name="type_of_disability" id="type_of_disability"
                                value="{{ old('type_of_disability', $plantilla->type_of_disability) }}"
                                placeholder="Type of Disability (e.g. Physical, Visual)"
                                class="border border-gray-300 rounded-lg px-2 py-1 text-xs focus:ring-2 focus:ring-blue-500 outline-none bg-white {{ old('is_pwd', $plantilla->is_pwd) ? '' : 'hidden' }}">
                        </div>
                        <div class="flex items-center gap-2" style="flex-wrap: wrap;">
                            @php $oldIp = old('indigenous_people', $plantilla->indigenous_people); @endphp
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
                                            {{ $ipGroup }}
                                        </option>
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
                            <input type="checkbox" name="is_health_worker" value="1" {{ old('is_health_worker', $plantilla->is_health_worker) ? 'checked' : '' }}
                                class="rounded text-pink-600 focus:ring-pink-500">
                            <span class="text-pink-700 font-semibold"><i class="bi bi-heart-pulse-fill"></i> Health
                                Worker</span>
                        </label>
                        {{-- Vacant status is auto-detected: cleared employee name → Vacant; name present → Filled --}}
                        <div class="flex flex-col gap-2 p-3 bg-purple-50 rounded-lg border border-purple-200 w-full sm:col-span-3">
                            <label class="flex items-center gap-2 text-sm font-semibold text-purple-800 cursor-pointer"
                                title="Process separation (e.g. Retirement, Resignation) and auto-declare vacant">
                                <input type="checkbox" name="process_separation" id="process_separation" value="1"
                                    class="rounded text-purple-600 focus:ring-purple-500"> 🛫 Mark Employee as Separated/Retired
                            </label>
                            <div id="separation_details" class="hidden flex flex-col gap-2 pl-6 mt-2">
                                <p class="text-xs text-purple-600 mb-1">Checking this will record the separation and <strong>automatically clear the employee's information</strong> to make the position vacant upon saving.</p>
                                <div class="flex flex-wrap gap-4">
                                    <div class="flex-1 min-w-[200px]">
                                        <label class="block text-[10px] font-bold text-purple-700 uppercase mb-1">Nature of Separation</label>
                                        <select name="nature_of_separation" class="w-full border border-purple-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 bg-white outline-none">
                                            <option value="COMPULSORY RETIREMENT">Compulsory Retirement</option>
                                            <option value="OPTIONAL RETIREMENT">Optional Retirement</option>
                                            <option value="RESIGNED">Resigned</option>
                                            <option value="TERMINATED">Terminated</option>
                                            <option value="DECEASED">Deceased</option>
                                            <option value="DROPPED FROM ROLLS">Dropped from Rolls</option>
                                            <option value="TRANSFERRED">Transferred</option>
                                        </select>
                                    </div>
                                    <div class="flex-1 min-w-[200px]">
                                        <label class="block text-[10px] font-bold text-purple-700 uppercase mb-1">Effectivity Date</label>
                                        <input type="date" name="date_separated" value="{{ date('Y-m-d') }}" class="w-full border border-purple-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 bg-white outline-none">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                            <input type="checkbox" name="abolished" value="1" {{ old('abolished', $plantilla->abolished) ? 'checked' : '' }} class="rounded"> Abolished
                        </label>
                        <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                            <input type="checkbox" name="dissolved" value="1" {{ old('dissolved', $plantilla->dissolved) ? 'checked' : '' }} class="rounded"> Dissolved
                        </label>
                        <div class="flex flex-col gap-2 p-3 bg-orange-50 rounded-lg border border-orange-200">
                            <label class="flex items-center gap-2 text-sm font-semibold text-orange-800 cursor-pointer"
                                title="Excludes employee from NOSI/NOLP step increment processing">
                                <input type="checkbox" name="is_apprehended" id="is_apprehended" value="1" {{ old('is_apprehended', $plantilla->is_apprehended) ? 'checked' : '' }}
                                    class="rounded text-orange-600 focus:ring-orange-500"> ⚠ Apprehended
                            </label>
                            <div id="apprehended_dates"
                                class="{{ old('is_apprehended', $plantilla->is_apprehended) ? '' : 'hidden' }} flex items-center gap-2 pl-6">
                                <span class="text-xs text-orange-700 font-medium">From:</span>
                                <input type="date" name="apprehended_from"
                                    value="{{ old('apprehended_from', $plantilla->apprehended_from?->format('Y-m-d')) }}"
                                    class="border border-orange-300 rounded-lg px-2 py-1 text-xs focus:ring-2 focus:ring-orange-500 outline-none bg-white">
                            </div>
                        </div>

                        <div class="flex flex-col gap-2 p-3 bg-red-50 rounded-lg border border-red-200">
                            <label class="flex items-center gap-2 text-sm font-semibold text-red-800 cursor-pointer"
                                title="Excludes employee from NOSI/NOLP step increment processing">
                                <input type="checkbox" name="is_admin_charge" id="is_admin_charge" value="1" {{ old('is_admin_charge', $plantilla->is_admin_charge) ? 'checked' : '' }}
                                    class="rounded text-red-600 focus:ring-red-500"> ⚠ Admin Charge
                            </label>
                            <div id="admin_charge_details"
                                class="{{ old('is_admin_charge', $plantilla->is_admin_charge) ? '' : 'hidden' }} flex flex-col gap-2 pl-6 mt-2">
                                @php
                                    $oldCharges = old('admin_charges', is_array($plantilla->admin_charges) ? $plantilla->admin_charges : []);
                                    if (empty($oldCharges) && !empty($plantilla->admin_charge_from)) {
                                        $oldCharges[] = [
                                            'from' => $plantilla->admin_charge_from?->format('Y-m-d'),
                                            'to' => $plantilla->admin_charge_to?->format('Y-m-d'),
                                            'type' => $plantilla->admin_charge_type
                                        ];
                                    }
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
                                                    <span class="text-[10px] text-red-700 font-bold uppercase">Type:</span>
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

                    {{-- Termination --}}
                    @php
                        $terminationOptions = [
                            'Retirement Mandatory',
                            'Retirement Optional',
                            'Separation Voluntary',
                            'Separation Involuntary',
                        ];
                        $oldTermination = old('nature_of_separation', $plantilla->nature_of_separation);
                        $isTerminationOther = $oldTermination && !in_array($oldTermination, $terminationOptions);
                    @endphp
                    <div class="mt-4">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Termination</label>
                        <div class="flex flex-col gap-2">
                            <select id="termination_select"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white"
                                onchange="handleTerminationChange(this)">
                                <option value="">Select termination type…</option>
                                @foreach($terminationOptions as $tOpt)
                                    <option value="{{ $tOpt }}" {{ $oldTermination === $tOpt ? 'selected' : '' }}>{{ $tOpt }}</option>
                                @endforeach
                                <option value="__others__" {{ $isTerminationOther ? 'selected' : '' }}>Others (Please Specify)</option>
                            </select>
                            <input type="text" name="nature_of_separation" id="termination_input"
                                value="{{ $oldTermination }}"
                                placeholder="Please specify termination reason..."
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none {{ $isTerminationOther ? '' : 'hidden' }}">
                            {{-- Hidden input used when a known option is selected --}}
                            <input type="hidden" id="termination_known" name="nature_of_separation" value="{{ $isTerminationOther ? '' : $oldTermination }}">
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Nature of separation / termination from service.</p>
                    </div>

                    <div class="mt-4">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Comment / Annotation</label>
                        <textarea name="remarks_annotation" rows="2"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">{{ old('remarks_annotation', $plantilla->remarks_annotation) }}</textarea>
                    </div>
                    
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex gap-3 justify-end">
                <a href="{{ session('last_index_url', route('all-data.index')) }}"
                    class="px-5 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                    Cancel
                </a>
                <button type="submit"
                    class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sgInput = document.getElementById('salary_grade');
            const stepSelect = document.getElementById('step');
            const authSalaryInput = document.getElementById('authorized_annual_salary');
            const actualSalaryInput = document.getElementById('base_salary_amount');

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
                                authSalaryInput.value = window.pesoFormat ? window.pesoFormat(data.annual_salary) : data.annual_salary;
                                actualSalaryInput.value = window.pesoFormat ? window.pesoFormat(data.annual_salary) : data.annual_salary;
                            }
                        })
                        .catch(err => console.error('Error fetching salary:', err));
                }
            }

            if (sgInput && stepSelect) {
                sgInput.addEventListener('change', fetchSalary);
                stepSelect.addEventListener('change', fetchSalary);

                // Don't auto-fetch on load for edit form unless both salaries are blank
                if (sgInput.value && (!authSalaryInput.value || !actualSalaryInput.value)) {
                    fetchSalary();
                }
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

            // Separation details toggle
            const processSeparationCheck = document.getElementById('process_separation');
            const separationDetails = document.getElementById('separation_details');
            if (processSeparationCheck && separationDetails) {
                processSeparationCheck.addEventListener('change', function () {
                    if (this.checked) separationDetails.classList.remove('hidden');
                    else separationDetails.classList.add('hidden');
                });
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

            // OFFICE Toggle
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

            // Edit record SweetAlert confirmation
            const editForm = document.getElementById('edit-record-form');
            if (editForm) {
                editForm.addEventListener('submit', function (e) {
                    e.preventDefault();
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Save Changes?',
                            text: "Are you sure you want to update this record? This action will permanently modify the database.",
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, save it!',
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
                                editForm.submit();
                            }
                        });
                    } else {
                        editForm.submit();
                    }
                });
            }
        });

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

        // Employment Status "Others" handler
        window.handleEmpStatusChange = function (sel) {
            const textInput   = document.getElementById('emp_status_input');
            const hiddenInput = document.getElementById('emp_status_known');
            if (!textInput || !hiddenInput) return;

            if (sel.value === '__others__') {
                // Show text field; disable the hidden so only text submits
                textInput.classList.remove('hidden');
                textInput.disabled = false;
                textInput.value = '';
                textInput.focus();
                hiddenInput.disabled = true;
            } else {
                // Hide text field; set hidden to the chosen value
                textInput.classList.add('hidden');
                textInput.disabled = true;
                hiddenInput.value    = sel.value;
                hiddenInput.disabled = false;
            }
        };

        // Termination "Others" handler
        window.handleTerminationChange = function (sel) {
            const textInput   = document.getElementById('termination_input');
            const hiddenInput = document.getElementById('termination_known');
            if (!textInput || !hiddenInput) return;

            if (sel.value === '__others__') {
                textInput.classList.remove('hidden');
                textInput.disabled = false;
                textInput.value = '';
                textInput.focus();
                hiddenInput.disabled = true;
            } else {
                textInput.classList.add('hidden');
                textInput.disabled = true;
                hiddenInput.value    = sel.value;
                hiddenInput.disabled = false;
            }
        };

        // On page load — sync disabled states for pre-filled values
        (function () {
            // Employment Status
            const empSel    = document.getElementById('emp_status_select');
            const empText   = document.getElementById('emp_status_input');
            const empHidden = document.getElementById('emp_status_known');
            if (empSel && empText && empHidden) {
                if (empSel.value === '__others__') {
                    empText.disabled   = false;
                    empHidden.disabled = true;
                } else {
                    empText.disabled   = true;
                    empHidden.disabled = false;
                }
            }

            // Termination
            const termSel    = document.getElementById('termination_select');
            const termText   = document.getElementById('termination_input');
            const termHidden = document.getElementById('termination_known');
            if (termSel && termText && termHidden) {
                if (termSel.value === '__others__') {
                    termText.disabled   = false;
                    termHidden.disabled = true;
                } else {
                    termText.disabled   = true;
                    termHidden.disabled = false;
                }
            }
        })();

        // ── Dynamic Item Dropdown ─────────────────────────────────────────
        const fetchItemsForEdit = function() {
            const ouSelect = document.getElementById('org_unit_select');
            const ouInput = document.getElementById('org_unit_input');
            const itemDropWrap = document.getElementById('item_dropdown_wrap');
            const itemCodeSelect = document.getElementById('item_code_select');
            
            let office = '';
            if (ouSelect && ouSelect.value && ouSelect.value !== 'Others') {
                office = ouSelect.value;
            } else if (ouInput) {
                office = ouInput.value;
            }

            if (!office || !itemDropWrap || !itemCodeSelect) {
                if (itemDropWrap) itemDropWrap.classList.add('hidden');
                return;
            }

            fetch(`/all-data/items-by-office?office=${encodeURIComponent(office)}`)
                .then(r => r.json())
                .then(items => {
                    itemCodeSelect.innerHTML = '';
                    if (items.length > 0) {
                        const blank = document.createElement('option');
                        blank.value = '';
                        blank.textContent = `— ${items.length} vacant item(s) available —`;
                        itemCodeSelect.appendChild(blank);

                        items.forEach(row => {
                            const opt = document.createElement('option');
                            opt.value = row.item;
                            const sg  = row.salary_grade ? ` (SG-${row.salary_grade})` : '';
                            const pos = row.position_title ? ` — ${row.position_title}` : '';
                            opt.textContent = `${row.item}${pos}${sg}`;
                            itemCodeSelect.appendChild(opt);
                        });
                        itemDropWrap.classList.remove('hidden');
                    } else {
                        const blank = document.createElement('option');
                        blank.value = '';
                        blank.textContent = 'No vacant items for this office';
                        itemCodeSelect.appendChild(blank);
                        itemDropWrap.classList.remove('hidden');
                    }
                })
                .catch(err => console.error('Error fetching items:', err));
        };

        const itemCodeSelectEl = document.getElementById('item_code_select');
        const itemCodeInputEl = document.getElementById('item_code');
        if (itemCodeSelectEl) {
            itemCodeSelectEl.addEventListener('change', function() {
                if (this.value && itemCodeInputEl) {
                    itemCodeInputEl.value = this.value;
                    itemCodeInputEl.dispatchEvent(new Event('input', { bubbles: true }));
                    
                    // Fetch details for the selected item and auto-fill position fields
                    fetch(`/plantilla/item-details?item=${encodeURIComponent(this.value)}`)
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
                                    'base_salary_amount': data.base_salary_amount,
                                    'emp_status_select': data.employment_status,
                                    'area_code': data.area_code,
                                    'area_type': data.area_type,
                                    'level': data.level
                                };
                                
                                for (const [idOrName, value] of Object.entries(fieldsToFill)) {
                                    if (value !== null && value !== undefined) {
                                        // Try by ID first (for custom selects), then by name
                                        let input = document.getElementById(idOrName) || document.querySelector(`[name="${idOrName}"]`);
                                        if (input) {
                                            // Handle specific cases for "Others" dropdowns
                                            if (idOrName === 'pos_title_select') {
                                                let optionExists = Array.from(input.options).some(opt => opt.value === value);
                                                if (optionExists) {
                                                    input.value = value;
                                                    window.handlePosTitleChange(input);
                                                } else {
                                                    input.value = 'Others';
                                                    window.handlePosTitleChange(input);
                                                    document.getElementById('pos_title_input').value = value;
                                                }
                                            } else if (idOrName === 'emp_status_select') {
                                                let optionExists = Array.from(input.options).some(opt => opt.value === value);
                                                if (optionExists) {
                                                    input.value = value;
                                                    window.handleEmpStatusChange(input);
                                                } else {
                                                    input.value = '__others__';
                                                    window.handleEmpStatusChange(input);
                                                    document.getElementById('emp_status_input').value = value;
                                                }
                                            } else {
                                                const pesoFields = ['authorized_annual_salary', 'base_salary_amount'];
                                                input.value = pesoFields.includes(idOrName) && window.pesoFormat
                                                    ? window.pesoFormat(value)
                                                    : value;
                                            }
                                        }
                                    }
                                }
                            }
                        })
                        .catch(error => console.error('Error fetching item details:', error));
                }
            });
        }

        const orgSelectEl = document.getElementById('org_unit_select');
        const orgInputEl = document.getElementById('org_unit_input');
        if (orgSelectEl) orgSelectEl.addEventListener('change', fetchItemsForEdit);
        if (orgInputEl) orgInputEl.addEventListener('input', fetchItemsForEdit);

        // Fetch on load
        fetchItemsForEdit();

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
        let lastGeneratedCode = empCodeInput ? empCodeInput.value : '';
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