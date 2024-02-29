<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Logs\LogUser;
use Jenssegers\Agent\Agent;

/**
 * Class UserRepository
 * @package App\Repositories
 * @version November 2, 2020, 3:59 pm UTC
*/

class UserRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'user_role_id','unit_pembangkit_id','name', 'email', 'password', 'username', 'nip',
        'status','gender', 'role', 'job', 'office', 'phone',
        'terms',
    ];

    /**
     * Return searchable fields
     *
     * @return array
     */
    public function getFieldsSearchable()
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return User::class;
    }

    public function lastLogin($id_user, $platform_app=null){
        $check = LogUser::select('id_log','tgl_login')->where('id_user', $id_user)->where('app', $platform_app)->whereNull('tgl_logout')->first();
        return $check;
    }

    public function userLogChecking($id_user, $platform_app=null, $isLogout=false){
        $agent      = new Agent();
        $lastLoginInfo  = $this->lastLogin($id_user, $platform_app); 
        $dateNow = \Carbon\Carbon::now();

        /** If exist or not */
        if($lastLoginInfo){
            $ll = LogUser::find($lastLoginInfo->id_log);
            $ll->tgl_logout = $dateNow;
            $ll->save();
        }else if(!$isLogout){
            $ll = new LogUser;
            $ll->app = $platform_app;
            $ll->id_user = $id_user;
            $ll->host = request()->ip();
            $ll->tgl_login = $dateNow;
            $ll->agent = $_SERVER['HTTP_USER_AGENT'];
            $ll->platform = $agent->platform();
            $ll->device = $agent->device();
            $ll->browser = $agent->browser();
            $ll->version = ($agent->isMobile() || $platform_app=='mobile') ?  $agent->version($ll->platform) : $agent->version($ll->browser);
    
            $ll->save();
        }
    }


    public function removeAvatar($user){
      \File::delete(public_path($user->avatar));
      $user = User::find($user->id_user);
      $user->avatar = NULL;
      $user->save();

      return $user;
    }

    public function changePassword($input){
      $userid = Auth::user()->id_user;
      $rules = array(
          'old_password' => 'required',
          'new_password' => 'required|min:6',
          'password_confirmation' => 'required|same:new_password',
      );
      $validator = Validator::make($input, $rules);
      if ($validator->fails()) {
          $result = array("status" => 400, "message" => $validator->errors()->first(), "data" => array());
      } else {
          try {
              if ((Hash::check(request('old_password'), Auth::user()->password)) == false) {
                  $result = array("status" => 400, "message" => "Check your old password.", "data" => array());
              } else if ((Hash::check(request('new_password'), Auth::user()->password)) == true) {
                  $result = array("status" => 400, "message" => "Please enter a password which is not similar then current password.", "data" => array());
              } else {
                  User::where('id_user', $userid)->update(['password' => Hash::make($input['new_password'])]);
                  $result = array("status" => 200, "message" => "Password updated successfully.", "data" => array());
              }
          } catch (\Exception $ex) {
              if (isset($ex->errorInfo[2])) {
                  $msg = $ex->errorInfo[2];
              } else {
                  $msg = $ex->getMessage();
              }
              $result = array("status" => 400, "message" => $msg, "data" => array());
          }
      }

      return $result;
    }
}
