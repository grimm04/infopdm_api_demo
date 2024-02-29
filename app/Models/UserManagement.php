<?php

namespace App\Models;

use Eloquent as Model;

/**
 * @SWG\Definition(
 *      definition="UserManagement",
 *      required={""}, 
 *       @SWG\Property(
 *          property="role_id",
 *          description="role_id",
 *          type="integer",
 *          format="int32"
 *      ),
 *       @SWG\Property(
 *          property="unit_pembangkit_id",
 *          description="unit_pembangkit_id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="nip",
 *          description="nip",
 *          type="string",
 *      ),
 *      @SWG\Property(
 *          property="status",
 *          description="status",
 *          type="string",
 *      ),
 *      @SWG\Property(
 *          property="name",
 *          description="name",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="username",
 *          description="username",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="email",
 *          description="email",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="phone",
 *          description="phone",
 *          type="string"
 *      ), 
 *      @SWG\Property(
 *          property="gender",
 *          description="gender",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="job",
 *          description="job",
 *          type="string"
 *      ), 
 *      @SWG\Property(
 *          property="avatar",
 *          description="avatar",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="email_verified_at",
 *          description="email_verified_at",
 *           type="string",
 *          format="date-time"
 *      ),
 *      @SWG\Property(
 *          property="password",
 *          description="password",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="terms",
 *          description="terms",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="remember_token",
 *          description="remember_token",
 *          type="string"
 *      ), 
 * )
 */

 /**
 * @SWG\Definition(
 *      definition="UserManagementResetPassword",
 *      required={""},  
 *      @SWG\Property(
 *          property="new_password",
 *          description="new_password",
 *          type="string"
 *      )
 * )
 */
class UserManagement extends Model
{

    public $table = 'users';
    public $primaryKey = 'id'; 

    public $fillable = [
        'unit_pembangkit_id',
        'nip',
        'status',
        'name',
        'username',
        'email',
        'email_verified_at',
        'remember_token',
        'password', 
        'phone',  
        'gender',
        'job',
        'office',
        'avatar',
        'terms'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The attributes for searchable like %% by keyword request
     *
     * @var array
     */
    public $searchable = [
        'name',
        'email',
        'username',
        'phone',
        'nip',
        'status' 
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [    
    ]; 

    public function userRoleId()
    {    
        $app = ENV('APP_ALIAS'); 
        return $this->hasMany(\App\Models\UserRole::class,'user_id','id')->whereHas('roleId', function ($query) use($app){ 
            $query->where('application',$app);   
        }); 
    } 

    public function userRoles()
    {
        return $this->hasMany(\App\Models\UserRole::class,'user_id','id')->select('id','role_id','user_id');
    }

    public function roleId()
    {
        return $this->belongsTo(\App\Models\Role::class, 'role_id', 'id');
    }

    public function unitPembangkitId()
    {
        return $this->belongsTo(\App\Models\UnitPembangkit::class, 'unit_pembangkit_id', 'id');
    }
}
