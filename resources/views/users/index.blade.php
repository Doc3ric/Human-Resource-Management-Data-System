<x-dashboard-app>
    <style>
        .users-hero {
            background: linear-gradient(135deg, #1e3a5f 0%, #1a5276 55%, #0f4c75 100%);
            border-radius: 14px;
            padding: 28px 32px;
            position: relative;
            overflow: hidden;
            margin-bottom: 24px;
        }

        .users-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle, rgba(255, 255, 255, .08) 1px, transparent 1px);
            background-size: 22px 22px;
        }

        .users-hero-inner {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .users-hero h1 {
            color: #fff;
            font-size: 24px;
            font-weight: 800;
            margin: 0;
        }

        .users-hero p {
            color: rgba(255, 255, 255, .65);
            font-size: 13px;
            margin: 5px 0 0;
        }

        .card-panel {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .05);
        }

        .users-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .users-table thead tr {
            background: linear-gradient(135deg, #1e3a5f 0%, #1a5276 100%);
        }

        .users-table th {
            text-align: left;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: rgba(255, 255, 255, .75);
            padding: 11px 16px;
            white-space: nowrap;
            border-bottom: 2px solid rgba(255, 255, 255, .12);
        }

        .users-table tbody tr:nth-child(even) td {
            background: #f8fafc;
        }

        .users-table tbody tr:hover td {
            background: #eff6ff !important;
        }

        .users-table td {
            padding: 12px 16px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            font-size: 13px;
            color: #374151;
        }

        .users-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .role-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 99px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }

        .role-super_admin {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }

        .role-salary_admin {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }

        .role-inventory_admin {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .btn-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: auto;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            transition: all .2s;
            border: none;
            gap: 4px;
            cursor: pointer;
        }

        .btn-icon.approve {
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff;
            box-shadow: 0 2px 6px rgba(16, 185, 129, 0.3);
        }

        .btn-icon.approve:hover {
            background: linear-gradient(135deg, #059669, #047857);
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.4);
        }

        .btn-icon.edit {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #fff;
            box-shadow: 0 2px 6px rgba(217, 119, 6, 0.3);
        }

        .btn-icon.edit:hover {
            background: linear-gradient(135deg, #d97706, #b45309);
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(217, 119, 6, 0.4);
        }

        .btn-icon.del {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #fff;
            box-shadow: 0 2px 6px rgba(220, 38, 38, 0.3);
        }

        .btn-icon.del:hover {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(220, 38, 38, 0.4);
        }

        .alert-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 16px;
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 16px;
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
    </style>

    {{-- Hero --}}
    <div class="users-hero">
        <div class="users-hero-inner">
            <div>
                <h1><i class="bi bi-people-fill me-2"></i>User Management</h1>
                <p>Manage system user accounts and role assignments</p>
            </div>
            <a href="{{ route('users.create') }}" style="display:inline-flex;align-items:center;gap:7px;
                  background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.3);
                  color:#fff;padding:10px 20px;border-radius:10px;
                  font-size:13px;font-weight:600;text-decoration:none;
                  transition:background .2s;">
                <i class="bi bi-person-plus-fill"></i> New User
            </a>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert-success"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert-error"><i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}</div>
    @endif

    {{-- Users table --}}
    <div class="card-panel">
        <div class="overflow-x-auto">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th style="text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td style="color:#9ca3af;font-size:12px;">{{ $user->id }}</td>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    @if($user->profile_picture)
                                        <img src="{{ asset('storage/' . $user->profile_picture) }}" alt="Profile Picture"
                                            style="width:34px;height:34px;border-radius:50%;object-fit:cover;flex-shrink:0;box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                    @else
                                        <div style="width:34px;height:34px;border-radius:50%;
                                                    background:linear-gradient(135deg,#3b82f6,#2563eb);
                                                    display:flex;align-items:center;justify-content:center;
                                                    font-size:13px;font-weight:800;color:#fff;flex-shrink:0;">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div style="font-weight:700;color:#0f172a;font-size:13px;">{{ $user->name }}</div>
                                        @if($user->id === auth()->id())
                                            <div style="font-size:10px;color:#22c55e;font-weight:700;">YOU</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td style="color:#6b7280;">{{ $user->email }}</td>
                            <td>
                                <span class="role-badge role-{{ $user->role }}">
                                    @if($user->role === 'super_admin')
                                        <i class="bi bi-shield-fill-check"></i> Super Admin
                                    @elseif($user->role === 'salary_admin')
                                        <i class="bi bi-cash-stack"></i> Salary Admin
                                    @else
                                        <i class="bi bi-archive-fill"></i> Inventory Admin
                                    @endif
                                </span>
                            </td>
                            <td>
                                @if($user->is_approved)
                                    <span style="color:#10b981;font-weight:700;font-size:12px;"><i
                                            class="bi bi-check-circle-fill"></i> Active</span>
                                @else
                                    <span style="color:#f59e0b;font-weight:700;font-size:12px;"><i class="bi bi-clock-fill"></i>
                                        Pending</span>
                                @endif
                            </td>
                            <td style="color:#9ca3af;font-size:12px;">{{ $user->created_at?->format('M d, Y') }}</td>
                            <td style="text-align:center;">
                                <div style="display:flex;gap:6px;justify-content:center;align-items:center;">
                                    @if(!$user->is_approved)
                                        @if(auth()->user()->isSuperAdmin())
                                            <form method="POST" action="{{ route('users.approve', $user) }}"
                                                style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn-icon approve" title="Approve User">
                                                    <i class="bi bi-check-lg"></i> APPROVE
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('users.reject', $user) }}" style="display:inline;"
                                                id="form-reject-user-{{ $user->id }}">
                                                @csrf @method('DELETE')
                                                <button type="button"
                                                    onclick="confirmRejectUser('{{ $user->id }}', '{{ addslashes($user->name) }}')"
                                                    class="btn-icon del" title="Reject User">
                                                    <i class="bi bi-x-lg"></i> REJECT
                                                </button>
                                            </form>
                                        @else
                                            <span style="font-size:11px;color:#9ca3af;font-style:italic;font-weight:600;">Pending
                                                Approval</span>
                                        @endif
                                    @else
                                        <a href="{{ route('users.edit', $user) }}" class="btn-icon edit" title="Edit user">
                                            <i class="bi bi-pencil"></i> EDIT
                                        </a>
                                        @if($user->id !== auth()->id())
                                            <form method="POST" action="{{ route('users.destroy', $user) }}"
                                                id="form-delete-user-{{ $user->id }}" style="display:inline;">
                                                @csrf @method('DELETE')
                                                <button type="button"
                                                    onclick="confirmDeleteUser('{{ $user->id }}', '{{ addslashes($user->name) }}')"
                                                    class="btn-icon del" title="Delete user">
                                                    <i class="bi bi-trash"></i> DELETE
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center;padding:48px;color:#9ca3af;font-style:italic;">
                                No users found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div style="padding:16px 20px;border-top:1px solid #f1f5f9;">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDeleteUser(id, name) {
            Swal.fire({
                title: 'Delete user ' + name + '?',
                text: "This action cannot be undone.",
                icon: 'warning',
                iconColor: '#dc3545',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete user!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('form-delete-user-' + id).submit();
                }
            });
        }

        function confirmRejectUser(id, name) {
            Swal.fire({
                title: 'Reject user ' + name + '?',
                text: "This user will be permanently deleted and denied access.",
                icon: 'warning',
                iconColor: '#f59e0b',
                showCancelButton: true,
                confirmButtonColor: '#f59e0b',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, reject user!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('form-reject-user-' + id).submit();
                }
            });
        }
    </script>

</x-dashboard-app>