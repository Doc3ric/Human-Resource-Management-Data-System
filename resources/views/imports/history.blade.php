<x-dashboard-app>
    <div class="max-w-5xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('imports.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Import History</h1>
                    <p class="text-gray-500 text-sm">Log of all past plantilla data imports</p>
                </div>
            </div>
        </div>

        @if($logs->isEmpty())
        <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p class="text-gray-500 font-medium">No import history yet.</p>
            <p class="text-gray-400 text-sm mt-1">Completed imports will appear here.</p>
        </div>
        @else
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">Date & Time</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">User</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">Mode</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">Created</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">Updated</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">Deleted</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">Skipped</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($logs as $log)
                    @php
                        // Parse description — may be JSON (new) or plain string (old)
                        $info = null;
                        if ($log->description) {
                            $decoded = json_decode($log->description, true);
                            if (json_last_error() === JSON_ERROR_NONE) {
                                $info = $decoded;
                            } else {
                                // Legacy string format — extract numbers via regex
                                preg_match('/Created (\d+)/', $log->description, $m);    $info['created'] = $m[1] ?? '—';
                                preg_match('/Updated (\d+)/', $log->description, $m);    $info['updated'] = $m[1] ?? '—';
                                preg_match('/Deleted (\d+)/', $log->description, $m);    $info['deleted'] = $m[1] ?? '—';
                                $info['mode']    = 'upsert';
                                $info['skipped'] = '—';
                            }
                        }
                    @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 text-gray-700">
                            <div class="font-medium">{{ $log->created_at->format('M d, Y') }}</div>
                            <div class="text-xs text-gray-400">{{ $log->created_at->format('h:i A') }}</div>
                        </td>
                        <td class="px-4 py-3 text-gray-700">
                            <div class="font-medium">{{ $log->user?->name ?? '—' }}</div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if(($info['mode'] ?? '') === 'replace_all')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">Replace All</span>
                            @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">Upsert</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="font-bold text-green-600">{{ number_format($info['created'] ?? 0) }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="font-bold text-blue-600">{{ number_format($info['updated'] ?? 0) }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="font-bold {{ ($info['deleted'] ?? 0) > 0 ? 'text-red-600' : 'text-gray-400' }}">
                                {{ ($info['deleted'] ?? '—') !== '—' ? number_format($info['deleted']) : '—' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="font-bold {{ ($info['skipped'] ?? 0) > 0 ? 'text-amber-600' : 'text-gray-400' }}">
                                {{ ($info['skipped'] ?? '—') !== '—' ? number_format($info['skipped']) : '—' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if(($info['created'] ?? 0) > 0)
                                <form action="{{ route('imports.undo', $log->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="confirmUndo(this, {{ $info['created'] }}, '{{ addslashes($info['file_name'] ?? 'Unknown File') }}')" style="display:inline-flex;align-items:center;gap:4px;background:#fef2f2;color:#dc2626;border:1px solid #fecaca;padding:4px 10px;border-radius:6px;font-size:11px;font-weight:700;transition:all .15s;">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg> Undo
                                    </button>
                                </form>
                            @else
                                <span class="text-gray-400 text-xs">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($logs->hasPages())
        <div>{{ $logs->links() }}</div>
        @endif
        @endif
    </div>

    <!-- SweetAlert2 for Premium Modals -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmUndo(button, count, fileName) {
            const fileLabel = fileName ? `<br><span style="font-size:12px;color:#6b7280;">File: <strong style="color:#374151;">${fileName}</strong></span>` : '';
            Swal.fire({
                title: 'Undo Data Import?',
                html: `Are you sure you want to delete the <strong>${count} records</strong> created by this import?${fileLabel}<br><br><span class="text-xs text-red-500 font-semibold">This action cannot be reversed!</span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#f1f5f9',
                confirmButtonText: 'Yes, delete records',
                cancelButtonText: '<span style="color: #475569">Cancel</span>',
                customClass: {
                    title: 'text-gray-800 text-xl font-bold',
                    popup: 'rounded-xl shadow-xl border border-gray-100',
                    confirmButton: 'rounded-lg px-4 py-2 text-sm font-semibold shadow-sm',
                    cancelButton: 'rounded-lg px-4 py-2 text-sm font-semibold border border-gray-200'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    button.closest('form').submit();
                }
            });
        }
    </script>
</x-dashboard-app>
