<?php

namespace App\Repositories;

use App\Models\Vibrasi;
use App\Repositories\BaseRepository;

/**
 * Class VibrasiRepository
 * @package App\Repositories
 * @version November 4, 2021, 2:40 am UTC
*/

class VibrasiRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'equipments_id', 
        'unit_item_id',
        'zone',
        'bulan',
        'tahun'
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
        return Vibrasi::class;
    }

    public function trendGet($request){
        $table = $this->model->table;  

        $query = $this->model->select($table.".*", "unit_pembangkit.name as unit_pembangkit",  "equipments.name as equipment",  "unit_item.name as unit_item")
            ->leftJoin('equipments', 'equipments.id', '=', 'vibrasi.equipments_id')
            ->leftJoin('unit_pembangkit', 'unit_pembangkit.id', '=', 'equipments.unit_pembangkit_id')
            ->leftJoin('unit_item', 'unit_item.id', '=', 'vibrasi.unit_item_id'); 
 
        if(isset($request['unit_pembangkit_id'])){
            $query->where("unit_pembangkit.id", $request['unit_pembangkit_id']);
        }

        if(isset($request['equipments_id'])){
            $query->where("equipments.id", $request['equipments_id']);
        }

        if(isset($request['unit_item_id'])){
            $query->where("unit_item.id", $request['unit_item_id']);
        }   
        if(isset($request['tahun'])){
            $query->where("vibrasi.tahun",$request['tahun']); 
        }  
        $query->orderBy("vibrasi.bln_tahun",'asc');  
        
        return $query->get(); 
    }

    public function getIndexRelation($request){
        $table = $this->model->table; 

        /** set pagination */
        $showPerPage = isset($request['limit']) ? $request['limit'] : 10; 
        $page = isset($request['page']) ? $request['page'] : 1; 

        $query = $this->model->select($table.".*",  "unit_pembangkit.id as unit_pembangkit_id", "unit_pembangkit.name as unit_pembangkit",   "equipments.name as equipment", "unit_item.name as unit_item")
            ->leftJoin('equipments', 'equipments.id', '=', 'vibrasi.equipments_id')
            ->leftJoin('unit_pembangkit', 'unit_pembangkit.id', '=', 'equipments.unit_pembangkit_id')
            ->leftJoin('unit_item', 'unit_item.id', '=', 'vibrasi.unit_item_id');  

        if(isset($request['equipment'])){
            $query->where("equipments.name",'LIKE','%'.$request['equipment'].'%'); 
        }
        if(isset($request['unit'])){
            $query->where("unit_item.name",'LIKE','%'.$request['unit'].'%'); 
        } 
        if(isset($request['zone'])){
            $query->where("vibrasi.zone",'LIKE','%'.$request['zone'].'%'); 
        }
        if(isset($request['bulan'])){
            $query->where("vibrasi.bulan",$request['bulan']); 
        }
        if(isset($request['tahun'])){
            $query->where("vibrasi.tahun",$request['tahun']); 
        }  
        if(isset($request['analisis'])){
            $query->where("vibrasi.analisis",'LIKE','%'.$request['analisis'].'%'); 
        }
        if(isset($request['rekomendasi'])){
            $query->where("vibrasi.rekomendasi",'LIKE','%'.$request['rekomendasi'].'%'); 
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
                        ->orWhere("vibrasi.zone",'LIKE','%'.$search_keyword.'%')
                        ->orWhere("vibrasi.bulan",'LIKE','%'.$search_keyword.'%')
                        ->orWhere("vibrasi.tahun",'LIKE','%'.$search_keyword.'%') 
                        ->orWhere("vibrasi.analisis",'LIKE','%'.$search_keyword.'%')
                        ->orWhere("vibrasi.rekomendasi",'LIKE','%'.$search_keyword.'%');
            }); 
        }


        $sort = $this->sortBase($request);
        $schemaCheck = $this->model->connection ? $sort && \Schema::connection($this->model->connection)->hasColumn($this->model->table,$sort[0]) : $sort && \Schema::hasColumn($this->model->table,$sort[0]);
        
        if($schemaCheck){
            $query->orderBy($sort[0], $sort[1]);
        }else { 
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
