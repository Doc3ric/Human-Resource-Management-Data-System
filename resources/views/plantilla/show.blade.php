<x-dashboard-app>
    <div class="max-w-5xl mx-auto space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('plantilla.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-gray-800">{{ $plantilla->position_title }}</h1>
                    <p class="text-gray-500 text-sm">{{ $plantilla->item_no_new }} • {{ $plantilla->office_department }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if(!$plantilla->is_vacant)
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" @click.away="open = false"
                       class="inline-flex items-center gap-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg font-medium text-sm transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Generate Document
                        <svg class="w-4 h-4 transition-transform" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open" style="display: none;" class="absolute right-0 mt-2 w-56 rounded-xl shadow-xl bg-white border border-gray-100 z-50 overflow-hidden">
                        <div class="py-1" role="menu">
                            <a href="{{ route('plantilla.service-record', $plantilla) }}" target="_blank" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 font-medium transition">Service Record</a>
                            <a href="{{ route('plantilla.form33', $plantilla) }}" target="_blank" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 font-medium transition">CSC Form 33 (Appointment)</a>
                            <div class="border-t border-gray-100"></div>
                            <div class="px-4 py-1.5 text-xs font-bold text-gray-400 uppercase tracking-wider">Step Increment (NOSI)</div>
                            <a href="{{ route('step-increment.pdf.nosi', $plantilla) }}" target="_blank" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition pl-6"><i class="bi bi-file-earmark-pdf mr-2 text-red-500"></i> Export as PDF</a>
                            <a href="{{ route('step-increment.docx.nosi', $plantilla) }}" target="_blank" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition pl-6"><i class="bi bi-file-earmark-word mr-2 text-blue-500"></i> Export as DOCX</a>
                            
                            <a href="{{ route('step-increment.pdf.nosa', $plantilla) }}" target="_blank" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition border-t border-gray-100">Notice of Salary Adj. (NOSA)</a>
                            
                            @if(isset($plantilla->is_hospital_personnel) && $plantilla->is_hospital_personnel)
                                <div class="px-4 py-1.5 text-xs font-bold text-gray-400 uppercase tracking-wider border-t border-gray-100 mt-1 pt-2">Longevity Pay (NOLP)</div>
                                <a href="{{ route('step-increment.pdf.nolp', $plantilla) }}" target="_blank" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition pl-6"><i class="bi bi-file-earmark-pdf mr-2 text-red-500"></i> Export as PDF</a>
                                <a href="{{ route('step-increment.docx.nolp', $plantilla) }}" target="_blank" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition pl-6"><i class="bi bi-file-earmark-word mr-2 text-blue-500"></i> Export as DOCX</a>
                            @endif
                            <div class="border-t border-gray-100"></div>
                            <a href="{{ route('step-increment.pdf.loyalty-incentive', $plantilla) }}" target="_blank" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">Loyalty Incentive Certificate</a>
                        </div>
                    </div>
                </div>
                @endif
                @if(auth()->user()->canAccess('plantilla'))
                <a href="{{ route('plantilla.edit', $plantilla) }}"
                   class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg font-medium text-sm transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                </a>
                @if(!$plantilla->is_vacant)
                <a href="{{ route('plantilla.promote.form', $plantilla) }}"
                   class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium text-sm transition shadow-sm">
                    <i class="bi bi-person-up"></i>
                    Promote / Transfer
                </a>
                @endif
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left - Key Info --}}
            <div class="lg:col-span-1 space-y-4">
                {{-- Status Card --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <h2 class="text-sm font-semibold text-gray-600 mb-3">Position Status</h2>
                    <div class="space-y-2 text-sm">
                        @if($plantilla->is_vacant)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-amber-100 text-amber-800">VACANT</span>
                        @elseif($plantilla->abolished)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-700">ABOLISHED</span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-700">FILLED</span>
                        @endif

                        <div class="pt-2 space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Employment Status</span>
                                <span class="font-medium">{{ $plantilla->employment_status ?: 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Salary Grade</span>
                                <span class="font-bold text-lg">SG {{ $plantilla->salary_grade }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Step</span>
                                <span class="font-medium">Step {{ $plantilla->step }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Salary Card --}}
                <div class="bg-blue-600 text-white rounded-xl shadow-sm p-5">
                    <h2 class="text-sm font-semibold opacity-80 mb-2">Annual Salary</h2>
                    <div class="text-2xl font-bold">₱{{ number_format($plantilla->base_salary_amount, 2) }}</div>
                    <div class="text-sm opacity-70 mt-1">Monthly: ₱{{ number_format($plantilla->monthly_salary, 2) }}</div>
                    <div class="mt-3 pt-3 border-t border-blue-500 text-sm">
                        <div class="flex justify-between opacity-80">
                            <span>Authorized</span>
                            <span>₱{{ number_format($plantilla->authorized_annual_salary, 2) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Step Increment Card --}}
                @if(!$plantilla->is_vacant && $plantilla->step < 8)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <h2 class="text-sm font-semibold text-gray-600 mb-3">Step Increment</h2>
                    <div class="text-sm space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Current Step</span>
                            <span class="font-medium">Step {{ $plantilla->step }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Next Due</span>
                            <span class="font-medium {{ $plantilla->is_step_due ? 'text-red-600' : 'text-gray-800' }}">
                                {{ $plantilla->next_step_due_date?->format('M d, Y') ?? 'N/A' }}
                                @if($plantilla->is_step_due) <span class="text-xs">(OVERDUE)</span> @endif
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Years of Service</span>
                            <span class="font-medium">{{ number_format($plantilla->years_of_service, 1) }} yrs</span>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            {{-- Right - Details --}}
            <div class="lg:col-span-2 space-y-4">
                {{-- Employee Details --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <h2 class="text-sm font-semibold text-gray-600 mb-4">Employee Information</h2>
                    @if($plantilla->is_vacant)
                        <p class="text-gray-400 italic">This position is vacant.</p>
                    @else
                    <div class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                        @php
                            $details = [
                                'Full Name'                 => $plantilla->full_name,
                                'Sex'                       => $plantilla->sex ?? 'N/A',
                                'Birthday'             => $plantilla->date_of_birth?->format('F d, Y') ?? 'N/A',
                                'TIN'                       => $plantilla->tin ?? 'N/A',
                                'GSIS BP Number'            => $plantilla->gsis_bp_number ?? 'N/A',
                                'UMID'                      => $plantilla->umid ?? 'N/A',
                                'Date of Appointment'       => $plantilla->date_original_appointment?->format('F d, Y') ?? 'N/A',
                                'Date of Last Promotion'    => $plantilla->date_last_promotion?->format('F d, Y') ?? 'N/A',
                                'Civil Service Eligibility' => $plantilla->civil_service_eligibility ?? 'N/A',
                                'PWD'                       => $plantilla->is_pwd ? 'Yes' : 'No',
                                'Indigenous People'         => $plantilla->indigenous_people ?: 'No',
                                'Solo Parent'               => $plantilla->solo_parent ?: 'No',
                            ];
                        @endphp
                        @foreach($details as $label => $value)
                        <div>
                            <span class="text-gray-400 block text-xs">{{ $label }}</span>
                            <span class="text-gray-800 font-medium">{{ $value }}</span>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

                {{-- Position Details --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <h2 class="text-sm font-semibold text-gray-600 mb-4">Position Details</h2>
                    <div class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                        <div><span class="text-gray-400 text-xs block">Classification</span><span class="font-medium">{{ $plantilla->position_classification ?? 'N/A' }}</span></div>
                        <div><span class="text-gray-400 text-xs block">Level</span><span class="font-medium">{{ $plantilla->level ?? 'N/A' }}</span></div>
                        <div><span class="text-gray-400 text-xs block">Area Code</span><span class="font-medium">{{ $plantilla->area_code ?? 'N/A' }}</span></div>
                        <div><span class="text-gray-400 text-xs block">Area Type</span><span class="font-medium">{{ $plantilla->area_type ?? 'N/A' }}</span></div>
                        <div><span class="text-gray-400 text-xs block">Abolished</span><span class="font-medium">{{ $plantilla->abolished ? 'Yes' : 'No' }}</span></div>
                        <div><span class="text-gray-400 text-xs block">Dissolved</span><span class="font-medium">{{ $plantilla->dissolved ? 'Yes' : 'No' }}</span></div>
                    </div>
                    @if($plantilla->remarks_annotation)
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <span class="text-gray-400 text-xs block mb-1">Comment / Annotation</span>
                        <p class="text-gray-700 text-sm">{{ $plantilla->remarks_annotation }}</p>
                    </div>
                    @endif
                </div>

                {{-- Digital 201 File / Attachments --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mt-4" x-data="{
                        activeTab: 'all',
                        previewModal: false,
                        previewUrl: '',
                        previewType: '',
                        previewName: '',
                        openPreview(url, type, name) {
                            this.previewUrl = url;
                            this.previewType = type.toLowerCase();
                            this.previewName = name;
                            this.previewModal = true;
                        }
                    }">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-sm font-semibold text-gray-600">Digital 201 File (Attachments)</h2>
                        <button type="button" class="text-xs bg-blue-50 text-blue-600 hover:bg-blue-100 px-3 py-1.5 rounded-lg font-medium transition" onclick="document.getElementById('upload-modal').classList.remove('hidden')">
                            <i class="bi bi-upload"></i> Upload File
                        </button>
                    </div>

                    @if($plantilla->attachments->count() > 0)
                        {{-- Tabs --}}
                        <div class="flex flex-wrap gap-2 mb-4 border-b border-gray-100 pb-2">
                            <button @click="activeTab = 'all'" 
                                    :class="{'bg-blue-50 text-blue-700 font-medium': activeTab === 'all', 'text-gray-500 hover:bg-gray-50': activeTab !== 'all'}" 
                                    class="px-3 py-1.5 text-xs rounded-full transition">
                                All ({{ $plantilla->attachments->count() }})
                            </button>
                            
                            @php
                                $categories = $plantilla->attachments->groupBy('document_type');
                            @endphp
                            
                            @foreach($categories as $type => $files)
                                <button @click="activeTab = '{{ Str::slug($type) }}'" 
                                        :class="{'bg-blue-50 text-blue-700 font-medium': activeTab === '{{ Str::slug($type) }}', 'text-gray-500 hover:bg-gray-50': activeTab !== '{{ Str::slug($type) }}'}" 
                                        class="px-3 py-1.5 text-xs rounded-full transition">
                                    {{ $type }} ({{ $files->count() }})
                                </button>
                            @endforeach
                        </div>

                        <div class="space-y-3">
                            @foreach($plantilla->attachments as $attachment)
                            <div x-show="activeTab === 'all' || activeTab === '{{ Str::slug($attachment->document_type) }}'"
                                 x-transition.opacity
                                 class="flex items-center justify-between p-3 border border-gray-100 rounded-lg hover:bg-gray-50 transition">
                                <div class="flex items-center gap-3 overflow-hidden">
                                    <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0">
                                        @if(in_array(strtolower($attachment->file_type), ['pdf']))
                                            <i class="bi bi-file-earmark-pdf text-xl"></i>
                                        @elseif(in_array(strtolower($attachment->file_type), ['doc', 'docx']))
                                            <i class="bi bi-file-earmark-word text-xl"></i>
                                        @elseif(in_array(strtolower($attachment->file_type), ['jpg', 'jpeg', 'png', 'gif', 'svg']))
                                            <i class="bi bi-file-image text-xl"></i>
                                        @else
                                            <i class="bi bi-file-earmark text-xl"></i>
                                        @endif
                                    </div>
                                    <div class="truncate">
                                        <p class="text-sm font-medium text-gray-800 truncate" title="{{ $attachment->file_name }}">
                                            {{ $attachment->document_type }} 
                                            <span class="text-xs text-gray-400 font-normal ml-1">({{ $attachment->file_name }})</span>
                                        </p>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            Uploaded by {{ $attachment->uploaded_by }} • {{ $attachment->created_at->format('M d, Y h:i A') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0 ml-4">
                                    @if(in_array(strtolower($attachment->file_type), ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'svg']))
                                    <button @click="openPreview('{{ Storage::url($attachment->file_path) }}', '{{ $attachment->file_type }}', '{{ $attachment->file_name }}')" 
                                            class="text-gray-400 hover:text-indigo-600 transition p-1" title="Preview">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    @endif
                                    <a href="{{ route('plantilla.attachments.download', $attachment) }}" class="text-gray-400 hover:text-blue-600 transition p-1" title="Download">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    <form action="{{ route('plantilla.attachments.destroy', $attachment) }}" method="POST" class="inline" id="delete-attach-{{ $attachment->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" onclick="confirmDeleteAttachment({{ $attachment->id }})" class="text-gray-400 hover:text-red-600 transition p-1" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6 border-2 border-dashed border-gray-100 rounded-lg">
                            <i class="bi bi-folder-x text-gray-300 text-3xl mb-2"></i>
                            <p class="text-sm text-gray-500">No attachments found.</p>
                            <p class="text-xs text-gray-400 mt-1">Upload PDS, Oath of Office, and other 201 files here.</p>
                        </div>
                    @endif

                    {{-- Preview Modal --}}
                    <div x-show="previewModal" style="display: none;" class="fixed inset-0 z-[110] bg-gray-900/80 backdrop-blur-sm flex items-center justify-center p-4">
                        <div @click.away="previewModal = false" class="bg-white rounded-xl shadow-2xl w-full max-w-5xl h-[85vh] flex flex-col overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between bg-gray-50">
                                <h3 class="text-base font-bold text-gray-800" x-text="previewName"></h3>
                                <button @click="previewModal = false" class="text-gray-400 hover:text-gray-600 transition">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                            <div class="flex-1 bg-gray-100 p-4 overflow-auto flex items-center justify-center relative">
                                <!-- Loading Spinner (optional, Alpine handles this fast but for UI) -->
                                <div class="absolute inset-0 flex items-center justify-center text-gray-400" x-show="previewUrl === ''">
                                    <i class="bi bi-hourglass-split text-3xl animate-pulse"></i>
                                </div>
                                
                                <!-- PDF Preview -->
                                <template x-if="previewType === 'pdf'">
                                    <iframe :src="previewUrl" class="w-full h-full rounded shadow-sm border-0" title="PDF Preview"></iframe>
                                </template>
                                
                                <!-- Image Preview -->
                                <template x-if="['jpg', 'jpeg', 'png', 'gif', 'svg'].includes(previewType)">
                                    <img :src="previewUrl" class="max-w-full max-h-full object-contain rounded shadow-sm" alt="Image Preview">
                                </template>

                                <!-- Unsupported text fallback (should not hit this since preview button only shows for supported types, but good practice) -->
                                <template x-if="!['pdf', 'jpg', 'jpeg', 'png', 'gif', 'svg'].includes(previewType)">
                                    <div class="text-gray-500 text-center">
                                        <i class="bi bi-file-earmark-x text-4xl mb-2"></i>
                                        <p>Preview not available for this file type.</p>
                                        <a :href="previewUrl" target="_blank" class="text-blue-600 hover:underline text-sm mt-2 inline-block">Download instead</a>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Upload Attachment Modal -->
    <div id="upload-modal" class="fixed inset-0 z-[100] hidden bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full flex items-center justify-center p-4">
        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                <h3 class="text-lg font-bold text-gray-800">Upload Attachment</h3>
                <button onclick="document.getElementById('upload-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            
            <form action="{{ route('plantilla.attachments.store', $plantilla) }}" method="POST" enctype="multipart/form-data" class="p-6">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Document Type <span class="text-red-500">*</span></label>
                        <select name="document_type" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="">Select Document Type...</option>
                            <option value="Personal Data Sheet (PDS)">Personal Data Sheet (PDS)</option>
                            <option value="Oath of Office">Oath of Office</option>
                            <option value="Assumption of Duty">Assumption of Duty</option>
                            <option value="SALN">SALN</option>
                            <option value="Medical Certificate">Medical Certificate</option>
                            <option value="Clearance">Clearance</option>
                            <option value="IPCR">IPCR</option>
                            <option value="Training Certificate">Training Certificate</option>
                            <option value="Other">Other Document</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">File <span class="text-red-500">*</span></label>
                        <input type="file" name="attachment" required class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition">
                        <p class="text-xs text-gray-400 mt-2">Max file size: 10MB. Accepted formats: PDF, DOC, DOCX, JPG, PNG.</p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('upload-modal').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-sm transition inline-flex items-center gap-2">
                        <i class="bi bi-upload"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    function confirmDeleteAttachment(id) {
        Swal.fire({
            title: 'Delete Attachment?',
            text: 'This attachment will be permanently removed from this record.',
            icon: 'warning',
            iconColor: '#dc2626',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="bi bi-trash my-1"></i> Yes, Delete it',
            cancelButtonText: 'Cancel',
            customClass: { popup: 'rounded-xl' }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-attach-' + id).submit();
            }
        });
    }
    </script>
</x-dashboard-app>
