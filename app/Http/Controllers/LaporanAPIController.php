<?php

namespace App\Http\Controllers;

 
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Response;
use Illuminate\Support\Facades\Validator;

use App\Repositories\VibrasiRepository;
use App\Repositories\NonRutinVibrasiRepository; 
use App\Models\UnitItem;
use App\Models\Vibrasi;
use App\Models\Termografi;
use App\Models\Tribologi;
use App\Models\UnitPembangkit;
use App\Helper; 
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Presentasi;   
use Carbon\Carbon;
use PDF;
use Storage;
/**
 * Class LaporanController
 * @package App\Http\Controllers
 */

class LaporanAPIController extends AppBaseController
{
  
    protected $helper;
    private $vibrasiRepository;  
    private $nonRutinVibrasiRepository; 
    private $public_upload;

    public function __construct(Helper $help,VibrasiRepository $vibrasiRepo,NonRutinVibrasiRepository $nonRutinVibrasiRepo)
    {   
        $this->vibrasiRepository = $vibrasiRepo;  
        $this->nonRutinVibrasiRepository = $nonRutinVibrasiRepo; 
        $this->helper = $help;
        $this->public_upload = env('PUBLIC_UPLOAD');

    }

    /**
     * @param Request $request
     * @return Response
     *
     * @SWG\Get(
     *      path="/laporan/vibrasi",
     *      summary="Get a listing of the Laporan Vibrasi",
     *      tags={"Laporan"},
     *      description="Get Laporan Vibrasi",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }},  
     *      @SWG\Parameter(
     *          name="unit_pembangkit_id",
     *          description="id of unit pembangkit",
     *          type="integer",
     *          required=true,
     *          in="query"
     *      ),
     *       @SWG\Parameter(
     *          name="unit_item_id",
     *          description="unit_item_id",
     *          type="string",
     *          required=true,
     *          in="query"
     *      ),
     *      @SWG\Parameter(
     *          name="bulan",
     *          description="bulan",
     *          type="string",
     *          required=true,
     *          in="query"
     *      ),
     *      @SWG\Parameter(
     *          name="tahun",
     *          description="tahun",
     *          type="string",
     *          required=true,
     *          in="query"
     *      ),
     *       @SWG\Parameter(
     *          name="non_rutin",
     *          description="True=1",
     *          type="integer",
     *          required=false,
     *          in="query"
     *      ),
     *      @SWG\Parameter(
     *          name="download",
     *          description="True=1",
     *          type="integer",
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
    public function vibrasi(Request $request)
    {
        $laporan = "";

        $input = $request->all();
        

        $validatorParams = [ 
            'unit_pembangkit_id' => 'required|integer|exists:unit_pembangkit,id',
            'unit_item_id' => 'required|integer|exists:mysql-app.unit_item,id',
            'bulan' => 'required', 
            'tahun' => 'required'
        ]; 
        $validator = Validator::make($input, $validatorParams);

        /** InValid */
        if ($validator->fails())
        {
            return $this->sendError('Gagal Mengambil Data', 422, ['invalid'=>$validator->errors()]);
        } 
 
        $bulan = $request->bulan;
        $tahun = $request->tahun;
        $unitPembangkit  = $this->helper->unitPembangkit($request->unit_pembangkit_id);
        $unitItem  = $this->helper->unitItem($request->unit_item_id); 
        $unit_pembangkit_id = $request->unit_pembangkit_id;
        $laporan = $this->vibrasiRepository->trendGet($request->except(['skip'])); 
        $zone = [];
        if(count($laporan)===0){
            $count = [];
            foreach ($laporan as $d) {
                $count[] = $d->zone;
            }
            $zone = collect($count)->filter()->sort()->countBy()->flatten();
        }
        $filename = 'Vibrasi  '.$unitPembangkit->name.'  '.$unitItem->name.'-'.$bulan.'-'.$tahun.'.pdf';
        if($request->non_rutin ==1){
            $filename = 'Vibrasi dan Non Rutin Vibrasi '.$unitPembangkit->name.' '.$unitItem->name.'-'.$bulan.'-'.$tahun.'.pdf'; 
            $nonrutin = $this->nonRutinVibrasiRepository->dataGet($request->except(['skip']));
            $laporan = (object)array(
                'vibrasi'=> $laporan,
                'non_rutin_vibrasi'=> $nonrutin,
            );

        }
       
        
        $date = Carbon::now('Asia/Makassar')->format('D, d M Y H:i');

        // return $laporan;
        if($request->download==1){  
            
            $pdf = PDF::loadView('exports.laporan-vibrasi', [
                'bulan'=>$bulan, 
                'tahun'=>$tahun,
                'laporan'=>$laporan, 
                'unitItem'=>$unitItem,
                'unitPembangkit'=>$unitPembangkit,
                'zone'=>$zone,
                'date'=>$date,
                'non_rutin_vibrasi'=>$request->non_rutin != null ? $request->non_rutin  : 0 ,
                'path' => $this->public_upload,  
                ]); 
            $pdf->setOption('enable-javascript', true);
            $pdf->setOption('enable-local-file-access', true);
            $pdf->setOption('javascript-delay', 1000);
            $pdf->setOption('no-stop-slow-scripts', true);
            $pdf->setOption('page-size', 'a4');
            $pdf->setOption('orientation', 'landscape');
            $pdf->setOption('enable-smart-shrinking', true);
            $pdf->setOption('header-font-size', 8);
            $pdf->setOption('header-spacing', 3);
            $pdf->setOption('footer-font-size', 8); 
            $pdf->setOption('footer-center', "Halaman [page] dari [topage]"); 
            return $pdf->download($filename); 
        }
        // return $this->sendResponse($laporan, 'Laporans retrieved successfully');
    }
    /**
     * @param Request $request
     * @return Response
     *
     * @SWG\Get(
     *      path="/laporan/presentasi",
     *      summary="Get a listing of the Laporan Vibrasi",
     *      tags={"Laporan"},
     *      description="Get Laporan Vibrasi",
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
     *          name="bulan",
     *          description="bulan",
     *          type="string",
     *          required=true,
     *          in="query"
     *      ),
     *      @SWG\Parameter(
     *          name="tahun",
     *          description="tahun",
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
    public function presentasi(Request $request)
    {
        $input = $request->all();
        

        $validatorParams = [ 
            'unit_pembangkit_id' => 'required|integer|exists:unit_pembangkit,id',
            'bulan' => 'required', 
            'tahun' => 'required'
        ];

        $validator = Validator::make($input, $validatorParams);

        /** InValid */
        if ($validator->fails())
        {
            return $this->sendError('Gagal Mengambil Data', 422, ['invalid'=>$validator->errors()]);
        } 

        $bulan = $request->bulan;
        $tahun = $request->tahun;

        #mengambil config pada unit pembangkit
        $unit_pembangkit    = UnitPembangkit::find($request->unit_pembangkit_id);
        $vibrasi_config     = json_decode($unit_pembangkit->vibrasi_config_detail);
        $termografi_config  = json_decode($unit_pembangkit->termografi_config_detail);
        $tribologi_config   = json_decode($unit_pembangkit->tribologi_config_detail);


        $unit = UnitItem::where('unit_pembangkit_id',$request->unit_pembangkit_id)->get(); 
        $zone = ['A', 'B' , 'C', 'D']; 
        $vibrasi =[];
        $termografi =[];
        $tribologi =[];
        //cek data vibrasi
        $countVibrasi = Vibrasi::where([['bulan', $bulan],  ['tahun', $tahun],  ])->count();
        //cek data termografi
        $countTermografi = Termografi::where([['bulan', $bulan],  ['tahun', $tahun], ['status', 'abnormal']  ])->count();
        //cek data tribologi
        $countTribologi = Tribologi::where([['bulan', $bulan],  ['tahun', $tahun],  ])->count();
        // Menghitung Zona A s.d D pada setiap unit nya
        foreach ($unit as $u) {
            // kondisi zona vibrasi  
            
            if($countVibrasi != 0){
                for ($i = 0; $i <=count($zone)-1; $i++) {
                    $dataVibrasi = $this->helper->vibrasiUnit($u->id, $bulan, $tahun, $vibrasi_config,$zone[$i]);
                    $zona[$zone[$i]] = [
                        'total' => Vibrasi::where([
                            ['unit_item_id', $u->id],
                            ['bulan', $bulan],
                            ['tahun', $tahun],
                            ['zone', $zone[$i]],
                        ])->count(),
                        'data'=>$dataVibrasi 
                    ];   
                }
                $totaldata = Vibrasi::where([
                    ['unit_item_id', $u->id],
                    ['bulan', $bulan],
                    ['tahun', $tahun],
                ])->count();
                if($totaldata != 0){
                    $vibrasi[] = [
                        'unit_item_id' => $u->id,
                        'unit_name'=> $u->name,
                        'total' => $totaldata ,
                        'zone'=> $zona
                    ];
                } else {
                    $vibrasi[] = [
                        'unit_item_id' => $u->id,
                        'unit_name'=> $u->name, 
                        'total' => Null,
                        'zone'=> Null
                    ];
                }
            }else {
                // $vibrasi = Null; 
                $vibrasi[] = [
                    'unit_item_id' => $u->id,
                    'unit_name'=> $u->name, 
                    'total' => Null,
                    'zone'=> Null
                ];
            } 
            // status data termografi 
            if($countTermografi != 0){
                $dataTermografi = $this->helper->termografiUnit($u->id, $bulan, $tahun,$tribologi_config); 
            
                $totaltermografi = Termografi::where([
                    ['unit_item_id', $u->id],
                    ['bulan', $bulan],
                    ['tahun', $tahun],
                    ['status', 'abnormal'],
                ])->count();
                if($totaltermografi!= 0){
                    $termografi[] = [
                        'unit_item_id' => $u->id,
                        'unit_name'=> $u->name,
                        'total'=> $totaltermografi,
                        'data'=>  $dataTermografi 
                    ];
                }else {
                    $termografi[] = [
                        'unit_item_id' => $u->id,
                        'unit_name'=> $u->name,
                        'total' => Null,
                        'data'=> Null
                    ];
                }
               
            }else {
                $termografi[] = [
                    'unit_item_id' => $u->id,
                    'unit_name'=> $u->name, 
                    'total' => Null,
                    'data'=> Null
                ];
            }
            
            
            
            // Tribologi
            if($countTribologi != 0){
                $dataTribologi = $this->helper->tribologiUnit($u->id, $bulan, $tahun);
                $totaltribologi = Tribologi::where([
                    ['unit_item_id', $u->id],
                    ['bulan', $bulan],
                    ['tahun', $tahun]
                ])->count();
                if($totaltribologi != 0){
                    $tribologi[] = [
                        'unit_item_id' => $u->id,
                        'unit_name'=> $u->name,
                        'total' => $totaltribologi,
                        'data'=>  $dataTribologi 
                    ];
                }else {
                    $tribologi[] = [
                        'unit_item_id' => $u->id,
                        'unit_name'=> $u->name,
                        'total' => Null,
                        'data'=> Null
                    ];
                }
            }else {
                $tribologi[] = [
                    'unit_item_id' => $u->id,
                    'unit_name'=> $u->name, 
                    'total' => Null,
                    'data'=> Null
                ];
            } 
        } 
        $laporan = [
            'bulan'=>$bulan,
            'tahun'=>$tahun, 
            'vibrasi'=>$vibrasi,
            'termografi'=>$termografi,
            'tribologi'=>$tribologi
        ]; 
        
        return $this->sendResponse($laporan, 'response.laporan.view');
    } 
    /**
     * @param Request $request
     * @return Response
     *
     * @SWG\Get(
     *      path="/laporan/summary",
     *      summary="Get a listing of the summary",
     *      tags={"Laporan"},
     *      description="Get summary",
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
     *          name="bulan",
     *          description="bulan",
     *          type="string",
     *          required=true,
     *          in="query"
     *      ),
     *      @SWG\Parameter(
     *          name="tahun",
     *          description="tahun",
     *          type="string",
     *          required=true,
     *          in="query"
     *      ),
     *       @SWG\Parameter(
     *          name="download",
     *          description="True=1",
     *          type="integer",
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
    public function summary(Request $request)
    {
        $laporan = "";

        $input = $request->all();
        

        $validatorParams = [ 
            'unit_pembangkit_id' => 'required|integer|exists:unit_pembangkit,id', 
            'bulan' => 'required', 
            'tahun' => 'required'
        ]; 
        $validator = Validator::make($input, $validatorParams);

        /** InValid */
        if ($validator->fails())
        {
            return $this->sendError('Gagal Mengambil Data', 422, ['invalid'=>$validator->errors()]);
        } 

        $bulan = $request->bulan;
        $tahun = $request->tahun;
        $unitPembangkit  = $this->helper->unitPembangkit($request->unit_pembangkit_id); 

        $vibrasi_config     = json_decode($unitPembangkit->vibrasi_config_detail);
        $termografi_config  = json_decode($unitPembangkit->termografi_config_detail);
        $tribologi_config   = json_decode($unitPembangkit->tribologi_config_detail);


        $unit_pembangkit_id = $request->unit_pembangkit_id; 
        $filename = 'Summary  '.$unitPembangkit->name.'-'.$bulan.'-'.$tahun.'.pdf';

        $unit = UnitItem::where('unit_pembangkit_id',$unitPembangkit->id)->get(); 
        $zone = ['A', 'B' , 'C', 'D'];
        $status = ['normal', 'abnormal'];
        $vibrasi = [];
        $termografi = [];
        $tribologi = [];
        $zona = [];
        // Status peralatan
        foreach ($unit as $u) {
            // Zona A s.d D setiap unit
            for ($i = 0; $i <=count($zone)-1; $i++) {
                $dataVibrasi = $this->helper->vibrasiUnit($u->id, $bulan, $tahun, $vibrasi_config,$zone[$i]);
                $zona[$zone[$i]] = [
                    'data'=> $dataVibrasi,
                    'total' => Vibrasi::where([
                        ['unit_item_id', $u->id],
                        ['bulan', $bulan],
                        ['tahun', $tahun],
                        ['zone', $zone[$i]],
                    ])->count()
                ];   
            }
            $vibrasi[] = [
                'unit_item_id' => $u->id,
                'total' => Vibrasi::where([
                    ['unit_item_id', $u->id],
                    ['bulan', $bulan],
                    ['tahun', $tahun],
                ])->count(),
                'unit_name'=> $u->name,
                'zone'=> $zona
            ];

            // status data termografi 
            $dataTermografi = $this->helper->termografiUnit($u->id, $bulan, $tahun,$tribologi_config);
           
            $termografi[] = [
                'unit_item_id' => $u->id,
                'unit_name'=> $u->name,
                'total'=> Termografi::where([
                    ['unit_item_id', $u->id],
                    ['bulan', $bulan],
                    ['tahun', $tahun],
                ])->count(),
                'detail'=> [
                    'value'=> Termografi::where([
                        ['unit_item_id', $u->id],
                        ['bulan', $bulan],
                        ['tahun', $tahun],
                        ['status', 'abnormal'], 
                    ])->count(),
                    'data'=> $dataTermografi
                ]
            ];
            
            
            // Tribologi
            $dataTribologi = $this->helper->tribologiUnit($u->id, $bulan, $tahun);
 
            $tribologi[] = [
                'unit_item_id' => $u->id,
                'unit_name'=> $u->name,
                'total'=> Tribologi::where([
                    ['unit_item_id', $u->id],
                    ['bulan', $bulan],
                    ['tahun', $tahun],
                ])->count(),
                'detail'=> [ 
                    'data'=> $dataTribologi
                ]
            ];
        } 
        // $dataTribologi = $this->helper->tribologiUnit('2', $bulan, $tahun);
        // foreach ($dataTribologi as $tribo) {
        //     $detail = collect(json_decode($tribo['data_detail'] )); 
        //     $totalkey = [];
        //     // foreach ($detail as $key => $det) {
        //     //     $totalkey[] = $key+1;
        //     // }
        //     $data = [];
        //     for ($i=0; $i <count($detail) ; $i++) {  
        //          foreach ($detail[$i] as $key => $value) {
        //              $data[] = [
        //                  'key'=> $key,
        //                  'value' =>$value
        //              ];
        //          }
        //     }
        // }
        // return $data;
        
        $date = Carbon::now('Asia/Makassar')->format('D, d M Y H:i'); 
        $laporan = [
            'bulan'=>$bulan,
            'tahun'=>$tahun, 
            'dataVibrasi'=>$vibrasi,
            'dataTermografi' => $termografi, 
            'dataTribologi' => $tribologi,  
        ];  
        if($request->download==1){  
           
            $pdf = PDF::loadView('exports.summary', [  
                'bulan' => $bulan, 
                'tahun' => $tahun, 
                'unit' => $unit, 
                'date' => $date,
                // Vibrasi
                'unitPembangkit' => $unitPembangkit, 
                'dataVibrasi' => $vibrasi, 
                'zona' => $zona,
                // Termografi
                'dataTermografi' => $termografi, 
                // Termografi
                'tribologi_config' => $tribologi_config, 
                'dataTribologi' => $tribologi, 
                'path' => $this->public_upload, 
                ]);
            $pdf->setOption('enable-javascript', true);
            $pdf->setOption('enable-local-file-access', true); 
            $pdf->setOption('javascript-delay', 1000);
            $pdf->setOption('no-stop-slow-scripts', true);
            $pdf->setOption('page-size', 'a4');
            // $pdf->setOption('orientation', 'potrait');
            $pdf->setOption('enable-smart-shrinking', true);
            $pdf->setOption('header-font-size', 6);
            $pdf->setOption('header-spacing', 2.5);
            $pdf->setOption('footer-font-size', 6);
            $pdf->setOption('footer-center', "Halaman [page] dari [topage]");
            return $pdf->download($filename); 
        }

        // return $this->sendResponse($laporan, 'Summary retrieved successfully');
    } 
 
}
