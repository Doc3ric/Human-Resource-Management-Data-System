<?php

namespace App\Console\Commands;

use App\Models\DetailOrder;
use App\Support\DetailOrder\DetailOrderStatusService;
use Illuminate\Console\Command;

class CheckDetailOrderStatus extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'detail-orders:check-status {--dry-run : Show what would be flagged without making changes}';

    /**
     * The console command description.
     */
    protected $description = 'Flag detail orders nearing/past the CSC 1-year detail limit and auto-draft recall letters for overdue orders.';

    public function handle(DetailOrderStatusService $service): int
    {
        $isDryRun = $this->option('dry-run');

        $orders = DetailOrder::with('plantillaRecord')
            ->whereNotIn('status', ['Recalled', 'Extended'])
            ->get();

        if ($orders->isEmpty()) {
            $this->info('✅  No active detail orders to check.');
            return Command::SUCCESS;
        }

        $rows = $orders->map(function (DetailOrder $order) {
            $record = $order->plantillaRecord;
            $daysRemaining = $order->days_remaining;

            return [
                $order->detail_id,
                $order->detail_order_no,
                $record ? trim($record->last_name . ', ' . $record->first_name) : 'Unknown',
                $order->detailed_unit,
                $order->status,
                $daysRemaining,
                $daysRemaining <= 30 && $daysRemaining > 0 ? 'Nearing Expiry'
                    : ($daysRemaining <= 0 ? 'Overdue' : 'No change'),
            ];
        });

        $this->table(
            ['#', 'Order No.', 'Employee', 'Detailed Unit', 'Current Status', 'Days Remaining', 'Would Become'],
            $rows->toArray()
        );

        if ($isDryRun) {
            $this->warn('DRY RUN — no changes made.');
            return Command::SUCCESS;
        }

        $flagged = 0;
        foreach ($orders as $order) {
            $previousStatus = $order->status;
            $service->checkStatus($order);
            if ($order->fresh()->status !== $previousStatus) {
                $flagged++;
            }
        }

        $this->info("✅  Checked {$orders->count()} detail order(s). {$flagged} status change(s) applied.");

        return Command::SUCCESS;
    }
}
