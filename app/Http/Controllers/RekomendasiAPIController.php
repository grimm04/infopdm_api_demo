<?php

namespace App\Http\Controllers;

use App\Http\Requests\API\CreateRekomendasiAPIRequest;
use App\Http\Requests\API\UpdateRekomendasiAPIRequest;
use App\Models\Rekomendasi;
use App\Repositories\RekomendasiRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Response;

/**
 * Class RekomendasiController
 * @package App\Http\Controllers\API
 */

class RekomendasiAPIController extends AppBaseController
{
    /** @var  RekomendasiRepository */
    private $rekomendasiRepository;
    private $with = ['unitItemId:id,name','equipmentId:id,name'];

    public function __construct(RekomendasiRepository $rekomendasiRepo)
    {
        $this->rekomendasiRepository = $rekomendasiRepo;
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @SWG\Get(
     *      path="/rekomendasi",
     *      summary="Get a listing of the Rekomendasi.",
     *      tags={"Rekomendasi"},
     *      description="Get all Rekomendasi",
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
     *          name="keyword",
     *          description="keyword search data by 'analisis','rekomendasi','feedback','status','akurasi",
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
     *          name="tanggal_open",
     *          description="tanggal_open",
     *          type="string",
     *          required=false,
     *          in="query"
     *      ),
     *      @SWG\Parameter(
     *          name="tanggal_closed",
     *          description="tanggal_closed",
     *          type="string",
     *          required=false,
     *          in="query"
     *      ),
     *      @SWG\Parameter(
     *          name="akurasi",
     *          description="akurasi",
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
     *                  @SWG\Items(ref="#/definitions/Rekomendasi")
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
        $rekomendasi = $this->rekomendasiRepository->getIndexRelation($request->except(['skip']), $this->with);

        return $this->sendResponse($rekomendasi->toArray(), 'response.rekomendasi.view');
    }

     /**
     * @param Request $request
     * @return Response
     *
     * @SWG\Get(
     *      path="/rekomendasi/persentase",
     *      summary="Get Data Persentase of the Rekomendasi.",
     *      tags={"Rekomendasi"},
     *      description="Get all Rekomendasi",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }},  
     *      @SWG\Parameter(
     *          name="unit_pembangkit_id",
     *          description="unit_pembangkit_id",
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
    public function persentase(Request $request)
    {   
        $unit_pembangkit_id = $request->unit_pembangkit_id;
        $rekomendasi =  Rekomendasi::whereHas('equipmentId', function ($query) use($unit_pembangkit_id){
            if($unit_pembangkit_id != null){
                return $query->where('unit_pembangkit_id', '=', $unit_pembangkit_id);
            } 
        })->get(); 
        $total  = $rekomendasi->count();
        $open   = $rekomendasi->where('status','open')->count();
        $closed = $rekomendasi->where('status','closed')->count();


        if($total != 0) {
            $persen_open = round(($open/$total), 2)*100;
            $persen_closed = round(($closed/$total), 2)*100;
        }
        else {
            $persen_open = 0; 
            $persen_closed = 0;
        }

        $rekomendasi = [
            'open'=> $open,
            'closed'=> $closed,
            'persen_open'=> $persen_open,
            'persen_closed'=> $persen_closed,
            'akurasi'=> $total,
        ]; 

        return $this->sendResponse($rekomendasi, 'response.rekomendasi.view');
    }

    /**
     * @param CreateRekomendasiAPIRequest $request
     * @return Response
     *
     * @SWG\Post(
     *      path="/rekomendasi",
     *      summary="Store a newly created Rekomendasi in storage",
     *      tags={"Rekomendasi"},
     *      description="Store Rekomendasi",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }},
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="Rekomendasi that should be stored",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/Rekomendasi")
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
     *                  ref="#/definitions/Rekomendasi"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateRekomendasiAPIRequest $request)
    {
        $input = $request->all();

        $validatorParams = [ 
            'equipments_id' => 'required|integer|exists:mysql-app.equipments,id', 
            'unit_item_id' => 'required|integer|exists:mysql-app.unit_item,id',
            'status' => 'required' 
        ];

        $validator = Validator::make($input, $validatorParams);

        /** InValid */
        if ($validator->fails())
        {
            return $this->sendError('response.rekomendasi.failed', 422, ['invalid'=>$validator->errors()]);
        } 
        $input['token']= Str::random(40); 

        $rekomendasi = $this->rekomendasiRepository->create($input);
        return $this->sendResponse($rekomendasi->toArray(), 'response.rekomendasi.create');
    }

    /**
     * @param int $id
     * @return Response
     *
     * @SWG\Get(
     *      path="/rekomendasi/{id}",
     *      summary="Display the specified Rekomendasi",
     *      tags={"Rekomendasi"},
     *      description="Get Rekomendasi",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of Rekomendasi",
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
     *                  ref="#/definitions/Rekomendasi"
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
        /** @var Rekomendasi $rekomendasi */
        $rekomendasi = $this->rekomendasiRepository->find($id,"*",$this->with);

        if (empty($rekomendasi)) {
            return $this->sendError('response.rekomendasi.not_found');
        }
        
        return $this->sendResponse($rekomendasi->toArray(), 'response.rekomendasi.view');
    }

    /**
     * @param int $id
     * @param UpdateRekomendasiAPIRequest $request
     * @return Response
     *
     * @SWG\Put(
     *      path="/rekomendasi/{id}",
     *      summary="Update the specified Rekomendasi in storage",
     *      tags={"Rekomendasi"},
     *      description="Update Rekomendasi",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of Rekomendasi",
     *          type="integer",
     *          required=true,
     *          in="path"
     *      ),
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="Rekomendasi that should be updated",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/Rekomendasi")
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
     *                  ref="#/definitions/Rekomendasi"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateRekomendasiAPIRequest $request)
    {
        $input = $request->all();

        /** @var Rekomendasi $rekomendasi */
        $rekomendasi = $this->rekomendasiRepository->find($id);

        if (empty($rekomendasi)) {
            return $this->sendError('response.rekomendasi.not_found');
        }
 
        $validatorParams = []; 

        if($rekomendasi->unit_item_id != $request->unit_item_id){
            $validatorParams['unit_item_id']   = 'required|integer|exists:mysql-app.unit_item,id';
        }
        if($rekomendasi->equipments_id != $request->equipments_id){
            $validatorParams['equipments_id']   = 'required|integer|exists:mysql-app.equipments,id';
        }

        $validator = Validator::make($input, $validatorParams);

        /** InValid */
        if ($validator->fails())
        { 
            return $this->sendError('Gagal Merubah Data.', 422, ['invalid'=>$validator->errors()]);
        }
 
        $rekomendasi = $this->rekomendasiRepository->update($input, $id);

        return $this->sendResponse($rekomendasi->toArray(), 'response.rekomendasi.update');
    }

    /**
     * @param int $id
     * @return Response
     *
     * @SWG\Delete(
     *      path="/rekomendasi/{id}",
     *      summary="Remove the specified Rekomendasi from storage",
     *      tags={"Rekomendasi"},
     *      description="Delete Rekomendasi",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of Rekomendasi",
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
        /** @var Rekomendasi $rekomendasi */
        $rekomendasi = $this->rekomendasiRepository->find($id);

        if (empty($rekomendasi)) {
            return $this->sendError('response.rekomendasi.not_found');
        }

        $rekomendasi->delete();

        return $this->sendSuccess('response.rekomendasi.delete');
    }
}
