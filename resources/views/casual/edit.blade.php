<x-dashboard-app>
    <div style="max-width:900px;margin:0 auto;">
        {{-- Header --}}
        <div style="background:linear-gradient(135deg,#831843,#be185d);border-radius:14px;padding:22px 28px;margin-bottom:22px;display:flex;align-items:center;justify-content:space-between;gap:16px;">
            <div>
                <h1 style="color:#fff;font-size:20px;font-weight:800;margin:0;">
                    <i class="bi bi-pencil-square me-2"></i>Edit Casual Employee
                </h1>
                <p style="color:rgba(255,255,255,.65);font-size:12px;margin:4px 0 0;">
                    Editing: <strong>{{ $casual->is_vacant ? 'VACANT — ' . $casual->position_title : $casual->full_name }}</strong>
                </p>
            </div>
            <a href="{{ session('last_index_url', route('casual.index')) }}"
               style="display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.25);color:#fff;padding:8px 16px;border-radius:8px;font-size:12px;font-weight:700;text-decoration:none;">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>

        @if($errors->any())
            <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;border-radius:10px;padding:12px 16px;margin-bottom:16px;font-size:13px;">
                <strong><i class="bi bi-exclamation-circle-fill"></i> Please fix the following errors:</strong>
                <ul style="margin:6px 0 0 16px;padding:0;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @include('casual.form', [
            'casual'     => $casual,
            'offices'    => $offices,
            'formAction' => route('casual.update', $casual),
            'formMethod' => 'PUT',
        ])
    </div>
</x-dashboard-app>
