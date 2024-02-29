<?php

namespace App\Http\Controllers;

use App\Http\Requests\API\CreateEquipmentAPIRequest;
use App\Http\Requests\API\UpdateEquipmentAPIRequest;
use App\Models\Equipment;
use App\Repositories\EquipmentRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Illuminate\Support\Facades\Validator;
use Response;
use App\Imports\EquipmentImport; 
use Maatwebsite\Excel\Facades\Excel;

/**
 * Class EquipmentController
 * @package App\Http\Controllers\API
 */

class EquipmentAPIController extends AppBaseController
{
    /** @var  EquipmentRepository */
    private $equipmentRepository;
    private $with = ['unitPembangkitId:id,name'];

    public function __construct(EquipmentRepository $equipmentRepo)
    {
        $this->equipmentRepository = $equipmentRepo;
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @SWG\Get(
     *      path="/equipment",
     *      summary="Get a listing of the Equipment.",
     *      tags={"Equipment"},
     *      description="Get all Equipment",
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
     *          description="keyword",
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
     *                  @SWG\Items(ref="#/definitions/Equipment")
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
        $equipment = $this->equipmentRepository->paginate("*", $request->except(['skip']),$this->with);
        return $this->sendResponse($equipment->toArray(), 'response.equipment.view');
    }

    /**
     * @param CreateEquipmentAPIRequest $request
     * @return Response
     *
     * @SWG\Post(
     *      path="/equipment",
     *      summary="Store a newly created Equipment in storage",
     *      tags={"Equipment"},
     *      description="Store Equipment",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }},
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="Equipment that should be stored",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/Equipment")
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
     *                  ref="#/definitions/Equipment"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateEquipmentAPIRequest $request)
    {   
 
        $input = $request->all();

        $validatorParams = [
            'unit_pembangkit_id' => 'required|integer|exists:unit_pembangkit,id',   
            'name' => 'required|string|max:200|unique:mysql-app.equipments,name,NULL,id,unit_pembangkit_id,'.$request->unit_pembangkit_id, 
        ];

        $validator = Validator::make($input, $validatorParams);

        $message = ['Equipment Sudah Ada.'];
        /** InValid */
        if ($validator->fails())
        {
            return $this->sendError('Gagal Menambahkan Data.', 422, ['invalid'=>$validator->errors()]);
        }
        $equipment = $this->equipmentRepository->create($input);

        return $this->sendResponse($equipment->toArray(),  'response.equipment.create');
    }

     /**
     * @param CreateVibrasiAPIRequest $request
     * @return Response
     *
     * @SWG\Post(
     *      path="/equipment/import",
     *      summary="Store a newly created Equipment in storage",
     *      tags={"Equipment"},
     *      description="Store Equipment",
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
            return $this->sendError('response.equipment.failed_create', 422, ['invalid'=>$validator->errors()]);
        }  
        
        Excel::import(new EquipmentImport($request->unit_pembangkit_id), $request->file('file'));
        $equipment  = "";

        return $this->sendResponse($equipment, 'response.equipment.create');
    }

    /**
     * @param int $id
     * @return Response
     *
     * @SWG\Get(
     *      path="/equipment/{id}",
     *      summary="Display the specified Equipment",
     *      tags={"Equipment"},
     *      description="Get Equipment",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of Equipment",
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
     *                  ref="#/definitions/Equipment"
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
        /** @var Equipment $equipment */
        $equipment = $this->equipmentRepository->find($id,'*',$this->with);

        if (empty($equipment)) {
            return $this->sendError('response.equipment.not_found');
        }

        return $this->sendResponse($equipment->toArray(), 'response.equipment.view');
    }

    /**
     * @param int $id
     * @param UpdateEquipmentAPIRequest $request
     * @return Response
     *
     * @SWG\Put(
     *      path="/equipment/{id}",
     *      summary="Update the specified Equipment in storage",
     *      tags={"Equipment"},
     *      description="Update Equipment",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of Equipment",
     *          type="integer",
     *          required=true,
     *          in="path"
     *      ),
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="Equipment that should be updated",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/Equipment")
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
     *                  ref="#/definitions/Equipment"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateEquipmentAPIRequest $request)
    {
        $input = $request->all();

        /** @var Equipment $equipment */
        $equipment = $this->equipmentRepository->find($id);

        if (empty($equipment)) {
            return $this->sendError('response.equipment.not_found');
        } 

        $validatorParams = []; 

        if($request->unit_pembangkit_id && $equipment->unit_pembangkit_id != $request->unit_pembangkit_id){
            $validatorParams['unit_pembangkit_id']   = 'required|integer|exists:unit_pembangkit,id';
            $validatorParams['name']   = 'required|string|max:200|unique:mysql-app.equipments,name,NULL,id,unit_pembangkit_id,'.$request->unit_pembangkit_id;
        }
        if($request->name && $equipment->name != $request->name){
            $validatorParams['name']   = 'required|string|max:200|unique:mysql-app.equipments,name,NULL,id,unit_pembangkit_id,'.$request->unit_pembangkit_id;
        }

        $validator = Validator::make($input, $validatorParams);

        $message = ['Equipment Sudah Ada.'];
        /** InValid */
        if ($validator->fails())
        {
            return $this->sendError('Gagal Merubah Data.', 422, ['invalid'=>$validator->errors()]);
        }

        $equipment = $this->equipmentRepository->update($input, $id);

        return $this->sendResponse($equipment->toArray(), 'response.equipment.update');
    }

    /**
     * @param int $id
     * @return Response
     *
     * @SWG\Delete(
     *      path="/equipment/{id}",
     *      summary="Remove the specified Equipment from storage",
     *      tags={"Equipment"},
     *      description="Delete Equipment",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of Equipment",
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
        /** @var Equipment $equipment */
        $equipment = $this->equipmentRepository->find($id);

        if (empty($equipment)) {
            return $this->sendError('response.equipment.not_found');
        }

        $equipment->delete();

        return $this->sendSuccess('response.equipment.delete');
    }
}
