<x-dashboard-app>
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
.profile-page * { font-family: 'Inter', system-ui, sans-serif; }

/* Global overrides for this view */
.profile-page {
    max-width: 900px;
    margin: 0 auto;
    padding: 20px 0;
    color: #111827;
}

/* Tabs */
.p-tab {
    background: none; border: none; font-size: 14px; font-weight: 600; 
    color: #4b5563; padding: 12px 0; margin-right: 32px;
    border-bottom: 2px solid transparent; cursor: pointer; transition: all 0.2s;
    display: inline-flex; align-items: center; gap: 8px;
}
.p-tab:hover { color: #111827; }
.p-tab.active { color: #2563eb; border-bottom-color: #2563eb; }

.p-panel { display: none; }
.p-panel.active { display: block; animation: fadeIn 0.3s ease-out; }

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(4px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Inputs */
.p-label {
    display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px;
}
.p-input {
    width: 100%; padding: 10px 14px; background: #f9fafb; border: 1px solid #e5e7eb;
    border-radius: 8px; font-size: 14px; color: #111827; outline: none; transition: 0.2s;
}
.p-input:focus {
    background: #ffffff; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
}
.p-input:disabled { color: #6b7280; cursor: not-allowed; }

/* Photo Upload Box */
.p-upload-box {
    border: 2px dashed #d1d5db; border-radius: 12px; padding: 30px 20px;
    text-align: center; cursor: pointer; transition: 0.2s; background: #f9fafb;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    position: relative; overflow: hidden; height: 160px;
}
.p-upload-box:hover { border-color: #93c5fd; background: #eff6ff; }
.p-upload-box input[type="file"] {
    position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;
}

/* Primary Button */
.btn-primary {
    background: #2563eb; color: #ffffff; border: none; padding: 10px 24px;
    border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; transition: 0.2s;
    display: inline-flex; align-items: center; justify-content: center;
}
.btn-primary:hover { background: #1d4ed8; }
.btn-secondary {
    background: #f3f4f6; color: #4b5563; border: none; padding: 10px 24px;
    border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; transition: 0.2s;
}
.btn-secondary:hover { background: #e5e7eb; color: #111827; }

/* Alert Box */
.info-alert {
    background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px; padding: 20px;
    display: flex; gap: 12px; align-items: flex-start; margin-top: 24px;
}
</style>

<div class="profile-page">

    {{-- ── SUCCESS BANNERS / MODALS ────────────────────────────────── --}}
    @if(session('status') === 'profile-updated')
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Profile information updated successfully.',
                        confirmButtonColor: '#2563eb',
                        timer: 3000
                    });
                }
            });
        </script>
    @endif
    @if(session('status') === 'password-updated')
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Password changed successfully.',
                        confirmButtonColor: '#2563eb',
                        timer: 3000
                    });
                }
            });
        </script>
    @endif

    {{-- HEADER AVATAR REGION --}}
    <div style="display: flex; align-items: center; gap: 24px; margin-bottom: 32px;">
        <div style="position: relative;">
            <div style="width: 100px; height: 100px; border-radius: 50%; background: #e5e7eb; border: 4px solid #ffffff; box-shadow: 0 4px 12px rgba(0,0,0,0.08); overflow: hidden; display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: 700; color: #6b7280;">
                <img id="avatar-preview-header" src="{{ auth()->user()->profile_picture ? Storage::url(auth()->user()->profile_picture) : '' }}" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover; {{ auth()->user()->profile_picture ? '' : 'display: none;' }}">
                <span id="avatar-initials-header" style="{{ auth()->user()->profile_picture ? 'display: none;' : '' }}">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </span>
            </div>
            <div style="position: absolute; bottom: 0; right: -4px; width: 32px; height: 32px; background: #2563eb; color: #fff; border-radius: 50%; border: 3px solid #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 5px rgba(0,0,0,0.1);" onclick="document.getElementById('profile_picture').click()">
                <i class="bi bi-camera-fill" style="font-size: 14px;"></i>
            </div>
        </div>
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <h1 style="font-size: 28px; font-weight: 800; color: #111827; margin: 0; line-height: 1;">{{ auth()->user()->name }}</h1>
                @php
                    $roleLabel = ucwords(str_replace('_', ' ', auth()->user()->role));
                @endphp
                <span style="background: #e0e7ff; color: #2563eb; font-size: 10px; font-weight: 800; padding: 4px 10px; border-radius: 99px; text-transform: uppercase; letter-spacing: 0.05em;">{{ $roleLabel }}</span>
            </div>
            <div style="display: flex; align-items: center; gap: 6px; color: #6b7280; font-size: 14px; font-weight: 500;">
                <i class="bi bi-envelope"></i> {{ auth()->user()->email }}
            </div>
        </div>
    </div>

    {{-- TABS --}}
    <div style="border-bottom: 1px solid #e5e7eb; margin-bottom: 32px; display: flex; gap: 16px;">
        <button class="p-tab active" onclick="switchTab('info')" id="tab-info">
            <i class="bi bi-person-fill" style="margin-right: 4px;"></i> Profile Information
        </button>
        <button class="p-tab" onclick="switchTab('password')" id="tab-password">
            <i class="bi bi-key-fill" style="margin-right: 4px;"></i> Change Password
        </button>
        <button class="p-tab" onclick="switchTab('security')" id="tab-security">
            <i class="bi bi-shield-fill-check" style="margin-right: 4px;"></i> Security & 2FA
        </button>
    </div>

    {{-- TAB PANEL 1: PROFILE INFO --}}
    <div id="panel-info" class="p-panel active">
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" id="profile-form">
            @csrf
            @method('PATCH')

            <div style="display: flex; flex-wrap: wrap; gap: 32px;">
                
                {{-- Left Col: Personal Details --}}
                <div style="flex: 1; min-width: 300px;">
                    <div style="background: #ffffff; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.02); border: 1px solid #f3f4f6;">
                        <h3 style="font-size: 18px; font-weight: 800; color: #111827; margin: 0 0 24px 0;">Personal Details</h3>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
                            {{-- Full Name --}}
                            <div style="grid-column: span 1;">
                                <label class="p-label" for="name">Full Name</label>
                                <input type="text" id="name" name="name" class="p-input" value="{{ old('name', auth()->user()->name) }}" required>
                                @foreach($errors->get('name') as $msg)
                                    <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $msg }}</div>
                                @endforeach
                            </div>
                            
                            {{-- Email Address --}}
                            <div style="grid-column: span 1;">
                                <label class="p-label" for="email">Email Address</label>
                                <input type="email" id="email" name="email" class="p-input" value="{{ old('email', auth()->user()->email) }}" required>
                                @foreach($errors->get('email') as $msg)
                                    <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $msg }}</div>
                                @endforeach
                            </div>

                            {{-- Department (Role) --}}
                            <div style="grid-column: span 1;">
                                <label class="p-label">Department</label>
                                <select class="p-input" style="color: #111827; appearance: none; background: #f9fafb url('data:image/svg+xml;utf8,<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"12\" height=\"12\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"%236b7280\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><polyline points=\"6 9 12 15 18 9\"></polyline></svg>') no-repeat right 14px center;">
                                    <option selected>{{ $roleLabel }}</option>
                                </select>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- Right Col: Photo & Info --}}
                <div style="flex: 0 0 340px;">
                    
                    {{-- Update Photo Card --}}
                    <div style="background: #ffffff; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.02); border: 1px solid #f3f4f6; text-align: center;">
                        <h3 style="font-size: 18px; font-weight: 800; color: #111827; margin: 0 0 24px 0; text-align: left;">Update Photo</h3>
                        
                        <div class="p-upload-box" id="upload-box" style="margin-bottom: 16px;">
                            <div style="width: 48px; height: 48px; background: #e0e7ff; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-bottom: 12px;">
                                <i class="bi bi-file-earmark-arrow-up-fill" style="font-size: 24px; color: #6366f1;"></i>
                            </div>
                        </div>
                        <div style="font-size: 11px; color: #6b7280; font-weight: 500; margin-bottom: 24px; text-align: center;">Allowed JPG, GIF or PNG. Max size of 2MB</div>
                        
                        <input type="file" name="profile_picture" id="profile_picture" accept="image/*" style="display: none;" onchange="previewAvatar(this)">

                        @foreach($errors->get('profile_picture') as $msg)
                            <div style="color: #ef4444; font-size: 12px; margin-bottom: 16px;">{{ $msg }}</div>
                        @endforeach

                        <button type="button" class="btn-primary" style="width: 100%; margin-bottom: 8px;" onclick="document.getElementById('profile_picture').click()">
                            Upload New
                        </button>
                        <button type="button" style="background: none; border: none; color: #6b7280; font-size: 13px; font-weight: 500; cursor: pointer;" onclick="removeAvatar()">
                            Reset Avatar Edit
                        </button>
                    </div>

                    {{-- Info Alert --}}
                    <div class="info-alert">
                        <i class="bi bi-info-circle-fill" style="color: #2563eb; font-size: 20px; margin-top: 2px;"></i>
                        <div>
                            <div style="font-size: 14px; font-weight: 800; color: #111827; margin-bottom: 6px;">Account Information</div>
                            <div style="font-size: 13px; color: #4b5563; line-height: 1.6;">
                                Your profile is visible to other administrators in the "Team" section. Ensure your details are up to date for better collaboration.
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Bottom Action Bar --}}
            <div style="margin-top: 48px; padding-top: 24px; border-top: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: space-between;">
                {{-- Delete Button triggers modal --}}
                <button type="button" onclick="confirmDelete()" style="background: none; border: none; color: #2563eb; font-size: 14px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; padding: 8px 0;">
                    <i class="bi bi-trash3-fill"></i> Delete Account
                </button>

                <div style="display: flex; gap: 16px; align-items: center;">
                    <button type="button" style="background: none; border: none; color: #111827; font-size: 14px; font-weight: 600; cursor: pointer; padding: 10px 16px;" onclick="window.location.reload()">Discard</button>
                    <button type="submit" class="btn-primary" style="padding: 12px 32px; border-radius: 8px;">Save Changes</button>
                </div>
            </div>

        </form>
    </div>

    {{-- TAB PANEL 2: CHANGE PASSWORD --}}
    <div id="panel-password" class="p-panel">
        <form method="POST" action="{{ route('password.update') }}" id="password-form">
            @csrf
            @method('PUT')
            
            <div style="background: #ffffff; border-radius: 12px; padding: 32px; box-shadow: 0 1px 3px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.02); border: 1px solid #f3f4f6; max-width: 650px; margin: 0 auto;">
                <h3 style="font-size: 20px; font-weight: 800; color: #111827; margin: 0 0 8px 0;">Change Password</h3>
                <p style="font-size: 14px; color: #6b7280; margin: 0 0 32px 0;">Ensure your account is using a long, random password to stay secure.</p>
                
                <div style="display: flex; flex-direction: column; gap: 20px;">
                    <div>
                        <label class="p-label" for="current_password">Current Password</label>
                        <div style="position: relative;">
                            <input type="password" id="current_password" name="current_password" class="p-input" style="padding-right: 60px;" required autocomplete="current-password">
                            <button type="button" onclick="togglePassword('current_password', this)" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; font-size: 11px; font-weight: 800; color: #6b7280; cursor: pointer;">SHOW</button>
                        </div>
                        @foreach($errors->updatePassword->get('current_password') as $msg)
                            <div style="color: #ef4444; font-size: 12px; margin-top: 6px;">{{ $msg }}</div>
                        @endforeach
                    </div>
                    <div>
                        <label class="p-label" for="new_password">New Password</label>
                        <div style="position: relative;">
                            <input type="password" id="new_password" name="password" class="p-input" style="padding-right: 60px;" required autocomplete="new-password">
                            <button type="button" onclick="togglePassword('new_password', this)" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; font-size: 11px; font-weight: 800; color: #6b7280; cursor: pointer;">SHOW</button>
                        </div>
                        @foreach($errors->updatePassword->get('password') as $msg)
                            <div style="color: #ef4444; font-size: 12px; margin-top: 6px;">{{ $msg }}</div>
                        @endforeach
                    </div>
                    <div>
                        <label class="p-label" for="password_confirmation">Confirm New Password</label>
                        <div style="position: relative;">
                            <input type="password" id="password_confirmation" name="password_confirmation" class="p-input" style="padding-right: 60px;" required autocomplete="new-password">
                            <button type="button" onclick="togglePassword('password_confirmation', this)" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; font-size: 11px; font-weight: 800; color: #6b7280; cursor: pointer;">SHOW</button>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 32px; display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn-primary" style="padding: 12px 32px; border-radius: 8px;">Update Password</button>
                </div>
            </div>
        </form>
    </div>

    {{-- TAB PANEL 3: SECURITY & 2FA --}}
    <div id="panel-security" class="p-panel">
        <div style="background: #ffffff; border-radius: 12px; padding: 32px; box-shadow: 0 1px 3px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.02); border: 1px solid #f3f4f6; max-width: 800px; margin: 0 auto;">
            <h3 style="font-size: 20px; font-weight: 800; color: #111827; margin: 0 0 8px 0;">Account Overview</h3>
            <p style="font-size: 14px; color: #6b7280; margin: 0 0 32px 0;">An overview of your account status and recent logins.</p>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px;">
                    <div style="display: flex; align-items: center; gap: 8px; color: #6b7280; font-size: 12px; font-weight: 700; text-transform: uppercase;">
                        <i class="bi bi-calendar-check"></i> Joined
                    </div>
                    <div style="font-size: 16px; font-weight: 800; color: #111827; margin-top: 8px;">{{ auth()->user()->created_at->format('M d, Y') }}</div>
                </div>
                <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px;">
                    <div style="display: flex; align-items: center; gap: 8px; color: #6b7280; font-size: 12px; font-weight: 700; text-transform: uppercase;">
                        <i class="bi bi-clock-history"></i> Last Login
                    </div>
                    <div style="font-size: 16px; font-weight: 800; color: #111827; margin-top: 8px;">
                        {{ auth()->user()->last_login ? \Carbon\Carbon::parse(auth()->user()->last_login)->format('M d, Y h:i A') : 'N/A' }}
                    </div>
                </div>
                <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px;">
                    <div style="display: flex; align-items: center; gap: 8px; color: #6b7280; font-size: 12px; font-weight: 700; text-transform: uppercase;">
                        <i class="bi bi-activity"></i> Status
                    </div>
                    <div style="font-size: 16px; font-weight: 800; color: #16a34a; margin-top: 8px;">
                        {{ ucfirst(auth()->user()->status ?? 'Active') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- MODAL: DELETE ACCOUNT CONFIRMATION --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius: 12px; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
            <div class="modal-body p-5 text-center">
                <div style="width: 60px; height: 60px; background: #fee2e2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                    <i class="bi bi-exclamation-triangle-fill" style="color: #ef4444; font-size: 28px;"></i>
                </div>
                <h4 style="font-size: 20px; font-weight: 800; color: #111827; margin-bottom: 12px;">Delete Account</h4>
                <p style="font-size: 14px; color: #4b5563; margin-bottom: 32px; line-height: 1.6;">Are you sure you want to continuously delete your account? All of your data will be permanently removed. This action cannot be undone.</p>
                
                <form method="POST" action="{{ route('profile.destroy') }}">
                    @csrf @method('DELETE')
                    <input type="password" name="password" class="p-input" placeholder="Password to confirm" required style="margin-bottom: 24px; text-align: center;">
                    
                    <div style="display: flex; gap: 16px; justify-content: center;">
                        <button type="button" class="btn-secondary" data-bs-dismiss="modal" style="flex: 1;">Cancel</button>
                        <button type="submit" class="btn-primary" style="background: #ef4444; flex: 1;">Delete Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Tab Switching logic
function switchTab(tabId) {
    document.querySelectorAll('.p-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.p-panel').forEach(p => p.classList.remove('active'));
    document.getElementById('tab-' + tabId).classList.add('active');
    document.getElementById('panel-' + tabId).classList.add('active');
}

// Open correct tab if there's a password error
@if($errors->updatePassword->any())
    switchTab('password');
@endif

// Delete confirm modal
function confirmDelete() {
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// Dynamic avatar preview 
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            let img = document.getElementById('avatar-preview-header');
            let initials = document.getElementById('avatar-initials-header');
            if(img) {
                img.src = e.target.result;
                img.style.display = 'block';
            }
            if(initials) {
                initials.style.display = 'none';
            }
        }
        reader.readAsDataURL(input.files[0]);
        document.getElementById('upload-box').style.borderColor = "#2563eb";
        document.getElementById('upload-box').style.backgroundColor = "#eff6ff";
    }
}

function removeAvatar() {
    document.getElementById('profile_picture').value = '';
    let img = document.getElementById('avatar-preview-header');
    let initials = document.getElementById('avatar-initials-header');
    let origUrl = '{{ auth()->user()->profile_picture ? Storage::url(auth()->user()->profile_picture) : '' }}';
    
    if (origUrl) {
        if(img) {
            img.src = origUrl;
            img.style.display = 'block';
        }
        if(initials) initials.style.display = 'none';
    } else {
        if(img) {
            img.src = '';
            img.style.display = 'none';
        }
        if(initials) initials.style.display = 'block';
    }
    document.getElementById('upload-box').style.borderColor = "#d1d5db";
    document.getElementById('upload-box').style.backgroundColor = "#f9fafb";
}

// Toggle password visibility
function togglePassword(inputId, btn) {
    let input = document.getElementById(inputId);
    if (input.type === "password") {
        input.type = "text";
        btn.textContent = "HIDE";
        btn.style.color = "#2563eb";
    } else {
        input.type = "password";
        btn.textContent = "SHOW";
        btn.style.color = "#6b7280";
    }
}
</script>
</script>

{{-- SweetAlert2 for Popup Modals --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Profile update confirmation
    const profileForm = document.getElementById('profile-form');
    if(profileForm) {
        profileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Save Changes?',
                text: "Are you sure you want to update your profile information? This action will permanently modify the database.",
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
                    profileForm.submit();
                }
            });
        });
    }

    // Password update confirmation
    const passwordForm = document.getElementById('password-form');
    if(passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Change Password?',
                text: "Are you sure you want to update your password? This action will permanently modify the database.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, change it!',
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
                    passwordForm.submit();
                }
            });
        });
    }
});
</script>

</x-dashboard-app>
