<?php

namespace App\Imports;

use App\Models\Equipment; 
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class EquipmentImport implements ToModel,WithHeadingRow,WithValidation
{
    public function __construct($unit_pembangkit_id){ 
        $this->unit_pembangkit_id = $unit_pembangkit_id; 
    }
   /**
    * @param Collection $collection
    */
    public function model(array $row)
    {    
        return new Equipment([
            'unit_pembangkit_id'  => $this->unit_pembangkit_id,
            'name'      => $row['name'],  
            'kks'       => $row['kks'],  
            'daya'       => $row['daya'],  
            'rpm'       => $row['rpm'],  
            'note'      => $row['note'],  
        ]);
    }
    public function rules(): array
    {
        return [
            'unit_pembangkit_id' => 'integer|exists:mysql-app.unit_pembangkit,id',
            'name' => 'required|string|max:250|unique:mysql-app.equipments,name,NULL,id,unit_pembangkit_id,'. $this->unit_pembangkit_id,
            'kks'=>'nullable', 
            'daya'=>'nullable|integer', 
            'rpm'=>'nullable|integer', 
            'note'=>'nullable|string', 
        ]; 
    }
}
