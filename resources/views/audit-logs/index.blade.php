<x-dashboard-app>
    <div style="max-width: 1100px; margin: 0 auto; display: flex; flex-direction: column; gap: 1.5rem;">
        
        {{-- Header Area --}}
        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #e5e7eb;">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <a href="{{ route('dashboard') }}" style="color: #6b7280; text-decoration: none; display: flex; align-items: center; justify-content: center; transition: color 0.2s;">
                    <i class="bi bi-arrow-left" style="font-size: 1.5rem;"></i>
                </a>
                <h1 style="font-size: 1.5rem; font-weight: 700; color: #111827; margin: 0;">System Audit Trail</h1>
            </div>
            <div style="display: flex; align-items: center; gap: 1rem;">
                <button type="button" style="background: none; border: none; color: #6b7280; cursor: pointer; display: flex; align-items: center; transition: color 0.2s;">
                    <i class="bi bi-bell" style="font-size: 1.25rem;"></i>
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
            <form method="GET" action="{{ route('audit-logs.index') }}" style="padding: 1.25rem; display: flex; flex-wrap: wrap; gap: 1rem; align-items: center; justify-content: space-between;">
                <div style="flex: 1; min-width: 250px; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 200px; position: relative;">
                        <i class="bi bi-search" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #9ca3af;"></i>
                        <input type="text" name="user" value="{{ request('user') }}" placeholder="Search logs by user..." 
                               style="width: 100%; padding: 0.5rem 1rem 0.5rem 2.5rem; border: 1px solid #e5e7eb; border-radius: 0.5rem; font-size: 0.875rem; outline: none; background-color: #f9fafb; color: #111827;" onchange="this.form.submit()">
                    </div>
                    <div>
                        <select name="user_id" style="width: 200px; padding: 0.5rem 2rem 0.5rem 1rem; background-color: white; border: 1px solid #e5e7eb; color: #4b5563; border-radius: 0.5rem; font-size: 0.8125rem; font-weight: 600; cursor: pointer; outline: none; appearance: auto;" onchange="this.form.submit()">
                            <option value="">All Registered Users</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
                    <select name="event" style="padding: 0.5rem 2rem 0.5rem 1rem; background-color: white; border: 1px solid #e5e7eb; color: #4b5563; border-radius: 0.5rem; font-size: 0.8125rem; font-weight: 600; cursor: pointer; outline: none; appearance: auto;" onchange="this.form.submit()">
                        <option value="">All Actions</option>
                        <option value="created" {{ request('event') == 'created' ? 'selected' : '' }}>Created</option>
                        <option value="updated" {{ request('event') == 'updated' ? 'selected' : '' }}>Updated</option>
                        <option value="deleted" {{ request('event') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                        <option value="restored" {{ request('event') == 'restored' ? 'selected' : '' }}>Restored</option>
                    </select>

                    @if(request('user') || request('event') || request('user_id'))
                        <a href="{{ route('audit-logs.index') }}" style="padding: 0.5rem 1rem; background-color: #f3f4f6; color: #4b5563; border-radius: 0.5rem; font-size: 0.8125rem; font-weight: 600; text-decoration: none;">Clear</a>
                    @endif
                </div>
            </form>
            
            {{-- Tabs (Fully Inline to bypass global CSS overrides) --}}
            <div class="nav" role="tablist" style="display: flex; gap: 1rem; margin: 0 1.5rem 1rem 1.5rem; border-bottom: 2px solid #e5e7eb; padding: 0;">
                <button id="spatie-tab" data-bs-toggle="tab" data-bs-target="#spatie-pane" type="button" role="tab" 
                        class="active"
                        style="padding: 0.75rem 1rem; font-weight: 700; background: transparent; border: none; border-bottom: 2px solid #2563eb; margin-bottom: -2px; font-size: 0.875rem; cursor: pointer;"
                        onclick="document.getElementById('spatie-text').style.color='#2563eb'; this.style.borderBottom='2px solid #2563eb'; document.getElementById('custom-text').style.color='#6b7280'; document.getElementById('custom-tab').style.borderBottom='2px solid transparent';">
                    <span id="spatie-text" style="color: #2563eb; opacity: 1; visibility: visible;">Data Changes (System)</span>
                </button>
                <button id="custom-tab" data-bs-toggle="tab" data-bs-target="#custom-pane" type="button" role="tab" 
                        style="padding: 0.75rem 1rem; font-weight: 700; background: transparent; border: none; border-bottom: 2px solid transparent; margin-bottom: -2px; font-size: 0.875rem; cursor: pointer;"
                        onclick="document.getElementById('custom-text').style.color='#2563eb'; this.style.borderBottom='2px solid #2563eb'; document.getElementById('spatie-text').style.color='#6b7280'; document.getElementById('spatie-tab').style.borderBottom='2px solid transparent';">
                    <span id="custom-text" style="color: #6b7280; opacity: 1; visibility: visible;">Action Logs (Manual)</span>
                </button>
            </div>

            <div class="tab-content" id="auditTabsContent">
                {{-- Spatie Logs --}}
                <div class="tab-pane fade show active" id="spatie-pane" role="tabpanel">
                    <div style="overflow-x: auto; width: 100%;">
                        <table style="width: 100%; min-width: 800px; border-collapse: collapse; text-align: left;">
                            <thead>
                                <tr style="border-top: 1px solid #f3f4f6; border-bottom: 1px solid #f3f4f6; background-color: #ffffff;">
                                    <th style="padding: 1rem 1.5rem; font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; color: #6b7280; letter-spacing: 0.05em;">User</th>
                                    <th style="padding: 1rem 1.5rem; font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; color: #6b7280; letter-spacing: 0.05em;">Action</th>
                                    <th style="padding: 1rem 1.5rem; font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; color: #6b7280; letter-spacing: 0.05em; width: 35%;">Module / Record</th>
                                    <th style="padding: 1rem 1.5rem; font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; color: #6b7280; letter-spacing: 0.05em;">Timestamp</th>
                                    <th style="padding: 1rem 1.5rem; font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; color: #6b7280; letter-spacing: 0.05em; text-align: center;">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                    @php
                                        $badgeClass  = match($log->event) {
                                            'created'  => 'background-color:#dcfce7; color:#15803d;',
                                            'updated'  => 'background-color:#dbeafe; color:#1e40af;',
                                            'deleted'  => 'background-color:#fee2e2; color:#b91c1c;',
                                            'restored' => 'background-color:#fef3c7; color:#d97706;',
                                            default    => 'background-color:#f3f4f6; color:#4b5563;',
                                        };
                                        $actionText = ucfirst($log->event);

                                        $avatarStyles = [
                                            'background-color:#dbeafe; color:#2563eb;',
                                            'background-color:#f3e8ff; color:#9333ea;',
                                            'background-color:#dcfce7; color:#16a34a;',
                                            'background-color:#fef3c7; color:#d97706;',
                                            'background-color:#e0e7ff; color:#4f46e5;'
                                        ];
                                        $hash = strlen($log->causer ? $log->causer->name : 'System');
                                        $avatarClass = $avatarStyles[$hash % count($avatarStyles)];

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
                                                    @if($log->causer && $log->causer->profile_picture)
                                                        <img src="{{ Storage::url($log->causer->profile_picture) }}" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                                                    @else
                                                        @php
                                                            $name = $log->causer ? $log->causer->name : 'System';
                                                            $initials = collect(explode(' ', $name))->map(fn($n) => substr($n, 0, 1))->take(2)->implode('');
                                                        @endphp
                                                        {{ strtoupper($initials) }}
                                                    @endif
                                                </div>
                                                <div>
                                                    <p style="margin: 0; font-size: 0.875rem; font-weight: 700; color: #111827; line-height: 1.25;">{{ $log->causer ? $log->causer->name : 'System / Unknown' }}</p>
                                                    <p style="margin: 0; font-size: 0.75rem; color: #9ca3af; margin-top: 0.125rem;">{{ $log->causer ? $log->causer->role_label ?? $log->causer->role : 'System Action' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td style="padding: 1rem 1.5rem;">
                                            <span style="display: inline-flex; align-items: center; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.6875rem; font-weight: 700; letter-spacing: 0.025em; {{ $badgeClass }}">
                                                {{ $actionText }}
                                            </span>
                                        </td>
                                        <td style="padding: 1rem 1.5rem; font-size: 0.8125rem; color: #4b5563; font-weight: 500; line-height: 1.4;">
                                            <div style="font-weight: 600; color: #111827;">{{ class_basename($log->subject_type) }}</div>
                                            <div style="font-size: 0.75rem; color: #6b7280;">ID: {{ $log->subject_id }}</div>
                                            <div style="font-size: 0.75rem; color: #6b7280; margin-top: 4px; font-style: italic;">{{ $log->description }}</div>
                                        </td>
                                        <td style="padding: 1rem 1.5rem; font-size: 0.8125rem; color: #6b7280; white-space: nowrap;">
                                            {{ $dateStr }}
                                        </td>
                                        <td style="padding: 1rem 1.5rem; text-align: center;">
                                            <div style="display: flex; justify-content: center; align-items: center; gap: 0.25rem; flex-wrap: wrap;">
                                                <button type="button"
                                                        class="btn-audit changes"
                                                        onclick="viewAuditLog(this)"
                                                        data-user="{{ $log->causer ? $log->causer->name : 'System' }}"
                                                        data-action="{{ $log->event }}"
                                                        data-module="{{ class_basename($log->subject_type) }} (ID: {{ $log->subject_id }})"
                                                        data-date="{{ $log->created_at->format('M d, Y h:i A') }}"
                                                        data-changes="{{ json_encode($log->properties) }}"
                                                        title="View Changes">
                                                    <i class="bi bi-eye"></i> Changes
                                                </button>

                                                {{-- Undo button — only for 'updated' events on employee models --}}
                                                @if($log->event === 'updated' && !empty($log->properties['old']))
                                                <form method="POST" action="{{ route('audit-logs.undo', $log->id) }}" id="audit-undo-form-{{ $log->id }}">
                                                    @csrf
                                                    <button type="button"
                                                            class="btn-audit undo"
                                                            onclick="confirmAuditUndo('{{ $log->id }}')"
                                                            title="Revert this change">
                                                        <i class="bi bi-arrow-counterclockwise"></i> Undo
                                                    </button>
                                                </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" style="padding: 2rem 1.5rem; text-align: center; color: #6b7280;">
                                            <i class="bi bi-shield-check" style="font-size: 1.875rem; display: block; margin-bottom: 0.5rem; opacity: 0.5;"></i>
                                            No data changes found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{-- Pagination --}}
                    <div style="padding: 1rem 1.5rem; border-top: 1px solid #f3f4f6; background-color: white; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem;">
                        <div style="font-size: 0.75rem; color: #6b7280; font-weight: 500;">
                            Showing {{ $logs->firstItem() ?? 0 }}-{{ $logs->lastItem() ?? 0 }} of {{ number_format($logs->total()) }} entries
                        </div>
                        <div>
                            {{ $logs->appends(request()->except('spatie_page'))->links() }}
                        </div>
                    </div>
                </div>

                {{-- Custom Logs --}}
                <div class="tab-pane fade" id="custom-pane" role="tabpanel">
                    <div style="overflow-x: auto; width: 100%;">
                        <table style="width: 100%; min-width: 800px; border-collapse: collapse; text-align: left;">
                            <thead>
                                <tr style="border-top: 1px solid #f3f4f6; border-bottom: 1px solid #f3f4f6; background-color: #ffffff;">
                                    <th style="padding: 1rem 1.5rem; font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; color: #6b7280; letter-spacing: 0.05em;">User</th>
                                    <th style="padding: 1rem 1.5rem; font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; color: #6b7280; letter-spacing: 0.05em;">Action</th>
                                    <th style="padding: 1rem 1.5rem; font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; color: #6b7280; letter-spacing: 0.05em; width: 35%;">Description</th>
                                    <th style="padding: 1rem 1.5rem; font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; color: #6b7280; letter-spacing: 0.05em;">Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($customLogs as $log)
                                    @php
                                        $avatarStyles = [
                                            'background-color:#dbeafe; color:#2563eb;',
                                            'background-color:#f3e8ff; color:#9333ea;',
                                            'background-color:#dcfce7; color:#16a34a;',
                                            'background-color:#fef3c7; color:#d97706;',
                                            'background-color:#e0e7ff; color:#4f46e5;'
                                        ];
                                        $hash = strlen($log->user ? $log->user->name : 'System');
                                        $avatarClass = $avatarStyles[$hash % count($avatarStyles)];

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
                                                    <p style="margin: 0; font-size: 0.875rem; font-weight: 700; color: #111827; line-height: 1.25;">{{ $log->user ? $log->user->name : 'System / Unknown' }}</p>
                                                    <p style="margin: 0; font-size: 0.75rem; color: #9ca3af; margin-top: 0.125rem;">{{ $log->user ? $log->user->role_label ?? $log->user->role : 'System Action' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td style="padding: 1rem 1.5rem;">
                                            <span style="display: inline-flex; align-items: center; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.6875rem; font-weight: 700; letter-spacing: 0.025em; background-color:#fef3c7; color:#d97706;">
                                                {{ $log->action }}
                                            </span>
                                        </td>
                                        <td style="padding: 1rem 1.5rem; font-size: 0.8125rem; color: #4b5563; font-weight: 500; line-height: 1.4;">
                                            {{ $log->description }}
                                        </td>
                                        <td style="padding: 1rem 1.5rem; font-size: 0.8125rem; color: #6b7280; white-space: nowrap;">
                                            {{ $dateStr }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" style="padding: 2rem 1.5rem; text-align: center; color: #6b7280;">
                                            <i class="bi bi-clock-history" style="font-size: 1.875rem; display: block; margin-bottom: 0.5rem; opacity: 0.5;"></i>
                                            No manual action logs found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{-- Pagination --}}
                    <div style="padding: 1rem 1.5rem; border-top: 1px solid #f3f4f6; background-color: white; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem;">
                        <div style="font-size: 0.75rem; color: #6b7280; font-weight: 500;">
                            Showing {{ $customLogs->firstItem() ?? 0 }}-{{ $customLogs->lastItem() ?? 0 }} of {{ number_format($customLogs->total()) }} entries
                        </div>
                        <div>
                            {{ $customLogs->appends(request()->except('custom_page'))->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bottom Summary Cards --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-top: 0.5rem;">
            {{-- Total Audits --}}
            <div style="background-color: white; border-radius: 0.75rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; padding: 1.25rem;">
                <p style="font-size: 0.6875rem; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 0.75rem 0;">Total Events Logged</p>
                <h3 style="font-size: 1.875rem; font-weight: 700; color: #111827; margin: 0 0 0.5rem 0;">{{ number_format($totalActions) }}</h3>
                <p style="font-size: 0.6875rem; font-weight: 700; color: #16a34a; letter-spacing: 0.025em; margin: 0;">Comprehensive Tracking</p>
            </div>
            {{-- Created --}}
            <div style="background-color: white; border-radius: 0.75rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; padding: 1.25rem;">
                <p style="font-size: 0.6875rem; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 0.75rem 0;">Records Created</p>
                <h3 style="font-size: 1.875rem; font-weight: 700; color: #16a34a; margin: 0 0 1rem 0;">{{ number_format($createdCount) }}</h3>
                <div style="width: 100%; background-color: #f3f4f6; border-radius: 9999px; height: 0.25rem; overflow: hidden;">
                    <div style="background-color: #22c55e; height: 100%; border-radius: 9999px; width: {{ $totalActions > 0 ? ($createdCount/$totalActions)*100 : 0 }}%;"></div>
                </div>
            </div>
            {{-- Updated --}}
            <div style="background-color: white; border-radius: 0.75rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; padding: 1.25rem;">
                <p style="font-size: 0.6875rem; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 0.75rem 0;">Records Updated</p>
                <h3 style="font-size: 1.875rem; font-weight: 700; color: #2563eb; margin: 0 0 1rem 0;">{{ number_format($updatedCount) }}</h3>
                <div style="width: 100%; background-color: #f3f4f6; border-radius: 9999px; height: 0.25rem; overflow: hidden;">
                    <div style="background-color: #3b82f6; height: 100%; border-radius: 9999px; width: {{ $totalActions > 0 ? ($updatedCount/$totalActions)*100 : 0 }}%;"></div>
                </div>
            </div>
            {{-- Deleted --}}
            <div style="background-color: white; border-radius: 0.75rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; padding: 1.25rem;">
                <p style="font-size: 0.6875rem; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 0.75rem 0;">Records Deleted</p>
                <h3 style="font-size: 1.875rem; font-weight: 700; color: #dc2626; margin: 0 0 1rem 0;">{{ number_format($deletedCount) }}</h3>
                <div style="width: 100%; background-color: #f3f4f6; border-radius: 9999px; height: 0.25rem; overflow: hidden;">
                    <div style="background-color: #ef4444; height: 100%; border-radius: 9999px; width: {{ $totalActions > 0 ? ($deletedCount/$totalActions)*100 : 0 }}%;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Audit Log Details Modal -->
    <div class="modal fade" id="auditLogModal" tabindex="-1" aria-labelledby="auditLogModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-3 shadow" style="border-radius: 0.75rem !important;">
                <div class="modal-header bg-light border-bottom border-light p-4" style="border-radius: 0.75rem 0.75rem 0 0;">
                    <h5 class="modal-title fw-bold text-dark fs-5 d-flex align-items-center gap-2" id="auditLogModalLabel">
                        <i class="bi bi-file-diff text-primary"></i> Data Changes
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" style="gap: 1rem; display: flex; flex-direction: column;">
                    <div class="row border-bottom border-light pb-3">
                        <div class="col-4 text-secondary fw-semibold text-uppercase" style="font-size: 0.8125rem;">User</div>
                        <div class="col-8 text-dark fw-medium" style="font-size: 0.875rem;" id="modalAuditUser"></div>
                    </div>
                    <div class="row border-bottom border-light pb-3">
                        <div class="col-4 text-secondary fw-semibold text-uppercase" style="font-size: 0.8125rem;">Module / Record</div>
                        <div class="col-8 text-dark fw-medium" style="font-size: 0.875rem;" id="modalAuditModule"></div>
                    </div>
                    <div class="row border-bottom border-light pb-3">
                        <div class="col-4 text-secondary fw-semibold text-uppercase" style="font-size: 0.8125rem;">Date & Time</div>
                        <div class="col-8 text-secondary" style="font-size: 0.875rem;" id="modalAuditDate"></div>
                    </div>
                    <div>
                        <div class="text-secondary fw-semibold text-uppercase mb-2" style="font-size: 0.8125rem;">Detailed Changes</div>
                        <div class="bg-light p-3 rounded-3 border border-light text-dark" style="font-size: 0.8125rem; font-family: monospace; max-height: 300px; overflow-y: auto;" id="modalAuditChanges"></div>
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
            background-color: #1e3a8a !important; /* Premium Blue for Audit */
            border-color: #1e3a8a !important;
            color: #fff !important;
            z-index: 1 !important;
        }
        .pagination .page-item.disabled .page-link {
            color: #9ca3af !important;
            background-color: #f9fafb !important;
            cursor: not-allowed !important;
        }
        
        /* Formatter for JSON differences */
        .diff-container { display: flex; flex-direction: column; gap: 8px; }
        .diff-row { display: flex; flex-direction: column; padding: 10px 12px; background: white; border: 1px solid #e5e7eb; border-radius: 6px; gap: 6px; }
        .diff-key { font-weight: 700; color: #4b5563; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.025em; border-bottom: 1px dashed #e5e7eb; padding-bottom: 4px; word-break: break-word; }
        .diff-val-old { color: #dc2626; text-decoration: line-through; margin-right: 8px; word-break: break-all; }
        .diff-val-new { color: #16a34a; word-break: break-all; }

        /* Audit Table Buttons */
        .btn-audit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            text-decoration: none;
            transition: all .15s;
            gap: 4px;
            cursor: pointer;
        }

        .btn-audit.changes {
            background: #eff6ff;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }

        .btn-audit.changes:hover {
            background: #dbeafe;
            border-color: #93c5fd;
        }

        .btn-audit.undo {
            background: #fffbeb;
            color: #d97706;
            border: 1px solid #fde68a;
        }

        .btn-audit.undo:hover {
            background: #fef3c7;
            border-color: #fcd34d;
        }
    </style>

    <script>
        function confirmAuditUndo(id) {
            Swal.fire({
                title: 'Undo This Change?',
                text: 'This will revert the record to its state before this edit was made. The change will be logged.',
                icon: 'warning',
                iconColor: '#1e40af',
                showCancelButton: true,
                confirmButtonColor: '#1e40af',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, undo it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('audit-undo-form-' + id).submit();
                }
            });
        }

        function viewAuditLog(button) {
            const user = button.getAttribute('data-user');
            const module = button.getAttribute('data-module');
            const date = button.getAttribute('data-date');
            const changesRaw = button.getAttribute('data-changes');
            let changes = {};
            
            try {
                changes = JSON.parse(changesRaw);
            } catch(e) {
                console.error("Failed to parse changes JSON", e);
            }

            document.getElementById('modalAuditUser').textContent = user;
            document.getElementById('modalAuditModule').textContent = module;
            document.getElementById('modalAuditDate').textContent = date;

            const changesContainer = document.getElementById('modalAuditChanges');
            changesContainer.innerHTML = '';

            if (Object.keys(changes).length === 0) {
                changesContainer.innerHTML = '<span style="color: #9ca3af; font-style: italic;">No detailed properties recorded.</span>';
            } else {
                let html = '<div class="diff-container">';
                
                if (changes.attributes && changes.old) {
                    // Update: show before and after
                    for (const key in changes.attributes) {
                        if (key !== 'updated_at' && key !== 'deleted_at') {
                            const newValue = changes.attributes[key];
                            const oldValue = changes.old[key] !== undefined ? changes.old[key] : null;
                            const newStr = typeof newValue === 'object' ? JSON.stringify(newValue) : (newValue || 'null');
                            const oldStr = typeof oldValue === 'object' ? JSON.stringify(oldValue) : (oldValue || 'null');
                            
                            html += `
                            <div class="diff-row">
                                <div class="diff-key">${key}</div>
                                <div style="flex: 1;">
                                    <span class="diff-val-old">${oldStr}</span>
                                    <i class="bi bi-arrow-right mx-1 text-muted" style="font-size: 10px;"></i>
                                    <span class="diff-val-new">${newStr}</span>
                                </div>
                            </div>`;
                        }
                    }
                } else if (changes.attributes) {
                    // Create: show new attributes
                    for (const key in changes.attributes) {
                        if (key !== 'created_at' && key !== 'updated_at') {
                            const newValue = changes.attributes[key];
                            const newStr = typeof newValue === 'object' ? JSON.stringify(newValue) : (newValue || 'null');
                            html += `
                            <div class="diff-row">
                                <div class="diff-key">${key}</div>
                                <div style="flex: 1;"><span class="diff-val-new">${newStr}</span></div>
                            </div>`;
                        }
                    }
                } else if (changes.old) {
                    // Delete: show old attributes
                    for (const key in changes.old) {
                        if (key !== 'created_at' && key !== 'updated_at' && key !== 'deleted_at') {
                            const oldValue = changes.old[key];
                            const oldStr = typeof oldValue === 'object' ? JSON.stringify(oldValue) : (oldValue || 'null');
                            html += `
                            <div class="diff-row">
                                <div class="diff-key">${key}</div>
                                <div style="flex: 1;"><span class="diff-val-old" style="text-decoration:none;">${oldStr}</span></div>
                            </div>`;
                        }
                    }
                }
                
                html += '</div>';
                changesContainer.innerHTML = html || '<span style="color: #9ca3af; font-style: italic;">No relevant attribute changes to display.</span>';
            }

            const modalElement = document.getElementById('auditLogModal');
            let modalInstance = bootstrap.Modal.getInstance(modalElement);
            if (!modalInstance) {
                modalInstance = new bootstrap.Modal(modalElement);
            }
            modalInstance.show();
        }
    </script>
</x-dashboard-app>
