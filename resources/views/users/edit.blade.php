<x-dashboard-app>
<style>
.form-hero {
    background: linear-gradient(135deg, #082977ff 0%, #0f172a 100%);
    border-radius: 14px; padding: 24px 28px;
    position: relative; overflow: hidden; margin-bottom: 24px;
}
.form-hero::before {
    content: '';
    position: absolute; inset: 0;
    background-image: radial-gradient(circle, rgba(255,255,255,.07) 1px, transparent 1px);
    background-size: 20px 20px;
}
.form-hero-inner { position: relative; z-index: 1; display: flex; align-items: center; gap: 14px; }
.form-hero h1 { color: #fff; font-size: 20px; font-weight: 800; margin: 0; }
.form-hero p  { color: rgba(255,255,255,.65); font-size: 12px; margin: 4px 0 0; }

.form-layout {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 24px;
    align-items: start;
}
@media (max-width: 768px) {
    .form-layout { grid-template-columns: 1fr; }
}
.form-sidebar {
    background: #fff; border: 1px solid #e5e7eb;
    border-radius: 14px; padding: 28px 24px;
    box-shadow: 0 1px 6px rgba(0,0,0,.04);
    text-align: center;
}
.form-card {
    background: #fff; border: 1px solid #e5e7eb;
    border-radius: 14px; padding: 32px;
    box-shadow: 0 1px 6px rgba(0,0,0,.05);
}
.form-fields-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0 24px;
}
.form-fields-grid .full-width { grid-column: 1 / -1; }
.form-group { margin-bottom: 20px; }
.form-label {
    display: block; font-size: 11px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .6px;
    color: #6b7280; margin-bottom: 7px;
}
.form-input, .form-select {
    width: 100%; padding: 10px 14px;
    border: 1px solid #e5e7eb; border-radius: 9px;
    font-size: 13px; color: #1f2937; outline: none;
    transition: border .15s, box-shadow .15s;
    background: #fafafa;
    box-sizing: border-box;
}
.form-input:focus, .form-select:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59,130,246,.12);
    background: #fff;
}
.form-error { font-size: 11px; color: #dc2626; margin-top: 5px; font-weight: 600; }
.form-hint  { font-size: 11px; color: #9ca3af; margin-top: 5px; }

.btn-save {
    display: inline-flex; align-items: center; gap: 7px;
    background: linear-gradient(135deg,#1e3a5f,#1a5276);
    color: #fff; padding: 11px 24px; border-radius: 10px;
    font-size: 13px; font-weight: 700; border: none; cursor: pointer;
    transition: opacity .15s, transform .15s;
    box-shadow: 0 4px 14px rgba(30,58,95,.3);
}
.btn-save:hover { opacity: .88; transform: translateY(-1px); }
.btn-cancel {
    display: inline-flex; align-items: center; gap: 7px;
    background: #f8fafc; color: #475569; padding: 11px 22px;
    border-radius: 10px; font-size: 13px; font-weight: 600;
    border: 1px solid #e2e8f0; text-decoration: none;
    transition: background .15s;
}
.btn-cancel:hover { background: #f1f5f9; color: #374151; }

.role-option-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; }
.role-option { display: none; }
.role-option-label {
    display: flex; flex-direction: column; align-items: center;
    gap: 6px; padding: 14px 10px;
    border: 2px solid #e5e7eb; border-radius: 12px;
    cursor: pointer; text-align: center; transition: all .15s;
    background: #fafafa;
}
.role-option-label:hover { border-color: #94a3b8; background: #f8fafc; }
.role-option:checked + .role-option-label {
    border-color: #0f172a; background: #fffbeb;
    box-shadow: 0 0 0 3px rgba(180,83,9,.1);
}
.role-hint { font-size: 11px; color: #9ca3af; margin-bottom: 10px; }
.role-icon { font-size: 22px; }
.role-name  { font-size: 12px; font-weight: 700; color: #0f172a; }
.role-desc  { font-size: 10px; color: #9ca3af; line-height: 1.4; }

.user-avatar-xl {
    width: 88px; height: 88px; border-radius: 50%;
    background: linear-gradient(135deg,#1e3a5f,#1a5276);
    display: flex; align-items: center; justify-content: center;
    font-size: 34px; font-weight: 800; color: #fff;
    margin: 0 auto 14px;
    box-shadow: 0 4px 16px rgba(30,58,95,.25);
}
</style>

{{-- Hero --}}
<div class="form-hero">
    <div class="form-hero-inner">
        <div style="width:42px;height:42px;border-radius:12px;
                    background:rgba(255,255,255,.18);flex-shrink:0;
                    display:flex;align-items:center;justify-content:center;
                    font-size:20px;color:#fff;">
            <i class="bi bi-person-gear"></i>
        </div>
        <div>
            <h1>Edit User</h1>
            <p>Update account details and role assignment</p>
        </div>
    </div>
</div>

{{-- Expanded Two-Column Layout --}}
<div class="form-layout">

    {{-- Sidebar: User Preview --}}
    <div class="form-sidebar">
        <div class="user-avatar-xl">{{ strtoupper(substr($user->name,0,1)) }}</div>
        <div style="font-weight:800;color:#0f172a;font-size:16px;margin-bottom:4px;">{{ $user->name }}</div>
        <div style="font-size:12px;color:#6b7280;margin-bottom:16px;">{{ $user->email }}</div>

        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px;text-align:left;">
            <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#94a3b8;margin-bottom:10px;">Account Info</div>
            <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:8px;">
                <span style="color:#6b7280;">User ID</span>
                <span style="font-weight:700;color:#0f172a;">#{{ $user->id }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:8px;">
                <span style="color:#6b7280;">Joined</span>
                <span style="font-weight:700;color:#0f172a;">{{ $user->created_at?->format('M d, Y') }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:12px;">
                <span style="color:#6b7280;">Status</span>
                @if($user->is_approved)
                    <span style="font-weight:700;color:#10b981;">✓ Active</span>
                @else
                    <span style="font-weight:700;color:#f59e0b;">⏳ Pending</span>
                @endif
            </div>
        </div>

        <div style="margin-top:16px;padding-top:16px;border-top:1px solid #f1f5f9;">
            <a href="{{ route('users.index') }}" style="display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:600;color:#64748b;text-decoration:none;">
                <i class="bi bi-arrow-left"></i> Back to Users
            </a>
        </div>
    </div>

    {{-- Main form card --}}
    <div class="form-card">
        <div style="margin-bottom:24px;">
            <div style="font-size:17px;font-weight:800;color:#0f172a;">Edit Account Details</div>
            <div style="font-size:12px;color:#6b7280;margin-top:2px;">Update name, email, password, and role assignment</div>
        </div>

        <form method="POST" action="{{ route('users.update', $user) }}">
            @csrf @method('PUT')

            <div class="form-fields-grid">

                {{-- Name --}}
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                           class="form-input" required>
                    @error('name')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                {{-- Email --}}
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                           class="form-input" required>
                    @error('email')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                {{-- Password --}}
                <div class="form-group">
                    <label class="form-label">New Password <span style="color:#9ca3af;font-weight:400;text-transform:none;">(leave blank to keep current)</span></label>
                    <input type="password" name="password" class="form-input"
                           placeholder="At least 8 characters">
                    @error('password')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                {{-- Confirm Password --}}
                <div class="form-group">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="password_confirmation" class="form-input"
                           placeholder="Re-enter new password">
                </div>

                {{-- Role --}}
                <div class="form-group full-width">
                    <label class="form-label">Role Assignment</label>
                    <div class="role-hint">Select one or more roles. Access is the union of all selected roles' permissions.</div>
                    @php $currentRoleNames = old('roles', $user->roles->pluck('name')->all()); @endphp
                    <div class="role-option-grid" style="grid-template-columns:1fr 1fr 1fr 1fr;">
                        @foreach($roles as $r)
                        <div>
                            <input type="checkbox" name="roles[]" id="role_{{ $r->id }}" value="{{ $r->name }}"
                                   class="role-option" {{ in_array($r->name, $currentRoleNames, true) ? 'checked' : '' }}>
                            <label for="role_{{ $r->id }}" class="role-option-label">
                                <span class="role-icon">👤</span>
                                <span class="role-name">{{ $r->name }}</span>
                                <span class="role-desc">Custom access based on Role-Permission Matrix.</span>
                            </label>
                        </div>
                        @endforeach
                    </div>
                    @error('roles')<div class="form-error mt-1">{{ $message }}</div>@enderror
                    @error('roles.*')<div class="form-error mt-1">{{ $message }}</div>@enderror
                </div>

            </div>{{-- end grid --}}

            {{-- Actions --}}
            <div style="display:flex;gap:12px;margin-top:28px;padding-top:24px;border-top:1px solid #f1f5f9;">
                <button type="submit" class="btn-save">
                    <i class="bi bi-check-lg"></i> Save Changes
                </button>
                <a href="{{ route('users.index') }}" class="btn-cancel">
                    <i class="bi bi-x-lg"></i> Cancel
                </a>
            </div>
        </form>
    </div>

</div>{{-- end .form-layout --}}
</x-dashboard-app>
