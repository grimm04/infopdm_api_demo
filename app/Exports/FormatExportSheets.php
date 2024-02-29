<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class FormatExportSheets implements FromArray, WithMultipleSheets
{
    protected $sheets; 
    public function __construct(array $sheets)
    {
        $this->sheets = $sheets;
    }

    public function array(): array
    {
        return $this->sheets;
    }

    public function sheets(): array
    {
        $sheets = [
            new FormatExportTransaksi($this->sheets['head'],$this->sheets['key']), 
            new FormatExportData($this->sheets['equipments']), 
            new UnitItem($this->sheets['unit_item']), 
        ];

        return $sheets;
    }
}
