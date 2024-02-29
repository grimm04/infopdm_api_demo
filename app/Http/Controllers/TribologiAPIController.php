<?php

namespace App\Http\Controllers;

use App\Http\Requests\API\CreateTribologiAPIRequest;
use App\Http\Requests\API\UpdateTribologiAPIRequest;
use App\Models\Tribologi;
use App\Repositories\TribologiRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Response;
use Illuminate\Support\Facades\Validator;

use App\Helper;
/**
 * Class TribologiController
 * @package App\Http\Controllers\API
 */

class TribologiAPIController extends AppBaseController
{
    /** @var  TribologiRepository */
    private $tribologiRepository;
    private $with = ['unitItemId:id,name','equipmentId:id,unit_pembangkit_id,name'];
    private $helper;


    public function __construct(TribologiRepository $tribologiRepo)
    {
        $this->tribologiRepository = $tribologiRepo;
        $this->helper = new Helper();

    }

    /**
     * @param Request $request
     * @return Response
     *
     * @SWG\Get(
     *      path="/tribologi",
     *      summary="Get a listing of the Tribologi.",
     *      tags={"Tribologi"},
     *      description="Get all Tribologi",
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
     *          description="Unit Item",
     *          type="integer",
     *          required=false,
     *          in="query"
     *      ), 
     *      @SWG\Parameter(
     *          name="keyword",
     *          description="keyword search data by 'bulan','tahun','status'",
     *          type="string",
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
     *          name="status",
     *          description="status",
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
     *                  @SWG\Items(ref="#/definitions/Tribologi")
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
        $tribologi = $this->tribologiRepository->getIndexRelation($request->except(['skip']));
         
        return $this->sendResponse($tribologi->toArray(), 'response.tribologi.view');
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @SWG\Get(
     *      path="/tribologi/trend-data",
     *      summary="Get a listing of the Trend Data tribologi.",
     *      tags={"Tribologi"},
     *      description="Get all tribologi",
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
            return $this->sendError('response.tribologi.not_found', 422, ['invalid'=>$validator->errors()]);
        }
         
        $tribologi = $this->tribologiRepository->trendGet($request->except(['skip']));

        if(!isset($tribologi)){
            return $this->sendError('response.tribologi.not_found',422); 
        }   

        $config = $this->helper->unitPembangkit($request->unit_pembangkit_id);
        $data_config = json_decode($config->tribologi_config_detail); 
        $detail = [];
        if(!empty($data_config)){ 

            foreach ($data_config as $det) {
                $trend_data = [];
                if(isset($tribologi)){
                    foreach ($tribologi as $data) { 
                        $data_detail = json_decode($data->data_detail);
                        $val = 0;
                        $var = $det->key;
                        if($data_detail!= null){
                            foreach ($data_detail as $item) {
                                if($det->no == $item->no){ 
                                    $val = $item->$var;
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
        return $this->sendResponse($detail, 'response.tribologi.view');  
     }

    /**
     * @param CreateTribologiAPIRequest $request
     * @return Response
     *
     * @SWG\Post(
     *      path="/tribologi",
     *      summary="Store a newly created Tribologi in storage",
     *      tags={"Tribologi"},
     *      description="Store Tribologi",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }},
     *@SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="Tribologi that should be stored",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/Tribologi")
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
     *                  ref="#/definitions/Tribologi"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateTribologiAPIRequest $request)
    {
        $input = $request->all();

        $validatorParams = [ 
            'equipments_id' => 'required|integer|exists:mysql-app.equipments,id', 
            'unit_item_id' => 'required|integer|exists:mysql-app.unit_item,id',
            'bulan' => 'required',
            'tahun' => 'required'
        ];

        $validator = Validator::make($input, $validatorParams);

        /** InValid */
        if ($validator->fails())
        {
            return $this->sendError('response.tribologi.failed_create', 422, ['invalid'=>$validator->errors()]);
        }
        $nmonth = $this->helper->month($request->bulan);
        $bln_tahun = $request->tahun .'-'.$nmonth.'-01';
        $input['bln_tahun'] = $bln_tahun;    

        $tribologi = $this->tribologiRepository->create($input);

        return $this->sendResponse($tribologi->toArray(), 'response.tribologi.create');
    }

    /**
     * @param int $id
     * @return Response
     *
     * @SWG\Get(
     *      path="/tribologi/{id}",
     *      summary="Display the specified Tribologi",
     *      tags={"Tribologi"},
     *      description="Get Tribologi",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }},
     *@SWG\Parameter(
     *          name="id",
     *          description="id of Tribologi",
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
     *                  ref="#/definitions/Tribologi"
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
        /** @var Tribologi $tribologi */
        $tribologi = $this->tribologiRepository->find($id, '*', $this->with);

        if (empty($tribologi)) {
            return $this->sendError('response.tribologi.not_found');
        }

        return $this->sendResponse($tribologi->toArray(), 'response.tribologi.view');
    }

    /**
     * @param int $id
     * @param UpdateTribologiAPIRequest $request
     * @return Response
     *
     * @SWG\Put(
     *      path="/tribologi/{id}",
     *      summary="Update the specified Tribologi in storage",
     *      tags={"Tribologi"},
     *      description="Update Tribologi",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }},
     *@SWG\Parameter(
     *          name="id",
     *          description="id of Tribologi",
     *          type="integer",
     *          required=true,
     *          in="path"
     *      ),
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="Tribologi that should be updated",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/Tribologi")
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
     *                  ref="#/definitions/Tribologi"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateTribologiAPIRequest $request)
    {
        $input = $request->all();

        /** @var Tribologi $tribologi */
        $tribologi = $this->tribologiRepository->find($id);

        if (empty($tribologi)) {
            return $this->sendError('response.tribologi.not_found');
        }

        // return $input;
        $validatorParams = []; 
 
        if(isset($request->unit_item_id)  && $tribologi->unit_item_id != $request->unit_item_id){
            $validatorParams['unit_item_id']   = 'integer|exists:mysql-app.unit_item,id';
        }
        if(isset($request->equipments_id)  && $tribologi->equipments_id!= $request->equipments_id){
            $validatorParams['equipments_id']   = 'integer|exists:mysql-app.equipments,id';
        }
        if(isset($request->bulan) && $tribologi->bulan != $request->bulan){
            $validatorParams['bulan']   = 'string';
            $input['bulan'] = ucfirst($request->bulan);

        }
        if(isset($request->tahun) && $tribologi->tahun != $request->tahun){
            $validatorParams['tahun']   = 'integer';
        }

        $validator = Validator::make($input, $validatorParams);

        /** InValid */
        if ($validator->fails())
        { 
            return $this->sendError('response.tribologi.failed_update', 422, ['invalid'=>$validator->errors()]);
        } 
        
        if(isset($request->bulan) || isset($request->tahun)){ 
            if(isset($request->bulan)){
                $nmonth = $request->bulan;
            }else {
                $nmonth = $tribologi->bulan;
            }   
            $nmonth = $this->helper->month(ucfirst($nmonth)); 
            if($nmonth == false){ 
                return $this->sendError('Bulan yang anda masukan tidak sesuai', 422); 
            }else{
                $nmonth = $nmonth;
                if(isset($request->bulan)){
                    $input['bulan']  = $request->bulan; 
                }
            }
            if(isset($request->tahun)){
                $tahun = $request->tahun;
            }else {
                $tahun = $tribologi->tahun;
            } 
            $bln_tahun = $tahun .'-'.$nmonth.'-01';
            $input['bln_tahun'] = $bln_tahun;
            
        }     
        $tribologi = $this->tribologiRepository->update($input, $id);

        return $this->sendResponse($tribologi->toArray(), 'response.tribologi.update');
    }

    /**
     * @param int $id
     * @return Response
     *
     * @SWG\Delete(
     *      path="/tribologi/{id}",
     *      summary="Remove the specified Tribologi from storage",
     *      tags={"Tribologi"},
     *      description="Delete Tribologi",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of Tribologi",
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
        /** @var Tribologi $tribologi */
        $tribologi = $this->tribologiRepository->find($id);

        if (empty($tribologi)) {
            return $this->sendError('response.tribologi.not_found');
        }

        $tribologi->delete();

        return $this->sendSuccess( 'response.tribologi.delete');
    }
}
