<?php

namespace App\Models\Logs;

use Eloquent as Model;

/**
 * @SWG\Definition(
 *      definition="LogUser",
 *      required={"id_user"},
 *      @SWG\Property(
 *          property="id",
 *          description="id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="id_user",
 *          description="id_user",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="tgl_login",
 *          description="tgl_login",
 *          type="string",
 *          format="date-time"
 *      ),
 *      @SWG\Property(
 *          property="tgl_logout",
 *          description="tgl_logout",
 *          type="string",
 *          format="date-time"
 *      ),
 *      @SWG\Property(
 *          property="host",
 *          description="host",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="created_at",
 *          description="created_at",
 *          type="string",
 *          format="date-time"
 *      ),
 *      @SWG\Property(
 *          property="updated_at",
 *          description="updated_at",
 *          type="string",
 *          format="date-time"
 *      )
 * )
 */
class LogUser extends Model
{
    public $table = 'trans_log_user';
    public $primaryKey = 'id_log';
    public $timestamps = false;  

    public $fillable = [
        'id_user',
        'tgl_login',
        'tgl_logout',
        'host',
        'access_token_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'id_user' => 'integer',
        'host' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'id_user' => 'required'
    ];

    
}
