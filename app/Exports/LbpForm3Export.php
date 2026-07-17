<?php

namespace App\Exports;

use App\Models\PlantillaRecord;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LbpForm3Export implements FromView, ShouldAutoSize, WithStyles
{
    protected $employmentType;
    protected $office;
    protected $year;
    protected $preparedBy;
    protected $reviewedBy;
    protected $approvedBy;
    protected $currentTranche;
    protected $proposedTranche;

    public function __construct($employmentType, $office, $year, $preparedBy, $reviewedBy, $approvedBy, $currentTranche, $proposedTranche)
    {
        $this->employmentType = $employmentType;
        $this->office = $office;
        $this->year = $year;
        $this->preparedBy = $preparedBy;
        $this->reviewedBy = $reviewedBy;
        $this->approvedBy = $approvedBy;
        $this->currentTranche = $currentTranche;
        $this->proposedTranche = $proposedTranche;
    }

    public function view(): View
    {
        $query = PlantillaRecord::query()
            ->where('abolished', false);

        if ($this->employmentType === 'Permanent') {
            $query->whereIn('employment_status', ['P', 'Permanent']);
        } elseif ($this->employmentType === 'Casual') {
            $query->whereIn('employment_status', ['Casual', 'CASUAL', 'C']);
        }

        if (!empty($this->office)) {
            $query->where('office_department', $this->office);
        }

        $records = $query->orderBy('office_department')
            ->orderBy('item_no_new')
            ->get();

        $groupedRecords = $records->groupBy('office_department');

        return view('plantilla.exports.lbp-form-3', [
            'groupedRecords' => $groupedRecords,
            'employmentType' => $this->employmentType,
            'office' => $this->office,
            'year' => $this->year,
            'preparedBy' => $this->preparedBy,
            'reviewedBy' => $this->reviewedBy,
            'approvedBy' => $this->approvedBy,
            'currentTranche' => $this->currentTranche,
            'proposedTranche' => $this->proposedTranche,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true]],
            2    => ['font' => ['bold' => true]],
        ];
    }
}
