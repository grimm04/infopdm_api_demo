<?php

namespace App\Imports;
 
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class TransaksiImport implements WithMultipleSheets
{   
    public function __construct($config,$transaksi){ 
        $this->config = $config;
        $this->transaksi = $transaksi;
    }
    public function sheets(): array
    {   
        if($this->transaksi === 'vibrasi'){
            $import = new VibrasiImport($this->config);
        }else {
            $import = new TermografiImport($this->config);

        }
        return [
           
            '0' => $import
        ];
    }
}
