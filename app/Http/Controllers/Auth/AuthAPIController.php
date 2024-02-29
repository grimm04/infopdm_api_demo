<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\AppBaseController;
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;

use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Mail\Message;

use Illuminate\Support\Facades\Request as RequestFacade;
use Laravel\Passport\Client;
use Laravel\Passport\Token;
use Lcobucci\JWT\Parser;

use App\Http\Requests\CreateUserManagementAPIRequest;
use App\Http\Requests\UpdateUserManagementAPIRequest;
use App\Models\User; 
use App\Models\UserManagement;
use App\Models\UserRole;
use App\Repositories\UserManagementRepository;
use App\Repositories\UserRepository;
use Jenssegers\Agent\Agent;

use App\AuthHelper;
use App\Rules\Platform;

/**
 * Class AUTH
 * @package App\Http\Controllers\Auth
 */
class AuthAPIController extends AppBaseController
{
    
    /** @var  UserManagementRepository */
    private $userRepository;
    private $userRole; 
    private $userManagementRepository;   
    private $with = ['unitPembangkitId:id,name','userRoleId.roleIdApp:*'];


    public function __construct(UserManagementRepository $userManagementRepo, UserRepository $userRepo,UserRole $userRoleMod)
    {
        $this->userManagementRepository = $userManagementRepo;
        $this->userRepository = $userRepo;
        $this->userRole = $userRoleMod;

    }

    /**
     * @param Request $request
     * @return Response 
     *
     * @SWG\Post(
     *      path="/auth/login",
     *      summary="Login authentication.",
     *      tags={"Authentication"},
     *      description="Post login get token authentication",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="login params authentication",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/RefUserLogin")
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
    public function login(Request $request) {
        $request = AuthHelper::getFieldLoginBy(); 

        $validator = Validator::make($request->all(), [
            'email' => 'nullable|string|email|max:100',
            'username' => 'nullable|string|max:50', 
            'password' => 'required|string', 
        ]);

        /** Fails */
        if ($validator->fails())
        {
            return $this->sendError('Gagal Login.', 422, ['invalid'=>$validator->errors()]);
        }

        /** Success */
        $user = User::where($request->login_by, $request->user)->first();

        if ($user) { 

            if(!(Hash::check($request->password, $user->password))){
                $response = "Password Tidak Sama";
                return $this->sendError($response, 422);
            } 
            else if($user->status=="inactive"){
                return $this->sendError("Maaf akun anda tidak aktif", 422);
            }
            else{
                $checkrole = $this->userRole->roleCheck($user->id);
                
                if($checkrole->isEmpty()){
                    return $this->sendError("Maaf, akun anda tidak mempunyai akses.", 422); 
                }
                $this->userRepository->userLogChecking($user->id);

                /** Login */
                $params = [
                    'grant_type' => 'password',
                    'client_id' => env('API_CLIENT_ID'),
                    'client_secret' => env('API_CLIENT_SECRET'),
                    'username' => $user->email, 
                    'password' => $request->password,
                    'scope' => '*'
                ];

                // print_r($params);
                $request = Request::create(route('passport.token'), 'POST', $params);
                $content = json_decode(app()->handle($request)->getContent());

                if(isset($content->access_token)){
                    return $this->sendResponse($content, 'Berhasil login');
                }else{
                    return $this->sendError(isset($content->message) ? $content->message : 'Failed sign in', 422);
                }
            }
        } else {
            $response = "User/ email tidak terdaftar";
            return $this->sendError($response, 422);
        }

        return $this->sendError('Gagal sign in', 422, $user);
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @SWG\Post(
     *      path="/auth/refresh-token",
     *      summary="Refresh token user",
     *      tags={"Authentication"},
     *      description="Post Refresh token user",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="refresh token params authentication",
     *          required=false,
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(
     *                  property="refresh_token",
     *                  description="refresh_token",
     *                  type="string"
     *              )
     *          )
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
    public function refreshToken(Request $request)
    {
        // $client = DB::table('oauth_clients')->where('password_client', true)->first();

        $data = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $request->refresh_token,
            'client_id' => env('API_CLIENT_ID'), // $client->id
            'client_secret' => env('API_CLIENT_SECRET'), // $client->secret
            'scope' => ''
        ];

        $request = Request::create(route('passport.token'), 'POST', $data);
        $content = json_decode(app()->handle($request)->getContent());

        if(isset($content->message) && $content->message=='unauthenticated'){
            return $this->sendError($content->message, 401);
        }else if(isset($content->access_token)){
            return $this->sendResponse($content, 'Berhasil Membuat refresh token');
        }
        return $this->sendError(isset($content->message) ? $content->message : 'Gagal membuat refresh token', 400);
    }


     /**
     * @param Request $request
     * @return Response
     *
     * @SWG\Post(
     *      path="/auth/register",
     *      summary="Register new user",
     *      tags={"Authentication"},
     *      description="Post register new user",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="register params authentication",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/RefUserRegister")
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
    public function register (Request $request) {
        $input = $request->all(); 

        $validator = Validator::make($request->all(), [
            'nip'     => 'required|string|max:255|unique:users',
            'name'     => 'required|string|max:255', 
            'password'     => 'required|min:8', 
            'email'    => 'required|string|email|max:100|unique:users',
            'username' => 'nullable|string|max:50|unique:users',
            'phone'    => 'nullable|string|max:15|unique:users' 
        ]);

        if ($validator->fails())
        {
            return $this->sendError("Registrasi akun gagal.", 422, ['invalid'=>$validator->errors()]);
        } 
        $input['remember_token'] = Str::random(10);
        $input['password'] = Hash::make($request['password']); 
        $input['terms']="Saya Setuju";
        $input['status']="inactive";
        $input['role']="User"; 
        
        $user = new User($input);
        $user->save(); 
        
        /** Login */
        // $params = [
        //     'grant_type' => 'password',
        //     'client_id' => env('API_CLIENT_ID'),
        //     'client_secret' => env('API_CLIENT_SECRET'),
        //     'username' => $user->email, 
        //     'password' => $user->password,
        //     'scope' => '*'
        // ];
        
        // print_r($params);exit();
        // $request = Request::create(route('passport.token'), 'POST', $params);
        // $content = json_decode(app()->handle($request)->getContent());

        // if(isset($content->access_token)){
            /** Kirim verifikasi email */
            // $user->sendEmailVerificationNotification();
 
        return $this->sendResponse($user->toArray(), 'Berhasil Mendaftar.');
        // }else{
        //     return $this->sendError($content, 422);
        // }
    } 
 
    /**
     * @param Request $request
     * @return Response
     *
     * @SWG\Post(
     *      path="/auth/logout",
     *      summary="Logout",
     *      tags={"Authentication"},
     *      description="User logout",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }},
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="login params authentication",
     *          required=false,
     *          @SWG\Schema(
     *             type="object", 
     *             @SWG\Property(
     *                  property="platform",
     *                  description="platform ex: web/mobile",
     *                  type="string",
     *                  example="web/mobile"
     *            )
     *          )
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
    public function logout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'platform' => ['required', new Platform()]
        ]);

        /** Fails */
        if ($validator->fails())
        { 
            return $this->sendError('Gagal logout.', 422, ['invalid'=>$validator->errors()]);
        }

        $user = Auth::user(); 
        $this->userRepository->userLogChecking($user->id_user, $request->platform, true); 

        $tokenId = AuthHelper::findByRequest($request, 'token');
 

        $tokenRepository = app('Laravel\Passport\TokenRepository');
        $refreshTokenRepository = app('Laravel\Passport\RefreshTokenRepository');
        // Revoke an access token...
        $tokenRepository->revokeAccessToken($tokenId);
        // Revoke all of the token's refresh tokens...
        $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($tokenId);

        if($tokenRepository && $refreshTokenRepository){ 
            return $this->sendResponse(null, 'successfully_logged_out');
        }
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @SWG\Post(
     *      path="/auth/details",
     *      summary="Detail credentials user logged in",
     *      tags={"Authentication"},
     *      description="Detail credentials user logged in",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }},
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
    public function details(Request $request)
    
    { 
        $user = Auth::user(); 
        /** @var UserManagement $userManagement */
        $userManagement = $this->userManagementRepository->find($user->id, '*',$this->with); 

        $token = AuthHelper::findByRequest($request);

        if (empty($userManagement)) {
            return $this->sendError('Detail akun tidak adaa');
        }

        $userManagement = $userManagement->toArray(); 

        return $this->sendResponse($userManagement, 'Berhasi mendapatkan detail akun');
    }
 

    /**
     * @param Request $request
     * @return Response
     *
     * @SWG\Put(
     *      path="/auth/change-password",
     *      summary="Reset password from forgot password from link",
     *      tags={"Authentication"},
     *      description="Reset password from forgot password from link",
     *      produces={"application/json"},
     *      security={ {"Bearer": {} }},
     *      @SWG\Parameter(
     *          name="old_password",
     *          description="old_password",
     *          type="string",
     *          required=true,
     *          in="query"
     *      ),
     *      @SWG\Parameter(
     *          name="password",
     *          description="password",
     *          type="string",
     *          required=true,
     *          in="query"
     *      ),
     *      @SWG\Parameter(
     *          name="password_confirmation",
     *          description="password_confirmation",
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
    public function changePassword(Request $request){
        
        $input = $request->all();

        $validator = Validator::make($input, [ 
            'old_password' => 'required', 
            'password' => 'required|min:8|confirmed',
        ]);

        /** InValid */
        if ($validator->fails())
        {
            return $this->sendError('Gagal Mereset password', 422, ['invalid'=>$validator->errors()]); 
        }

        $user = Auth::user(); 

        if (!Hash::check($request->old_password, $user->password)) { 
            return $this->sendError('Password tidak sama', 422); 
 
        }  
        
        
        $user->fill([
            'password' => Hash::make($request->password)
        ])->save();
        $user->setRememberToken(Str::random(60));
 
        return $this->sendResponse(null, 'Berhasil reset password');
        
    }


 

}
