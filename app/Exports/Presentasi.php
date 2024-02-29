<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class Presentasi implements FromView
{
    protected $data;

    function __construct($data) {
            $this->data = $data;
    }
    
    /**
    * @return \Illuminate\Support\FromView
    */
    public function view(): View
    {
        return view('exports.presentasi', [
            'data' => $this->data
        ]);
    }
}
