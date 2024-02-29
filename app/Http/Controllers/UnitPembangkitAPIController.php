<?php

namespace App\Http\Controllers;

use App\Http\Requests\API\CreateUnitPembangkitAPIRequest;
use App\Http\Requests\API\UpdateUnitPembangkitAPIRequest;
use App\Models\UnitPembangkit;
use App\Repositories\UnitPembangkitRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Illuminate\Support\Facades\Validator; 
use Response;

/**
 * Class UnitPembangkitController
 * @package App\Http\Controllers
 */

class UnitPembangkitAPIController extends AppBaseController
{
    /** @var  UnitPembangkitRepository */
    private $unitPembangkitRepository;

    public function __construct(UnitPembangkitRepository $unitPembangkitRepo)
    {
        $this->unitPembangkitRepository = $unitPembangkitRepo;
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @SWG\Get(
     *      path="/unit-pembangkit",
     *      summary="Get a listing of the UnitPembangkit.",
     *      tags={"UnitPembangkit"},
     *      description="Get all UnitPembangkit",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }},  
     *      @SWG\Parameter(
     *          name="id",
     *          description="unit_pembangkit_id",
     *          type="integer",
     *          required=false,
     *          in="query"
     *      ),
     *      @SWG\Parameter(
     *          name="keyword",
     *          description="keyword search data by 'name','address,'email','status'",
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
     *                  @SWG\Items(ref="#/definitions/UnitPembangkit")
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
    

        $unitPembangkit = $this->unitPembangkitRepository->paginate("*", $request->except(['skip'])); 

        return $this->sendResponse($unitPembangkit->toArray(), 'response.unitpembangkit.view');
    }
     /**
     * @param Request $request
     * @return Response
     *
     * @SWG\Get(
     *      path="/unit-pembangkit/get-data",
     *      summary="Get a listing of the UnitPembangkit.",
     *      tags={"UnitPembangkit"},
     *      description="Get all UnitPembangkit",
     *      produces={"application/json"},  
     *      @SWG\Parameter(
     *          name="id",
     *          description="unit_pembangkit_id",
     *          type="integer",
     *          required=false,
     *          in="query"
     *      ),
     *      @SWG\Parameter(
     *          name="keyword",
     *          description="keyword search data by 'name','address,'email','status'",
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
     *                  @SWG\Items(ref="#/definitions/UnitPembangkitAll")
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */

    public function getall(Request $request)
    {
        $unitPembangkit = new UnitPembangkit;  
        $unitPembangkit = $unitPembangkit->select('id','name','address','image','status'); 
        if($request->id){
            $unitPembangkit= $unitPembangkit->where('id',$request->id);
        }
        if($request->keyword){
            $unitPembangkit= $unitPembangkit->orWhere('name','like', '%'.$request->keyword.'%');
        }
        if($request->sort_by && $request->sort){
            $unitPembangkit= $unitPembangkit->orderBy($request->sort_by,$request->sort);
        }
        $unitPembangkit = $unitPembangkit->get(); 
        return $this->sendResponse($unitPembangkit->toArray(), 'response.unitpembangkit.view');
    }

    /**
     * @param CreateUnitPembangkitAPIRequest $request
     * @return Response
     *
     * @SWG\Post(
     *      path="/unit-pembangkit",
     *      summary="Store a newly created UnitPembangkit in storage",
     *      tags={"UnitPembangkit"},
     *      description="Store UnitPembangkit",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }}, 
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="UnitPembangkit that should be stored",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/UnitPembangkit")
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
     *                  ref="#/definitions/UnitPembangkit"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateUnitPembangkitAPIRequest $request)
    {
        $input = $request->all();

        $validatorParams = [ 
            'email' => 'string|email|max:100|unique:unit_pembangkit',   
        ];

        $validator = Validator::make($input, $validatorParams);

        /** InValid */
        if ($validator->fails())
        {
            return $this->sendError('response.unitpembangkit.failed_create', 422, ['invalid'=>$validator->errors()]);
        }

        $unitPembangkit = $this->unitPembangkitRepository->create($input);

        return $this->sendResponse($unitPembangkit->toArray(), 'response.unitpembangkit.create');
    }

    /**
     * @param int $id
     * @return Response
     *
     * @SWG\Get(
     *      path="/unit-pembangkit/{id}",
     *      summary="Display the specified UnitPembangkit",
     *      tags={"UnitPembangkit"},
     *      description="Get UnitPembangkit",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }}, 
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of UnitPembangkit",
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
     *                  ref="#/definitions/UnitPembangkit"
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
        /** @var UnitPembangkit $unitPembangkit */
        $unitPembangkit = $this->unitPembangkitRepository->find($id);

        if (empty($unitPembangkit)) {
            return $this->sendError('response.unitpembangkit.not_found');
        }

        return $this->sendResponse($unitPembangkit->toArray(), 'response.unitpembangkit.view');
    }

    /**
     * @param int $id
     * @param UpdateUnitPembangkitAPIRequest $request
     * @return Response
     *
     * @SWG\Put(
     *      path="/unit-pembangkit/{id}",
     *      summary="Update the specified UnitPembangkit in storage",
     *      tags={"UnitPembangkit"},
     *      description="Update UnitPembangkit",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }}, 
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of UnitPembangkit",
     *          type="integer",
     *          required=true,
     *          in="path"
     *      ),
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="UnitPembangkit that should be updated",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/UnitPembangkit")
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
     *                  ref="#/definitions/UnitPembangkit"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateUnitPembangkitAPIRequest $request)
    {
        $input = $request->all();

        /** @var UnitPembangkit $unitPembangkit */
        $unitPembangkit = $this->unitPembangkitRepository->find($id);

        if (empty($unitPembangkit)) {
            return $this->sendError('response.unitpembangkit.not_found');
        }

        $validatorParams = []; 

        if($request->email && $unitPembangkit->email!=$request->email){
            $validatorParams['email']   = 'string|email|max:100|unique:unit_pembangkit';
        }

        $validator = Validator::make($input, $validatorParams);

        /** InValid */
        if ($validator->fails())
        { 
            return $this->sendError( 'response.unitpembangkit.failed_update', 422, ['invalid'=>$validator->errors()]);
        }

        $unitPembangkit = $this->unitPembangkitRepository->update($input, $id);

        return $this->sendResponse($unitPembangkit->toArray(), 'response.unitpembangkit.update');
    }

    /**
     * @param int $id
     * @return Response
     *
     * @SWG\Delete(
     *      path="/unit-pembangkit/{id}",
     *      summary="Remove the specified UnitPembangkit from storage",
     *      tags={"UnitPembangkit"},
     *      description="Delete UnitPembangkit",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }}, 
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of UnitPembangkit",
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
        /** @var UnitPembangkit $unitPembangkit */
        $unitPembangkit = $this->unitPembangkitRepository->find($id);

        if (empty($unitPembangkit)) {
            return $this->sendError('response.unitpembangkit.not_found');
        }

        $unitPembangkit->delete();

        return $this->sendSuccess('response.unitpembangkit.delete');
    }
}
