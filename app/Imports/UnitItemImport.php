<?php

namespace App\Imports;
use App\Models\UnitItem; 
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class UnitItemImport implements ToModel,WithHeadingRow,WithValidation
{   
    public function __construct($unit_pembangkit_id){ 
        $this->unit_pembangkit_id = $unit_pembangkit_id; 
    }
   /**
    * @param Collection $collection
    */
    public function model(array $row)
    {    
        return new UnitItem([
            'unit_pembangkit_id'  => $this->unit_pembangkit_id,
            'name'      => $row['name'],  
        ]);
    }
    public function rules(): array
    {
        return [
            'unit_pembangkit_id' => 'integer|exists:mysql-app.unit_pembangkit,id',
            'name' => 'required|string|max:250|unique:mysql-app.unit_item,name,NULL,id,unit_pembangkit_id,'. $this->unit_pembangkit_id, 
        ]; 
    }
}
