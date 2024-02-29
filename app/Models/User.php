<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @SWG\Definition(
 *      definition="RefUserLogin",
 *      required={""}, 
 *      @SWG\Property(
 *          property="user",
 *          description="user",
 *          type="string",
 *          example="username/your@email.com/phone number"
 *      ),
 *      @SWG\Property(
 *          property="password",
 *          description="password",
 *          type="string",
 *          example="SECURE_PASS"
 *      )
 * )
 */ 
 /**
 * @SWG\Definition(
 *      definition="RefUser",
 *      required={""},
 *       @SWG\Property(
 *          property="unit_pembangkit_id",
 *          description="unit_pembangkit_id",
 *          type="integer",
 *          format="int32"
 *      ),
 *       @SWG\Property(
 *          property="name",
 *          description="name",
 *          type="string",
 *          example="Maalulana malik"
 *      ),
 *      @SWG\Property(
 *          property="nip",
 *          description="nip",
 *          type="string",
 *          example="0000000001"
 *      ), 
 *      @SWG\Property(
 *          property="username",
 *          description="username",
 *          type="string",
 *          example=null
 *      ),
 *      @SWG\Property(
 *          property="email",
 *          description="email",
 *          type="string",
 *          example="your@email.com"
 *      ), 
 *      @SWG\Property(
 *          property="phone",
 *          description="phone",
 *          type="string",
 *          example=null
 *      ),
 *      @SWG\Property(
 *          property="gender",
 *          description="gender",
 *          type="string",
 *          example="Laki-laki/Perempuan"
 *      ), 
 *      @SWG\Property(
 *          property="job",
 *          description="job",
 *          type="string",
 *          example="Staf PDM"
 *      ),
 *       @SWG\Property(
 *          property="avatar",
 *          description="avatar",
 *          type="string",
 *          example=null
 *      )
 * )
 */

/**
 * @SWG\Definition(
 *      definition="RefUserWithoutPassword",
 *      required={""}, 
 *      @SWG\Property(
 *          property="name",
 *          description="name",
 *          type="string",
 *          example="Maalulana malik"
 *      ),
 *      @SWG\Property(
 *          property="nip",
 *          description="nip",
 *          type="string",
 *          example="0000000001"
 *      ), 
 *      @SWG\Property(
 *          property="username",
 *          description="username",
 *          type="string",
 *          example=null
 *      ),
 *      @SWG\Property(
 *          property="email",
 *          description="email",
 *          type="string",
 *          example="your@email.com"
 *      ), 
 *      @SWG\Property(
 *          property="phone",
 *          description="phone",
 *          type="string",
 *          example=null
 *      ),
 *      @SWG\Property(
 *          property="gender",
 *          description="gender",
 *          type="string",
 *          example="Laki-laki/Perempuan"
 *      ), 
 *      @SWG\Property(
 *          property="job",
 *          description="job",
 *          type="string",
 *          example="Staf PDM"
 *      ),
 *      @SWG\Property(
 *          property="avatar",
 *          description="avatar",
 *          type="string",
 *          example=null
 *      )
 * )
 */ 
 
/**
 * @SWG\Definition(
 *      definition="RefUserRegister",
 *      required={""},  
 *       @SWG\Property(
 *          property="unit_pembangkit_id",
 *          description="unit_pembangkit_id",
 *          type="integer",
 *          format="int32"
 *      ),
 *       @SWG\Property(
 *          property="name",
 *          description="name",
 *          type="string",
 *          example="Maalulana malik"
 *      ), 
 *      @SWG\Property(
 *          property="nip",
 *          description="nip",
 *          type="string",
 *          example="0000000001"
 *      ), 
 *      @SWG\Property(
 *          property="username",
 *          description="username",
 *          type="string",
 *          example=null
 *      ),
 *      @SWG\Property(
 *          property="email",
 *          description="email",
 *          type="string",
 *          example="your@email.com"
 *      ), 
 *      @SWG\Property(
 *          property="phone",
 *          description="phone",
 *          type="string",
 *          example=null
 *      ),
 *      @SWG\Property(
 *          property="gender",
 *          description="gender",
 *          type="string",
 *          example="Laki-laki/Perempuan"
 *      ), 
 *      @SWG\Property(
 *          property="job",
 *          description="job",
 *          type="string",
 *          example="Staf PDM"
 *      ),
 *      @SWG\Property(
 *          property="password",
 *          description="password",
 *          type="string", 
 *      )
 * )
 */
 

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public $table = 'users';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [ 
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
        'avatar',
        'terms'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
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
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function unitId()
    {
        return $this->belongsTo(\App\Models\Unit::class, 'unit_pembangkit_id', 'id');
    }
    
    public function userRoleId()
    {
        return $this->belongsTo(\App\Models\UserRole::class, 'id', 'user_id')->select('id','role_id','user_id');
    }

    public function userRoles()
    {
        return $this->hasMany(\App\Models\UserRole::class,'user_id','id')->select('id','role_id','user_id');
    }
}
