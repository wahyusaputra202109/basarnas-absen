<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
// use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class SiagaAbsentByMonth implements FromView, ShouldAutoSize//, WithColumnFormatting
{

    protected $header;
    protected $collect;

    public function __construct($header, $collect)
    {
        $this->header = $header;
        $this->collect = $collect;
    }

    public function view(): View
    {
        return view('exports.siaga-absent-by-month', [ 'header' => $this->header, 'data' => $this->collect ]);
    }

}
