<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\PlantillaRecord;
use Illuminate\Console\Command;

/**
 * Applies the precomputed Reg3rd.xlsx reconciliation instructions
 * (storage/app/reports/reg3rd_instructions.json) to plantilla_records.
 *
 * The instructions were built by matching Reg3rd.xlsx (Regular/Permanent
 * 3rd Tranche) against a full export of the live database, entirely offline,
 * with extensive verification (see the accompanying audit report). This
 * command is a thin, auditable applier: it does no matching or business
 * logic of its own -- it just executes each update/create/archive through
 * Eloquent so PlantillaRecord's existing Spatie Activitylog (logOnlyDirty)
 * captures a field-level trail automatically, same as any other edit.
 */
class ApplyReg3rdInstructions extends Command
{
    protected $signature = 'reg3rd:apply
        {file : Path to the instructions JSON}
        {--dry-run : Report what would happen without writing anything}
        {--actor= : User ID to attribute the activity log entry to (required for a live run)}';

    protected $description = 'Apply the precomputed Reg3rd.xlsx reconciliation instructions to plantilla_records.';

    public function handle(): int
    {
        $path = $this->argument('file');
        if (!file_exists($path)) {
            $this->error("File not found: {$path}");
            return Command::FAILURE;
        }
        $dryRun = (bool) $this->option('dry-run');
        $this->info($dryRun ? 'DRY RUN — no database changes will be made.' : 'Live run — changes WILL be written.');

        $instructions = json_decode(file_get_contents($path), true);

        $updated = 0;
        $updateErrors = [];
        foreach ($instructions['updates'] as $u) {
            try {
                if (!$dryRun) {
                    $record = PlantillaRecord::find($u['db_id']);
                    if (!$record) {
                        $updateErrors[] = "id {$u['db_id']} ({$u['name']}) not found";
                        continue;
                    }
                    $record->update($u['changes']);
                }
                $updated++;
            } catch (\Throwable $e) {
                $updateErrors[] = "id {$u['db_id']} ({$u['name']}): " . $e->getMessage();
            }
        }

        $createdIds = [];
        $createErrors = [];
        foreach ($instructions['creates'] as $c) {
            unset($c['_row_ref']);
            $c['employment_status'] = 'P';
            try {
                if (!$dryRun) {
                    $createdIds[] = PlantillaRecord::create($c)->id;
                } else {
                    $createdIds[] = null;
                }
            } catch (\Throwable $e) {
                $createErrors[] = ($c['last_name'] ?? '?') . ', ' . ($c['first_name'] ?? '?') . ': ' . $e->getMessage();
            }
        }

        $vacantCreatedIds = [];
        $vacantErrors = [];
        foreach ($instructions['vacant_creates'] as $v) {
            unset($v['_row_ref']);
            $v['employment_status'] = 'P';
            try {
                if (!$dryRun) {
                    $vacantCreatedIds[] = PlantillaRecord::create($v)->id;
                } else {
                    $vacantCreatedIds[] = null;
                }
            } catch (\Throwable $e) {
                $vacantErrors[] = ($v['office_department'] ?? '?') . ' item ' . ($v['item_no_new'] ?? '?') . ': ' . $e->getMessage();
            }
        }

        $archived = 0;
        $archiveErrors = [];
        foreach ($instructions['archive_ids'] as $id) {
            try {
                if (!$dryRun) {
                    $record = PlantillaRecord::find($id);
                    if (!$record) {
                        $archiveErrors[] = "id {$id} not found";
                        continue;
                    }
                    $record->delete(); // soft delete = archive
                }
                $archived++;
            } catch (\Throwable $e) {
                $archiveErrors[] = "id {$id}: " . $e->getMessage();
            }
        }

        $this->info('');
        $this->info('=== SUMMARY ===');
        $this->info("Updated:        {$updated}");
        $this->info('Created (named): ' . count($createdIds));
        $this->info('Created (vacant): ' . count($vacantCreatedIds));
        $this->info("Archived:       {$archived}");
        if ($updateErrors || $createErrors || $vacantErrors || $archiveErrors) {
            $totalErrors = count($updateErrors) + count($createErrors) + count($vacantErrors) + count($archiveErrors);
            $this->warn("Errors: {$totalErrors}");
            foreach (array_slice(array_merge($updateErrors, $createErrors, $vacantErrors, $archiveErrors), 0, 20) as $e) {
                $this->warn('  ' . $e);
            }
        }

        if (!$dryRun) {
            $actorId = (int) $this->option('actor');
            ActivityLog::create([
                'user_id' => $actorId,
                'action' => 'Reconciled Regular/Permanent Data from Reg3rd.xlsx',
                'description' => json_encode([
                    'mode' => 'reconcile',
                    'source_file' => 'Reg3rd.xlsx',
                    'updated' => $updated,
                    'created_named' => count($createdIds),
                    'created_vacant' => count($vacantCreatedIds),
                    'archived' => $archived,
                    'created_ids' => array_values(array_filter($createdIds)),
                    'vacant_created_ids' => array_values(array_filter($vacantCreatedIds)),
                    'archived_ids' => $instructions['archive_ids'],
                    'errors' => array_merge($updateErrors, $createErrors, $vacantErrors, $archiveErrors),
                ]),
            ]);
        }

        return Command::SUCCESS;
    }
}
