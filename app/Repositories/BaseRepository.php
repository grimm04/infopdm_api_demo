<?php

namespace App\Repositories;

use Illuminate\Container\Container as Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;

abstract class BaseRepository
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @var Application
     */
    protected $app;

    protected $ignoreParams  = ['sort_by', 'sort', 'keyword','page','limit','filter_date_by','from_date','to_date'];

    /**
     * @param Application $app
     *
     * @throws \Exception
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->makeModel();
    }

    /**
     * Get searchable fields array
     *
     * @return array
     */
    abstract public function getFieldsSearchable();

    /**
     * Configure the Model
     *
     * @return string
     */
    abstract public function model();

    /**
     * Make Model instance
     *
     * @throws \Exception
     *
     * @return Model
     */
    public function makeModel()
    {
        $model = $this->app->make($this->model());

        if (!$model instanceof Model) {
            throw new \Exception("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->model = $model;
    }

    /*
     * Paginate records for scaffold.
     *
     * @param int $perPage
     * @param array $columns
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     * 
     * Example=
     * $whereRelation = [
     *  {
     *   relation: 'userRoleId', 
     *   field: 'user_id', 
     *   operation: '=', 
     *   value: 9
     *  }
     * ]
     */
    public function paginate($columns = ['*'], $request, $with=[], $withCount=[], $whereRelation=null)
    {
        /** Set sorting */
        $sort = $this->sortBase($request);

        $showPerPage = isset($request['limit']) ? $request['limit'] : 10; 
        $page = isset($request['page']) ? $request['page'] : 1; 

        if($page==-1){ 
            return $this->all($request, null, null , ['*'], $with, $withCount, $sort, $whereRelation);
        }else{
            $query  = $this->allQuery($columns, $request, null, null, $with, $withCount,  $sort, $whereRelation);
        }

        /** !IMPORTANT DEBUG SHOW MYSQL QUERY */
        // print_r($query->toSql());exit;
       
        return $query->paginate($showPerPage, $columns);
    }

    /**
     * Build a query for retrieving all records.
     *
     * @param array $search
     * @param int|null $skip
     * @param int|null $limit
     * @param array|[] $with
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function allQuery($columns = ['*'], $request = [], $skip = null, $limit = null, $with=[], $withCount=[], $sort=null, $whereRelation)
    {
        $table = $this->model->table;
        $query = $this->model->newQuery()->select($columns);
        $query = $query->with(($this->model->with) ? $this->model->with : $with)->withCount(($this->model->withCount) ? $this->model->withCount : $withCount); 

        $schemaCheck = $this->model->connection ? $sort && \Schema::connection($this->model->connection)->hasColumn($this->model->table,$sort[0]) : $sort && \Schema::hasColumn($this->model->table,$sort[0]);
        
        if($schemaCheck){
            $query->orderBy($sort[0], $sort[1]);
        }

        /** Search using Like % {data} % */
        $query = $this->whereBase($query, $request);

        /*
        * EXAMPLE USAGE
        * $whereRelation = [
        *  {
        *   relation: 'userRoleId', 
        *   field: 'user_id', 
        *   operation: '=', 
        *   value: 9
        *  }
            $role_id = null;
            if($request['role_id']){
                $role_id = [
                    (object) [
                        "relation" => 'userRoleId',
                        "field" => 'role_id',
                        "operator" => '=',
                        "value" => $request['role_id'],
                    ]
                ];
            }
            
            $userManagement = $this->userManagementRepository->paginate(
                "*", 
                $request->except(['skip']), 
                ['unitPembangkitId:id,name','userRoleId:id,user_id,role_id','userRoleId.roleId:id,name,level'],
                [],
                $role_id
            );
        * ]
        */
        if($whereRelation){
            foreach($whereRelation as $kr => $rv){
                $firstStringValue = substr($rv->value,0,1);
                $firstTwoStringValue = substr($rv->value,0,2);
                
                $checkSingleOperator = array_search($firstStringValue, $this->allowOperatorComparisonQuery);
                $checkTwoOperator = array_search($firstTwoStringValue, $this->allowOperatorComparisonQuery);

                if($rv->value!='exist' && $rv->value!='empty' && $checkSingleOperator=='' && $checkTwoOperator==''){
                    $query = $query->whereRelation($rv->relation, $rv->field, $rv->operator, $rv->value);
                }
            }
        }
       

        if(isset($request['from_date']) && isset($request['to_date'])){
            $fromDate = $request['from_date'];
            $toDate = $request['to_date'];
            $toDate = (strlen($toDate)<=10) ? $toDate : $toDate; // $toDate.' 23:59'
            $fieldDateSearch = isset($this->model->date_search_field) ? $this->model->date_search_field : 'created_at';
            $fieldDateSearch = isset($request['filter_date_by']) ? $request['filter_date_by'] : $fieldDateSearch;
            
            if(in_array($fieldDateSearch, $this->getFieldsSearchable())){
                $query = $query->whereBetween($fieldDateSearch, [$fromDate, $toDate]);
            }
        }

        if (!is_null($skip)) {
            $query->skip($skip);
        }

        if (!is_null($limit)) {
            $query->limit($limit);
        }

        return $query;
    }

    public function sortBase($request){
        $sort = null;
        if(Arr::exists($request, 'sort_by')){
            $sort[0] = $request['sort_by'];
            $sort[1] = 'desc';

            if(Arr::exists($request, 'sort')){
                $sort[1] = $request['sort'];
            }
        } 

        return $sort;
    }

    public $allowOperatorComparisonQuery = array("<"=>"<", ">"=>">", "<="=>"<=", ">="=>">=", "!="=>"!");

    public function whereBase($query, $request){
        $table = $this->model->table;

        /** Search using Like % {data} % */
        $search = $request;
        $this->request = $search;
        
        if(Arr::exists($search, 'keyword') && $this->model->searchable){ 
            $query->where(function ($query) use($table, $search) {
                foreach($this->model->searchable as $key => $field){ 
                    if (in_array($field, $this->getFieldsSearchable())) {
                        if($key==0) $query->where("$table.$field", 'like', '%' . $search['keyword'] . '%');
                        else $query->orWhere("$table.$field", 'like', '%' . $search['keyword'] . '%');
                    } 
                }
            });
        }

        /** Search using == */
        $search = Arr::except($search, $this->ignoreParams); 
 
        if (count($search)) {
            foreach($search as $key => $value) { 
                if (in_array($key, $this->getFieldsSearchable())) {
                    $firstStringValue = substr($value,0,1);
                    $firstTwoStringValue = substr($value,0,2);
                    
                    $checkSingleOperator = array_search($firstStringValue, $this->allowOperatorComparisonQuery);
                    $checkTwoOperator = array_search($firstTwoStringValue, $this->allowOperatorComparisonQuery);
 
                    if($value=='empty'){
                        $query->where(function ($query) use($table, $key) {
                            $query->where("$table.$key",'=','')->orWhereNull("$table.$key");
                        });
                    }else if($value=='exist'){
                        $query->where(function ($query) use($table, $key) {
                            $query->where("$table.$key",'!=','')->orWhereNotNull("$table.$key");
                        });
                    }else if($checkTwoOperator){
                        $valueSkipTwoFirst = substr($value,2,strlen($value));
                        $query->where("$table.$key",$checkTwoOperator, $valueSkipTwoFirst);
                    }
                    else if($checkSingleOperator){
                        $valueSkipOneFirst = substr($value,1,strlen($value));
                        $query->where("$table.$key",$checkSingleOperator, $valueSkipOneFirst);
                    }
                    else{
                        $query->where("$table.$key", $value);
                    }
                }
            }
        }  
        return $query;
    }

    /**
     * Retrieve all records with given filter criteria
     *
     * @param array $search
     * @param int|null $skip
     * @param int|null $limit
     * @param array $columns
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */ 
    public function all($search = [], $skip = null, $limit = null, $columns = ['*'], $with=[], $withCount=[], $sort = [], $whereRelation=null)
    {
        $query = $this->allQuery($columns, $search, $skip, $limit, $with, $withCount, $sort, $whereRelation);

        // echo json_encode($query->toSql());exit;
        return $query->get($columns);
    }

    /**
     * Create model record
     *
     * @param array $input
     *
     * @return Model
     */
    public function create($input, $setUserId='')
    {
        if($setUserId=='id_user'){ $input['id_user'] = Auth::user()->id_user; }

        if(isset($input[0])){
            $model = $this->model->insert($input);
        }else{
            $model = $this->model->newInstance($input);
            $model->save();
        }

        return $model;
    }

    /**
     * Find model record for given id
     *
     * @param int $id
     * @param array $columns
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Model|null
     */
    public function find($id, $columns = ['*'], $with=[])
    {
        $query = $this->model->newQuery()
                             ->with(($this->model->with) ? $this->model->with : $with)
                             ->withCount(($this->model->withCount) ? $this->model->withCount : []);

        return $query->find($id, $columns);
    }

    /**
     * Update model record for given id
     *
     * @param array $input
     * @param int $id
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Model
     */
    public function update($input, $id, $setUserId='', $deleteFile='')
    {
        /** Set user_id field using auth */
        if($setUserId=='id_user'){ $input['id_user'] = Auth::user()->id_user; } 

        $query = $this->model->newQuery();

        $model = $query->findOrFail($id);

        /** Delete file */
        $deleteParam = explode(":", $deleteFile);
        $deleteEntity = (count($deleteParam)>1) ? $deleteParam[1] : null;
        if($deleteEntity && array_key_exists($deleteEntity, $input) && $model && $deleteParam[0]=='delete' && $deleteEntity && \File::exists(public_path($model->$deleteEntity))){
            \File::delete(public_path($model->$deleteEntity));
        }

        $model->fill($input);

        $model->save();

        return $model;
    }

    public function multiUpdate($params, $ids,$jml_pakai = null, $jml_kembali= null){ 
        
        foreach ($ids as $key => $id) {   
            $data = array(); 
            
            if($jml_pakai!= null){
                $pakai = $jml_pakai[$key];
                $kembali = $jml_kembali[$key] ; 
                if( $kembali >= $pakai){
                    foreach ($params as $keyinp => $val) {   
                        $data[$keyinp] = $val[$key]; 
                    }  
                    $data['status_pemakaian'] = "Selesai";
                }else{
                    $jum_selisih = $pakai - $kembali; 
                    $data['jumlah_pakai'] = $jum_selisih;
                }
            }   
            $query = $this->model->newQuery()->where($this->model->primaryKey, $id)->update($data); 
        }  
        return true;
    }

    public function updateIncreament($id,$field){
        $query = $this->model->newQuery();
        return $query->find($id)->increment($field);
    }

    /**
     * @param int $id
     *
     * @throws \Exception
     *
     * @return bool|mixed|null
     */
    public function delete($id, $relation=[])
    { 
        $relationArr = \is_array($relation) ? $relation : [$relation]; 
        $isAlreadyUsed = false;

        /** Checking relation multi table */
        foreach ($relationArr as $key => $value) {
            if($this->model->where($this->model->primaryKey, $id)->has($value)->exists()){
                $isAlreadyUsed = true;
                break;
            }
        }


        /** Deleting or Not */
        if($isAlreadyUsed){
            return ['status'=>false, 'message'=>'used'];
        }else{
            $query = $this->model->newQuery();

            $model = $query->findOrFail($id);
            $delete = $model->delete();

            return ['status'=>$delete, 'message'=>''];
        }
        
    }
}
