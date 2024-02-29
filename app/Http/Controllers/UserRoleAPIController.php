<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateRoleAPIRequest;
use App\Http\Requests\UpdateRoleAPIRequest;
use App\Models\Role;
use App\Repositories\RoleRepository;  
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Response;
use Illuminate\Support\Facades\Validator; 
use Illuminate\Validation\Rule;


/**
 * Class UserRoleController
 * @package App\Http\Controllers
 */

class UserRoleAPIController extends AppBaseController
{
    /** @var  RoleRepository */
    private $roleRepository;
    private $with = ['unitPembangkitId:id,name'];

    public function __construct(RoleRepository $roleRepo)
    {
        $this->roleRepository = $roleRepo;
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @SWG\Get(
     *      path="/user-role",
     *      summary="Get a listing of the UserRole.",
     *      tags={"UserRole"},
     *      description="Get all UserRole",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }},
     *      @SWG\Parameter(
     *          name="unit_pembangkit_id",
     *          description="unit_pembangkit_id (exist or empty)",
     *          type="string",
     *          required=false,
     *          in="query"
     *      ),
     *      @SWG\Parameter(
     *          name="status",
     *          description="Filter by '0','1'",
     *          type="string",
     *          required=false,
     *          in="query"
     *      ),
     *      @SWG\Parameter(
     *          name="keyword",
     *          description="keyword search data by 'name','status'",
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
     *                  @SWG\Items(ref="#/definitions/Role")
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
        $req = $request->except(['unit_pembangkit_id']);
        $req['application'] = env('APP_ALIAS');

        $whereRelation = array();

        if($request['unit_pembangkit_id']){
            $whereUnitPembangkitId = array(
                (object) [
                    "relation" => 'unitPembangkitId', 
                    "field" => 'id',
                    "operator" => '=',
                    "value" => $request['unit_pembangkit_id'],
                ]
            ); 
            $whereRelation = array_merge($whereRelation, $whereUnitPembangkitId);
            $req['id_unit_pembangkit'] = $request['unit_pembangkit_id'];
        }  
         
        $userRole = $this->roleRepository->paginate( "*", $req, $this->with, [], (count($whereRelation)==0 ? null : $whereRelation) );

        
        return $this->sendResponse($userRole->toArray(), 'response.role.view');
    }

    /**
     * @param CreateRoleAPIRequest $request
     * @return Response
     *
     * @SWG\Post(
     *      path="/user-role",
     *      summary="Store a newly created UserRole in storage",
     *      tags={"UserRole"},
     *      description="Store UserRole",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }}, 
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="UserRole that should be stored",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/Role")
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
     *                  ref="#/definitions/Role"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateRoleAPIRequest $request)
    {
        $input = $request->all();

        $validatorParams = [ 
            'unit_pembangkit_id' => 'nullable|integer|exists:unit_pembangkit,id',    
            // 'name' => 'required|string|max:50|unique:roles,name,NULL,id,id_unit_pembangkit,'.$request->unit_pembangkit_id, 
            'name' => ['required', 'string', 'max:50',Rule::unique('roles')->where(function ($query) use ($input) {
                    $app =  isset($input['application']) ? $input['application']:'infopdm';
                    $query->where('id_unit_pembangkit', $input['unit_pembangkit_id'])->where('application', $app);
            })], 
        ];

        $validator = Validator::make($input, $validatorParams);

        /** InValid */
        if ($validator->fails())
        {
            return $this->sendError('response.role.failed_create', 422, ['invalid'=>$validator->errors()]);
        }
        $input['application'] = env('APP_ALIAS');
        if($request->unit_pembangkit_id){ 
            $input['id_unit_pembangkit'] = $request->unit_pembangkit_id;
        }

        $userRole = $this->roleRepository->create($input);

        return $this->sendResponse($userRole->toArray(), 'response.role.create');
    }

    /**
     * @param int $id
     * @return Response
     *
     * @SWG\Get(
     *      path="/user-role/{id}",
     *      summary="Display the specified UserRole",
     *      tags={"UserRole"},
     *      description="Get UserRole",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }}, 
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of UserRole",
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
     *                  ref="#/definitions/Role"
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
        /** @var UserRole $userRole */
        $userRole = $this->roleRepository->find($id,"*",$this->with);

        if (empty($userRole)) {
            return $this->sendError('response.role.not_found');
        } 

        return $this->sendResponse($userRole->toArray(), 'response.role.view');
    }

    /**
     * @param int $id
     * @param UpdateRoleAPIRequest $request
     * @return Response
     *
     * @SWG\Put(
     *      path="/user-role/{id}",
     *      summary="Update the specified UserRole in storage",
     *      tags={"UserRole"},
     *      description="Update UserRole",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }}, 
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of UserRole",
     *          type="integer",
     *          required=true,
     *          in="path"
     *      ),
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="UserRole that should be updated",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/Role")
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
     *                  ref="#/definitions/UserRole"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateRoleAPIRequest $request)
    {
        $input = $request->except(['unit_pembangkit_id']);
        $input['application'] = env('APP_ALIAS');
        /** @var UserRole $userRole */
        $userRole = $this->roleRepository->find($id);

        if (empty($userRole)) {
            return $this->sendError('response.role.not_found');
        }
        // return $input;
        $validatorParams = [];  

        if($userRole->id_unit_pembangkit != $request->unit_pembangkit_id){
            if($request->unit_pembangkit_id == null){ 
                $input['id_unit_pembangkit'] = Null;
            }  else {
                $validatorParams['unit_pembangkit_id']   = 'nullable|integer|exists:unit_pembangkit,id'; 
            }  
        } 
        if($userRole->name != $request->name){ 
            $validatorParams = [
                'name' => ['required', 'string', 'max:50',Rule::unique('roles')->where(function ($query) use ($input) {
                                $query->where('id_unit_pembangkit', $input['unit_pembangkit_id'])->where('application', $input['application']);
                        })], 
            ]; 
        } 

        $validator = Validator::make($input, $validatorParams);

        /** InValid */
        if ($validator->fails())
        { 
            return $this->sendError('response.role.failed_update', 422, ['invalid'=>$validator->errors()]);
        }
        
        if($request->unit_pembangkit_id){ 
            $input['id_unit_pembangkit'] = $request->unit_pembangkit_id;
        } 

        $userRole = $this->roleRepository->update($input, $id);

        return $this->sendResponse($userRole->toArray(), 'response.role.update');
    }

    /**
     * @param int $id
     * @return Response
     *
     * @SWG\Delete(
     *      path="/user-role/{id}",
     *      summary="Remove the specified UserRole from storage",
     *      tags={"UserRole"},
     *      description="Delete UserRole",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }}, 
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of UserRole",
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
        /** @var UserRole $userRole */
        $userRole = $this->roleRepository->find($id);

        if (empty($userRole)) {
            return $this->sendError('response.role.not_found');
        }

        $userRole->delete();

        return $this->sendSuccess('response.role.delete');
    }
}
