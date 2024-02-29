<?php

namespace App\Repositories;

use App\Models\NonRutinVibrasi;
use App\Repositories\BaseRepository;

/**
 * Class NonRutinVibrasiRepository
 * @package App\Repositories
 * @version November 3, 2021, 4:11 am UTC
*/

class NonRutinVibrasiRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'equipments_id', 
        'unit_item_id',
        'zone',
        'date'
    ];

    /**
     * Return searchable fields
     *
     * @return array
     */
    public function getFieldsSearchable()
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return NonRutinVibrasi::class;
    }

    public function month($month){
        switch ($month) {
            case 'January':
                return $month = '1';
            case 'Februari':
                return $month = '2';
            case 'Maret':
                return $month = '3';
            case 'April':
                return $month = '4';
            case 'Mei':
                return $month = '5';
            case 'Juni':
                return $month = '6';
            case 'Juli':
                return $month = '7';
            case 'Agustus':
                return $month = '8';
            case 'September':
                return $month = '9';
            case 'Oktober':
                return $month = '10';
            case 'November':
                return $month = '11';
            case 'Desember':
                return $month = '12';
                // code here
                break;
        }
    }
    public function dataGet($request){
        $table = $this->model->table;   
        $month = $this->month($request['bulan']);
        $query = $this->model->select($table.".*", "unit_pembangkit.name as unit_pembangkit","equipments.name as equipment","unit_item.name as unit_item")
            ->leftJoin('equipments', 'equipments.id', '=', 'non_rutin_vibrasi.equipments_id')
            ->leftJoin('unit_pembangkit', 'unit_pembangkit.id', '=', 'equipments.unit_pembangkit_id')
            ->leftJoin('unit_item', 'unit_item.id', '=', 'non_rutin_vibrasi.unit_item_id'); 
 
        if(isset($request['unit_pembangkit_id'])){
            $query->where("unit_pembangkit.id", $request['unit_pembangkit_id']);
        }

        if(isset($request['equipments_id'])){
            $query->where("equipments.id", $request['equipments_id']);
        }

        if(isset($request['unit_item_id'])){
            $query->where("unit_item.id", $request['unit_item_id']);
        }
        if(isset($request['bulan'])){
            $query->whereMonth("non_rutin_vibrasi.date", $month);
        }
        if(isset($request['tahun'])){
            $query->whereYear("non_rutin_vibrasi.date", $request['tahun']);
        }
 
        
        return $query->get(); 
    }
    
    public function getIndexRelation($request){
        $table = $this->model->table; 

        /** set pagination */
        $showPerPage = isset($request['limit']) ? $request['limit'] : 10; 
        $page = isset($request['page']) ? $request['page'] : 1; 

        $query = $this->model->select($table.".*",  "unit_pembangkit.id as unit_pembangkit_id","unit_pembangkit.name as unit_pembangkit",  "equipments.name as equipment", "unit_item.name as unit_item")
            ->leftJoin('equipments', 'equipments.id', '=', 'non_rutin_vibrasi.equipments_id')
            ->leftJoin('unit_pembangkit', 'unit_pembangkit.id', '=', 'equipments.unit_pembangkit_id')
            ->leftJoin('unit_item', 'unit_item.id', '=', 'non_rutin_vibrasi.unit_item_id'); 
 

        
        if(isset($request['date'])){
            $query->whereDate("non_rutin_vibrasi.date",$request['date']); 
        } 
        
        if(isset($request['equipment'])){
            $query->where("equipments.name",'LIKE','%'.$request['equipment'].'%'); 
        }
        if(isset($request['unit'])){
            $query->where("unit_item.name",'LIKE','%'.$request['unit'].'%'); 
        }
        if(isset($request['zone'])){
            $query->where("non_rutin_vibrasi.zone",$request['zone']); 
        }
        if(isset($request['date'])){
            $query->whereDate("non_rutin_vibrasi.date", $request['date']); 
        }

        if(isset($request['unit_pembangkit_id'])){
            $query->where("unit_pembangkit.id", $request['unit_pembangkit_id']);
        }

        if(isset($request['equipments_id'])){
            $query->where("equipments.id", $request['equipments_id']);
        }

        if(isset($request['unit_item_id'])){
            $query->where("unit_item.id", $request['unit_item_id']);
        }

        if(isset($request['keyword'])){ 
            $search_keyword = $request['keyword']; 
            $query->where(function ($query) use ($search_keyword) {   
                $query->orWhere("unit_item.name",'LIKE','%'.$search_keyword.'%')
                        ->orWhere("equipments.name",'LIKE','%'.$search_keyword.'%') 
                        ->orWhere("non_rutin_vibrasi.zone",'LIKE','%'.$search_keyword.'%')
                        ->orWhere("non_rutin_vibrasi.time",'LIKE','%'.$search_keyword.'%')
                        ->orWhere("non_rutin_vibrasi.keterangan",'LIKE','%'.$search_keyword.'%')
                        ->orWhere("non_rutin_vibrasi.rekomendasi",'LIKE','%'.$search_keyword.'%'); 
            }); 
        }


        $sort = $this->sortBase($request);
        
        $schemaCheck = $this->model->connection ? $sort && \Schema::connection($this->model->connection)->hasColumn($this->model->table,$sort[0]) : $sort && \Schema::hasColumn($this->model->table,$sort[0]);
        
        if($schemaCheck){
            $query->orderBy($sort[0], $sort[1]);
        } else { 
            if(isset($request['sort_by']) == 'equipment'){ 
                $query->orderBy('equipments.name', $sort[1]);
            }
            if(isset($request['sort_by']) == 'unit_item'){
                $query->orderBy('unit_item.name', $sort[1]); 
            }  
        }

        /** Set sorting */
        
        if($page==-1){
            return $query->get($showPerPage);
        }
        else{
            return $query->paginate($showPerPage);
        }
    }
}
