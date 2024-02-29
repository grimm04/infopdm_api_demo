<?php

namespace App\Http\Controllers;

use App\Http\Requests\API\CreateUnitItemAPIRequest;
use App\Http\Requests\API\UpdateUnitItemAPIRequest;
use App\Models\UnitItem;
use App\Repositories\UnitItemRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Response;
use Illuminate\Support\Facades\Validator;
use App\Imports\UnitItemImport; 
use Maatwebsite\Excel\Facades\Excel;


/**
 * Class UnitItemController
 * @package App\Http\Controllers
 */

class UnitItemAPIController extends AppBaseController
{
    /** @var  UnitItemRepository */
    private $unitItemRepository;
    private $with = ['unitPembangkitId:id,name']; 

    public function __construct(UnitItemRepository $unitItemRepo)
    {
        $this->unitItemRepository = $unitItemRepo;
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @SWG\Get(
     *      path="/unit-item",
     *      summary="Get a listing of the UnitItem.",
     *      tags={"UnitItem"},
     *      description="Get all UnitItem",
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
     *          name="keyword",
     *          description="keyword search data by 'name'",
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
     *                  @SWG\Items(ref="#/definitions/UnitItem")
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
        
        $unitItem = $this->unitItemRepository->paginate("*", $request->except(['skip']),$this->with); 

        return $this->sendResponse($unitItem->toArray(), 'response.unit_item.view');
    }

    /**
     * @param CreateUnitItemAPIRequest $request
     * @return Response
     *
     * @SWG\Post(
     *      path="/unit-item",
     *      summary="Store a newly created UnitItem in storage",
     *      tags={"UnitItem"},
     *      description="Store UnitItem",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }},  
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="UnitItem that should be stored",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/UnitItem")
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
     *                  ref="#/definitions/UnitItem"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateUnitItemAPIRequest $request)
    {
        $input = $request->all(); 
        $validatorParams = [
            'unit_pembangkit_id' => 'required|integer|exists:unit_pembangkit,id',    
            'name' => 'required|string|max:250|unique:mysql-app.unit_item,name,NULL,id,unit_pembangkit_id,'.$request->unit_pembangkit_id,  
        ];

        $validator = Validator::make($input, $validatorParams);

        $message = ['Unit Item Sudah Ada.'];
        /** InValid */
        if ($validator->fails())
        {
            return $this->sendError('response.unit_item.failed_create', 422, ['invalid'=>$validator->errors()]);
        }

        $unitItem = $this->unitItemRepository->create($input);

        return $this->sendResponse($unitItem->toArray(), 'response.unit_item.create');
    }

    /**
     * @param int $id
     * @return Response
     *
     * @SWG\Get(
     *      path="/unit-item/{id}",
     *      summary="Display the specified UnitItem",
     *      tags={"UnitItem"},
     *      description="Get UnitItem",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }},  
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of UnitItem",
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
     *                  ref="#/definitions/UnitItem"
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
        /** @var UnitItem $unitItem */
        $unitItem = $this->unitItemRepository->find($id,"*",$this->with);

        if (empty($unitItem)) {
            return $this->sendError('response.unit_item.not_found');
        }

        return $this->sendResponse($unitItem->toArray(), 'response.unit_item.view');
    }

    /**
     * @param int $id
     * @param UpdateUnitItemAPIRequest $request
     * @return Response
     *
     * @SWG\Put(
     *      path="/unit-item/{id}",
     *      summary="Update the specified UnitItem in storage",
     *      tags={"UnitItem"},
     *      description="Update UnitItem",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }},  
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of UnitItem",
     *          type="integer",
     *          required=true,
     *          in="path"
     *      ),
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="UnitItem that should be updated",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/UnitItem")
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
     *                  ref="#/definitions/UnitItem"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateUnitItemAPIRequest $request)
    {
        $input = $request->all();

        /** @var UnitItem $unitItem */
        $unitItem = $this->unitItemRepository->find($id);

        if (empty($unitItem)) {
            return $this->sendError('response.unit_item.not_found');
        }

        $validatorParams = []; 
        if($request->unit_pembangkit_id && $unitItem->unit_pembangkit_id!=$request->unit_pembangkit_id){
            $validatorParams['unit_pembangkit_id']   = 'required|integer|exists:unit_pembangkit,id';
            $validatorParams['name']   = 'required|string|max:250|unique:mysql-app.unit_item,name,NULL,id,unit_pembangkit_id,'.$request->unit_pembangkit_id;
        }
        if($request->name && $unitItem->name != $request->name){
            $validatorParams['name']   = 'required|string|max:250|unique:mysql-app.unit_item,name,NULL,id,unit_pembangkit_id,'.$request->unit_pembangkit_id;
        }

        $validator = Validator::make($input, $validatorParams);

        $message = ['Unit Item Sudah Ada.'];
        /** InValid */
        if ($validator->fails())
        {
            return $this->sendError('response.unit_item.failed_update', 422, ['invalid'=>$validator->errors()]);
        }

        $unitItem = $this->unitItemRepository->update($input, $id);

        return $this->sendResponse($unitItem->toArray(), 'response.unit_item.update');
    }

    /**
     * @param CreateVibrasiAPIRequest $request
     * @return Response
     *
     * @SWG\Post(
     *      path="/unit-item/import",
     *      summary="Store a newly created UnitItem in storage",
     *      tags={"UnitItem"},
     *      description="Store UnitItem",
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
     *          name="file",
     *          description="import data",
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
    public function import(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'unit_pembangkit_id' => 'required|integer|exists:unit_pembangkit,id',
            'file' => 'required|mimes:xlsx,csv,xls'
        ]);

        if ($validator->fails())
        {
            return $this->sendError('response.unit_item.failed_create', 422, ['invalid'=>$validator->errors()]);
        }  
        
        Excel::import(new UnitItemImport($request->unit_pembangkit_id), $request->file('file'));
        $unit_item  = "";

        return $this->sendResponse($unit_item, 'response.unit_item.create');
    }

    /**
     * @param int $id
     * @return Response
     *
     * @SWG\Delete(
     *      path="/unit-item/{id}",
     *      summary="Remove the specified UnitItem from storage",
     *      tags={"UnitItem"},
     *      description="Delete UnitItem",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }},  
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of UnitItem",
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
        /** @var UnitItem $unitItem */
        $unitItem = $this->unitItemRepository->find($id);

        if (empty($unitItem)) {
            return $this->sendError('response.unit_item.not_found');
        }

        $unitItem->delete();

        return $this->sendSuccess('response.unit_item.delete');
    }
}
