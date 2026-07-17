<x-dashboard-app>
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
.create-user-page * { font-family: 'Inter', system-ui, sans-serif; box-sizing: border-box; }

.create-user-page {
    max-width: 900px;
    margin: 0 auto;
    padding: 20px 0 60px 0;
    color: #111827;
}

/* Page Header */
.page-title { font-size: 26px; font-weight: 800; color: #0f172a; margin: 0 0 8px 0; letter-spacing: -0.02em; }
.page-subtitle { font-size: 14px; color: #64748b; margin: 0 0 32px 0; }

/* Section Cards */
.section-card {
    background: #ffffff;
    border: 1px solid #f1f5f9;
    border-radius: 12px;
    padding: 28px 32px;
    margin-bottom: 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02), 0 4px 12px rgba(0,0,0,0.02);
}

.section-header {
    display: flex; align-items: center; gap: 10px;
    margin-bottom: 24px;
}
.section-icon {
    color: #2563eb; font-size: 16px;
}
.section-title {
    font-size: 15px; font-weight: 700; color: #0f172a; margin: 0;
}

/* Form Grid */
.form-grid {
    display: grid; grid-template-columns: 1fr 1fr; gap: 24px;
}
@media(max-width: 640px) { .form-grid { grid-template-columns: 1fr; } }

/* Inputs */
.cu-label {
    display: block; font-size: 12px; font-weight: 600; color: #334155; margin-bottom: 8px;
}
.cu-input {
    width: 100%; padding: 12px 16px; 
    background: #ffffff; border: 1px solid #e2e8f0;
    border-radius: 8px; font-size: 14px; color: #0f172a; 
    outline: none; transition: 0.2s;
}
.cu-input::placeholder { color: #94a3b8; }
.cu-input:focus {
    border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
}
.cu-error { color: #ef4444; font-size: 12px; margin-top: 6px; font-weight: 500; }
.cu-hint { font-size: 11px; color: #94a3b8; margin-top: 8px; font-style: italic; }

/* Role Cards Grid */
.role-grid {
    display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;
}
@media(max-width: 640px) { .role-grid { grid-template-columns: 1fr; } }

/* Custom Radio Cards */
.role-option { display: none; }
.role-card {
    background: #ffffff; border: 1px solid #f1f5f9; border-radius: 10px;
    padding: 24px 16px; text-align: center; cursor: pointer;
    transition: all 0.2s ease; position: relative; height: 100%;
    display: flex; flex-direction: column; align-items: center; justify-content: flex-start;
}
.role-card:hover { border-color: #cbd5e1; background: #f8fafc; }

/* Selected State */
.role-option:checked + .role-card {
    border: 2px solid #3b82f6; background: #f0f9ff; box-shadow: 0 4px 14px rgba(59,130,246,0.1); padding: 23px 15px; /* adjust padding for 2px border */
}

/* Selected Checkmark Icon */
.role-check-indicator {
    position: absolute; top: 12px; right: 12px;
    color: #3b82f6; font-size: 14px; opacity: 0; transform: scale(0.5); transition: 0.2s;
}
.role-option:checked + .role-card .role-check-indicator {
    opacity: 1; transform: scale(1);
}

.role-icon-wrapper {
    width: 44px; height: 44px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 16px auto; font-size: 20px;
}
.role-title { font-size: 14px; font-weight: 700; color: #0f172a; margin: 0 0 8px 0; }
.role-desc { font-size: 11px; color: #64748b; line-height: 1.5; margin: 0; }

/* Colors for specific roles based on screenshot */
.role-super .role-icon-wrapper { background: #eff6ff; color: #3b82f6; }
.role-salary .role-icon-wrapper { background: #f0fdf4; color: #22c55e; }
.role-inventory .role-icon-wrapper { background: #fff7ed; color: #f97316; }
.role-viewer .role-icon-wrapper { background: #f5f3ff; color: #7c3aed; }

/* Bottom Actions container */
.bottom-actions {
    display: flex; justify-content: space-between; align-items: center;
    margin-top: 32px; padding-top: 24px; border-top: 1px solid #e2e8f0;
}

/* Buttons */
.btn-primary {
    background: #2563eb; color: #ffffff; border: none; padding: 12px 32px;
    border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; transition: 0.2s;
}
.btn-primary:hover { background: #1d4ed8; }
.btn-secondary {
    background: #ffffff; color: #334155; border: 1px solid #cbd5e1; padding: 12px 32px;
    border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; transition: 0.2s;
    text-decoration: none; display: inline-block;
}
.btn-secondary:hover { background: #f8fafc; color: #0f172a; }
.btn-clear {
    background: none; border: none; color: #64748b; font-size: 13px; font-weight: 600;
    cursor: pointer; display: flex; align-items: center; gap: 8px; padding: 8px 0;
}
.btn-clear:hover { color: #0f172a; }

</style>

<div class="create-user-page">
    
    <h1 class="page-title">Create New User</h1>
    <p class="page-subtitle">Fill in the details below to add a new administrator to the system.</p>

    <form method="POST" action="{{ route('users.store') }}" id="createUserForm">
        @csrf

        {{-- Section 1: Personal Information --}}
        <div class="section-card">
            <div class="section-header">
                <i class="bi bi-person-fill section-icon"></i>
                <h2 class="section-title">Personal Information</h2>
            </div>
            
            <div class="form-grid">
                <div>
                    <label class="cu-label" for="name">Full Name</label>
                    <input type="text" id="name" name="name" class="cu-input" value="{{ old('name') }}" placeholder="e.g. John Doe" required autocomplete="off">
                    @error('name')<div class="cu-error">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="cu-label" for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="cu-input" value="{{ old('email') }}" placeholder="e.g. john@admincore.com" required autocomplete="off">
                    @error('email')<div class="cu-error">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        {{-- Section 2: Account Security --}}
        <div class="section-card">
            <div class="section-header">
                <i class="bi bi-shield-check section-icon"></i>
                <h2 class="section-title">Account Security</h2>
            </div>
            
            <div class="form-grid" style="margin-bottom: 8px;">
                <div>
                    <label class="cu-label" for="password">Password</label>
                    <div style="position:relative;">
                        <input type="password" id="password" name="password" class="cu-input" placeholder="••••••••" required autocomplete="new-password" style="padding-right:44px;">
                        <button type="button" onclick="togglePwd('password','eyeIcon1')" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#94a3b8;padding:0;line-height:1;" title="Show/Hide Password">
                            <i id="eyeIcon1" class="bi bi-eye" style="font-size:18px;"></i>
                        </button>
                    </div>
                    @error('password')<div class="cu-error">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="cu-label" for="password_confirmation">Confirm Password</label>
                    <div style="position:relative;">
                        <input type="password" id="password_confirmation" name="password_confirmation" class="cu-input" placeholder="••••••••" required autocomplete="new-password" style="padding-right:44px;">
                        <button type="button" onclick="togglePwd('password_confirmation','eyeIcon2')" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#94a3b8;padding:0;line-height:1;" title="Show/Hide Password">
                            <i id="eyeIcon2" class="bi bi-eye" style="font-size:18px;"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="cu-hint">Password should be at least 8 characters long with a mix of letters and numbers.</div>
        </div>

        {{-- Section 3: Assign User Role --}}
        <div class="section-card">
            <div class="section-header">
                <i class="bi bi-people-fill section-icon"></i>
                <h2 class="section-title">Assign User Role</h2>
            </div>
            
            <p class="cu-hint" style="margin-top:0;margin-bottom:16px;">Select one or more roles. Access is the union of all selected roles' permissions.</p>
            @php $selectedRoleNames = old('roles', ['System & Administration']); @endphp
            <div class="role-grid">
                @foreach($roles as $r)
                <div class="role-generic">
                    <input type="checkbox" name="roles[]" id="role_{{ $r->id }}" value="{{ $r->name }}" class="role-option" {{ in_array($r->name, $selectedRoleNames, true) ? 'checked' : '' }}>
                    <label for="role_{{ $r->id }}" class="role-card">
                        <i class="bi bi-check-circle-fill role-check-indicator"></i>
                        <div class="role-icon-wrapper" style="background:#eff6ff; color:#3b82f6;">
                            <i class="bi bi-person-badge"></i>
                        </div>
                        <h3 class="role-title">{{ $r->name }}</h3>
                        <p class="role-desc">Custom access based on Role-Permission Matrix.</p>
                    </label>
                </div>
                @endforeach

            </div>
            @error('roles')<div class="cu-error" style="margin-top: 12px; text-align: center;">{{ $message }}</div>@enderror
            @error('roles.*')<div class="cu-error" style="margin-top: 12px; text-align: center;">{{ $message }}</div>@enderror
        </div>

        {{-- Bottom Actions --}}
        <div class="bottom-actions">
            <button type="button" class="btn-clear" onclick="document.getElementById('createUserForm').reset()">
                <i class="bi bi-trash"></i> Clear Form
            </button>
            
            <div style="display: flex; gap: 16px;">
                <a href="{{ route('users.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Create User</button>
            </div>
        </div>

    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Show/Hide password toggle
function togglePwd(inputId, iconId) {
    const inp = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (inp.type === 'password') {
        inp.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        inp.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}
</script>

</x-dashboard-app>

