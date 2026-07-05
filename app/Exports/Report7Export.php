<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection, WithDrawings, WithEvents, WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class Report7Export implements FromCollection, WithDrawings, WithEvents, WithCustomStartCell
{
    use \App\Exports\Traits\HasPhrmoHeader;

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        //
    }
}
