<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── plantilla_records ────────────────────────────────────────────────
        $this->deduplicateColumn('plantilla_records', 'employee_code');
        if (!$this->indexExists('plantilla_records', 'plantilla_records_employee_code_unique')) {
            Schema::table('plantilla_records', function (Blueprint $table) {
                $table->unique('employee_code');
            });
        }

        // ── casual_employees ─────────────────────────────────────────────────
        $this->deduplicateColumn('casual_employees', 'employee_code');
        if (!$this->indexExists('casual_employees', 'casual_employees_employee_code_unique')) {
            Schema::table('casual_employees', function (Blueprint $table) {
                $table->unique('employee_code');
            });
        }

        // ── job_orders ───────────────────────────────────────────────────────
        $this->deduplicateColumn('job_orders', 'employee_code');
        if (!$this->indexExists('job_orders', 'job_orders_employee_code_unique')) {
            Schema::table('job_orders', function (Blueprint $table) {
                $table->unique('employee_code');
            });
        }
    }

    public function down(): void
    {
        foreach (['plantilla_records', 'casual_employees', 'job_orders'] as $table) {
            $indexName = $table . '_employee_code_unique';
            if ($this->indexExists($table, $indexName)) {
                Schema::table($table, function (Blueprint $blueprint) use ($indexName) {
                    $blueprint->dropUnique($indexName);
                });
            }
        }
    }

    /** Check whether a named index already exists on a table. */
    private function indexExists(string $table, string $indexName): bool
    {
        $sm = Schema::getConnection()->getDoctrineSchemaManager();
        $indexesFound = $sm->listTableIndexes($table);
        return array_key_exists($indexName, $indexesFound);
    }

    /**
     * Find duplicate employee_code values in a table and append a numeric
     * suffix (2, 3, 4…) to all but the first occurrence so the unique
     * constraint can be applied cleanly.
     */
    private function deduplicateColumn(string $table, string $column): void
    {
        // Find all codes that appear more than once (excluding NULLs)
        $duplicates = DB::table($table)
            ->select($column)
            ->whereNotNull($column)
            ->groupBy($column)
            ->havingRaw('COUNT(*) > 1')
            ->pluck($column);

        foreach ($duplicates as $code) {
            // Get all rows with this code, ordered by id, skip the first one
            $rows = DB::table($table)
                ->where($column, $code)
                ->orderBy('id')
                ->skip(1)  // keep the first occurrence as-is
                ->get(['id']);

            $suffix = 2;
            foreach ($rows as $row) {
                // Keep incrementing until we find a non-taken code
                do {
                    $newCode = $code . $suffix;
                    $taken = DB::table($table)->where($column, $newCode)->exists();
                    if ($taken) {
                        $suffix++;
                    }
                } while ($taken);

                DB::table($table)->where('id', $row->id)->update([$column => $newCode]);
                $suffix++;
            }
        }
    }
};
