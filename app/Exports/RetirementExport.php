<?php

namespace App\Exports;

use App\Models\PlantillaRecord;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class RetirementExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithDrawings, WithEvents, WithCustomStartCell
{
    use \App\Exports\Traits\HasPhrmoHeader;

    const RETIREMENT_AGE = 65;
    protected string $tab;
    protected string $search;
    protected string $year;

    public function __construct(string $tab = 'overdue', string $search = '', string $year = '')
    {
        $this->tab = $tab;
        $this->search = $search;
        $this->year = $year;
    }

    public function collection()
    {
        $cutoffDate = now()->subYears(self::RETIREMENT_AGE)->toDateString();

        if ($this->tab === 'near') {
            $nearStart = now()->subYears(self::RETIREMENT_AGE)->addMonths(1)->toDateString();
            $nearEnd = now()->subYears(self::RETIREMENT_AGE)->addMonths(12)->toDateString();
            $records = PlantillaRecord::where('is_vacant', false)->where('abolished', false)
                ->whereNotIn('employment_status', ['JO'])
                ->whereNotNull('date_of_birth')->whereNull('retired_at')
                ->whereBetween('date_of_birth', [$nearStart, $nearEnd])
                ->orderBy('date_of_birth')->get();

        } elseif ($this->tab === 'optional') {
            $optionalStart = $cutoffDate;
            $optionalEnd = now()->subYears(60)->toDateString();
            $records = PlantillaRecord::where('is_vacant', false)->where('abolished', false)
                ->whereNotIn('employment_status', ['JO'])
                ->whereNotNull('date_of_birth')->whereNull('retired_at')
                ->where('date_of_birth', '>', $optionalStart)
                ->where('date_of_birth', '<=', $optionalEnd)
                ->orderBy('date_of_birth', 'desc')->get();

        } elseif ($this->tab === 'history') {
            $query = PlantillaRecord::whereNotNull('retired_at')->orderBy('retired_at', 'desc');
            if ($this->search) {
                $term = '%' . $this->search . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('office_department', 'like', $term)
                        ->orWhere('position_title', 'like', $term)
                        ->orWhere('remarks_annotation', 'like', $term);
                });
            }
            if ($this->year) {
                $query->whereYear('retired_at', $this->year);
            }
            $records = $query->get();

        } else {
            // overdue (default)
            $records = PlantillaRecord::where('is_vacant', false)->where('abolished', false)
                ->whereNotIn('employment_status', ['JO'])
                ->whereNotNull('date_of_birth')->whereNull('retired_at')
                ->where('date_of_birth', '<=', $cutoffDate)
                ->orderBy('date_of_birth')->get();
        }

        // For history, extract former name from remarks_annotation
        if ($this->tab === 'history') {
            return $records->values()->map(fn($r, $i) => [
                'No.' => $i + 1,
                'Item No.' => $r->item_no_new,
                'Office' => $r->office_department,
                'Last Name' => $this->parseFormerName($r->remarks_annotation ?? '')['last'],
                'First Name' => $this->parseFormerName($r->remarks_annotation ?? '')['first'],
                'Position' => $r->position_title,
                'SG-Step' => "SG-{$r->salary_grade} Step {$r->step}",
                'Date Retired' => $r->retired_at?->format('m/d/Y') ?? '',
                'Annotation' => $r->remarks_annotation ?? '',
            ]);
        }

        return $records->map(fn($r, $i) => [
            'No.' => $i + 1,
            'Item No.' => $r->item_no_new,
            'Last Name' => strtoupper($r->last_name ?? ''),
            'First Name' => $r->first_name ?? '',
            'Middle Name' => $r->middle_name ?? '',
            'Birthday' => $r->date_of_birth?->format('m/d/Y') ?? '',
            'Age' => $r->age ?? '',
            'Position' => $r->position_title,
            'Office' => $r->office_department,
            'SG-Step' => "SG-{$r->salary_grade} Step {$r->step}",
        ]);
    }

    private function parseFormerName(string $annotation): array
    {
        if (preg_match('/Former employee:\s*([^.]+)\./i', $annotation, $m)) {
            $namePart = trim($m[1]);
            if (str_contains($namePart, ',')) {
                [$ln, $fn] = explode(',', $namePart, 2);
                return ['last' => strtoupper(trim($ln)), 'first' => trim($fn)];
            }
            return ['last' => strtoupper(trim($namePart)), 'first' => ''];
        }
        return ['last' => '', 'first' => ''];
    }

    public function headings(): array
    {
        if ($this->tab === 'history') {
            return ['No.', 'Item No.', 'Office', 'Last Name', 'First Name', 'Position Title', 'SG-Step', 'Date Retired', 'Annotation'];
        }
        return [
            'No.',
            'Item No.',
            'Last Name',
            'First Name',
            'Middle Name',
            'Birthday',
            'Age',
            'Position Title',
            'Office / Unit',
            'SG-Step',
        ];
    }

    public function title(): string
    {
        return match ($this->tab) {
            'near' => 'Near Retirement',
            'optional' => 'Optional Retirement',
            'history' => 'Retirement History',
            default => 'Overdue Retirement',
        };
    }
}
