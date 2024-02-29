<?php

namespace App\Imports;

use Illuminate\Support\Collection; 
use App\Models\Vibrasi;  
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Helper;

class VibrasiImport implements ToModel,WithHeadingRow,WithValidation
{   
 
    private $helper;

    public function __construct($config){ 
        $this->helper = new Helper();
        $this->config = $config;
    }
    /**
    * @param Collection $collection
    */
    public function model(array $row)
    {   
        $detail = []; 
        foreach ($this->config as $con) {
            $det = $con['value'];
            $data = [
                'no' => $con['key'],
                'value' => $row[$con['value']]
            ];
            array_push($detail,$data);
        }
        $nmonth = $this->helper->month(preg_replace('/\s+/', '', strtolower($row['bulan'])));
        $bln_tahun = preg_replace('/\s+/', '', $row['tahun']) .'-'.$nmonth.'-01';
        // return $detail;
        return new Vibrasi([
            'equipments_id'  => $row['equipments_id'],
            'unit_item_id'   => $row['unit_item_id'],
            'data_detail'    => json_encode($detail),
            'zone'           => preg_replace('/\s+/', '', $row['zone']),
            'analisis'       => $row['analisis'],
            'rekomendasi'    => $row['rekomendasi'],
            'bulan'          => preg_replace('/\s+/', '', strtolower($row['bulan'])),
            'tahun'          => preg_replace('/\s+/', '', $row['tahun']), 
            'bln_tahun'      => $bln_tahun,  
        ]);
    }
    public function rules(): array
    {
        return [
            'equipments_id' => 'required|integer|exists:mysql-app.equipments,id',
            'unit_item_id' => 'required|integer|exists:mysql-app.unit_item,id', 
            'bulan' => 'required|string|in:januari,februari,maret,april,mei,juni,juli,agustus,september,oktober,november,desember,JANUARI,FEBRUARI,MARET,APRIL,MEI,JUNI,JULI,AGUSTUS,SEPTEMBER,OKTOBER,NOVEMBER,DESEMBER,Januari,Februari,Maret,April,Mei,Juni,Juli,Agustus,September,Oktober,November,Desember',
            'tahun' => 'required|integer|digits:4|max:'.(date('Y')+1)
        ];
    
    }

   
}

 