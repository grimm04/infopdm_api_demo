<?php

namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Response;
use App\Exports\FormatExportTransaksi;
use App\Exports\FormatExportSheets;
use App\Exports\FormatExportData;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\UnitPembangkit;
use App\Models\Equipment;
use App\Models\UnitItem;
/**
 * Class FormatExportController
 * @package App\Http\Controllers
 */

class FormatExportAPIController extends AppBaseController
{
  
 

    
    /**
     * @param int $id
     * @return Response
     *
     * @SWG\Post(
     *      path="/format-export",
     *      summary="Display the specified FormatExport",
     *      tags={"FormatExport"},
     *      description="Get FormatExport",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }},  
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of unit pembangkit",
     *          type="integer",
     *          required=true,
     *          in="query"
     *      ),
     *      @SWG\Parameter(
     *          name="transaksi",
     *          description="example: vibrasi/termografi",
     *          type="string",
     *          required=true,
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
    public function show(Request $request)
    {
        /** @var FormatExport $formatExport */
        $formatExport = UnitPembangkit::find($request->id); 

        if (empty($formatExport)) {
            return $this->sendError('Unit Pembangkit not found');
        }

        if($request->transaksi == 'vibrasi' || $request->transaksi == 'termografi') {
            $key_config = $request->transaksi.'_config_detail';
            if($formatExport->$key_config == null){
                return $this->sendError('response.formatexport.not_found'); 
            }
            
            $transaksi = $formatExport->$key_config;
            $data = json_decode($transaksi); 
            
            $data_array = [];
            foreach($data as $key => $value){ 
                $temp =  strtolower($value->key);
                array_push($data_array,$temp); 
            } 

            $equipments = Equipment::where('unit_pembangkit_id',$request->id)->get(); 
            $data_eq = [];
            foreach($equipments as $eq){ 
                $data_eq[] = [
                    'id' => $eq->id,
                    'name' => $eq->name,
                ];
            } 

            $unit_item = UnitItem::where('unit_pembangkit_id',$request->id)->get(); 
            $data_ui = [];
            foreach($unit_item as $ui){ 
                $data_ui[] = [
                    'id' => $ui->id,
                    'name' => $ui->name,
                ];
            } 
            $data = [
                'head' => $data_array,
                'key' =>$key_config,
                'equipments' =>$data_eq,
                'unit_item' =>$data_ui,
            ];
            // return $data_eq;
            $export = new FormatExportSheets($data);

            return Excel::download($export, $request->transaksi.'-'.$formatExport->name.'-format.xlsx'); 
        }

        return $this->sendError('response.formatexport.not_found'); 
    }
     
}
