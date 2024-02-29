<?php

namespace App\Http\Controllers;

use App\Http\Requests\API\CreateTermografiAPIRequest;
use App\Http\Requests\API\UpdateTermografiAPIRequest;
use App\Models\Termografi;
use App\Repositories\TermografiRepository;
use Illuminate\Support\Facades\Validator;  
use App\Models\UnitPembangkit;  
use App\Imports\TransaksiImport;   
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Response;
use App\Helper;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Class TermografiController
 * @package App\Http\Controllers\API
 */

class TermografiAPIController extends AppBaseController
{
    /** @var  TermografiRepository */
    private $termografiRepository; 
    private $with = ['unitItemId:id,name','equipmentId:id,unit_pembangkit_id,name'];
    
    private $helper;


    public function __construct(TermografiRepository $termografiRepo)
    {
        $this->termografiRepository = $termografiRepo;
        $this->helper = new Helper();

    }

    /**
     * @param Request $request
     * @return Response
     *
     * @SWG\Get(
     *      path="/termografi",
     *      summary="Get a listing of the Termografi.",
     *      tags={"Termografi"},
     *      description="Get all Termografi",
     *      produces={"application/json"}, 
     *      security={ {"Bearer": {} }},
     *      @SWG\Parameter(
     *          name="unit_pembangkit_id",
     *          description="unit_pembangkit_id",
     *          type="integer",
     *          required=false,
     *          in="query"
     *      ),
     *      @SWG\Parameter(
     *          name="equipments_id",
     *          description="equipments_id",
     *          type="integer",
     *          required=false,
     *          in="query"
     *      ),
     *      @SWG\Parameter(
     *          name="unit_item_id",
     *          description="unit",
     *          type="integer",
     *          required=false,
     *          in="query"
     *      ),
     *      @SWG\Parameter(
     *          name="equipment",
     *          description="equipment",
     *          type="string",
     *          required=false,
     *          in="query"
     *      ),
     *      @SWG\Parameter(
     *          name="unit",
     *          description="unit",
     *          type="string",
     *          required=false,
     *          in="query"
     *      ), 
     *      @SWG\Parameter(
     *          name="bulan",
     *          description="bulan",
     *          type="string",
     *          required=false,
     *          in="query"
     *      ),
     *      @SWG\Parameter(
     *          name="tahun",
     *          description="tahun",
     *          type="string",
     *          required=false,
     *          in="query"
     *      ), 
     *      @SWG\Parameter(
     *          name="status",
     *          description="status",
     *          type="string",
     *          required=false,
     *          in="query"
     *      ),
     *      @SWG\Parameter(
     *          name="keterangan",
     *          description="keterangan",
     *          type="string",
     *          required=false,
     *          in="query"
     *      ),
     *      @SWG\Parameter(
     *          name="analisis",
     *          description="analisis",
     *          type="string",
     *          required=false,
     *          in="query"
     *      ),
     *      @SWG\Parameter(
     *          name="rekomendasi",
     *          description="rekomendasi",
     *          type="string",
     *          required=false,
     *          in="query"
     *      ),
     *      @SWG\Parameter(
     *          name="status",
     *          description="status",
     *          type="string",
     *          required=false,
     *          in="query"
     *      ),
     *      @SWG\Parameter(
     *          name="keyword",
     *          description="keyword search data by 'bulan','tahun','status','keterangan','analisis','rekomendasi'",
     *          type="string",
     *          required=false,
     *          in="query"
     *      ), 
     *      @SWG\Parameter(
     *          name="sort_by",
     *          description="sorting by field",
     *          type="string",
     *          required=false,
     *          in="query"
     *      ),
     *      @SWG\Parameter(
     *          name="sort",
     *          description="desc or asc sort",
     *          type="string",
     *          required=false,
     *          in="query"
     *      ),
     *      @SWG\Parameter(
     *          name="page",
     *          description="page number. isi -1 jika mau tanpa pagination",
     *          type="integer",
     *          required=true,
     *          in="query",
     *          default=1
     *      ),
     *      @SWG\Parameter(
     *          name="limit",
     *          description="show per page",
     *          type="integer",
     *          required=true,
     *          in="query",
     *          default=10
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @SWG\Property(
     *                  property="data",
     *                  type="array",
     *                  @SWG\Items(ref="#/definitions/Termografi")
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function index(Request $request)
    {
         
        $termografi = $this->termografiRepository->getIndexRelation($request->except(['skip']));

        return $this->sendResponse($termografi->toArray(), 'response.termografi.view');
    }
    
    /**
     * @param Request $request
     * @return Response
     *
     * @SWG\Get(
     *      path="/termografi/trend-data",
     *      summary="Get a listing of the Trend Data termografi.",
     *      tags={"Termografi"},
     *      description="Get all termografi",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }},
     *      @SWG\Parameter(
     *          name="unit_pembangkit_id",
     *          description="unit_pembangkit_id",
     *          type="integer",
     *          required=true,
     *          in="query"
     *      ),
     *      @SWG\Parameter(
     *          name="equipments_id",
     *          description="equipments_id",
     *          type="integer",
     *          required=true,
     *          in="query"
     *      ),
     *      @SWG\Parameter(
     *          name="unit_item_id",
     *          description="unit_item_id",
     *          type="integer",
     *          required=true,
     *          in="query"
     *      ), 
     *       @SWG\Parameter(
     *          name="tahun",
     *          description="tahun",
     *          type="string",
     *          required=false,
     *          in="query"
     *      ), 
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */

     //get trend data
     public function data_trend(Request $request)
     {
        
        $input = $request->all();

        $validator = Validator::make($input, [
            'unit_pembangkit_id' => 'integer|exists:unit_pembangkit,id',   
            'equipments_id' => 'integer|exists:mysql-app.equipments,id',   
            'unit_item_id' => 'integer|exists:mysql-app.unit_item,id', 
            'tahun' => 'integer',    
        ]);

        if ($validator->fails())
        {
            return $this->sendError('response.termografi.not_found', 422, ['invalid'=>$validator->errors()]);
        }
        $termografi = $this->termografiRepository->trendGet($request->except(['skip']));

        if(!isset($termografi)){
            return $this->sendError('response.termografi.not_found', 422); 
        }  
        $config = $this->helper->unitPembangkit($request->unit_pembangkit_id);
        $data_config = json_decode($config->termografi_config_detail); 
        $detail = [];
        if(!empty($data_config)){ 
            foreach ($data_config as $det) {
                $trend_data = [];
                if(isset($termografi)){
                    foreach ($termografi as $data) { 
                        $data_detail = json_decode($data->data_detail);
                        $val = 0;
                        if($data_detail!= null){ 
                            foreach ($data_detail as $item) {
                                if($det->no == $item->no){ 
                                    $val = $item->value;
                                }
                            } 
                        }  
                        $trend_data[] = [ 
                            'label'=> $data->bulan. '-'.$data->tahun, 
                            'bln_tahun' => $data->bln_tahun!= null ? date('Ymd', strtotime($data->bln_tahun)) :null,
                            'value'=> $val != "" || $val != null ? $val : 0
                        ];
                    }
                }
                 
                $detail[] = [  
                    'name'=> $det->name, 
                    'no' => $det->no,
                    'key'=>$det->key, 
                    'data' =>$trend_data
                ] ;
            }  
        }else {
            $detail[] = [  
                'name'=> null, 
                'no' => null,
                'key'=>null, 
                'data' =>null
            ] ;
        }
        return $this->sendResponse($detail, 'response.termografi.view'); 
        
     }
    /**
     * @param CreateTermografiAPIRequest $request
     * @return Response
     *
     * @SWG\Post(
     *      path="/termografi",
     *      summary="Store a newly created Termografi in storage",
     *      tags={"Termografi"},
     *      description="Store Termografi",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }}, 
     *      @SWG\Parameter(
     *          name="unit_pembangkit_id",
     *          description="id of unit pembangkit",
     *          type="integer",
     *          required=true,
     *          in="query"
     *      ),
     *      @SWG\Parameter(
     *          name="data_termografi",
     *          description="Upload Data Termografi",
     *          type="file",
     *          required=true,
     *          in="formData"
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(
     *                  property="success",
     *                  type="boolean"
     *              ), 
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'unit_pembangkit_id' => 'required|integer|exists:unit_pembangkit,id',
            'data_termografi' => 'required|mimes:xlsx,csv,xls'
        ]);

        if ($validator->fails())
        {
            return $this->sendError('response.termografi.failed_create', 422, ['invalid'=>$validator->errors()]);
        }
        $unit_pembangkit =  new UnitPembangkit();
        $data_json = $unit_pembangkit->where('id',$request->unit_pembangkit_id)->pluck('termografi_config_detail');

        $config = $this->helper->encode_config($data_json[0]); 
        
        Excel::import(new TransaksiImport($config,'termografi'), $request->file('data_termografi')); 

        $termografi = "";

        return $this->sendResponse($termografi, 'response.termografi.create');
    }

    /**
     * @param int $id
     * @return Response
     *
     * @SWG\Get(
     *      path="/termografi/{id}",
     *      summary="Display the specified Termografi",
     *      tags={"Termografi"},
     *      description="Get Termografi",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of Termografi",
     *          type="integer",
     *          required=true,
     *          in="path"
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @SWG\Property(
     *                  property="data",
     *                  ref="#/definitions/Termografi"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function show($id)
    {
        /** @var Termografi $termografi */
        $termografi = $this->termografiRepository->find($id, '*',$this->with);

        if (empty($termografi)) {
            return $this->sendError('response.termografi.not_found');
        }

        return $this->sendResponse($termografi->toArray(), 'response.termografi.view');
    }

    /**
     * @param int $id
     * @param UpdateTermografiAPIRequest $request
     * @return Response
     *
     * @SWG\Put(
     *      path="/termografi/{id}",
     *      summary="Update the specified Termografi in storage",
     *      tags={"Termografi"},
     *      description="Update Termografi",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of Termografi",
     *          type="integer",
     *          required=true,
     *          in="path"
     *      ),
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="Termografi that should be updated",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/Termografi")
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @SWG\Property(
     *                  property="data",
     *                  ref="#/definitions/Termografi"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateTermografiAPIRequest $request)
    {
        $input = $request->all();

        /** @var Termografi $termografi */
        $termografi = $this->termografiRepository->find($id);

        if (empty($termografi)) {
            return $this->sendError('response.termografi.not_found');
        }

        $validatorParams = [];  
        if(isset($request->unit_item_id)  && $termografi->unit_item_id != $request->unit_item_id){
            $validatorParams['unit_item_id']   = 'integer|exists:mysql-app.unit_item,id';  
        }
        if(isset($request->equipments_id)  && $termografi->equipments_id!= $request->equipments_id){
            $validatorParams['equipments_id']   = 'integer|exists:mysql-app.equipments,id';
        }
        if(isset($request->bulan) && $termografi->bulan != $request->bulan){
            $validatorParams['bulan']   = 'string'; 
            $input['bulan'] = ucfirst($request->bulan);
        }
        if(isset($request->tahun) && $termografi->tahun != $request->tahun){
            $validatorParams['tahun']   = 'integer';
        }


        $validator = Validator::make($input, $validatorParams);

        /** InValid */
        if ($validator->fails())
        { 
            return $this->sendError('response.termografi.failed_update', 422, ['invalid'=>$validator->errors()]);
        }
 

        if(isset($request->bulan) || isset($request->tahun)){ 
            if(isset($request->bulan)){
                $nmonth = $request->bulan;
            }else {
                $nmonth = $termografi->bulan;
            }   
            $nmonth = $this->helper->month(ucfirst($nmonth)); 
            if($nmonth == false){ 
                return $this->sendError('Bulan yang anda masukan tidak sesuai', 422); 
            }else{
                $nmonth = $nmonth;
                $input['bulan'] = $request->bulan; 
            }
 
            if(isset($request->tahun)){
                $tahun = $request->tahun;
            }else {
                $tahun = $termografi->tahun;
            }
            // return $nmonth;
            $bln_tahun = $tahun .'-'.$nmonth.'-01';
            $input['bln_tahun'] = $bln_tahun; 
        }      
        


        $termografi = $this->termografiRepository->update($input, $id);

        return $this->sendResponse($termografi->toArray(),'response.termografi.update');
    }

    /**
     * @param int $id
     * @return Response
     *
     * @SWG\Delete(
     *      path="/termografi/{id}",
     *      summary="Remove the specified Termografi from storage",
     *      tags={"Termografi"},
     *      description="Delete Termografi",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of Termografi",
     *          type="integer",
     *          required=true,
     *          in="path"
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @SWG\Property(
     *                  property="data",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function destroy($id)
    {
        /** @var Termografi $termografi */
        $termografi = $this->termografiRepository->find($id);

        if (empty($termografi)) {
            return $this->sendError('response.termografi.not_found');
        }

        $termografi->delete();

        return $this->sendSuccess('response.termografi.delete');
    }
}
