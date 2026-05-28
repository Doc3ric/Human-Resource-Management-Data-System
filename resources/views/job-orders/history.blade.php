<x-dashboard-app>
    <div class="max-w-5xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('job-orders.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">JO Import History</h1>
                    <p class="text-gray-500 text-sm">Log of all past Job Order data imports</p>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:10px;padding:12px 16px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px;">
                <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div style="background:#fef2f2;border:1px solid #fecaca;color:#dc2626;border-radius:10px;padding:12px 16px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px;">
                <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
            </div>
        @endif

        @if($logs->isEmpty())
        <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p class="text-gray-500 font-medium">No import history yet.</p>
            <p class="text-gray-400 text-sm mt-1">Completed Job Order imports will appear here.</p>
        </div>
        @else
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">Date & Time</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">User</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">Mode</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">Records Created</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">Undone</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">Skipped</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($logs as $log)
                    @php
                        $info = null;
                        if ($log->description) {
                            $decoded = json_decode($log->description, true);
                            if (json_last_error() === JSON_ERROR_NONE) {
                                $info = $decoded;
                            } else {
                                // Legacy string format
                                preg_match('/Imported (\d+)/', $log->description, $m);
                                $info['created'] = $m[1] ?? 0;
                                $info['mode']    = 'upsert';
                                $info['skipped'] = '—';
                                $info['deleted_via_undo'] = 0;
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
                            <span class="font-bold text-green-600">+{{ number_format($info['created'] ?? 0) }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="font-bold {{ ($info['deleted_via_undo'] ?? 0) > 0 ? 'text-red-600' : 'text-gray-400' }}">
                                {{ ($info['deleted_via_undo'] ?? 0) > 0 ? '-' . number_format($info['deleted_via_undo']) : '—' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="font-bold {{ ($info['skipped'] ?? 0) > 0 ? 'text-amber-600' : 'text-gray-400' }}">
                                {{ ($info['skipped'] ?? '—') !== '—' ? number_format($info['skipped']) : '—' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if(($info['created'] ?? 0) > 0)
                                <form action="{{ route('job-orders.import.undo', $log->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="confirmUndo(this, {{ $info['created'] }})" class="text-red-600 hover:text-red-800 font-medium text-xs bg-red-50 hover:bg-red-100 px-2 py-1 rounded transition">
                                        Undo
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
        function confirmUndo(button, count) {
            Swal.fire({
                title: 'Undo JO Data Import?',
                html: `Are you sure you want to delete the <strong>${count} records</strong> created by this import?<br>This will remove them from both the <strong>Job Orders</strong> directory and the <strong>All Data</strong> directory.<br><br><span class="text-xs text-red-500 font-semibold">This action cannot be reversed!</span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#f1f5f9',
                confirmButtonText: 'Yes, undo import',
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
