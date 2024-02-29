<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserManagementAPIRequest;
use App\Http\Requests\UpdateUserManagementAPIRequest;
use App\Models\UserManagement;
use App\Models\User;
use App\Repositories\UserManagementRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Helper;
use Illuminate\Support\Facades\Hash;
use App\Models\Logs\LogUserStatus;
use App\Models\Logs\LogUserMutasi;
use App\Models\UserRole; 
use Response;

/**
 * Class UserManagementController
 * @package App\Http\Controllers
 */

class UserManagementAPIController extends AppBaseController
{
    /** @var  UserManagementRepository */
    private $userManagementRepository;     
    private $with = ['unitPembangkitId:id,name','userRoleId.roleIdApp:id,id_unit_pembangkit,name,level,application','userRoles:id,user_id,role_id','userRoles.roleId:id,id_unit_pembangkit,name,level,application'];
    private $application;

    public function __construct(UserManagementRepository $userManagementRepo)
    {
        $this->userManagementRepository = $userManagementRepo;
        $this->application = ENV('APP_ALIAS');  

    }

    /**
     * @param Request $request
     * @return Response
     *
     * @SWG\Get(
     *      path="/users",
     *      summary="Get a listing of the UserManagement.",
     *      tags={"UserManagement"},
     *      security={ {"Bearer": {} }}, 
     *      description="Get all UserManagement", 
     *      @SWG\Parameter(
     *          name="role_id",
     *          description="role_id",
     *          type="integer",
     *          required=false,
     *          in="query"
     *      ),
     *      @SWG\Parameter(
     *          name="unit_pembangkit_id",
     *          description="unit_pembangkit_id (exist or empty)",
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
     *          name="keyword",
     *          description="keyword search data by 'nama','nip','username','email','job','phone','status'",
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
     *                  @SWG\Items(ref="#/definitions/UserManagement")
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
        
        $whereRelation = array();

        if($request['unit_pembangkit_id']){
            $whereIdUnitPembangkit = array(
                (object) [
                    "relation" => 'unitPembangkitId',
                    "field" => 'id',
                    "operator" => '=',
                    "value" => $request['unit_pembangkit_id'],
                ]
            ); 
            $whereRelation = array_merge($whereRelation, $whereIdUnitPembangkit);
        } 

        if($request['role_id']){
            $whereIdUnitPembangkit = array(
                (object) [
                    "relation" => 'userRoles',
                    "field" => 'role_id',
                    "operator" => '=',
                    "value" => $request['role_id'],
                ]
            );

            $whereRelation = array_merge($whereRelation, $whereIdUnitPembangkit);
        } 
         
        $userManagement = $this->userManagementRepository->paginate( "*", $request->except(['skip']), $this->with, [], (count($whereRelation)==0 ? null : $whereRelation) );
 
        return $this->sendResponse($userManagement->toArray(), 'response.usermanagement.view');
    }

    /**
     * @param CreateUserManagementAPIRequest $request
     * @return Response
     *
     * @SWG\Post(
     *      path="/users",
     *      summary="Store a newly created UserManagement in storage",
     *      tags={"UserManagement"},
     *      security={ {"Bearer": {} }}, 
     *      description="Store UserManagement",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="UserManagement that should be stored",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/UserManagement")
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
     *                  ref="#/definitions/UserManagement"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateUserManagementAPIRequest $request)
    {
        $input = $request->except(['role_id']);
        
        $validatorParams = [
            'username' => 'required|string|min:3|max:50|unique:users', 
            'password'     => 'required|min:8',   
            'email' => 'required|string|email|max:100|unique:users',
            'role_id' => 'integer|exists:roles,id', 
            'unit_pembangkit_id' => 'nullable|integer|exists:unit_pembangkit,id', 
        ];

       
        $validator = Validator::make($input, $validatorParams);

        /** InValid */
        if ($validator->fails())
        {
            return $this->sendError('response.usermanagement.failed_create', 422, ['invalid'=>$validator->errors()]);
        }
 
        $input['password'] = Hash::make($request['password']);   
        $input['terms'] ="Saya Setuju"; 
        
        $userManagement = $this->userManagementRepository->create($input);  

        if($request->role_id){ 
            $roles = new UserRole;
            $roles->user_id = $userManagement->id;
            $roles->role_id = $request->role_id;
            $roles->application = $this->application;
            $roles->save(); 
        } 

        return $this->sendResponse($userManagement->toArray(), 'response.usermanagement.create');
    }

    /**
     * @param int $id
     * @return Response
     *
     * @SWG\Get(
     *      path="/users/{id}",
     *      summary="Display the specified UserManagement",
     *      tags={"UserManagement"},
     *      security={ {"Bearer": {} }}, 
     *      description="Get UserManagement",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of UserManagement",
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
     *                  ref="#/definitions/UserManagement"
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
        /** @var UserManagement $userManagement */
        $userManagement = $this->userManagementRepository->find($id, '*', $this->with);

        if (empty($userManagement)) {
            return $this->sendError('response.usermanagement.not_found');
        }

        return $this->sendResponse($userManagement->toArray(), 'response.usermanagement.view');
    }

    /**
     * @param int $id
     * @param UpdateUserManagementAPIRequest $request
     * @return Response
     *
     * @SWG\Put(
     *      path="/users/{id}",
     *      summary="Update the specified UserManagement in storage",
     *      tags={"UserManagement"},
     * 
     *      description="Update UserManagement",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of UserManagement",
     *          type="integer",
     *          required=true,
     *          in="path"
     *      ),
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="UserManagement that should be updated",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/UserManagement")
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
     *                  ref="#/definitions/UserManagement"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */ 
    public function update($id, UpdateUserManagementAPIRequest $request)
    {
        $input = $request->except(['role_id']);
        
        /** @var UserManagement $userManagement */
        $userManagement = $this->userManagementRepository->find($id);
        // print_r($userManagement);exit();

        if (empty($userManagement)) {
            return $this->sendError('response.usermanagement.not_found');
        }

        $validatorParams = [];
        // return $input; 

        if($userManagement->unit_pembangkit_id != $request->unit_pembangkit_id){
            if($request->unit_pembangkit_id == null){ 
                $input['unit_pembangkit_id'] = Null;
            }  else {
                $validatorParams['unit_pembangkit_id']   = 'integer|exists:unit_pembangkit,id'; 
            } 
        }  

        if($request->nip && $userManagement->nip != $request->nip){
            $validatorParams['nip'] = 'string|max:18|unique:users';
        }

        if($request->username && $userManagement->username != $request->username){
            $validatorParams['username'] = 'string|max:50|unique:users';
        }
        
        if($request->phone && $userManagement->phone != $request->phone){
            $validatorParams['phone'] = 'string|max:15|unique:users';
        }

        if($request->email && $userManagement->email != $request->email){
            $validatorParams['email'] = 'string|email|max:100|unique:users';
        }

        $validator = Validator::make($input, $validatorParams);
 
        /** InValid */
        if ($validator->fails())
        { 
            return $this->sendError('response.usermanagement.failed_update', 422, ['invalid'=>$validator->errors()]);
        }
        // return $userManagement->userRoleId->id;
        /** Store data */
        $updateUserManagement = $this->userManagementRepository->update($input, $id);

        $usRoles = UserRole::where('user_id',$userManagement->id)->where('application',$this->application)->first(); 

        if(!empty($usRoles)){   
            if($request->role_id != null){ 
                $usRoles->role_id = $request->role_id;
                $usRoles->save(); 
            }else {
                $delUser = UserRole::where('user_id',$userManagement->id)->where('application',$this->application)->first(); 
                if($delUser){ 
                    $delUser->delete();
                }
            } 
        }else {
            if($request->role_id != null){ 
                $roles = new UserRole;
                $roles->user_id = $userManagement->id;
                $roles->role_id = $request->role_id;
                $roles->application = $this->application;
                $roles->save();
            }
        } 
        


        /** Jika status user di ubah, insert ke log */ 
        LogUserStatus::insertLogUserStatus($updateUserManagement->id, $userManagement->status, $updateUserManagement->status);  

        return $this->sendResponse($updateUserManagement->toArray(), 'response.usermanagement.update');
    }
 

     /**
     * @param Request $request
     * @return Response
     *
     * @SWG\Post(
     *      path="/users/change-status",
     *      summary="Change Status User",
     *      tags={"UserManagement"},
     * 
     *      description="Change Status User",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of User",
     *          type="integer",
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
     *                  property="data",
     *                  type="object"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function changeStatus(Request $request)
    {   

        $user = Auth::user();   
        /** @var UserManagement $userManagement */
        $input = $request->all();

        $validatorParams = [
            'id' => 'required',  
        ];

        $validator = Validator::make($input, $validatorParams);

        /** InValid */
        if ($validator->fails())
        { 
            return $this->sendError('response.usermanagement.failed_update', 422, ['invalid'=>$validator->errors()]);
        }
        $id = $request->id;

        $userManagement = $this->userManagementRepository->find($id);

        if (empty($userManagement)) {
            return $this->sendError('response.usermanagement.not_found');
        }
 

        if($user->id === $id){ 
            return $this->sendError('Anda tidak dapat mengubah status sendiri!', 422);
        }
        if($user->name === "admin"){
            return $this->sendError('Anda tidak dapat mengubah status admin utama!', 422); 
        }; 
 
        /** Store data */
        if($userManagement->status === 'active') {
            $input['status'] = "inactive";
            $updateUserManagement = $this->userManagementRepository->update($input,$id);
        }
        else {

            $input['status'] = "active";
            $updateUserManagement = $this->userManagementRepository->update($input,$id);
        } 

        // print($updateUserManagement);exit();
        /** Jika status user di ubah, insert ke log */ 
        LogUserStatus::insertLogUserStatus($updateUserManagement->id, $userManagement->status, $updateUserManagement->status);  

        return $this->sendResponse($updateUserManagement->toArray(), 'Status '.$userManagement->username.' telah diubah menjadi '.$updateUserManagement->status);
    }

    /**
     * @param int $id
     * @return Response
     *
     * @SWG\Delete(
     *      path="/users/{id}",
     *      summary="Remove the specified UserManagement from storage",
     *      tags={"UserManagement"},
     * 
     *      description="Delete UserManagement",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of UserManagement",
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
        /** @var UserManagement $userManagement */
        $userManagement = $this->userManagementRepository->find($id);

        if (empty($userManagement)) {
            return $this->sendError('response.usermanagement.not_found');
        }

        $deleteing = $this->userManagementRepository->delete($id);
        
        if($deleteing['status']==false && $deleteing['message']=='used'){
            return $this->sendError('response.usermanagement.failed_delete');
        }else{
            return $this->sendSuccess('response.usermanagement.delete');
        }
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @SWG\Put(
     *      path="/users/reset-password",
     *      summary="Reset password",
     *      tags={"UserManagement"},
     *      description="Reset password",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of user",
     *          type="string",
     *          required=true,
     *          in="query"
     *      ), 
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="UserManagement that should be updated",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/UserManagementResetPassword")
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
     *                  type="object"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function resetPassword(Request $request){
        
        $input = $request->all();

        $validator = Validator::make($input, [ 
            'id' => 'required',
            'new_password' => 'required|min:8',
        ]);

        /** InValid */
        if ($validator->fails())
        {
            return $this->sendError('Gagal Reset password', 422, ['invalid'=>$validator->errors()]); 
        }

        $user = User::find( $request->id); 

        if (empty($user)) {
            return $this->sendError('User tidak valid');
        } 
        $user->fill([
            'password' => Hash::make($request->new_password)
        ])->save();
        $user->setRememberToken(Str::random(60));
 
        return $this->sendResponse(null, 'Berhasil reset password');
        
    }
}
