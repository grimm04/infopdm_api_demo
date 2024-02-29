<?php

namespace App\Http\Controllers;

use App\Http\Requests\API\CreateVibrasiAPIRequest;
use App\Http\Requests\API\UpdateVibrasiAPIRequest;
use Illuminate\Support\Facades\Validator; 
use App\Models\Vibrasi;
use App\Models\UnitPembangkit;
use App\Imports\VibrasiImport; 
use App\Imports\TransaksiImport; 
use App\Repositories\VibrasiRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Response;
use Maatwebsite\Excel\Facades\Excel;
use App\Helper;


/**
 * Class VibrasiController
 * @package App\Http\Controllers\API
 */

class VibrasiAPIController extends AppBaseController
{
    /** @var  VibrasiRepository */
    private $vibrasiRepository;   
    private $with = ['unitItemId:id,name','equipmentId:id,unit_pembangkit_id,name'];

    private $helper;

    public function __construct(VibrasiRepository $vibrasiRepo)
    {
        $this->vibrasiRepository = $vibrasiRepo; 
        $this->helper = new Helper();
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @SWG\Get(
     *      path="/vibrasi",
     *      summary="Get a listing of the Vibrasi.",
     *      tags={"Vibrasi"},
     *      description="Get all Vibrasi",
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
     *          description="unit_item_id",
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
     *          name="zone",
     *          description="zone",
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
     *          name="keyword",
     *          description="keyword search data by 'zone','bulan','tahun,'analisis','rekomendasi'",
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
     *                  @SWG\Items(ref="#/definitions/Vibrasi")
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
          
        $vibrasi = $this->vibrasiRepository->getIndexRelation($request->except(['skip']));

        return $this->sendResponse($vibrasi->toArray(),'response.vibrasi.view');
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @SWG\Get(
     *      path="/vibrasi/trend-data",
     *      summary="Get a listing of the Trend Data Vibrasi.",
     *      tags={"Vibrasi"},
     *      description="Get all Vibrasi",
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
     *  @SWG\Parameter(
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
            return $this->sendError('response.vibrasi.not_found', 422, ['invalid'=>$validator->errors()]);
        }
        
        $vibrasi = $this->vibrasiRepository->trendGet($request->except(['skip']));

        // return $vibrasi;
        if(!isset($vibrasi)){
            return $this->sendError('response.vibrasi.not_found',422); 
        } 

        // return $vibrasi;

        $config = $this->helper->unitPembangkit($request->unit_pembangkit_id);
        $data_config = json_decode($config->vibrasi_config_detail); 
           $detail = [];
           if(!empty($data_config)){
                foreach ($data_config as $det) {
                    $trend_data = [];
                    // return $vibrasi;
                    if(isset($vibrasi)){
                        foreach ($vibrasi as $data) { 
                            $data_detail = json_decode($data->data_detail);
                            // return $data_detail; 
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
            
        return $this->sendResponse($detail, 'response.vibrasi.view'); 
       
    }
    /**
     * @param CreateVibrasiAPIRequest $request
     * @return Response
     *
     * @SWG\Post(
     *      path="/vibrasi",
     *      summary="Store a newly created Vibrasi in storage",
     *      tags={"Vibrasi"},
     *      description="Store Vibrasi",
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
     *          name="data_vibrasi",
     *          description="Upload dataVibrasi",
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
            'data_vibrasi' => 'required|mimes:xlsx,csv,xls'
        ]);

        if ($validator->fails())
        {
            return $this->sendError('response.vibrasi.failed_create', 422, ['invalid'=>$validator->errors()]);
        }
        $vibrasi =  new UnitPembangkit();
        $data_json = $vibrasi->where('id',$request->unit_pembangkit_id)->pluck('vibrasi_config_detail');
        
        
        $config = $this->helper->encode_config($data_json[0]);  
        // return $config;
        
        Excel::import(new TransaksiImport($config,'vibrasi'), $request->file('data_vibrasi'));
        $vibrasi  = "";

        return $this->sendResponse($vibrasi, 'response.vibrasi.create');
    }

    /**
     * @param int $id
     * @return Response
     *
     * @SWG\Get(
     *      path="/vibrasi/{id}",
     *      summary="Display the specified Vibrasi",
     *      tags={"Vibrasi"},
     *      description="Get Vibrasi",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of Vibrasi",
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
     *                  ref="#/definitions/Vibrasi"
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
        /** @var Vibrasi $vibrasi */
        $vibrasi = $this->vibrasiRepository->find($id,"*", $this->with);

        if (empty($vibrasi)) {
            return $this->sendError('response.vibrasi.not_found');
        }

        return $this->sendResponse($vibrasi->toArray(),'response.vibrasi.view');
    }

    /**
     * @param int $id
     * @param UpdateVibrasiAPIRequest $request
     * @return Response
     *
     * @SWG\Put(
     *      path="/vibrasi/{id}",
     *      summary="Update the specified Vibrasi in storage",
     *      tags={"Vibrasi"},
     *      description="Update Vibrasi",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of Vibrasi",
     *          type="integer",
     *          required=true,
     *          in="path"
     *      ),
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="Vibrasi that should be updated",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/Vibrasi")
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
     *                  ref="#/definitions/Vibrasi"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateVibrasiAPIRequest $request)
    {
        $input = $request->all();

        /** @var Vibrasi $vibrasi */
        $vibrasi = $this->vibrasiRepository->find($id);

        if (empty($vibrasi)) {
            return $this->sendError('response.vibrasi.not_found');
        }
        $validatorParams = [];  

        if(isset($request->unit_item_id)  && $vibrasi->unit_item_id != $request->unit_item_id){
            $validatorParams['unit_item_id']   = 'integer|exists:mysql-app.unit_item,id';
        }
        if(isset($request->equipments_id)  && $vibrasi->equipments_id!= $request->equipments_id){
            $validatorParams['equipments_id']   = 'integer|exists:mysql-app.equipments,id';
        }
        if(isset($request->bulan) && $vibrasi->bulan != $request->bulan){
            $validatorParams['bulan']   = 'string';
            $input['bulan'] = strtolower($request->bulan); 
        }
        if(isset($request->tahun) && $vibrasi->tahun != $request->tahun){
            $validatorParams['tahun']   = 'integer';
        }

        $validator = Validator::make($input, $validatorParams);

        /** InValid */
        if ($validator->fails())
        { 
            return $this->sendError('response.vibrasi.failed_update', 422, ['invalid'=>$validator->errors()]);
        }
 
        if(isset($request->bulan) || isset($request->tahun)){ 
            if(isset($request->bulan)){
                $nmonth = $request->bulan;
            }else {
                $nmonth = $vibrasi->bulan;
            }   
            $nmonth = $this->helper->month(strtolower($nmonth)); 
            if($nmonth == false){ 
                return $this->sendError('Bulan yang anda masukan tidak sesuai', 422); 
            }else{
                $nmonth = $nmonth;
                $input['bulan'] = strtolower($request->bulan); 
            }
 
            if(isset($request->tahun)){
                $tahun = $request->tahun;
            }else {
                $tahun = $vibrasi->tahun;
            } 
            $bln_tahun = $tahun .'-'.$nmonth.'-01';
            $input['bln_tahun'] = $bln_tahun; 
        }    
 
        $vibrasi = $this->vibrasiRepository->update($input, $id);

        return $this->sendResponse($vibrasi->toArray(), 'response.vibrasi.update');
    }

    /**
     * @param int $id
     * @return Response
     *
     * @SWG\Delete(
     *      path="/vibrasi/{id}",
     *      summary="Remove the specified Vibrasi from storage",
     *      tags={"Vibrasi"},
     *      description="Delete Vibrasi",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of Vibrasi",
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
        /** @var Vibrasi $vibrasi */
        $vibrasi = $this->vibrasiRepository->find($id);

        if (empty($vibrasi)) {
            return $this->sendError('response.vibrasi.not_found');
        }

        $vibrasi->delete();

        return $this->sendSuccess('response.vibrasi.delete');
    }
}
