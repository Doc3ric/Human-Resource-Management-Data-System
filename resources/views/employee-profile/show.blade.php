<x-dashboard-app>
    <style>
        .profile-hero {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            border-radius: 16px;
            padding: 32px;
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.5);
            margin-bottom: 24px;
        }

        .profile-hero::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            opacity: 0.5;
            pointer-events: none;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: white;
            color: #1e3a8a;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            font-weight: 800;
            border: 4px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .profile-badge {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(4px);
            padding: 4px 12px;
            border-radius: 99px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .info-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            border: 1px solid #f1f5f9;
            height: 100%;
        }

        .info-card-title {
            font-size: 14px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 12px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .info-item-label {
            font-size: 11px;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 4px;
        }

        .info-item-value {
            font-size: 14px;
            font-weight: 600;
            color: #1e293b;
        }

        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 7px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e2e8f0;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 24px;
        }

        .timeline-item:last-child {
            margin-bottom: 0;
        }

        .timeline-dot {
            position: absolute;
            left: -30px;
            top: 4px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #3b82f6;
            border: 4px solid white;
            box-shadow: 0 0 0 2px #bfdbfe;
        }

        .timeline-date {
            font-size: 12px;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 4px;
        }

        .timeline-content {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 13px;
            color: #334155;
            font-weight: 500;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #64748b;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 16px;
            transition: color 0.2s;
        }

        .btn-back:hover {
            color: #1e293b;
        }

        .attachment-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-bottom: 8px;
        }

        .attachment-item:last-child {
            margin-bottom: 0;
        }
    </style>

    <a href="javascript:history.back()" class="btn-back">
        <i class="bi bi-arrow-left"></i> Back to List
    </a>

    <div class="profile-hero">
        <div style="display: flex; align-items: center; gap: 24px; position: relative; z-index: 10;">
            <div style="display: flex; flex-direction: column; align-items: center; gap: 8px;">
                <div class="profile-avatar-container" style="position: relative;">
                    <div class="profile-avatar" style="overflow: hidden; position: relative;">
                        @if($employee->profile_picture)
                            <img id="avatar-preview" src="{{ Storage::url($employee->profile_picture) }}"
                                alt="Profile Picture" style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            <img id="avatar-preview" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                            <span
                                id="avatar-initials">{{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}</span>
                        @endif
                    </div>

                    <form id="profile-pic-form"
                        action="{{ route('employees.profile.picture', ['type' => $type, 'id' => $employee->id]) }}"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="profile_picture" id="profile-pic-input" accept="image/*"
                            style="display: none;" onchange="previewProfilePic(event)">
                        <button type="button" id="camera-btn"
                            onclick="document.getElementById('profile-pic-input').click()"
                            class="btn btn-sm btn-primary rounded-circle"
                            style="position: absolute; bottom: 0; right: 0; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.2); border: 2px solid white;"
                            title="Change Profile Picture">
                            <i class="bi bi-camera-fill" style="font-size: 14px;"></i>
                        </button>
                    </form>
                </div>

                <div id="pic-actions" style="display: none; gap: 8px;">
                    <button type="button" class="btn btn-sm btn-light"
                        style="font-size: 11px; padding: 2px 10px; font-weight: 600;"
                        onclick="cancelProfilePic()">Cancel</button>
                    <button type="button" class="btn btn-sm btn-success"
                        style="font-size: 11px; padding: 2px 10px; font-weight: 600;"
                        onclick="document.getElementById('profile-pic-form').submit()">Save</button>
                </div>

                @if($employee->profile_picture)
                    <form id="remove-pic-form"
                        action="{{ route('employees.profile.picture.remove', ['type' => $type, 'id' => $employee->id]) }}"
                        method="POST" class="mb-0">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-link p-0 text-white"
                            style="font-size: 11px; text-decoration: underline; opacity: 0.8;" data-bs-toggle="modal"
                            data-bs-target="#removePicModal">Remove</button>
                    </form>


                @endif
            </div>
            <div>
                <div style="display: flex; gap: 12px; align-items: center; margin-bottom: 8px;">
                    <span class="profile-badge">
                        {{ $type == 'plantilla' ? 'Permanent' : ($type == 'casual' ? 'Casual' : 'Job Order') }}
                    </span>
                    @if(isset($employee->is_vacant) && $employee->is_vacant)
                        <span class="profile-badge"
                            style="background: rgba(239, 68, 68, 0.2); color: #fee2e2;">Vacant</span>
                    @else
                        <span class="profile-badge"
                            style="background: rgba(34, 197, 94, 0.2); color: #dcfce7;">Active</span>
                    @endif
                </div>
                <h1 style="font-size: 28px; font-weight: 800; margin: 0 0 4px 0;">
                    {{ $employee->first_name }} {{ $employee->middle_name ?? $employee->middle_initial }}
                    {{ $employee->last_name }} {{ $employee->name_extension }}
                </h1>
                <p style="font-size: 16px; font-weight: 500; opacity: 0.9; margin: 0;">
                    {{ $employee->position_title ?? 'N/A' }}
                </p>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="info-card">
                <div class="info-card-title">
                    <i class="bi bi-person-lines-fill text-primary"></i> Personal Information
                </div>
                <div class="info-grid">
                    <div>
                        <div class="info-item-label">SEX</div>
                        <div class="info-item-value">{{ $employee->sex ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div class="info-item-label">Birthday</div>
                        <div class="info-item-value">
                            {{ $employee->date_of_birth ? \Carbon\Carbon::parse($employee->date_of_birth)->format('M d, Y') : ($employee->date_of_birth ? \Carbon\Carbon::parse($employee->date_of_birth)->format('M d, Y') : 'N/A') }}
                        </div>
                    </div>
                    <div>
                        <div class="info-item-label">Civil Service Eligibility</div>
                        <div class="info-item-value">
                            {{ $employee->civil_service_eligibility ?? ($employee->eligibility ?? 'N/A') }}
                        </div>
                    </div>
                    <div>
                        <div class="info-item-label">TIN</div>
                        <div class="info-item-value">{{ $employee->tin ?? 'N/A' }}</div>
                    </div>
                    @if($type == 'job_orders' || $type == 'casual')
                        <div style="grid-column: 1 / -1;">
                            <div class="info-item-label">Address</div>
                            <div class="info-item-value">{{ $employee->address ?? 'N/A' }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="info-card">
                <div class="info-card-title">
                    <i class="bi bi-building text-primary"></i> Employment Details
                </div>
                <div class="info-grid" style="grid-template-columns: 1fr;">
                    <div>
                        <div class="info-item-label">OFFICE / Office</div>
                        <div class="info-item-value">
                            {{ $employee->office_department ?? ($employee->office ?? 'N/A') }}
                        </div>
                    </div>
                    @if($type == 'plantilla' || $type == 'permanent')
                        <div>
                            <div class="info-item-label">Item Number</div>
                            <div class="info-item-value">{{ $employee->item_no_new ?? 'N/A' }}</div>
                        </div>
                        <div>
                            <div class="info-item-label">Salary Grade / Step</div>
                            <div class="info-item-value">SG {{ $employee->salary_grade ?? 'N/A' }} / Step
                                {{ $employee->step ?? 'N/A' }}
                            </div>
                        </div>
                    @endif
                    @if($type == 'casual')
                        <div>
                            <div class="info-item-label">Rate / Day</div>
                            <div class="info-item-value">₱{{ number_format($employee->rate_per_day, 2) }}</div>
                        </div>
                    @endif
                    @if($type == 'job_orders')

                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="info-card">
                <div class="info-card-title">
                    <i class="bi bi-clock-history text-primary"></i> Service History
                </div>
                <div class="timeline">
                    @if(isset($employee->date_original_appointment) || isset($employee->first_day_of_service) || isset($employee->period_of_employment_from))
                        <div class="timeline-item">
                            <div class="timeline-dot"></div>
                            <div class="timeline-date">
                                {{ isset($employee->date_original_appointment) ? \Carbon\Carbon::parse($employee->date_original_appointment)->format('M d, Y') : (isset($employee->first_day_of_service) ? \Carbon\Carbon::parse($employee->first_day_of_service)->format('M d, Y') : \Carbon\Carbon::parse($employee->period_of_employment_from)->format('M d, Y')) }}
                            </div>
                            <div class="timeline-content">
                                Date of Original Appointment / First Day of Service
                            </div>
                        </div>
                    @endif

                    @if(isset($employee->date_last_promotion))
                        <div class="timeline-item">
                            <div class="timeline-dot"></div>
                            <div class="timeline-date">
                                {{ \Carbon\Carbon::parse($employee->date_last_promotion)->format('M d, Y') }}
                            </div>
                            <div class="timeline-content">
                                Date of Last Promotion
                            </div>
                        </div>
                    @endif

                    <div class="timeline-item">
                        <div class="timeline-dot"
                            style="background: #10b981; border-color: white; box-shadow: 0 0 0 2px #a7f3d0;"></div>
                        <div class="timeline-date">Present</div>
                        <div class="timeline-content" style="border-left: 3px solid #10b981;">
                            Current Status: {{ $employee->status ?? 'Active' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="info-card">
                <div class="info-card-title"
                    style="display: flex; justify-content: space-between; align-items: center;">
                    <div><i class="bi bi-paperclip text-primary"></i> Attachments & Documents</div>
                </div>

                @if(session('success'))
                    <div
                        class="alert alert-success bg-green-50 text-green-800 border border-green-200 p-3 rounded-lg text-sm mb-3">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div
                        class="alert alert-danger bg-red-50 text-red-800 border border-red-200 p-3 rounded-lg text-sm mb-3">
                        {{ session('error') }}
                    </div>
                @endif
                @if($errors->any())
                    <div
                        class="alert alert-danger bg-red-50 text-red-800 border border-red-200 p-3 rounded-lg text-sm mb-3">
                        <ul class="mb-0 pl-4 list-disc">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @php
                    // Determine the correct store route and param based on employee type
                    if ($type === 'plantilla' || $type === 'permanent') {
                        $storeRoute = route('plantilla.attachments.store', $employee->id);
                        $downloadRoute = 'plantilla.attachments.download';
                        $viewRoute = 'plantilla.attachments.view';
                        $destroyRoute = 'plantilla.attachments.destroy';
                    } elseif ($type === 'job_orders' || $type === 'job-order') {
                        $storeRoute = route('job-orders.attachments.store', $employee->id);
                        $downloadRoute = 'job-orders.attachments.download';
                        $viewRoute = 'job-orders.attachments.view';
                        $destroyRoute = 'job-orders.attachments.destroy';
                    } elseif ($type === 'casual') {
                        $storeRoute = route('casual.attachments.store', $employee->id);
                        $downloadRoute = 'casual.attachments.download';
                        $viewRoute = 'casual.attachments.view';
                        $destroyRoute = 'casual.attachments.destroy';
                    } else {
                        $storeRoute = null;
                        $downloadRoute = null;
                        $viewRoute = null;
                        $destroyRoute = null;
                    }
                @endphp

                {{-- Upload Form — available for all employee types --}}
                @if($storeRoute)
                    <form action="{{ $storeRoute }}" method="POST" enctype="multipart/form-data"
                        class="mb-4 bg-gray-50 p-3 rounded-lg border border-gray-200">
                        @csrf
                        <div class="row g-2 align-items-end">
                            <div class="col-md-5">
                                <label class="info-item-label mb-1">Document Type</label>
                                <div class="d-flex flex-column gap-2">
                                    <select id="doc_type_select" name="document_type" class="form-select form-select-sm"
                                        required style="font-size: 13px; border-radius: 6px;"
                                        onchange="handleDocType(this)">
                                        <option value="">Select Type...</option>
                                        <option value="Personal Data Sheet (PDS)">Personal Data Sheet (PDS)</option>
                                        <option value="Oath of Office">Oath of Office</option>
                                        <option value="Contract of Service">Contract of Service</option>
                                        <option value="Certificate of Assumption">Certificate of Assumption</option>
                                        <option value="Statement of Assets, Liabilities and Net Worth (SALN)">SALN</option>
                                        <option value="Medical Certificate">Medical Certificate</option>
                                        <option value="NBI Clearance">NBI Clearance</option>
                                        <option value="Certificate of Eligibility">Certificate of Eligibility</option>
                                        <option value="Transcript of Records">Transcript of Records</option>
                                        <option value="Performance Rating">Performance Rating</option>
                                        <option value="Others">Others (Please specify)</option>
                                    </select>
                                    <input type="text" id="doc_type_input" class="form-control form-control-sm d-none"
                                        style="font-size: 13px; border-radius: 6px;" placeholder="Please specify...">
                                </div>
                            </div>
                            <div class="col-md-5">
                                <label class="info-item-label mb-1">File (Max 10MB)</label>
                                <input type="file" name="attachment" class="form-control form-control-sm" required
                                    style="font-size: 13px; border-radius: 6px;">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary btn-sm w-100"
                                    style="font-size: 13px; font-weight: 600; border-radius: 6px;">
                                    Upload
                                </button>
                            </div>
                        </div>
                    </form>
                @endif

                @if(isset($employee->attachments) && $employee->attachments->count() > 0)
                    @foreach($employee->attachments as $attachment)
                        <div class="attachment-item">
                            <div style="display: flex; align-items: center; gap: 12px; flex: 1; min-width: 0;">
                                @php $ext = strtolower($attachment->file_type ?? ''); @endphp
                                @if($ext === 'pdf')
                                    <i class="bi bi-file-earmark-pdf-fill text-danger" style="font-size: 24px;"></i>
                                @elseif(in_array($ext, ['doc', 'docx']))
                                    <i class="bi bi-file-earmark-word-fill text-primary" style="font-size: 24px;"></i>
                                @elseif(in_array($ext, ['xls', 'xlsx']))
                                    <i class="bi bi-file-earmark-excel-fill text-success" style="font-size: 24px;"></i>
                                @elseif(in_array($ext, ['jpg', 'jpeg', 'png', 'gif']))
                                    <i class="bi bi-file-earmark-image-fill" style="font-size: 24px; color: #8b5cf6;"></i>
                                @else
                                    <i class="bi bi-file-earmark-fill" style="font-size: 24px;"></i>
                                @endif
                                <div style="min-width: 0; flex: 1;">
                                    <div style="font-size: 13px; font-weight: 600; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                                        title="{{ $attachment->file_name }}">
                                        {{ $attachment->document_type }}
                                        <span
                                            style="font-size:11px;color:#94a3b8;font-weight:400;margin-left:4px;">({{ $attachment->file_name }})</span>
                                    </div>
                                    <div style="font-size: 11px; color: #64748b;">
                                        Uploaded by {{ $attachment->uploaded_by ?? 'System' }}
                                        • {{ $attachment->created_at->format('M d, Y') }}
                                    </div>
                                </div>
                            </div>
                            <div style="display: flex; gap: 6px; flex-shrink: 0;">
                                @if($viewRoute)
                                    @php
                                        $viewableExts = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp'];
                                        $fileExt = strtolower($attachment->file_type ?? '');
                                        $isViewable = in_array($fileExt, $viewableExts);
                                    @endphp
                                    @if($isViewable)
                                        <a href="{{ route($viewRoute, $attachment->id) }}" target="_blank"
                                            class="btn btn-sm btn-light border"
                                            style="font-size: 12px; font-weight: 600; color: #16a34a; display: inline-flex; align-items: center; gap: 4px;"
                                            title="View in browser">
                                            <i class="bi bi-eye-fill"></i> View
                                        </a>
                                    @endif
                                @endif
                                @if($downloadRoute)
                                    <a href="{{ route($downloadRoute, $attachment->id) }}" class="btn btn-sm btn-light border"
                                        style="font-size: 12px; font-weight: 600; color: #3b82f6; display: inline-flex; align-items: center; gap: 4px;"
                                        title="Download">
                                        <i class="bi bi-download"></i> Download
                                    </a>
                                @endif
                                @if($destroyRoute)
                                    <form id="delete-attachment-form-{{ $attachment->id }}"
                                        action="{{ route($destroyRoute, $attachment->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                    <button type="button" class="btn btn-sm btn-light border"
                                        style="font-size: 12px; font-weight: 600; color: #ef4444; display: inline-flex; align-items: center; gap: 4px;"
                                        title="Delete"
                                        onclick="confirmDeleteAttachment('delete-attachment-form-{{ $attachment->id }}', '{{ addslashes($attachment->document_type) }}', '{{ addslashes($attachment->file_name) }}')">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @else
                    <div style="text-align: center; padding: 32px 0; color: #94a3b8;">
                        <i class="bi bi-folder2-open" style="font-size: 32px; margin-bottom: 8px; display: block;"></i>
                        <span style="font-size: 13px; font-weight: 500;">No documents attached.</span>
                    </div>
                @endif

            </div>
        </div>
    </div>

    @if($employee->profile_picture)
        <!-- Remove Picture Modal -->
        <div class="modal fade" id="removePicModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content border-0 shadow">
                    <div class="modal-body p-4 text-center">
                        <div class="mb-3">
                            <i class="bi bi-exclamation-circle text-danger" style="font-size: 48px;"></i>
                        </div>
                        <h5 class="fw-bold mb-2 text-dark">Remove Picture?</h5>
                        <p class="text-secondary mb-4" style="font-size: 14px;">Are you sure you want to remove
                            this profile picture? This action cannot be undone.</p>
                        <div class="d-flex justify-content-center gap-2">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-danger px-4"
                                onclick="document.getElementById('remove-pic-form').submit()">Remove</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Attachment Confirmation Modal -->
    <div class="modal fade" id="deleteAttachmentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow">
                <div class="modal-body p-4 text-center">
                    <div class="mb-3">
                        <div
                            style="width: 64px; height: 64px; border-radius: 50%; background: #fef2f2; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                            <i class="bi bi-trash3-fill" style="font-size: 28px; color: #ef4444;"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold mb-1 text-dark">Delete Document?</h5>
                    <p id="deleteAttachmentDocName" class="fw-semibold text-danger mb-1" style="font-size: 14px;"></p>
                    <p id="deleteAttachmentFileName" class="text-secondary mb-4" style="font-size: 12px;"></p>
                    <p class="text-secondary mb-4" style="font-size: 13px;">This action <strong>cannot be
                            undone</strong>. The file will be permanently removed.</p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                            <i class="bi bi-x-lg me-1"></i>Cancel
                        </button>
                        <button type="button" id="confirmDeleteAttachmentBtn" class="btn btn-danger px-4">
                            <i class="bi bi-trash3 me-1"></i>Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmDeleteAttachment(formId, docType, fileName) {
            document.getElementById('deleteAttachmentDocName').textContent = docType;
            document.getElementById('deleteAttachmentFileName').textContent = fileName ? '(' + fileName + ')' : '';
            document.getElementById('confirmDeleteAttachmentBtn').onclick = function () {
                document.getElementById(formId).submit();
            };
            var modal = new bootstrap.Modal(document.getElementById('deleteAttachmentModal'));
            modal.show();
        }

        function handleDocType(sel) {
            const inp = document.getElementById('doc_type_input');
            if (sel.value === 'Others') {
                inp.classList.remove('d-none');
                inp.setAttribute('name', 'document_type');
                inp.setAttribute('required', 'required');
                sel.removeAttribute('name');
                inp.focus();
            } else {
                inp.classList.add('d-none');
                inp.removeAttribute('name');
                inp.removeAttribute('required');
                sel.setAttribute('name', 'document_type');
                inp.value = '';
            }
        }

        function previewProfilePic(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const img = document.getElementById('avatar-preview');
                    const initials = document.getElementById('avatar-initials');
                    if (initials) initials.style.display = 'none';
                    img.src = e.target.result;
                    img.style.display = 'block';

                    document.getElementById('pic-actions').style.display = 'flex';
                    const removeForm = document.getElementById('remove-pic-form');
                    if (removeForm) removeForm.style.display = 'none';
                }
                reader.readAsDataURL(file);
            }
        }

        function cancelProfilePic() {
            window.location.reload();
        }
    </script>
</x-dashboard-app>