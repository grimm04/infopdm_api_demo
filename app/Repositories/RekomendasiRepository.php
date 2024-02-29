<?php

namespace App\Repositories;

use App\Models\Rekomendasi;
use App\Repositories\BaseRepository;

/**
 * Class RekomendasiRepository
 * @package App\Repositories
 * @version November 3, 2021, 6:47 am UTC
*/

class RekomendasiRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'equipments_id',
        'unit_item_id', 
        'analisis',
        'rekomendasi',
        'status'
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
        return Rekomendasi::class;
    }
    
    public function getIndexRelation($request){
        $table = $this->model->table; 

        /** set pagination */
        $showPerPage = isset($request['limit']) ? $request['limit'] : 10; 
        $page = isset($request['page']) ? $request['page'] : 1; 

        $query = $this->model->select($table.".*", "unit_pembangkit.name as unit_pembangkit",  "equipments.name as equipment",  "unit_item.name as unit_item")
            ->leftJoin('equipments', 'equipments.id', '=', 'rekomendasi.equipments_id')
            ->leftJoin('unit_pembangkit', 'unit_pembangkit.id', '=', 'equipments.unit_pembangkit_id')
            ->leftJoin('unit_item', 'unit_item.id', '=', 'rekomendasi.unit_item_id'); 

        $query = $this->whereBase($query, $request);

        // if(isset($request['keyword'])){
        //     $query->orWhere("unit_item.name",'LIKE','%'.$request['keyword'].'%');
        //     $query->orWhere("equipments.name",'LIKE','%'.$request['keyword'].'%');
        //     $query->orWhere("rekomendasi.status",'LIKE','%'.$request['keyword'].'%');
        //     $query->orWhere("rekomendasi.akurasi",'LIKE','%'.$request['keyword'].'%');
        // }
        if(isset($request['keyword'])){ 
            $search_keyword = $request['keyword']; 
            $query->where(function ($query) use ($search_keyword) {   
                $query->orWhere("unit_item.name",'LIKE','%'.$search_keyword.'%')
                        ->orWhere("equipments.name",'LIKE','%'.$search_keyword.'%') 
                        ->orWhere("rekomendasi.status",'LIKE','%'.$search_keyword.'%')
                        ->orWhere("rekomendasi.akurasi",'LIKE','%'.$search_keyword.'%')
                        ->orWhere("rekomendasi.feedback",'LIKE','%'.$search_keyword.'%')
                        ->orWhere("rekomendasi.status",'LIKE','%'.$search_keyword.'%')
                        ->orWhere("rekomendasi.rekomendasi",'LIKE','%'.$search_keyword.'%'); 
            }); 
        }

        if(isset($request['equipment'])){
            $query->where("equipments.name",'LIKE','%'.$request['equipment'].'%'); 
        }
        if(isset($request['unit'])){
            $query->where("unit_item.name",'LIKE','%'.$request['unit'].'%'); 
        }
        if(isset($request['status'])){
            $query->where("rekomendasi.status",'LIKE','%'.$request['status'].'%'); 
        }
        if(isset($request['tanggal_open'])){
            $query->whereDate("rekomendasi.tanggal_open",$request['tanggal_open']); 
        }
        if(isset($request['tanggal_closed'])){
            $query->whereDate("rekomendasi.tanggal_closed",$request['tanggal_closed']); 
        }
        if(isset($request['akurasi'])){
            $query->where("rekomendasi.akurasi",'LIKE','%'.$request['akurasi'].'%'); 
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


        $sort = $this->sortBase($request);
        $schemaCheck = $this->model->connection ? $sort && \Schema::connection($this->model->connection)->hasColumn($this->model->table,$sort[0]) : $sort && \Schema::hasColumn($this->model->table,$sort[0]);
        
        if($schemaCheck){
            $query->orderBy($sort[0], $sort[1]);
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
