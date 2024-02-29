<?php

namespace App\Http\Controllers;

use App\Http\Requests\API\CreateNonRutinVibrasiAPIRequest;
use App\Http\Requests\API\UpdateNonRutinVibrasiAPIRequest;
use App\Models\NonRutinVibrasi;
use App\Repositories\NonRutinVibrasiRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Illuminate\Support\Facades\Validator;
use Response;

/**
 * Class NonRutinVibrasiController
 * @package App\Http\Controllers\API
 */

class NonRutinVibrasiAPIController extends AppBaseController
{
    /** @var  NonRutinVibrasiRepository */
    private $nonRutinVibrasiRepository; 
    private $with = ['unitItemId:id,name','equipmentId:id,unit_pembangkit_id,name'];

    public function __construct(NonRutinVibrasiRepository $nonRutinVibrasiRepo)
    {
        $this->nonRutinVibrasiRepository = $nonRutinVibrasiRepo;
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @SWG\Get(
     *      path="/non-rutin-vibrasi",
     *      summary="Get a listing of the NonRutinVibrasi.",
     *      tags={"NonRutinVibrasi"},
     *      description="Get all NonRutinVibrasi",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }},
     *      @SWG\Parameter(
     *          name="unit_pembangkit_id",
     *          description="unit_pembangkit_id",
     *          type="string",
     *          required=false,
     *          in="query"
     *      ),
     *      @SWG\Parameter(
     *          name="equipments_id",
     *          description="equipments_id",
     *          type="string",
     *          required=false,
     *          in="query"
     *      ),
     *      @SWG\Parameter(
     *          name="unit_item_id",
     *          description="unit_item_id",
     *          type="string", 
     *          required=false,
     *          in="query"
     *      ),
     *      @SWG\Parameter(
     *          name="keyword",
     *          description="keyword search data by 'zone','time','keterangan','rekomendasi'",
     *          type="string",
     *          required=false,
     *          in="query"
     *      ),
     *      @SWG\Parameter(
     *          name="date",
     *          description="date",
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
     *          name="zone",
     *          description="zone",
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
     *                  @SWG\Items(ref="#/definitions/NonRutinVibrasi")
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
      
        $nonRutinVibrasi = $this->nonRutinVibrasiRepository->getIndexRelation($request->except(['skip']), $this->with); 
        return $this->sendResponse($nonRutinVibrasi->toArray(), 'response.nonrutinvibrasi.view');
    }

    /**
     * @param CreateNonRutinVibrasiAPIRequest $request
     * @return Response
     *
     * @SWG\Post(
     *      path="/non-rutin-vibrasi",
     *      summary="Store a newly created NonRutinVibrasi in storage",
     *      tags={"NonRutinVibrasi"},
     *      description="Store NonRutinVibrasi",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }},
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="NonRutinVibrasi that should be stored",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/NonRutinVibrasi")
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
     *                  ref="#/definitions/NonRutinVibrasi"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateNonRutinVibrasiAPIRequest $request)
    {
        $input = $request->all();

        $validatorParams = [ 
            'equipments_id' => 'required|integer|exists:mysql-app.equipments,id', 
            'unit_item_id' => 'required|integer|exists:mysql-app.unit_item,id',
            'date' => 'required',
            'time' => 'required'
        ];

        $validator = Validator::make($input, $validatorParams);

        /** InValid */
        if ($validator->fails())
        {
            return $this->sendError('response.nonrutinvibrasi.failed', 422, ['invalid'=>$validator->errors()]);
        }

        $nonRutinVibrasi = $this->nonRutinVibrasiRepository->create($input);

        return $this->sendResponse($nonRutinVibrasi->toArray(), 'response.nonrutinvibrasi.view');
    }

    /**
     * @param int $id
     * @return Response
     *
     * @SWG\Get(
     *      path="/non-rutin-vibrasi/{id}",
     *      summary="Display the specified NonRutinVibrasi",
     *      tags={"NonRutinVibrasi"},
     *      description="Get NonRutinVibrasi",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of NonRutinVibrasi",
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
     *                  ref="#/definitions/NonRutinVibrasi"
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
        /** @var NonRutinVibrasi $nonRutinVibrasi */
        $nonRutinVibrasi = $this->nonRutinVibrasiRepository->find($id,"*",$this->with);

        if (empty($nonRutinVibrasi)) {
            return $this->sendError('response.nonrutinvibrasi.not_found');
        }

        return $this->sendResponse($nonRutinVibrasi->toArray(),  'response.nonrutinvibrasi.view');
    }

    /**
     * @param int $id
     * @param UpdateNonRutinVibrasiAPIRequest $request
     * @return Response
     *
     * @SWG\Put(
     *      path="/non-rutin-vibrasi/{id}",
     *      summary="Update the specified NonRutinVibrasi in storage",
     *      tags={"NonRutinVibrasi"},
     *      description="Update NonRutinVibrasi",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of NonRutinVibrasi",
     *          type="integer",
     *          required=true,
     *          in="path"
     *      ),
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="NonRutinVibrasi that should be updated",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/NonRutinVibrasi")
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
     *                  ref="#/definitions/NonRutinVibrasi"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateNonRutinVibrasiAPIRequest $request)
    {
        $input = $request->all();

        /** @var NonRutinVibrasi $nonRutinVibrasi */
        $nonRutinVibrasi = $this->nonRutinVibrasiRepository->find($id);

        if (empty($nonRutinVibrasi)) {
            return $this->sendError('response.nonrutinvibrasi.not_found');
        }


        $validatorParams = [];  

        if($request->unit_item_id && $nonRutinVibrasi->unit_item_id != $request->unit_item_id){
            $validatorParams['unit_item_id']   = 'integer|exists:mysql-app.unit_item,id';
        }
        if($request->equipments_id && $nonRutinVibrasi->equipments_id!=$request->equipments_id){
            $validatorParams['equipments_id']   = 'integer|exists:mysql-app.equipments,id';
        }

        $validator = Validator::make($input, $validatorParams);

        /** InValid */
        if ($validator->fails())
        { 
            return $this->sendError('response.nonrutinvibrasi.failed', 422, ['invalid'=>$validator->errors()]);
        }
        $nonRutinVibrasi = $this->nonRutinVibrasiRepository->update($input, $id);

        return $this->sendResponse($nonRutinVibrasi->toArray(),'response.nonrutinvibrasi.update');
    }

    /**
     * @param int $id
     * @return Response
     *
     * @SWG\Delete(
     *      path="/non-rutin-vibrasi/{id}",
     *      summary="Remove the specified NonRutinVibrasi from storage",
     *      tags={"NonRutinVibrasi"},
     *      description="Delete NonRutinVibrasi",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of NonRutinVibrasi",
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
        /** @var NonRutinVibrasi $nonRutinVibrasi */
        $nonRutinVibrasi = $this->nonRutinVibrasiRepository->find($id);

        if (empty($nonRutinVibrasi)) {
            return $this->sendError('response.nonrutinvibrasi.not_found');
        }

        $nonRutinVibrasi->delete();

        return $this->sendSuccess('response.nonrutinvibrasi.delete');
    }
}
