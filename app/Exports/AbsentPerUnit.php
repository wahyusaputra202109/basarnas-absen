<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class AbsentPerUnit implements FromView, ShouldAutoSize, WithColumnFormatting
{
    
    protected $collect;

    public function __construct($collect)
    {
        $this->collect = $collect;
    }

    public function view(): View
    {
        return view('exports.absent-per-unit', [ 'data' => $this->collect ]);
    }

    public function columnFormats(): array
    {
        return [
            'C'     => NumberFormat::FORMAT_TEXT,
            'L'     => NumberFormat::FORMAT_TEXT,
        ];
    }

}
