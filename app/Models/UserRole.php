<?php

namespace App\Models;

use Eloquent as Model;



/**
 * @SWG\Definition(
 *      definition="UserRole",
 *      @SWG\Property(
 *          property="user_id",
 *          description="user_id",
 *          type="string"
 *      ),
 *       @SWG\Property(
 *          property="role_id",
 *          description="role_id", 
 *          type="integer",
 *          format="int32"
 *      )
 * )
 */
class UserRole extends Model
{
    public $table = 'users_role';

    public $fillable = [
        'role_id',
        'user_id',
        'application', 
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'role_id' => 'integer',
        'application' => 'string',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'user_id' => 'required',
        'role_id' => 'required'
    ];


    public function roleId()
    {
        return $this->belongsTo(\App\Models\Role::class, 'role_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\UserManagement::class, 'user_id', 'id');
    }

    public function roleIdApp()
    {
        return $this->belongsTo(\App\Models\Role::class, 'role_id', 'id')->where('application',ENV('APP_ALIAS'));
    }

    public function roleCheck($id){

        $app =ENV('APP_ALIAS'); 
        return $this->where('user_id',$id)->whereHas('roleId', function ($query) use($id, $app){  
                $query->where('application',$app);
                $query->where('status',1); 
        })->with('roleId')->get();
    }
}
