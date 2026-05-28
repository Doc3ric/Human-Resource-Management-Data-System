<x-dashboard-app>
    <div style="max-width:900px;margin:0 auto;">
        <div style="margin-bottom:20px;display:flex;align-items:center;gap:10px;">
            <a href="{{ route('job-orders.index') }}"
               style="display:inline-flex;align-items:center;gap:6px;color:#64748b;font-size:13px;font-weight:600;text-decoration:none;padding:6px 12px;border:1.5px solid #e2e8f0;border-radius:8px;background:#f8fafc;transition:all .15s;"
               onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'">
                <i class="bi bi-arrow-left"></i> Back to JO Inventory
            </a>
            <h2 style="font-size:19px;font-weight:800;color:#0f172a;margin:0;">
                <i class="bi bi-plus-circle-fill" style="color:#059669;"></i> Add Job Order Record
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
            'jo'         => null,
            'formAction' => route('job-orders.store'),
            'formMethod' => 'POST',
        ])
    </div>
</x-dashboard-app>
