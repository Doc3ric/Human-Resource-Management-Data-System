<x-dashboard-app>
    <div style="max-width:900px;margin:0 auto;">
        <div style="margin-bottom:20px;display:flex;align-items:center;gap:10px;">
            <a href="{{ session('last_index_url', route('job-orders.index')) }}"
               style="display:inline-flex;align-items:center;gap:6px;color:#64748b;font-size:13px;font-weight:600;text-decoration:none;padding:6px 12px;border:1.5px solid #e2e8f0;border-radius:8px;background:#f8fafc;transition:all .15s;"
               onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'">
                <i class="bi bi-arrow-left"></i> Back to JO Inventory
            </a>
            <h2 style="font-size:19px;font-weight:800;color:#0f172a;margin:0;">
                <i class="bi bi-pencil-square" style="color:#2563eb;"></i>
                Edit: {{ strtoupper($jobOrder->last_name) }}, {{ $jobOrder->first_name }}
            </h2>
        </div>

        @if($errors->any())
            <div style="background:#fef2f2;border:1px solid #fecaca;color:#dc2626;border-radius:10px;padding:14px 18px;margin-bottom:18px;font-size:13px;">
                <strong><i class="bi bi-exclamation-triangle-fill"></i> Please fix the following errors:</strong>
                <ul style="margin:8px 0 0 20px;padding:0;">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        @include('job-orders.form', [
            'jo'         => $jobOrder,
            'formAction' => route('job-orders.update', $jobOrder),
            'formMethod' => 'PUT',
        ])

        {{-- ── Document Attachments Panel ── --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:28px;box-shadow:0 1px 6px rgba(0,0,0,.05);margin-top:22px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
                <div style="font-size:11px;font-weight:800;letter-spacing:1px;text-transform:uppercase;color:#94a3b8;padding-bottom:8px;border-bottom:1px solid #f1f5f9;width:100%;">
                    <i class="bi bi-paperclip"></i> Document Attachments (201 File)
                </div>
            </div>

            {{-- Flash messages --}}
            @if(session('success'))
                <div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:13px;">
                    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div style="background:#fef2f2;border:1px solid #fecaca;color:#dc2626;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:13px;">
                    <i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}
                </div>
            @endif

            {{-- Existing Attachments List --}}
            @if($jobOrder->attachments->count() > 0)
                <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:20px;">
                    @foreach($jobOrder->attachments as $att)
                        <div style="display:flex;align-items:center;gap:12px;padding:12px 14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;">
                            {{-- File Icon --}}
                            <div style="font-size:22px;color:#64748b;min-width:28px;text-align:center;">
                                @if(in_array(strtolower($att->file_type), ['pdf']))
                                    <i class="bi bi-file-earmark-pdf-fill" style="color:#ef4444;"></i>
                                @elseif(in_array(strtolower($att->file_type), ['doc','docx']))
                                    <i class="bi bi-file-earmark-word-fill" style="color:#2563eb;"></i>
                                @elseif(in_array(strtolower($att->file_type), ['xls','xlsx']))
                                    <i class="bi bi-file-earmark-excel-fill" style="color:#16a34a;"></i>
                                @elseif(in_array(strtolower($att->file_type), ['jpg','jpeg','png','gif','svg']))
                                    <i class="bi bi-file-earmark-image-fill" style="color:#8b5cf6;"></i>
                                @else
                                    <i class="bi bi-file-earmark-fill"></i>
                                @endif
                            </div>
                            {{-- File Info --}}
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:13px;font-weight:700;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="{{ $att->file_name }}">
                                    {{ $att->document_type }}
                                    <span style="font-size:11px;color:#94a3b8;font-weight:400;margin-left:6px;">({{ $att->file_name }})</span>
                                </div>
                                <div style="font-size:11px;color:#94a3b8;margin-top:2px;">
                                    Uploaded by {{ $att->uploaded_by }} &bull; {{ $att->created_at->format('M d, Y h:i A') }}
                                </div>
                            </div>
                            {{-- Actions --}}
                            <div style="display:flex;align-items:center;gap:6px;">
                                <a href="{{ route('job-orders.attachments.download', $att) }}"
                                   style="display:inline-flex;align-items:center;gap:4px;padding:6px 10px;font-size:12px;font-weight:600;color:#2563eb;background:#eff6ff;border:1px solid #bfdbfe;border-radius:7px;text-decoration:none;transition:all .15s;"
                                   onmouseover="this.style.background='#dbeafe'" onmouseout="this.style.background='#eff6ff'">
                                    <i class="bi bi-download"></i> Download
                                </a>
                                <form action="{{ route('job-orders.attachments.destroy', $att) }}" method="POST" id="del-jo-att-{{ $att->id }}" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="button"
                                            onclick="confirmDeleteJOAttachment({{ $att->id }})"
                                            style="display:inline-flex;align-items:center;gap:4px;padding:6px 10px;font-size:12px;font-weight:600;color:#dc2626;background:#fef2f2;border:1px solid #fecaca;border-radius:7px;cursor:pointer;transition:all .15s;"
                                            onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fef2f2'">
                                        <i class="bi bi-trash3"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div style="text-align:center;padding:24px;background:#f8fafc;border-radius:10px;margin-bottom:20px;">
                    <i class="bi bi-folder2-open" style="font-size:28px;color:#cbd5e1;"></i>
                    <p style="margin:8px 0 0;font-size:13px;color:#94a3b8;">No attachments yet. Upload documents using the form below.</p>
                </div>
            @endif

            {{-- Upload Form --}}
            <div style="border-top:1px solid #f1f5f9;padding-top:18px;">
                <div style="font-size:12px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px;">
                    <i class="bi bi-cloud-upload"></i> Upload New Document
                </div>
                <form action="{{ route('job-orders.attachments.store', $jobOrder) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div style="display:grid;grid-template-columns:1fr 1fr auto;gap:12px;align-items:end;">
                        <div>
                            <label style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:5px;">Document Type</label>
                            <select name="document_type" required
                                    style="height:38px;border:1.5px solid #e2e8f0;border-radius:9px;padding:0 12px;font-size:13px;color:#0f172a;background:#f8fafc;width:100%;outline:none;font-family:'Inter',sans-serif;">
                                <option value="">— Select Type —</option>
                                <option value="PDS (Personal Data Sheet)">PDS (Personal Data Sheet)</option>
                                <option value="Oath of Office">Oath of Office</option>
                                <option value="Contract of Service">Contract of Service</option>
                                <option value="Certificate of Eligibility">Certificate of Eligibility</option>
                                <option value="Service Record">Service Record</option>
                                <option value="NBI Clearance">NBI Clearance</option>
                                <option value="Medical Certificate">Medical Certificate</option>
                                <option value="Diploma / TOR">Diploma / TOR</option>
                                <option value="Other Document">Other Document</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:5px;">File</label>
                            <input type="file" name="attachment" required
                                   style="height:38px;border:1.5px solid #e2e8f0;border-radius:9px;padding:4px 10px;font-size:12px;color:#0f172a;background:#f8fafc;width:100%;outline:none;">
                        </div>
                        <div>
                            <button type="submit"
                                    style="height:38px;padding:0 20px;background:linear-gradient(135deg,#3b82f6,#2563eb);color:#fff;border:none;border-radius:9px;font-size:13px;font-weight:700;cursor:pointer;white-space:nowrap;display:inline-flex;align-items:center;gap:6px;">
                                <i class="bi bi-upload"></i> Upload
                            </button>
                        </div>
                    </div>
                    <p style="margin:8px 0 0;font-size:11px;color:#94a3b8;">Accepted: PDF, Word, Excel, Images. Max size: 10MB.</p>
                </form>
            </div>
        </div>

    </div>

    <script>
    function confirmDeleteJOAttachment(id) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Delete Attachment?',
                text: 'This attachment will be permanently removed from this record.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, delete it',
                cancelButtonText: 'Cancel',
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('del-jo-att-' + id).submit();
                }
            });
        } else {
            if (confirm('Delete this attachment permanently?')) {
                document.getElementById('del-jo-att-' + id).submit();
            }
        }
    }
    </script>
</x-dashboard-app>
