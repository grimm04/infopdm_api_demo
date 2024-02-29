<?php

namespace App\Http\Controllers;
  
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Response;
use App\Models\UnitItem;
use App\Models\Vibrasi;
use App\Models\Termografi;
use Illuminate\Support\Facades\Validator;

/**
 * Class StatistikController
 * @package App\Http\Controllers
 */

class StatistikAPIController extends AppBaseController
{ 

    public function __construct( )
    { 
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @SWG\Get(
     *      path="/statistik/vibrasi",
     *      summary="Get a listing of the Statistik Vibrasi.",
     *      tags={"Statistik"},
     *      description="Get all Statistik Vibrasi",
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
     *          name="id_unit_item",
     *          description="id_unit_item",
     *          type="integer",
     *          required=false,
     *          in="query"
     *      ), 
     *      @SWG\Parameter(
     *          name="tahun",
     *          description="tahun",
     *          type="integer",
     *          required=false,
     *          in="query"
     *      ), 
     *       @SWG\Parameter(
     *          name="page",
     *          description="page number",
     *          type="integer",
     *          required=true,
     *          in="query",
     *          default=0
     *      ),
     *       @SWG\Parameter(
     *          name="limit",
     *          description="show per page",
     *          type="integer",
     *          required=true,
     *          in="query",
     *          default=4
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
    public function vibrasi(Request $request)
    {   
        $unit_pembangkit_id = $request->unit_pembangkit_id;
        $input = $request->all();

        $validatorParams = [ 
            'unit_pembangkit_id' => 'required|integer|exists:unit_pembangkit,id',  
        ];

        $validator = Validator::make($input, $validatorParams);

        /** InValid */
        if ($validator->fails())
        {
            return $this->sendError('response.statistik.failed', 422, ['invalid'=>$validator->errors()]);
        } 
        $showPerPage = isset($request->limit) ? $request->limit : 4; 
        $page =  isset($request->page) ? $request->page : 0;
        $tahun =  isset($request->tahun) ? $request->tahun : date('Y');
        #Get unit item by Unit Pembangkit
        if(isset($request->id_unit_item)){
            $unit_item = UnitItem::where('id',$request->id_unit_item)->get();
        }else { 
            $unit_item = UnitItem::where('unit_pembangkit_id',$unit_pembangkit_id)->skip($page)->take($showPerPage)->get();  
        }
        $collection = collect(['A','B','C','D']);
         
        $data = [];
        $data_zona = [];
        // return $unit_item;
        foreach ($unit_item as $key => $value) { 

            #get data vibrasi by unit_id and last data
            $vibrasi = Vibrasi::where('unit_item_id',$value->id);  
            $vibrasi->where('tahun',$tahun);  
            $vibrasi->whereHas('equipmentId', function ($query) use($unit_pembangkit_id){ 
                return $query->where('unit_pembangkit_id', '=', $unit_pembangkit_id); 
            });  
            $vibrasi->orderBy('id','desc');
            $vibrasi = $vibrasi->first();  
            if($vibrasi){   
                $bulan = $vibrasi->bulan;
                $tahun = $vibrasi->tahun;
                $unit_id = $vibrasi->unit_item_id; 
                $multiplied = $collection->map(function ($item, $key) use($bulan,$tahun,$unit_id){
                    #get data vibrasi by bulan & zone
                    $vibrasi_zone =  Vibrasi::where('unit_item_id',$unit_id)->where('bulan',$bulan)->where('tahun',$tahun)->where('zone','like', '%' .$item. '%')->get();
                    
                    $equipment_data = [];
                    foreach ($vibrasi_zone as $eq) {
                        $equipment_data[] =[
                            'id'=> $eq->equipments_id,
                            'name'=> $eq->equipmentId->name,
                        ];
                    }
                    return $data_zona = [
                        'key'=> $item,
                        'value'=> $vibrasi_zone->count(),
                        'equipments'=> $equipment_data
                    ]; 
                }); 
                $data[] = [
                    'unit_id'=> $value->id,
                    'name'=> $value->name,
                    'bulan'=> $bulan,
                    'tahun'=> $tahun,
                    'zona' =>$multiplied->all()
                ];
                    
            } 
            
        } 
        
        return $this->sendResponse( $data, 'response.statistik.view',);
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @SWG\Get(
     *      path="/statistik/termografi",
     *      summary="Get a listing of the Statistik Data Termografi.",
     *      tags={"Statistik"},
     *      description="Get all Statistik Data Termografi",
     *      produces={"application/json"}, 
     *      security={ {"Bearer": {} }},  
     *      @SWG\Parameter(
     *          name="unit_pembangkit_id",
     *          description="unit_pembangkit_id",
     *          type="integer",
     *          required=true,
     *          in="query"
     *      ),
     *       @SWG\Parameter(
     *          name="id_unit_item",
     *          description="id_unit_item",
     *          type="integer",
     *          required=false,
     *          in="query"
     *      ), 
     *      @SWG\Parameter(
     *          name="tahun",
     *          description="tahun",
     *          type="integer",
     *          required=false,
     *          in="query"
     *      ), 
     *       @SWG\Parameter(
     *          name="page",
     *          description="page number",
     *          type="integer",
     *          required=true,
     *          in="query",
     *          default=0
     *      ),
     *       @SWG\Parameter(
     *          name="limit",
     *          description="show per page",
     *          type="integer",
     *          required=true,
     *          in="query",
     *          default=4
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
    public function termografi(Request $request)
    {   
        $unit_pembangkit_id = $request->unit_pembangkit_id;
        $input = $request->all();

        $validatorParams = [ 
            'unit_pembangkit_id' => 'required', 
        ];

        $validator = Validator::make($input, $validatorParams);

        /** InValid */
        if ($validator->fails())
        {
            return $this->sendError('response.statistik.failed', 422, ['invalid'=>$validator->errors()]);
        } 

        $showPerPage = isset($request->limit) ? $request->limit : 4; 
        $page =  isset($request->page) ? $request->page : 0;
        $tahun =  isset($request->tahun) ? $request->tahun : date('Y');

        #Get unit item by Unit Pembangkit
        if(isset($request->id_unit_item)){
            $unit_item = UnitItem::where('id',$request->id_unit_item)->get();
        }else { 
            $unit_item = UnitItem::where('unit_pembangkit_id',$unit_pembangkit_id)->skip($page)->take($showPerPage)->get();  
        } 
         
        $status = ['normal','abnormal']; 
        
        $data = []; 
        foreach ($unit_item as $key => $value) { 
            // return $value;
            #get data termografi by unit_id and last data
            $termografi = Termografi::where('unit_item_id',$value->id);  
            $termografi->where('tahun',$tahun);  
            $termografi->whereHas('equipmentId', function ($query) use($unit_pembangkit_id){ 
                return $query->where('unit_pembangkit_id', '=', $unit_pembangkit_id); 
            });  
            $termografi->orderBy('id','desc');
            $termografi = $termografi->first(); 
            // return $termografi;
            if($termografi){  
                $unit_item_id = $termografi->unit_item_id;
                $bulan = $termografi->bulan;
                $tahun = $termografi->tahun;
                $datatermografi =  Termografi::where('unit_item_id',$unit_item_id)->where('bulan',$bulan)->where('tahun',$tahun);
                $total = $datatermografi->count();
                // $normal     = $datatermografi->where('status','normal')->count();
                $abnormal   = $datatermografi->where('status','abnormal')->get();
                // return $abnormal;
                $abnormal_data = array();
                foreach ($abnormal as $key =>$ab) {
                   $abnormal_data[] = [
                    'id' =>  $ab->id,
                    'equipments_id' => $ab->equipments_id,
                    'unit_item_id' => $ab->unit_item_id,
                    'equipment' => $ab->equipmentId->name
                   ];
                } 
                
                $count_abnormal =  count($abnormal);
                $persen_abnormal = round(($count_abnormal/$total), 3)*100; 
                $data[] = [
                    'unit_id'=> $value->id,
                    'name' => $value->name,
                    'bulan'=> $bulan,
                    'tahun'=> $tahun,
                    'total' =>$total,
                    'abnormal' =>$count_abnormal,
                    'persen_abnormal' =>$persen_abnormal,
                    'abnormal_data' =>$abnormal_data,
                ];
                    
            } 
            
        } 
        
        return $this->sendResponse( $data, 'response.statistik.view',);
    }


     
}
