<x-dashboard-app>
    <div class="max-w-4xl mx-auto space-y-6">
        {{-- Header --}}
        <div class="flex items-center gap-4">
            <a href="{{ route('plantilla.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ isset($plantilla) ? 'Edit Plantilla Record' : 'Add Plantilla Record' }}</h1>
                <p class="text-gray-500 text-sm">{{ isset($plantilla) ? 'Update the plantilla record details below' : 'Fill in the details to create a new plantilla record' }}</p>
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

        <form method="POST" action="{{ isset($plantilla) ? route('plantilla.update', $plantilla) : route('plantilla.store') }}" class="space-y-6">
            @csrf
            @if(isset($plantilla)) @method('PUT') @endif

            {{-- Position Information --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-base font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">Position Information</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

                    <div class="lg:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Organizational Unit <span class="text-red-500">*</span></label>
                        <div class="flex flex-col gap-2">
                            @php $oldOrg = old('organizational_unit', $plantilla->organizational_unit ?? ''); @endphp
                            <select id="org_unit_select" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 bg-white outline-none">
                                <option value="">Select an office...</option>
                                @foreach($offices as $office)
                                    <option value="{{ $office }}" {{ $oldOrg === $office ? 'selected' : '' }}>{{ $office }}</option>
                                @endforeach
                                <option value="Others" {{ $oldOrg && !$offices->contains($oldOrg) ? 'selected' : '' }}>Others (Please specify)</option>
                            </select>
                            <input type="text" name="organizational_unit" id="org_unit_input" 
                                value="{{ $oldOrg }}"
                                placeholder="Type organizational unit manually..."
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none {{ $oldOrg && !$offices->contains($oldOrg) ? '' : 'hidden' }}" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Item (Position Code) <span class="text-red-500">*</span></label>
                        <div class="flex flex-col gap-2">
                            @php
                                $oldItem = old('item', $plantilla->item ?? '');
                                $existingItems = $existingItems ?? collect();
                                $itemInList = $existingItems->contains($oldItem);
                            @endphp
                            <select id="item_select" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 bg-white outline-none font-mono"
                                {{ isset($plantilla) ? 'disabled' : '' }}>
                                <option value="">Select existing code...</option>
                                @foreach($existingItems as $code)
                                    <option value="{{ $code }}" {{ $oldItem === $code ? 'selected' : '' }}>{{ $code }}</option>
                                @endforeach
                                <option value="__others__" {{ ($oldItem && !$itemInList) ? 'selected' : '' }}>Others (Please Specify)</option>
                            </select>
                            <input type="text" name="item" id="item_input"
                                value="{{ $oldItem }}"
                                placeholder="Enter new position code..."
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none font-mono {{ (isset($plantilla) || ($oldItem && !$itemInList)) ? '' : 'hidden' }}"
                                {{ isset($plantilla) ? 'readonly' : '' }}
                                required>
                        </div>
                    </div>

                    <div class="lg:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Position Title <span class="text-red-500">*</span></label>
                        <input type="text" name="position_title" value="{{ old('position_title', $plantilla->position_title ?? '') }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" required>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Position Classification</label>
                        <select name="position_classification" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white">
                            <option value="">Select...</option>
                            <option value="EXECUTIVE/MANAGERIAL" {{ old('position_classification', $plantilla->position_classification ?? '') === 'EXECUTIVE/MANAGERIAL' ? 'selected' : '' }}>Executive/Managerial</option>
                            <option value="2nd Level" {{ old('position_classification', $plantilla->position_classification ?? '') === '2nd Level' ? 'selected' : '' }}>2nd Level</option>
                            <option value="1st Level" {{ old('position_classification', $plantilla->position_classification ?? '') === '1st Level' ? 'selected' : '' }}>1st Level</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Salary Grade <span class="text-red-500">*</span></label>
                        <input type="number" name="salary_grade" min="1" max="33" value="{{ old('salary_grade', $plantilla->salary_grade ?? '') }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" required>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Step <span class="text-red-500">*</span></label>
                        <select name="step" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white" required>
                            @for($s = 1; $s <= 8; $s++)
                                <option value="{{ $s }}" {{ (int)old('step', $plantilla->step ?? 1) === $s ? 'selected' : '' }}>Step {{ $s }}</option>
                            @endfor
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Authorized Annual Salary</label>
                        <input type="number" step="0.01" name="authorized_annual_salary" value="{{ old('authorized_annual_salary', $plantilla->authorized_annual_salary ?? '') }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Actual Annual Salary</label>
                        <input type="number" step="0.01" name="actual_annual_salary" value="{{ old('actual_annual_salary', $plantilla->actual_annual_salary ?? '') }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Employment Status</label>
                        <select name="employment_status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white">
                            <option value="">Select...</option>
                            <option value="E"      {{ old('employment_status', $plantilla->employment_status ?? '') === 'E'      ? 'selected':'' }}>Elected</option>
                            <option value="CT"     {{ old('employment_status', $plantilla->employment_status ?? '') === 'CT'     ? 'selected':'' }}>Co-Terminous</option>
                            <option value="P"      {{ old('employment_status', $plantilla->employment_status ?? '') === 'P'      ? 'selected':'' }}>Permanent</option>
                            <option value="Casual" {{ old('employment_status', $plantilla->employment_status ?? '') === 'Casual' ? 'selected':'' }}>Casual</option>
                            <option value="JO"     {{ old('employment_status', $plantilla->employment_status ?? '') === 'JO'     ? 'selected':'' }}>Job Order</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Area Code</label>
                        <input type="text" name="area_code" value="{{ old('area_code', $plantilla->area_code ?? '') }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Area Type</label>
                        <input type="text" name="area_type" value="{{ old('area_type', $plantilla->area_type ?? '') }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Level</label>
                        <input type="text" name="level" value="{{ old('level', $plantilla->level ?? '') }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>
                </div>
            </div>

            {{-- Employee Information --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-base font-semibold text-gray-800 mb-1 pb-2 border-b border-gray-100">Employee Information</h2>
                <p class="text-xs text-gray-400 mb-4">Leave blank if this is a vacant position.</p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Last Name</label>
                        <input type="text" name="last_name" value="{{ old('last_name', $plantilla->last_name ?? '') }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">First Name</label>
                        <input type="text" name="first_name" value="{{ old('first_name', $plantilla->first_name ?? '') }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Middle Name</label>
                        <input type="text" name="middle_name" value="{{ old('middle_name', $plantilla->middle_name ?? '') }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Sex</label>
                        <select name="sex" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white">
                            <option value="">Not specified</option>
                            <option value="M" {{ old('sex', $plantilla->sex ?? '') === 'M' ? 'selected':'' }}>Male (M)</option>
                            <option value="F" {{ old('sex', $plantilla->sex ?? '') === 'F' ? 'selected':'' }}>Female (F)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Date of Birth</label>
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth', isset($plantilla) && $plantilla->date_of_birth ? $plantilla->date_of_birth->format('Y-m-d') : '') }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">TIN</label>
                        <input type="text" name="tin" value="{{ old('tin', $plantilla->tin ?? '') }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Date of Original Appointment</label>
                        <input type="date" name="date_original_appointment" value="{{ old('date_original_appointment', isset($plantilla) && $plantilla->date_original_appointment ? $plantilla->date_original_appointment->format('Y-m-d') : '') }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Date of Last Promotion</label>
                        <input type="date" name="date_last_promotion" value="{{ old('date_last_promotion', isset($plantilla) && $plantilla->date_last_promotion ? $plantilla->date_last_promotion->format('Y-m-d') : '') }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Civil Service Eligibility</label>
                        <div class="flex flex-col gap-2">
                            @php $oldCse = old('civil_service_eligibility', $plantilla->civil_service_eligibility ?? ''); @endphp
                            <select id="cse_select" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 bg-white outline-none">
                                <option value="" {{ empty($oldCse) ? 'selected' : '' }}>None / Not Applicable</option>
                                <option value="Professional" {{ $oldCse === 'Professional' ? 'selected' : '' }}>Professional</option>
                                <option value="Sub-Professional" {{ $oldCse === 'Sub-Professional' ? 'selected' : '' }}>Sub-Professional</option>
                                <option value="Others" {{ $oldCse && !in_array($oldCse, ['Professional', 'Sub-Professional']) ? 'selected' : '' }}>Others (Please specify)</option>
                            </select>
                            <input type="text" name="civil_service_eligibility" id="cse_input" 
                                value="{{ $oldCse }}"
                                placeholder="Type eligibility manually..."
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none {{ $oldCse && !in_array($oldCse, ['Professional', 'Sub-Professional']) ? '' : 'hidden' }}">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">GSIS BP Number</label>
                        <input type="text" name="gsis_bp_number" value="{{ old('gsis_bp_number', $plantilla->gsis_bp_number ?? '') }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">UMID</label>
                        <input type="text" name="umid" value="{{ old('umid', $plantilla->umid ?? '') }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Solo Parent ID</label>
                        <input type="text" name="solo_parent" value="{{ old('solo_parent', $plantilla->solo_parent ?? '') }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1 flex items-center gap-1">
                            Employee Code
                            <span class="text-blue-500 text-xs font-normal">(auto-generated)</span>
                        </label>
                        <div class="flex gap-2">
                            <input type="text" name="employee_code" id="employee_code"
                                value="{{ old('employee_code', $plantilla->employee_code ?? '') }}"
                                placeholder="e.g. 23042004A"
                                maxlength="20"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none font-mono uppercase tracking-wider">
                            <button type="button" id="btn_autofill_code" title="Auto-generate from Last Name + Date of Birth"
                                class="flex-shrink-0 px-3 py-2 bg-blue-50 hover:bg-blue-100 border border-blue-300 text-blue-600 rounded-lg text-xs font-medium transition flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                Auto-fill
                            </button>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Format: DDMMYYYY + first letter of last name (e.g. <span class="font-mono">23042004A</span>)</p>
                    </div>
                </div>

                {{-- Checkboxes Row --}}
                <div class="flex flex-wrap gap-6 mt-4">
                    <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                        <input type="checkbox" name="is_pwd" value="1" {{ old('is_pwd', $plantilla->is_pwd ?? false) ? 'checked' : '' }} class="rounded">
                        PWD (Person with Disability)
                    </label>
                    <div class="flex items-center gap-2" style="flex-wrap: wrap;">
                        @php $oldIp = old('indigenous_people', $plantilla->indigenous_people ?? ''); @endphp
                        <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                            <input type="checkbox" id="is_ip_checkbox" {{ $oldIp ? 'checked' : '' }} class="rounded"> IP
                        </label>
                        
                        <div id="ip_container" class="flex gap-2 {{ $oldIp ? '' : 'hidden' }}">
                            <select id="ip_select" class="border border-gray-300 rounded-lg px-2 py-1 text-xs focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                                <option value="">Select IP Group</option>
                                @foreach(['Aeta','Ati','Badjao','Batak','Blaan','Bontoc','Bukidnon','Higaonon','Ibaloi','Ifugao','Ilongot','Itneg','Kalinga','Kankanaey','Lumad','Mangyan','Manobo','Mansaka','Palawano','Pala\'wan','Subanen','T\'boli','Tiruray','Yakan'] as $ipGroup)
                                    <option value="{{ $ipGroup }}" {{ $oldIp === $ipGroup ? 'selected' : '' }}>{{ $ipGroup }}</option>
                                @endforeach
                                <option value="Others" {{ $oldIp && !in_array($oldIp, ['Aeta','Ati','Badjao','Batak','Blaan','Bontoc','Bukidnon','Higaonon','Ibaloi','Ifugao','Ilongot','Itneg','Kalinga','Kankanaey','Lumad','Mangyan','Manobo','Mansaka','Palawano','Pala\'wan','Subanen','T\'boli','Tiruray','Yakan', 'Y']) ? 'selected' : '' }}>Others</option>
                            </select>
                            <input type="text" id="ip_others" placeholder="Specify..."
                                value="{{ $oldIp && !in_array($oldIp, ['Aeta','Ati','Badjao','Batak','Blaan','Bontoc','Bukidnon','Higaonon','Ibaloi','Ifugao','Ilongot','Itneg','Kalinga','Kankanaey','Lumad','Mangyan','Manobo','Mansaka','Palawano','Pala\'wan','Subanen','T\'boli','Tiruray','Yakan', 'Y']) ? $oldIp : '' }}"
                                class="border border-gray-300 rounded-lg px-2 py-1 text-xs focus:ring-2 focus:ring-blue-500 outline-none bg-white {{ $oldIp && !in_array($oldIp, ['Aeta','Ati','Badjao','Batak','Blaan','Bontoc','Bukidnon','Higaonon','Ibaloi','Ifugao','Ilongot','Itneg','Kalinga','Kankanaey','Lumad','Mangyan','Manobo','Mansaka','Palawano','Pala\'wan','Subanen','T\'boli','Tiruray','Yakan', 'Y']) ? '' : 'hidden' }}">
                        </div>
                        <input type="hidden" name="indigenous_people" id="ip_hidden" value="{{ $oldIp }}">
                    </div>
                    <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                        <input type="checkbox" name="abolished" value="1" {{ old('abolished', $plantilla->abolished ?? false) ? 'checked' : '' }} class="rounded">
                        Abolished
                    </label>
                    <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                        <input type="checkbox" name="dissolved" value="1" {{ old('dissolved', $plantilla->dissolved ?? false) ? 'checked' : '' }} class="rounded">
                        Dissolved
                    </label>
                </div>

                <div class="mt-4">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Comment / Annotation</label>
                    <textarea name="comment_annotation" rows="2"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">{{ old('comment_annotation', $plantilla->comment_annotation ?? '') }}</textarea>
                </div>
            </div>

            {{-- Appointment History & Separation --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-base font-semibold text-gray-800 mb-1 pb-2 border-b border-gray-100">Appointment History & Separation</h2>
                <p class="text-xs text-gray-400 mb-4">Used for official personnel reports (Newly Hired, Retirees, Terminated). Leave blank if not applicable.</p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nature of Appointment</label>
                        <select name="nature_of_appointment" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white">
                            <option value="">None / N/A</option>
                            <option value="Newly Hired"  {{ old('nature_of_appointment', $plantilla->nature_of_appointment ?? '') === 'Newly Hired'  ? 'selected':'' }}>Newly Hired</option>
                            <option value="Promoted"     {{ old('nature_of_appointment', $plantilla->nature_of_appointment ?? '') === 'Promoted'     ? 'selected':'' }}>Promoted</option>
                            <option value="Demoted"      {{ old('nature_of_appointment', $plantilla->nature_of_appointment ?? '') === 'Demoted'      ? 'selected':'' }}>Demoted</option>
                            <option value="Transferred"  {{ old('nature_of_appointment', $plantilla->nature_of_appointment ?? '') === 'Transferred'  ? 'selected':'' }}>Transferred</option>
                            <option value="Reappointed"  {{ old('nature_of_appointment', $plantilla->nature_of_appointment ?? '') === 'Reappointed'  ? 'selected':'' }}>Reappointed</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nature of Separation</label>
                        <select name="nature_of_separation" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white">
                            <option value="">None / Still Active</option>
                            <option value="Retired"           {{ old('nature_of_separation', $plantilla->nature_of_separation ?? '') === 'Retired'           ? 'selected':'' }}>Retired</option>
                            <option value="Resigned"          {{ old('nature_of_separation', $plantilla->nature_of_separation ?? '') === 'Resigned'          ? 'selected':'' }}>Resigned</option>
                            <option value="Dropped from Rolls"{{ old('nature_of_separation', $plantilla->nature_of_separation ?? '') === 'Dropped from Rolls'? 'selected':'' }}>Dropped from Rolls</option>
                            <option value="End of Contract"   {{ old('nature_of_separation', $plantilla->nature_of_separation ?? '') === 'End of Contract'   ? 'selected':'' }}>End of Contract</option>
                            <option value="Dismissed"         {{ old('nature_of_separation', $plantilla->nature_of_separation ?? '') === 'Dismissed'         ? 'selected':'' }}>Dismissed</option>
                            <option value="Death"             {{ old('nature_of_separation', $plantilla->nature_of_separation ?? '') === 'Death'             ? 'selected':'' }}>Death</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Date Separated / Effectivity</label>
                        <input type="date" name="date_separated"
                            value="{{ old('date_separated', isset($plantilla) && $plantilla->date_separated ? \Carbon\Carbon::parse($plantilla->date_separated)->format('Y-m-d') : '') }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>
                </div>
            </div>


            {{-- Actions --}}
            <div class="flex gap-3 justify-end">
                <a href="{{ route('plantilla.index') }}" class="px-5 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                    Cancel
                </a>
                <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition">
                    {{ isset($plantilla) ? 'Save Changes' : 'Create Record' }}
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Item (Position Code) Toggle
            const itemSelect = document.getElementById('item_select');
            const itemInput  = document.getElementById('item_input');
            if (itemSelect && itemInput) {
                itemSelect.addEventListener('change', function() {
                    if (this.value === '__others__') {
                        itemInput.classList.remove('hidden');
                        itemInput.value = '';
                        itemInput.focus();
                    } else if (this.value === '') {
                        itemInput.classList.add('hidden');
                        itemInput.value = '';
                    } else {
                        itemInput.classList.remove('hidden');
                        itemInput.value = this.value;
                    }
                });
            }

            // Organizational Unit Toggle
            const orgSelect = document.getElementById('org_unit_select');
            const orgInput = document.getElementById('org_unit_input');
            if (orgSelect && orgInput) {
                orgSelect.addEventListener('change', function() {
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
                cseSelect.addEventListener('change', function() {
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

            // ── Employee Code Auto-fill ───────────────────────────────────
            const lastNameInput  = document.querySelector('input[name="last_name"]');
            const dobInput       = document.querySelector('input[name="date_of_birth"]');
            const codeInput      = document.getElementById('employee_code');
            const btnAutoFill    = document.getElementById('btn_autofill_code');

            function buildCode() {
                const lastName = (lastNameInput?.value || '').trim();
                const dob = dobInput?.value || '';
                if (!lastName || !dob) return '';
                try {
                    const d = new Date(dob);
                    if (isNaN(d)) return '';
                    const dd  = String(d.getDate()).padStart(2, '0');
                    const mm  = String(d.getMonth() + 1).padStart(2, '0');
                    const yyyy = d.getFullYear();
                    const initial = lastName.charAt(0).toUpperCase();
                    return dd + mm + yyyy + initial;
                } catch(e) { return ''; }
            }

            function autoFillCode() {
                if (!codeInput) return;
                const code = buildCode();
                if (code) codeInput.value = code;
            }

            // Auto-fill when either field changes (only if code field is currently empty OR it matches old auto-value)
            if (lastNameInput) {
                lastNameInput.addEventListener('change', function() {
                    if (!codeInput || codeInput.value === '') autoFillCode();
                });
            }
            if (dobInput) {
                dobInput.addEventListener('change', function() {
                    if (!codeInput || codeInput.value === '') autoFillCode();
                });
            }

            // Manual trigger button always regenerates
            if (btnAutoFill) {
                btnAutoFill.addEventListener('click', autoFillCode);
            }
        });
    </script>
</x-dashboard-app>
