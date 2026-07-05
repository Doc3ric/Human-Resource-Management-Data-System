<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PerformanceExport implements FromCollection, WithHeadings, WithMapping
{
    protected $employees;
    protected $fields;

    public function __construct($employees, $fields)
    {
        $this->employees = $employees;
        $this->fields = $fields;
    }

    public function collection()
    {
        return $this->employees;
    }

    public function headings(): array
    {
        $headings = [];
        
        if (in_array('year', $this->fields)) $headings[] = 'Year';
        if (in_array('period', $this->fields)) $headings[] = 'Period';
        if (in_array('name', $this->fields)) $headings[] = 'Employee Name';
        if (in_array('position', $this->fields)) $headings[] = 'Position Title';
        if (in_array('status', $this->fields)) $headings[] = 'Employment Status';
        if (in_array('target_submitted', $this->fields)) $headings[] = 'Target Submitted';
        if (in_array('target_date', $this->fields)) $headings[] = 'Target Submission Date';
        if (in_array('initial_rating', $this->fields)) $headings[] = 'Initial Rating';
        if (in_array('rating_date', $this->fields)) $headings[] = 'Rating Submission Date';
        if (in_array('final_rating', $this->fields)) $headings[] = 'Final Rating';
        if (in_array('adjectival_rating', $this->fields)) $headings[] = 'Adjectival Rating';

        return $headings;
    }

    public function map($row): array
    {
        $data = [];
        
        $ipcr = $row->ipcrRatings->first();

        if (in_array('year', $this->fields)) {
            $data[] = ($ipcr) ? $ipcr->year : '';
        }
        if (in_array('period', $this->fields)) {
            if ($ipcr) {
                $data[] = $ipcr->period_type === 'custom' ? $ipcr->custom_period : ($ipcr->period_type === 'jan-jun' ? 'Jan-Jun' : 'Jul-Dec');
            } else {
                $data[] = '';
            }
        }
        if (in_array('name', $this->fields)) {
            $data[] = trim($row->last_name . ', ' . $row->first_name . ' ' . $row->middle_name . ' ' . $row->name_extension);
        }
        if (in_array('position', $this->fields)) {
            $data[] = $row->position_title;
        }
        if (in_array('status', $this->fields)) {
            $data[] = $row->employment_status;
        }
        if (in_array('target_submitted', $this->fields)) {
            $data[] = ($ipcr && $ipcr->target_submitted) ? 'Yes' : 'No';
        }
        if (in_array('target_date', $this->fields)) {
            $data[] = ($ipcr && $ipcr->target_submission_date) ? $ipcr->target_submission_date->format('Y-m-d') : '';
        }
        if (in_array('initial_rating', $this->fields)) {
            $data[] = ($ipcr && $ipcr->rating !== null) ? number_format($ipcr->rating, 2) : '';
        }
        if (in_array('rating_date', $this->fields)) {
            $data[] = ($ipcr && $ipcr->rating_submission_date) ? $ipcr->rating_submission_date->format('Y-m-d') : '';
        }
        if (in_array('final_rating', $this->fields)) {
            $data[] = ($ipcr && $ipcr->final_rating !== null) ? number_format($ipcr->final_rating, 2) : '';
        }
        if (in_array('adjectival_rating', $this->fields)) {
            $data[] = ($ipcr) ? $ipcr->adjectival_rating : '';
        }

        return $data;
    }
}
