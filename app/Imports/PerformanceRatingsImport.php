<?php

namespace App\Imports;

use App\Models\IpcrRating;
use App\Models\PlantillaRecord;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class PerformanceRatingsImport implements ToCollection, WithHeadingRow
{
    protected $year;
    protected $period_type;
    protected $custom_period;

    public function __construct($year, $period_type, $custom_period = null)
    {
        $this->year = $year;
        $this->period_type = $period_type;
        $this->custom_period = $custom_period;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // The heading row transforms to snake_case by default, e.g. "Employee ID" -> "employee_id"
            // "Target Submitted (Yes/No)" -> "target_submitted_yesno"
            // "Target Date (YYYY-MM-DD)" -> "target_date_yyyy_mm_dd"
            // "Rating Score" -> "rating_score"
            // "Rating Date (YYYY-MM-DD)" -> "rating_date_yyyy_mm_dd"

            $employeeId = $row['employee_id'] ?? null;
            if (!$employeeId) {
                continue; // Skip if no employee ID is provided
            }

            // Optional: verify the employee actually exists to avoid foreign key constraints failing
            $employeeExists = PlantillaRecord::where('id', $employeeId)->exists();
            if (!$employeeExists) {
                continue;
            }

            $targetSubmittedStr = strtolower(trim($row['target_submitted_yesno'] ?? 'no'));
            $targetSubmitted = in_array($targetSubmittedStr, ['yes', 'y', '1', 'true']);

            $targetDateStr = trim($row['target_date_yyyy_mm_dd'] ?? '');
            // Convert excel date if it's a numeric value
            if (is_numeric($targetDateStr)) {
                $targetDateStr = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($targetDateStr)->format('Y-m-d');
            } elseif (empty($targetDateStr)) {
                $targetDateStr = null;
            }

            $ratingStr = trim($row['rating_score'] ?? '');
            $rating = is_numeric($ratingStr) ? (float) $ratingStr : null;

            $ratingDateStr = trim($row['rating_date_yyyy_mm_dd'] ?? '');
            if (is_numeric($ratingDateStr)) {
                $ratingDateStr = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($ratingDateStr)->format('Y-m-d');
            } elseif (empty($ratingDateStr)) {
                $ratingDateStr = null;
            }

            IpcrRating::updateOrCreate(
                [
                    'plantilla_record_id' => $employeeId,
                    'year' => $this->year,
                    'period_type' => $this->period_type,
                    'custom_period' => $this->period_type === 'custom' ? $this->custom_period : null,
                ],
                [
                    'target_submitted' => $targetSubmitted,
                    'target_submission_date' => $targetDateStr,
                    'rating' => $rating,
                    'rating_submission_date' => $ratingDateStr,
                ]
            );
        }
    }
}
