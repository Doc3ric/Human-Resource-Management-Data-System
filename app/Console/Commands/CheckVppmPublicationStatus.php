<?php

namespace App\Console\Commands;

use App\Models\PublicationRequest;
use App\Support\Vppm\VppmStatusService;
use Illuminate\Console\Command;

class CheckVppmPublicationStatus extends Command
{
    protected $signature = 'vppm:check-publication-status {--dry-run : Show what would be flagged without making changes}';

    protected $description = 'Daily sweep of vacancy publication requests — posting-period compliance, validity expiry (RA 7041 / 2025 ORAOHRA Sec.26/30).';

    public function handle(VppmStatusService $service): int
    {
        $isDryRun = $this->option('dry-run');

        $requests = PublicationRequest::with('vacantPosition')
            ->whereIn('status', ['PUBLICATION_ACTIVE', 'PUBLICATION_DEFICIENT', 'VALID', 'NEAR_EXPIRY'])
            ->get();

        if ($requests->isEmpty()) {
            $this->info('✅  No active publication requests to check.');
            return Command::SUCCESS;
        }

        $rows = $requests->map(fn (PublicationRequest $r) => [
            $r->id,
            $r->vacantPosition->position_title,
            $r->status,
            $r->days_to_posting_compliance ?? '—',
            $r->days_to_expiry ?? '—',
        ]);

        $this->table(['#', 'Position', 'Current Status', 'Days to Posting Compliance', 'Days to Expiry'], $rows->toArray());

        if ($isDryRun) {
            $this->warn('DRY RUN — no changes made.');
            return Command::SUCCESS;
        }

        $flagged = 0;
        foreach ($requests as $request) {
            $previousStatus = $request->status;
            $service->checkStatus($request);
            if ($request->fresh()->status !== $previousStatus) {
                $flagged++;
            }
        }

        $this->info("✅  Checked {$requests->count()} publication request(s). {$flagged} status change(s) applied.");

        return Command::SUCCESS;
    }
}
