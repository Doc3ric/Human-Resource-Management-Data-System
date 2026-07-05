@extends('layouts.app')

@section('content')
<style>
    .page-title { font-size: 24px; font-weight: 700; color: #111827; margin: 0; }
    .page-subtitle { font-size: 14px; color: #6b7280; margin: 4px 0 24px; }
    .premium-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
    
    .log-item { display: flex; justify-content: space-between; align-items: center; padding: 16px 24px; border-bottom: 1px solid #e5e7eb; }
    .log-item:last-child { border-bottom: none; }
    
    .log-badge { width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; }
    .badge-pdf { background: #fef2f2; color: #ef4444; }
    .badge-add { background: #ecfdf5; color: #10b981; }
    .badge-edit { background: #eff6ff; color: #3b82f6; }
    .badge-default { background: #f3f4f6; color: #6b7280; }
    
    .log-tag { font-size: 10px; font-weight: 800; text-transform: uppercase; padding: 2px 6px; border-radius: 4px; margin-right: 6px; }
    .tag-pdf { background: #fef2f2; color: #ef4444; border: 1px solid #fecaca; }
    .tag-add { background: #ecfdf5; color: #10b981; border: 1px solid #a7f3d0; }
    .tag-edit { background: #eff6ff; color: #3b82f6; border: 1px solid #bfdbfe; }
    .tag-default { background: #f3f4f6; color: #6b7280; border: 1px solid #d1d5db; }
    
    .log-meta { font-size: 12px; color: #6b7280; margin-bottom: 4px; display: flex; align-items: center; }
    .log-desc { font-size: 14px; color: #374151; font-weight: 500; }
    .log-time { font-size: 12px; color: #9ca3af; text-align: right; }
</style>

<div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
    <div>
        <h2 class="page-title">Activity Log</h2>
        <p class="page-subtitle">Real-time record of system actions performed by users.</p>
    </div>
    
    <div style="display:flex; gap: 12px; align-items: center;">
        <div style="font-size: 12px; color: #9ca3af; padding: 6px 12px; background: #fff; border: 1px solid #e5e7eb; border-radius: 16px;">
            {{ $logs->total() }} entries
        </div>
        <button class="btn btn-outline-secondary btn-sm" style="background:#fff;"><i class="bi bi-funnel"></i> Filter</button>
    </div>
</div>

<div class="premium-card">
    @foreach($logs as $log)
        @php
            $actionUpper = strtoupper($log->action);
            $badgeClass = 'badge-default';
            $tagClass = 'tag-default';
            $icon = 'bi-info-circle';
            $tagText = $actionUpper;
            
            if (str_contains($actionUpper, 'PDF') || str_contains($actionUpper, 'NOTICE') || str_contains($actionUpper, 'GENERATED')) {
                $badgeClass = 'badge-pdf'; $tagClass = 'tag-pdf'; $icon = 'bi-file-earmark-pdf';
                $tagText = str_contains($actionUpper, 'PDF') ? 'PDF' : 'GENERATED';
            } elseif (str_contains($actionUpper, 'ADD') || str_contains($actionUpper, 'CREATE')) {
                $badgeClass = 'badge-add'; $tagClass = 'tag-add'; $icon = 'bi-plus-circle';
                $tagText = 'ADDED';
            } elseif (str_contains($actionUpper, 'EDIT') || str_contains($actionUpper, 'UPDATE') || str_contains($actionUpper, 'RESOLVE')) {
                $badgeClass = 'badge-edit'; $tagClass = 'tag-edit'; $icon = 'bi-pencil-square';
                $tagText = 'EDIT';
            }
        @endphp
        
        <div class="log-item">
            <div style="display:flex; gap: 16px; align-items: center;">
                <div class="log-badge {{ $badgeClass }}">
                    <i class="bi {{ $icon }}"></i>
                </div>
                <div>
                    <div class="log-meta">
                        <span class="log-tag {{ $tagClass }}">{{ $tagText }}</span>
                        by {{ $log->user ? $log->user->name : 'System' }}
                    </div>
                    <div class="log-desc">{{ $log->description }}</div>
                </div>
            </div>
            
            <div class="log-time">
                <div>{{ $log->created_at->format('M d, Y') }}</div>
                <div>{{ $log->created_at->format('h:i A') }}</div>
            </div>
        </div>
    @endforeach
    
    @if($logs->isEmpty())
        <div style="padding: 40px; text-align: center; color: #9ca3af;">No activity logs found.</div>
    @endif
    
    <div style="padding: 16px 24px; border-top: 1px solid #e5e7eb;">
        {{ $logs->links() }}
    </div>
</div>
@endsection
