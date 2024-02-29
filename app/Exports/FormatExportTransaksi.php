<?php

namespace App\Exports;

use App\Models\UnitPembangkit;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class FormatExportTransaksi implements WithHeadings,WithTitle
{   

    private $head;
    private $key;
    public function __construct($head,$key)
    {
        $this->head = $head;
        $this->key = $key;
    }

    /**
    * @return \Illuminate\Support\Collection
    */

    // public function collection()
    // {
    //       return collect([
    //           [
    //               'name' => $data->name,
    //               'email' => $data->email
    //           ]
    //       ]);
    // }

    public function title(): string
    {
        return 'Data';
    }

    public function headings(): array
    {    

        $default = ['equipments_id','unit_item_id'];
        switch ($this->key) {
          case "vibrasi_config_detail":
            $head_back = ['zone','analisis','rekomendasi','bulan','tahun'];
            break;
          case "termografi_config_detail":
            $head_back = ['keterangan','bulan','tahun','status','analisis','rekomendasi'];
            break; 
          default:
            $head_back = [];
        }
        $heading_format = array_merge($default,$this->head,$head_back);
        return $heading_format;
    }
}
