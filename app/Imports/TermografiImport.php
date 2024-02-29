<?php

namespace App\Imports;

use Illuminate\Support\Collection; 
use App\Models\Termografi;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use App\Helper;

class TermografiImport implements ToModel,WithHeadingRow,WithValidation
{
     
    public function __construct($config){ 
        $this->config = $config;       
        $this->helper = new Helper();


    }
    /**
    * @param Collection $collection
    */
    public function model(array $row)
    {   
        $detail = [];
        foreach ($this->config as $key => $value) {
            $data = [
                'no' => $value['key'],
                'value' => $row[$value['value']]
            ];
            array_push($detail,$data);
        }
        $nmonth = $this->helper->month(preg_replace('/\s+/', '', strtolower($row['bulan'])));
        $bln_tahun = $row['tahun'] .'-'.$nmonth.'-01';

        return new Termografi([
            'equipments_id'  => $row['equipments_id'],
            'unit_item_id'   => $row['unit_item_id'],
            'data_detail'    => json_encode($detail),  
            'keterangan'     => $row['keterangan'], 
            'bulan'          => strtolower($row['bulan']),
            'tahun'          => $row['tahun'], 
            'bln_tahun'      => $bln_tahun,  
            'status'         => $row['status'], 
            'analisis'       => $row['analisis'],
            'rekomendasi'    => $row['rekomendasi'], 
        ]);
    }
    public function rules(): array
    {
        return [
            'equipments_id' => 'required|integer|exists:mysql-app.equipments,id',
            'unit_item_id' => 'required|integer|exists:mysql-app.unit_item,id',  
            'status' => 'nullable|string',
            'bulan' => 'required|string|in:januari,februari,maret,april,mei,juni,juli,agustus,september,oktober,november,desember,JANUARI,FEBRUARI,MARET,APRIL,MEI,JUNI,JULI,AGUSTUS,SEPTEMBER,OKTOBER,NOVEMBER,DESEMBER,Januari,Februari,Maret,April,Mei,Juni,Juli,Agustus,September,Oktober,November,Desember',
            'tahun' => 'required|integer|digits:4|max:'.(date('Y')+2)
        ];
    
    }
}
