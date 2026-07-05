<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PlantillaRecord;
use Illuminate\Support\Str;

class NormalizeEncoding extends Command
{
    protected $signature = 'app:normalize-encoding';
    protected $description = 'Resolve character encoding issues by normalizing special characters to ASCII';

    public function handle()
    {
        $this->info("Normalizing character encoding for all records...");

        $records = PlantillaRecord::all();
        $bar = $this->output->createProgressBar(count($records));

        $columnsToNormalize = [
            'last_name', 'first_name', 'middle_name', 'name_extension',
            'position_title', 'office_department', 'office', 'employment_status',
            'designation', 'salary_grade'
        ];

        $updatedCount = 0;

        foreach ($records as $record) {
            $changed = false;

            foreach ($columnsToNormalize as $column) {
                // Not all columns might exist on the model dynamically, check attribute existence
                if (array_key_exists($column, $record->getAttributes()) && !empty($record->$column)) {
                    $original = $record->$column;
                    if (is_string($original)) {
                        $normalized = $this->normalizeText($original);

                        if ($original !== $normalized) {
                            $record->$column = $normalized;
                            $changed = true;
                        }
                    }
                }
            }

            if ($changed) {
                // Disable timestamps so we don't spam updated_at for a simple normalization
                $record->timestamps = false;
                $record->save();
                $updatedCount++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Normalization complete. Updated {$updatedCount} records.");
    }

    private function normalizeText($text)
    {
        // Fix common mojibake first
        $text = str_replace(
            ['Ã±', 'Ã‘', 'Ã©', 'Ã‰', 'Ã¡', 'Ã', 'Ã³', 'Ã“', 'Ãº', 'Ãš', 'Ã', 'Ã', 'Ã¯', 'Ã´', 'Ã¼', 'Â', ''],
            ['n',  'N',  'e',  'E',  'a',  'A',  'o',  'O',  'u',  'U',  'i', 'I',  'i',  'o',  'u',  '',  ''],
            $text
        );

        // Replace typical special chars
        $text = str_replace(['ñ', 'Ñ'], ['n', 'N'], $text);

        // Convert to ASCII representation
        $text = Str::ascii($text);

        // Remove any remaining non-printable characters or strange artifacts
        // Keep standard ASCII printable characters + common whitespace
        $text = preg_replace('/[^\x20-\x7E\t\n\r]/', '', $text);

        return $text;
    }
}
