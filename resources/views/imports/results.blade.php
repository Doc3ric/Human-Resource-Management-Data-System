<x-dashboard-app>
    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">
                    @if($stats['dry_run'] ?? false)
                        🧪 Dry Run Complete
                    @else
                        Import Complete
                    @endif
                </h1>
                <p class="text-gray-500 text-sm">
                    @if($stats['dry_run'] ?? false)
                        Simulation finished — <strong class="text-indigo-600">no data was changed</strong>.
                    @else
                        The import has finished processing your plantilla data.
                    @endif
                </p>
            </div>
            <a href="{{ route('imports.history') }}"
               class="inline-flex items-center gap-2 text-sm font-medium text-indigo-600 hover:text-indigo-800 border border-indigo-200 bg-indigo-50 px-3 py-2 rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                History
            </a>
        </div>

        {{-- Dry Run Banner --}}
        @if($stats['dry_run'] ?? false)
        <div class="bg-indigo-600 text-white rounded-xl p-5 flex items-start gap-4">
            <div class="text-3xl">🧪</div>
            <div>
                <p class="font-bold text-lg">DRY RUN — No Changes Saved</p>
                <p class="text-indigo-200 text-sm mt-1">The numbers below show what <em>would have happened</em> if this were a real import. The database is unchanged.</p>
            </div>
        </div>
        @endif

        {{-- Deleted Notice --}}
        @if(!empty($stats['deleted']) && $stats['deleted'] > 0)
        <div class="bg-red-100 border border-red-300 rounded-lg p-4 flex items-center gap-3">
            <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            <p class="text-red-700 text-sm font-medium">
                {{ number_format($stats['deleted']) }} existing records
                {{ ($stats['dry_run'] ?? false) ? 'would have been deleted' : 'were deleted' }}
                before importing (Replace All mode).
            </p>
        </div>
        @endif

        {{-- Stat Cards --}}
        <div class="grid grid-cols-3 gap-4">
            <div class="bg-green-600 text-white rounded-xl p-5 text-center shadow-sm">
                <div class="text-4xl font-bold">{{ number_format($stats['created']) }}</div>
                <div class="text-sm opacity-90 mt-1">{{ ($stats['dry_run'] ?? false) ? 'Would Create' : 'Records Created' }}</div>
            </div>
            <div class="bg-blue-600 text-white rounded-xl p-5 text-center shadow-sm">
                <div class="text-4xl font-bold">{{ number_format($stats['updated']) }}</div>
                <div class="text-sm opacity-90 mt-1">{{ ($stats['dry_run'] ?? false) ? 'Would Update' : 'Records Updated' }}</div>
            </div>
            <div class="bg-amber-500 text-white rounded-xl p-5 text-center shadow-sm">
                <div class="text-4xl font-bold">{{ number_format($stats['skipped']) }}</div>
                <div class="text-sm opacity-90 mt-1">Rows Skipped</div>
            </div>
        </div>

        {{-- Created Records Details --}}
        @if(!empty($stats['created_records']))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <p class="text-green-800 font-semibold text-sm mb-2">Created Records ({{ number_format($stats['created']) }} total):</p>
            <div class="max-h-48 overflow-y-auto">
                <ul class="list-disc list-inside text-green-700 text-xs space-y-0.5">
                    @foreach($stats['created_records'] as $record)
                        <li>{{ $record }}</li>
                    @endforeach
                </ul>
            </div>
            @if($stats['created'] > 100)
            <p class="text-green-600 text-xs mt-2 italic">
                <i class="bi bi-info-circle me-1"></i>Showing first 100 of {{ number_format($stats['created']) }} created records.
            </p>
            @endif
        </div>
        @endif

        {{-- Updated Records Details --}}
        @if(!empty($stats['updated_records']))
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <p class="text-blue-800 font-semibold text-sm mb-2">Updated Records ({{ number_format($stats['updated']) }} total):</p>
            <div class="max-h-48 overflow-y-auto">
                <ul class="list-disc list-inside text-blue-700 text-xs space-y-0.5">
                    @foreach($stats['updated_records'] as $record)
                        <li>{{ $record }}</li>
                    @endforeach
                </ul>
            </div>
            @if($stats['updated'] > 100)
            <p class="text-blue-600 text-xs mt-2 italic">
                <i class="bi bi-info-circle me-1"></i>Showing first 100 of {{ number_format($stats['updated']) }} updated records.
            </p>
            @endif
        </div>
        @endif

        {{-- Errors List --}}
        @if(!empty($stats['errors']))
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center justify-between mb-2">
                <p class="text-red-700 font-semibold text-sm">{{ count($stats['errors']) }} error(s) encountered (rows were skipped):</p>
                <a href="{{ route('imports.export-errors') }}"
                   class="inline-flex items-center gap-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Download Failed Rows (.xlsx)
                </a>
            </div>
            <div class="max-h-48 overflow-y-auto">
                <ul class="list-disc list-inside text-red-600 text-xs space-y-0.5">
                    @foreach($stats['errors'] as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        {{-- Action Buttons --}}
        <div class="flex gap-3">
            @if(!($stats['dry_run'] ?? false))
            <a href="{{ route('plantilla.index') }}"
               class="flex-1 text-center py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
                View Plantilla Records →
            </a>
            @endif
            <a href="{{ route('imports.index') }}"
               class="{{ ($stats['dry_run'] ?? false) ? 'flex-1 text-center bg-indigo-600 hover:bg-indigo-700 text-white' : 'px-5 border border-gray-300 text-gray-700 hover:bg-gray-50' }} py-3 rounded-lg font-medium transition">
                {{ ($stats['dry_run'] ?? false) ? 'Run Real Import Now' : 'Import Again' }}
            </a>
        </div>
    </div>
</x-dashboard-app>
