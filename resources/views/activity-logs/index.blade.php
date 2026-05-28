<x-dashboard-app>
    <div style="max-width: 1100px; margin: 0 auto; display: flex; flex-direction: column; gap: 1.5rem;">
        
        {{-- Header Area --}}
        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #e5e7eb;">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <a href="{{ route('dashboard') }}" style="color: #6b7280; text-decoration: none; display: flex; align-items: center; justify-content: center; transition: color 0.2s;">
                    <i class="bi bi-arrow-left" style="font-size: 1.5rem;"></i>
                </a>
                <h1 style="font-size: 1.5rem; font-weight: 700; color: #111827; margin: 0;">Activity Logs</h1>
            </div>
            <div style="display: flex; align-items: center; gap: 1rem;">
                <button type="button" style="background: none; border: none; color: #6b7280; cursor: pointer; display: flex; align-items: center; transition: color 0.2s;">
                    <i class="bi bi-bell" style="font-size: 1.25rem;"></i>
                </button>
                <a href="{{ route('archives.index') }}"
                   style="background-color: #7c3aed; color: white; border: none; padding: 0.5rem 1rem; border-radius: 0.5rem; font-weight: 600; font-size: 0.875rem; display: flex; align-items: center; gap: 0.5rem; text-decoration: none; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: background .15s;"
                   onmouseover="this.style.backgroundColor='#6d28d9';" onmouseout="this.style.backgroundColor='#7c3aed';">
                    <i class="bi bi-archive-fill"></i> View Archives
                </a>
                <button type="button" style="background-color: #22c55e; color: white; border: none; padding: 0.5rem 1rem; border-radius: 0.5rem; font-weight: 600; font-size: 0.875rem; display: flex; align-items: center; gap: 0.5rem; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                    <i class="bi bi-download"></i> Export Data
                </button>
            </div>
        </div>

        {{-- Flash messages --}}
        @if(session('success'))
        <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 0.75rem 1rem; border-radius: 0.5rem; font-size: 0.875rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="bi bi-check-circle-fill" style="color: #16a34a;"></i> {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div style="background-color: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 0.75rem 1rem; border-radius: 0.5rem; font-size: 0.875rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="bi bi-exclamation-triangle-fill" style="color: #dc2626;"></i> {{ session('error') }}
        </div>
        @endif

        {{-- Main White Card --}}
        <div style="background-color: white; border-radius: 0.75rem; border: 1px solid #f3f4f6; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03); overflow: hidden; display: flex; flex-direction: column;">
            
            {{-- Search & Filters --}}
            <form method="GET" action="{{ route('activity-logs.index') }}" style="padding: 1.25rem; display: flex; flex-wrap: wrap; gap: 1rem; align-items: center; justify-content: space-between;">
                <div style="flex: 1; min-width: 250px; position: relative;">
                    <i class="bi bi-search" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #9ca3af;"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search logs by user, action, or date..." 
                           style="width: 100%; padding: 0.5rem 1rem 0.5rem 2.5rem; border: 1px solid #e5e7eb; border-radius: 0.5rem; font-size: 0.875rem; outline: none; background-color: #f9fafb; color: #111827;" onchange="this.form.submit()">
                </div>
                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
                    <select name="action_filter" style="padding: 0.5rem 2rem 0.5rem 1rem; background-color: white; border: 1px solid #e5e7eb; color: #4b5563; border-radius: 0.5rem; font-size: 0.8125rem; font-weight: 600; cursor: pointer; outline: none; appearance: auto;" onchange="this.form.submit()">
                        <option value="">All Actions</option>
                        @foreach($actions as $act)
                            <option value="{{ $act }}" {{ request('action_filter') === $act ? 'selected' : '' }}>{{ $act }}</option>
                        @endforeach
                    </select>
                    
                    <div style="display: flex; align-items: center; gap: 0.5rem; background-color: white; border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 0.25rem 0.5rem;">
                        <input type="date" name="date_from" value="{{ request('date_from') }}" style="border: none; outline: none; font-size: 0.8125rem; color: #4b5563; cursor: pointer; background: transparent;" title="From Date" onchange="this.form.submit()">
                        <span style="color: #9ca3af; font-size: 0.8125rem;">-</span>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" style="border: none; outline: none; font-size: 0.8125rem; color: #4b5563; cursor: pointer; background: transparent;" title="To Date" onchange="this.form.submit()">
                    </div>

                    @if(request('search') || request('action_filter') || request('date_from') || request('date_to'))
                        <a href="{{ route('activity-logs.index') }}" style="padding: 0.5rem 1rem; background-color: #f3f4f6; color: #4b5563; border-radius: 0.5rem; font-size: 0.8125rem; font-weight: 600; text-decoration: none;">Clear</a>
                    @endif
                </div>
            </form>
            
            {{-- Table Wrapper --}}
            <div style="overflow-x: auto; width: 100%;">
                <table style="width: 100%; min-width: 800px; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="border-top: 1px solid #f3f4f6; border-bottom: 1px solid #f3f4f6; background-color: #ffffff;">
                            <th style="padding: 1rem 1.5rem; font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; color: #6b7280; letter-spacing: 0.05em;">User</th>
                            <th style="padding: 1rem 1.5rem; font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; color: #6b7280; letter-spacing: 0.05em;">Action</th>
                            <th style="padding: 1rem 1.5rem; font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; color: #6b7280; letter-spacing: 0.05em; width: 35%;">Resource</th>
                            <th style="padding: 1rem 1.5rem; font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; color: #6b7280; letter-spacing: 0.05em;">Timestamp</th>
                            <th style="padding: 1rem 1.5rem; font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; color: #6b7280; letter-spacing: 0.05em; text-align: center;">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            @php
                                $isVacated   = $log->action === 'Vacated Position';
                                $isRestored  = $log->action === 'Restored Employee';
                                $isExported  = str_contains($log->action, 'Export');
                                $isDeleted   = str_contains($log->action, 'Deleted');
                                $canUndo     = $isVacated && !empty($log->snapshot);

                                // Action badge colors based on screenshot
                                $badgeClass  = match(true) {
                                    $isVacated  => 'background-color:#fef3c7; color:#d97706;', // Amber
                                    $isRestored => 'background-color:#dcfce7; color:#15803d;', // Green
                                    $isDeleted  => 'background-color:#fee2e2; color:#b91c1c;', // Red
                                    $isExported => 'background-color:#e0e7ff; color:#4f46e5;', // Indigo
                                    default     => 'background-color:#f3f4f6; color:#4b5563;', // Gray
                                };
                                $actionText = $isVacated ? 'Vacated' : 
                                             ($isRestored ? 'Restored' : 
                                             ($isExported ? 'Exported' : 
                                             ($isDeleted ? 'Deleted' : 'Modified')));

                                // Avatar styles
                                $avatarStyles = [
                                    'background-color:#dbeafe; color:#2563eb;',
                                    'background-color:#f3e8ff; color:#9333ea;',
                                    'background-color:#dcfce7; color:#16a34a;',
                                    'background-color:#fef3c7; color:#d97706;',
                                    'background-color:#e0e7ff; color:#4f46e5;'
                                ];
                                $hash = strlen($log->user ? $log->user->name : 'System');
                                $avatarClass = $avatarStyles[$hash % count($avatarStyles)];

                                // Format date (Today, Yesterday, or Date)
                                $dateStr = '';
                                if ($log->created_at->isToday()) {
                                    $dateStr = 'Today, ' . $log->created_at->format('h:i A');
                                } elseif ($log->created_at->isYesterday()) {
                                    $dateStr = 'Yesterday, ' . $log->created_at->format('h:i A');
                                } else {
                                    $dateStr = $log->created_at->format('M d, Y, h:i A');
                                }
                            @endphp
                            <tr style="border-bottom: 1px solid #f3f4f6; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#f9fafb'" onmouseout="this.style.backgroundColor='transparent'">
                                <td style="padding: 1rem 1.5rem;">
                                    <div style="display: flex; align-items: center; gap: 0.875rem;">
                                        <div style="width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.875rem; flex-shrink: 0; {{ $avatarClass }}">
                                            @if($log->user && $log->user->profile_picture)
                                                <img src="{{ Storage::url($log->user->profile_picture) }}" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                                            @else
                                                @php
                                                    $name = $log->user ? $log->user->name : 'System';
                                                    $initials = collect(explode(' ', $name))->map(fn($n) => substr($n, 0, 1))->take(2)->implode('');
                                                @endphp
                                                {{ strtoupper($initials) }}
                                            @endif
                                        </div>
                                        <div>
                                            <p style="margin: 0; font-size: 0.875rem; font-weight: 700; color: #111827; line-height: 1.25;">{{ $log->user ? $log->user->name : 'System' }}</p>
                                            <p style="margin: 0; font-size: 0.75rem; color: #9ca3af; margin-top: 0.125rem;">{{ $log->user ? $log->user->email ?? 'user@system.com' : 'system@system.com' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding: 1rem 1.5rem;">
                                    <span style="display: inline-flex; align-items: center; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.6875rem; font-weight: 700; letter-spacing: 0.025em; {{ $badgeClass }}">
                                        {{ $actionText }}
                                    </span>
                                </td>
                                <td style="padding: 1rem 1.5rem; font-size: 0.8125rem; color: #4b5563; font-weight: 500; line-height: 1.4;">
                                    {{ \Illuminate\Support\Str::limit($log->description, 60) }}
                                </td>
                                <td style="padding: 1rem 1.5rem; font-size: 0.8125rem; color: #6b7280; white-space: nowrap;">
                                    {{ $dateStr }}
                                </td>
                                <td style="padding: 1rem 1.5rem; text-align: center;">
                                    <div style="display: flex; justify-content: center; align-items: center; gap: 0.25rem;">
                                        <button type="button"
                                                style="background: transparent; border: 1px solid #e5e7eb; color: #4b5563; border-radius: 9999px; padding: 0.25rem 0.75rem; display: inline-flex; align-items: center; justify-content: center; gap: 0.375rem; cursor: pointer; transition: all 0.2s; font-size: 0.75rem; font-weight: 600;"
                                                onmouseover="this.style.backgroundColor='#f3f4f6'; this.style.borderColor='#d1d5db';"
                                                onmouseout="this.style.backgroundColor='transparent'; this.style.borderColor='#e5e7eb';"
                                                onclick="viewActivityLog(this)"
                                                data-user="{{ $log->user ? $log->user->name : 'System' }}"
                                                data-action="{{ $log->action }}"
                                                data-details="{{ $log->description }}"
                                                data-date="{{ $log->created_at->format('M d, Y h:i A') }}"
                                                title="View Details">
                                            <i class="bi bi-eye" style="font-size: 0.875rem;"></i> View
                                        </button>

                                        {{-- Undo button — only for Vacated Position with snapshot --}}
                                        @if($canUndo)
                                        <form method="POST" action="{{ route('activity-logs.undo', $log) }}" id="form-restore-{{ $log->id }}">
                                            @csrf
                                            <button type="button" 
                                                    onclick="confirmRestore('{{ $log->id }}')"
                                                    class="btn btn-sm" 
                                                    style="background-color: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; font-size: 11px; padding: 4px 10px; border-radius: 6px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: all 0.2s; font-weight: 600;"
                                                    onmouseover="this.style.backgroundColor='#dcfce7'; this.style.transform='translateY(-1px)';"
                                                    onmouseout="this.style.backgroundColor='#f0fdf4'; this.style.transform='translateY(0)';">
                                                <i class="bi bi-arrow-counterclockwise" style="margin-right: 4px;"></i> Restore
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="padding: 2rem 1.5rem; text-align: center; color: #6b7280;">
                                    <i class="bi bi-inbox" style="font-size: 1.875rem; display: block; margin-bottom: 0.5rem; opacity: 0.5;"></i>
                                    No activity logs found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- Pagination & Footer --}}
            <div style="padding: 1rem 1.5rem; border-top: 1px solid #f3f4f6; background-color: white; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem;">
                <div style="font-size: 0.75rem; color: #6b7280; font-weight: 500;">
                    Showing {{ $logs->firstItem() ?? 0 }}-{{ $logs->lastItem() ?? 0 }} of {{ number_format($logs->total()) }} entries
                </div>
                <div>
                    {{ $logs->links() }}
                </div>
            </div>
        </div>

        {{-- Bottom Summary Cards --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-top: 0.5rem;">
            {{-- Total Actions --}}
            <div style="background-color: white; border-radius: 0.75rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; padding: 1.25rem;">
                <p style="font-size: 0.6875rem; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 0.75rem 0;">Total Actions</p>
                <h3 style="font-size: 1.875rem; font-weight: 700; color: #111827; margin: 0 0 0.5rem 0;">{{ number_format($totalActions) }}</h3>
                <p style="font-size: 0.6875rem; font-weight: 700; color: #16a34a; letter-spacing: 0.025em; margin: 0;">+12% from last week</p>
            </div>
            {{-- Restored --}}
            <div style="background-color: white; border-radius: 0.75rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; padding: 1.25rem;">
                <p style="font-size: 0.6875rem; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 0.75rem 0;">Restored</p>
                <h3 style="font-size: 1.875rem; font-weight: 700; color: #16a34a; margin: 0 0 1rem 0;">{{ number_format($restoredCount) }}</h3>
                <div style="width: 100%; background-color: #f3f4f6; border-radius: 9999px; height: 0.25rem; overflow: hidden;">
                    <div style="background-color: #22c55e; height: 100%; border-radius: 9999px; width: {{ $totalActions > 0 ? ($restoredCount/$totalActions)*100 : 0 }}%;"></div>
                </div>
            </div>
            {{-- Vacated --}}
            <div style="background-color: white; border-radius: 0.75rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; padding: 1.25rem;">
                <p style="font-size: 0.6875rem; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 0.75rem 0;">Vacated</p>
                <h3 style="font-size: 1.875rem; font-weight: 700; color: #f59e0b; margin: 0 0 1rem 0;">{{ number_format($vacatedCount) }}</h3>
                <div style="width: 100%; background-color: #f3f4f6; border-radius: 9999px; height: 0.25rem; overflow: hidden;">
                    <div style="background-color: #f59e0b; height: 100%; border-radius: 9999px; width: {{ $totalActions > 0 ? ($vacatedCount/$totalActions)*100 : 0 }}%;"></div>
                </div>
            </div>
            {{-- Security Alerts --}}
            <div style="background-color: white; border-radius: 0.75rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; padding: 1.25rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                    <p style="font-size: 0.6875rem; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin: 0;">Security Alerts</p>
                    <span style="background-color: #dcfce7; color: #15803d; font-size: 0.625rem; font-weight: 700; padding: 0.125rem 0.5rem; border-radius: 0.125rem; letter-spacing: 0.025em;">Normal</span>
                </div>
                <h3 style="font-size: 1.875rem; font-weight: 700; color: #111827; margin: 0 0 0.5rem 0;">0</h3>
                <p style="font-size: 0.6875rem; color: #6b7280; margin: 0; letter-spacing: 0.025em;">All systems operational</p>
            </div>
        </div>
    </div>

    <!-- Activity Log Details Modal -->
    <div class="modal fade" id="activityLogModal" tabindex="-1" aria-labelledby="activityLogModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-3 shadow" style="border-radius: 0.75rem !important;">
                <div class="modal-header bg-light border-bottom border-light p-4" style="border-radius: 0.75rem 0.75rem 0 0;">
                    <h5 class="modal-title fw-bold text-dark fs-5 d-flex align-items-center gap-2" id="activityLogModalLabel">
                        <i class="bi bi-info-circle text-primary"></i> Activity Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" style="gap: 1rem; display: flex; flex-direction: column;">
                    <div class="row border-bottom border-light pb-3">
                        <div class="col-4 text-secondary fw-semibold text-uppercase" style="font-size: 0.8125rem;">User</div>
                        <div class="col-8 text-dark fw-medium" style="font-size: 0.875rem;" id="modalLogUser"></div>
                    </div>
                    <div class="row border-bottom border-light pb-3">
                        <div class="col-4 text-secondary fw-semibold text-uppercase" style="font-size: 0.8125rem;">Action</div>
                        <div class="col-8">
                            <span class="badge bg-light text-dark border" style="padding: 0.25rem 0.625rem; font-size: 0.75rem; font-weight: 700; border-radius: 9999px;" id="modalLogAction"></span>
                        </div>
                    </div>
                    <div class="row border-bottom border-light pb-3">
                        <div class="col-4 text-secondary fw-semibold text-uppercase" style="font-size: 0.8125rem;">Date & Time</div>
                        <div class="col-8 text-secondary" style="font-size: 0.875rem;" id="modalLogDate"></div>
                    </div>
                    <div>
                        <div class="text-secondary fw-semibold text-uppercase mb-2" style="font-size: 0.8125rem;">Resource / Description</div>
                        <div class="bg-light p-3 rounded-3 border border-light text-dark" style="font-size: 0.875rem; line-height: 1.6;" id="modalLogDetails"></div>
                    </div>
                </div>
                <div class="modal-footer border-top border-light p-3 bg-light" style="border-radius: 0 0 0.75rem 0.75rem;">
                    <button type="button" class="btn btn-white border shadow-sm text-dark fw-medium px-4" data-bs-dismiss="modal" style="font-size: 0.875rem; background-color: white;">Close</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Custom Pagination styles for target UI */
        .pagination {
            display: flex;
            gap: 0.25rem;
            margin: 0;
            padding: 0;
            list-style: none;
        }
        .pagination .page-item .page-link {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            min-width: 32px !important;
            height: 32px !important;
            padding: 0 0.5rem !important;
            font-size: 0.875rem !important;
            font-weight: 500 !important;
            color: #4b5563 !important;
            background-color: #fff !important;
            border: 1px solid #e5e7eb !important;
            border-radius: 0.375rem !important;
            transition: all 0.2s !important;
            text-decoration: none !important;
        }
        .pagination .page-item .page-link:hover {
            background-color: #f9fafb !important;
            color: #111827 !important;
        }
        .pagination .page-item.active .page-link {
            background-color: #22c55e !important;
            border-color: #22c55e !important;
            color: #fff !important;
            z-index: 1 !important;
        }
        .pagination .page-item.disabled .page-link {
            color: #9ca3af !important;
            background-color: #f9fafb !important;
            cursor: not-allowed !important;
        }
    </style>

    <script>
        function viewActivityLog(button) {
            const user = button.getAttribute('data-user');
            const action = button.getAttribute('data-action');
            const details = button.getAttribute('data-details');
            const date = button.getAttribute('data-date');

            document.getElementById('modalLogUser').textContent = user;
            document.getElementById('modalLogAction').textContent = action;
            document.getElementById('modalLogDetails').textContent = details;
            document.getElementById('modalLogDate').textContent = date;

            const modalElement = document.getElementById('activityLogModal');
            let modalInstance = bootstrap.Modal.getInstance(modalElement);
            if (!modalInstance) {
                modalInstance = new bootstrap.Modal(modalElement);
            }
            modalInstance.show();
        }
    </script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmRestore(id) {
            Swal.fire({
                title: 'Restore Employee?',
                text: "Restore employee from this vacated position? This will undo the deletion and bring back all personal data.",
                icon: 'question',
                iconColor: '#198754',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, restore it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('form-restore-' + id).submit();
                }
            });
        }
    </script>
</x-dashboard-app>
